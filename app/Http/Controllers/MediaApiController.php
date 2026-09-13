<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $disk = Storage::disk('public');

        if ($request->boolean('all')) {
            $files = collect($disk->allFiles('uploads'))
                ->filter(fn (string $path): bool => (int) $disk->size($path) > 0)
                ->sortByDesc(fn (string $path): int => $disk->lastModified($path))
                ->map(fn (string $path): array => $this->serialize($path))
                ->values()
                ->all();

            return response()->json(['dir' => 'uploads', 'folders' => [], 'files' => $files]);
        }

        $dir = $this->resolveDir($request->query('dir'));

        $folders = collect($disk->directories($dir))
            ->filter(fn (string $path): bool => basename($path) !== '.DS_Store')
            ->map(fn (string $path): string => basename($path))
            ->sort()->values()->all();

        $files = collect($disk->files($dir))
            ->filter(fn (string $path): bool => (int) $disk->size($path) > 0)
            ->sortByDesc(fn (string $path): int => $disk->lastModified($path))
            ->map(fn (string $path): array => $this->serialize($path))
            ->values()->all();

        return response()->json(['dir' => $dir, 'folders' => $folders, 'files' => $files]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:307200'],
            'dir' => ['nullable', 'string'],
        ]);

        $dir = $this->resolveDir(strval($request->string('dir')) ?: 'uploads');
        $file = $request->file('file');
        $name = $this->uniqueName($dir, $file->getClientOriginalName());

        Storage::disk('public')->putFileAs($dir, $file, $name);

        return response()->json($this->serialize($dir.'/'.$name), 201);
    }

    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'dir' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9._\- ]*$/'],
        ]);

        $dir = $this->resolveDir(strval($request->string('dir')) ?: 'uploads');
        $name = trim(strval($request->string('name')));

        if (in_array($name, ['.', '..'], true)) {
            return response()->json(['message' => 'Invalid folder name.'], 422);
        }

        $path = $dir.'/'.$name;

        if (Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'A file or folder with this name already exists.'], 422);
        }

        Storage::disk('public')->makeDirectory($path);

        return response()->json(['created' => $path], 201);
    }

    public function delete(Request $request): JsonResponse
    {
        $disk = Storage::disk('public');
        $path = $this->normalize(strval($request->string('path')));

        if ($path === '' || ! str_starts_with($path, 'uploads/')) {
            return response()->json(['message' => 'Invalid path.'], 422);
        }

        if (! $disk->exists($path)) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($disk->directoryExists($path)) {
            if (count($disk->allFiles($path)) > 0 || count($disk->directories($path)) > 0) {
                return response()->json(['message' => 'Folder is not empty. Delete its contents first.'], 422);
            }

            $disk->deleteDirectory($path);

            return response()->json(['deleted' => true]);
        }

        $disk->delete($path);

        return response()->json(['deleted' => true]);
    }

    private function resolveDir(string $dir): string
    {
        if (str_contains($dir, '..')) {
            abort(422, 'Invalid directory.');
        }

        $clean = $this->normalize($dir);

        if (! str_starts_with($clean, 'uploads')) {
            abort(422, 'Invalid directory.');
        }

        return $clean;
    }

    private function normalize(string $dir): string
    {
        $dir = str_replace('\\', '/', $dir);

        if (str_contains($dir, '..')) {
            abort(422, 'Invalid path.');
        }

        $dir = (string) preg_replace('#/+#', '/', $dir);

        return trim($dir, '/');
    }

    private function uniqueName(string $dir, string $originalName): string
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $base = mb_substr($this->sanitizeName(pathinfo($originalName, PATHINFO_FILENAME)), 0, 80);
        $base = $base !== '' ? $base : 'file';
        $disk = Storage::disk('public');

        $candidate = $base.($ext !== '' ? '.'.$ext : '');
        $i = 0;

        while ($disk->exists($dir.'/'.$candidate)) {
            $candidate = $base.'-'.(++$i).($ext !== '' ? '.'.$ext : '');
        }

        return $candidate;
    }

    private function sanitizeName(string $name): string
    {
        return trim((string) preg_replace('/[^a-zA-Z0-9._\- ]+/', '-', $name));
    }

    /** @return array<string, mixed> */
    private function serialize(string $path): array
    {
        $disk = Storage::disk('public');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return [
            'path' => $path,
            'rel' => $disk->url($path),
            'url' => $disk->url($path),
            'name' => basename($path),
            'dir' => dirname($path),
            'bytes' => (int) $disk->size($path),
            'size' => $this->formatSize((int) $disk->size($path)),
            'type' => $this->typeFromExtension($ext),
            'modified' => date('Y-m-d H:i', $disk->lastModified($path)),
        ];
    }

    private function typeFromExtension(string $ext): string
    {
        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg']) => 'image',
            in_array($ext, ['mp4', 'm4v', 'mov', 'webm', 'ogv', 'ogg', 'avi', 'mkv']) => 'video',
            $ext === 'pdf' => 'pdf',
            default => 'file',
        };
    }

    private function formatSize(int $bytes): string
    {
        $gb = 1024 ** 3;
        $mb = 1024 ** 2;

        return match (true) {
            $bytes >= $gb => round($bytes / $gb, 2).' GB',
            $bytes >= $mb => round($bytes / $mb, 2).' MB',
            default => round($bytes / 1024, 1).' KB',
        };
    }
}
