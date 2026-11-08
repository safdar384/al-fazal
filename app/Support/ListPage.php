<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ListPage
{
    public const OPTIONS = [10, 25, 50, 100];

    public static function perPage(Request $request, int $fallback = 25, int $max = 100): int
    {
        $size = (int) $request->query('per_page', $fallback);
        if ($size > 100) {
            return min($max, $size);
        }

        return in_array($size, self::OPTIONS, true) ? $size : $fallback;
    }

    public static function paginate(Request $request, Collection $rows): LengthAwarePaginator
    {
        $perPage = self::perPage($request);
        $page = max(1, (int) $request->query('page', 1));
        $items = $rows->forPage($page, $perPage)->values();

        return new LengthAwarePaginator($items, $rows->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->except(['fragment', 'print', 'download_pdf']),
        ]);
    }
}
