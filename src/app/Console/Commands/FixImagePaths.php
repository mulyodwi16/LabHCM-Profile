<?php

namespace App\Console\Commands;

use App\Models\GalleryItem;
use App\Models\ProjectImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixImagePaths extends Command
{
    protected $signature = 'app:fix-image-paths {--dry-run : Show changes without writing}';

    protected $description = 'Normalize stored image paths and remove invalid project image rows';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $this->info($dryRun ? 'DRY RUN — no changes will be saved.' : 'Fixing image paths...');

        $this->fixProjectImages($disk, $dryRun);
        $this->fixGalleryItems($disk, $dryRun);

        return self::SUCCESS;
    }

    private function fixProjectImages($disk, bool $dryRun): void
    {
        foreach (ProjectImage::query()->cursor() as $image) {
            $before = $image->path;
            $after = $this->normalizePath($before);

            if ($after === null) {
                $this->warn("DELETE project_image#{$image->id} invalid path: {$before}");
                if (!$dryRun) {
                    $image->delete();
                }
                continue;
            }

            if ($after !== $before) {
                $this->line("project_image#{$image->id}: {$before} -> {$after}");
                if (!$dryRun) {
                    $image->update(['path' => $after]);
                }
                continue;
            }

            if (!$disk->exists($after)) {
                $this->warn("MISSING file for project_image#{$image->id}: {$after}");
            }
        }
    }

    private function fixGalleryItems($disk, bool $dryRun): void
    {
        foreach (GalleryItem::query()->cursor() as $item) {
            $before = $item->image_path;
            $after = $this->normalizePath($before);

            if ($after === null) {
                $this->warn("SKIP gallery_item#{$item->id} invalid path: {$before}");
                continue;
            }

            if ($after !== $before) {
                $this->line("gallery_item#{$item->id}: {$before} -> {$after}");
                if (!$dryRun) {
                    $item->update(['image_path' => $after]);
                }
                continue;
            }

            if (!$disk->exists($after)) {
                $this->warn("MISSING file for gallery_item#{$item->id}: {$after}");
            }
        }
    }

    private function normalizePath(?string $path): ?string
    {
        if ($path === null || $path === '' || $path === '0' || str_contains($path, '..')) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $clean = ltrim((string) preg_replace('#^(public/|storage/)#', '', $path), '/');

        return $clean !== '' ? $clean : null;
    }
}
