#!/bin/sh
# Deploy to production: git push -> VPS pull+rebuild -> optional seeders -> verify.
# Usage: ./scripts/deploy.sh [SeederClass[,SeederClass...]]
# Example: ./scripts/deploy.sh WordPressPageBuildersSeeder,WordPressPageBuildersCommentsSeeder
set -e

HOST=${DEPLOY_HOST:-pv-vps}
DIR=${DEPLOY_DIR:-/root/claz.site}
BRANCH=${DEPLOY_BRANCH:-main}

SEEDERS=${1:-}

cd "$(dirname "$0")/.."

git push origin "$BRANCH"

ssh "$HOST" "cd $DIR && git pull && docker compose up -d --build"

if [ -n "$SEEDERS" ]; then
    echo "$SEEDERS" | tr ',' '\n' | while read -r seeder; do
        [ -n "$seeder" ] || continue
        ssh "$HOST" "cd $DIR && docker compose exec app php artisan db:seed --class=$seeder --force"
    done
    ssh "$HOST" "cd $DIR && docker compose exec app php artisan cache:clear"
fi

for path in "/" "/sitemap.xml"; do
    code=$(curl -s -o /dev/null -w "%{http_code}" "https://claz.site$path" --max-time 20 || echo "000")
    echo "$path -> $code"
done