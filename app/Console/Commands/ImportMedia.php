<?php

namespace App\Console\Commands;

use App\Models\MediaItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImportMedia extends Command
{
    protected $signature = 'media:import {--dir=uploads : Directory on the public disk to import}';

    protected $description = 'Import existing files from storage into the media library';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dir = trim(strval($this->option('dir')), '/');

        if (! $disk->exists($dir)) {
            $this->error("Directory [{$dir}] does not exist.");

            return self::FAILURE;
        }

        $files = collect($disk->allFiles($dir))
            ->filter(fn (string $path): bool => (int) $disk->size($path) > 0);

        $imported = 0;
        $skipped = 0;

        foreach ($files as $path) {
            $exists = Media::query()
                ->where('model_type', MediaItem::class)
                ->where('file_name', basename($path))
                ->where('size', (int) $disk->size($path))
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            $item = new MediaItem;
            $item->save();
            $item->addMedia($disk->path($path))
                ->preservingOriginal()
                ->usingFileName(basename($path))
                ->usingName(pathinfo($path, PATHINFO_FILENAME))
                ->toMediaCollection('files');
            $imported++;
        }

        $this->info("Imported {$imported} file(s), skipped {$skipped} existing.");

        return self::SUCCESS;
    }
}
