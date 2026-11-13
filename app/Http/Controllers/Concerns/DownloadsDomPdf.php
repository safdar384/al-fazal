<?php

namespace App\Http\Controllers\Concerns;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

trait DownloadsDomPdf
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function downloadDomPdf(
        string $view,
        array $data,
        string $filename,
        string $paper = 'a4',
        string $orientation = 'portrait'
    ): Response {
        if (! class_exists(Pdf::class)) {
            abort(503, 'PDF package is not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation)
            ->download($filename);
    }
}
