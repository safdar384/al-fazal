<?php

namespace App\Console\Commands;

use App\Models\newProduct;
use App\Models\ProductVariant;
use App\Support\ProductImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OptimizeProductImages extends Command
{
    protected $signature = 'products:optimize-images
        {--limit=100 : Max records to process this run}
        {--dry-run : Show what would change without writing}
        {--force : Re-optimize even if already WebP in target size range}
        {--keep-originals : Copy old files to products/_originals before delete}
        {--products : Only product master images}
        {--variants : Only variant images}';

    protected $description = 'Safely batch-optimize existing product/variant images to WebP (max 500px, ~50–150KB)';

    public function handle(): int
    {
        $caps = ProductImageStorage::capabilities();
        $this->info('Image capabilities: '.json_encode($caps));

        if (! $caps['driver']) {
            $this->error('No GD/Imagick available. Enable php-gd (with WebP) on the server, then re-run.');

            return self::FAILURE;
        }

        if (! $caps['webp'] && $caps['driver'] === 'gd-jpeg') {
            $this->warn('WebP not available — will optimize as JPEG. Prefer enabling GD WebP on Hostinger.');
        }

        $limit = max(1, (int) $this->option('limit'));
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $keepOriginals = (bool) $this->option('keep-originals');
        $onlyProducts = (bool) $this->option('products');
        $onlyVariants = (bool) $this->option('variants');
        if (! $onlyProducts && ! $onlyVariants) {
            $onlyProducts = true;
            $onlyVariants = true;
        }

        $stats = ['scanned' => 0, 'optimized' => 0, 'skipped' => 0, 'failed' => 0, 'updated' => 0];

        if ($onlyProducts) {
            $this->line('— Products —');
            $query = newProduct::query()
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->orderBy('id');

            $rows = $query->limit($limit)->get(['id', 'productCode', 'image']);
            foreach ($rows as $row) {
                $stats['scanned']++;
                $this->processRow('product', $row->id, (string) $row->image, $dry, $force, $keepOriginals, $stats, function (string $newPath) use ($row) {
                    newProduct::where('id', $row->id)->update(['image' => $newPath]);
                });
            }
        }

        if ($onlyVariants) {
            $remaining = max(0, $limit - ($onlyProducts ? $stats['scanned'] : 0));
            if ($remaining > 0 || ! $onlyProducts) {
                $this->line('— Variants —');
                $vLimit = $onlyProducts ? $remaining : $limit;
                if ($vLimit > 0) {
                    $rows = ProductVariant::query()
                        ->whereNotNull('image')
                        ->where('image', '!=', '')
                        ->orderBy('id')
                        ->limit($vLimit)
                        ->get(['id', 'productCode', 'image']);

                    foreach ($rows as $row) {
                        $stats['scanned']++;
                        $this->processRow('variant', $row->id, (string) $row->image, $dry, $force, $keepOriginals, $stats, function (string $newPath) use ($row) {
                            ProductVariant::where('id', $row->id)->update(['image' => $newPath]);
                        });
                    }
                }
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. scanned=%d optimized=%d updated=%d skipped=%d failed=%d%s',
            $stats['scanned'],
            $stats['optimized'],
            $stats['updated'],
            $stats['skipped'],
            $stats['failed'],
            $dry ? ' (dry-run)' : ''
        ));

        $this->comment('Tip: re-run with --limit=200 until skipped/optimized cover all images.');

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  callable(string):void  $persist
     * @param  array<string, int>  $stats
     */
    protected function processRow(
        string $type,
        int $id,
        string $oldPath,
        bool $dry,
        bool $force,
        bool $keepOriginals,
        array &$stats,
        callable $persist
    ): void {
        $oldPath = ProductImageStorage::normalizeRelativePath($oldPath);
        $this->output->write("  [{$type}#{$id}] {$oldPath} … ");

        try {
            $result = ProductImageStorage::optimizeExistingPath($oldPath, $force);

            if ($result['skipped']) {
                $stats['skipped']++;
                $this->line('skip ('.$result['reason'].')');

                // Normalize legacy absolute-ish paths in DB even on skip
                if (! $dry && $oldPath !== '' && $result['path'] === $oldPath) {
                    // no-op
                }

                return;
            }

            $stats['optimized']++;
            $newPath = $result['path'];
            $kb = round($result['bytes'] / 1024, 1);
            $this->output->write("→ {$newPath} ({$kb} KB) ");

            if ($dry) {
                ProductImageStorage::deletePath($newPath);
                $this->line('[dry-run]');

                return;
            }

            DB::transaction(function () use ($persist, $newPath, $oldPath, $keepOriginals) {
                $persist($newPath);

                if ($keepOriginals && $oldPath !== $newPath && Storage::disk(ProductImageStorage::DISK)->exists($oldPath)) {
                    $archive = ProductImageStorage::ORIGINALS_DIR.'/'.basename($oldPath);
                    if (! Storage::disk(ProductImageStorage::DISK)->exists($archive)) {
                        Storage::disk(ProductImageStorage::DISK)->copy($oldPath, $archive);
                    }
                }
            });

            $stats['updated']++;

            // Delete old only after DB update, and only if unused
            if ($oldPath !== $newPath) {
                ProductImageStorage::deleteIfUnused($oldPath);
            }

            $this->line('OK');
        } catch (Throwable $e) {
            $stats['failed']++;
            $this->error('FAIL: '.$e->getMessage());
            report($e);
        }
    }
}
