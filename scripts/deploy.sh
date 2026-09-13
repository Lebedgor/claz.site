#!/bin/sh
# Deploy to production: git push -> VPS pull+rebuild -> FULL local DB + storage transfer -> verify.
# Local database and storage are the source of truth: by default every deploy
# replaces the production DB with the local one and merges local storage files.
# Usage:
#   ./scripts/deploy.sh                     # ship code + full DB + storage
#   ./scripts/deploy.sh --skip-db           # ship code only, keep the prod DB
#   ./scripts/deploy.sh --skip-db SeederA,SeederB   # ship code only + run seeders
set -e

HOST=${DEPLOY_HOST:-pv-vps}
DIR=${DEPLOY_DIR:-/root/claz.site}
BRANCH=${DEPLOY_BRANCH:-main}
LOCAL_DB_USER=${LOCAL_DB_USER:-yehor}
LOCAL_DB_NAME=${LOCAL_DB_NAME:-claz}
PROD_DB_USER=${PROD_DB_USER:-claz}

SKIP_DB=0
SEEDERS=""
for arg in "$@"; do
    case "$arg" in
        --skip-db) SKIP_DB=1 ;;
        *) SEEDERS="$SEEDERS $arg" ;;
    esac
done

cd "$(dirname "$0")/.."

git push origin "$BRANCH"

echo "==> VPS: git pull + rebuild"
ssh "$HOST" "cd $DIR && git pull && docker compose up -d --build"

if [ "$SKIP_DB" = "1" ]; then
    echo "==> Skipping DB transfer (--skip-db)"
else
    echo "==> Transferring full local DB to production"
    DUMP="/tmp/claz-deploy-$(date +%Y%m%d%H%M).dump"
    pg_dump -Fc -U "$LOCAL_DB_USER" -h 127.0.0.1 -d "$LOCAL_DB_NAME" -f "$DUMP"
    scp -q "$DUMP" "$HOST:/tmp/claz-deploy.dump"
    ssh "$HOST" "cd $DIR \
        && docker compose cp /tmp/claz-deploy.dump postgres:/tmp/ \
        && docker compose exec postgres psql -U $PROD_DB_USER -d claz -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;' \
        && docker compose exec postgres pg_restore -U $PROD_DB_USER -d claz --no-owner /tmp/claz-deploy.dump \
        && docker compose exec app php artisan migrate --force \
        && docker compose exec app php artisan config:cache \
        && docker compose exec app php artisan view:cache \
        && docker compose exec app php artisan cache:clear \
        && rm -f /tmp/claz-deploy.dump"
    rm -f "$DUMP"
fi

echo "==> Syncing local storage (media files, uploads) to production"
rsync -a --exclude '.DS_Store' storage/app/public/ "$HOST:/var/lib/docker/volumes/clazsite_uploads/_data/"

if [ -n "$SEEDERS" ]; then
    echo "$SEEDERS" | tr ',' '\n' | while read -r seeder; do
        [ -n "$seeder" ] || continue
        ssh "$HOST" "cd $DIR && docker compose exec app php artisan db:seed --class=$seeder --force"
    done
fi

ssh "$HOST" "cd $DIR && docker compose restart app"

echo "==> Health check"
for path in "/" "/sitemap.xml"; do
    code=$(curl -s -o /dev/null -w "%{http_code}" "https://claz.site$path" --max-time 20 || echo "000")
    echo "$path -> $code"
done