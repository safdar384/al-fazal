<?php

namespace App\Http\Controllers;

use App\Models\newProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductReportController extends Controller
{
    public function all(Request $request): Response|View
    {
        return $this->renderReport($request, 'all');
    }

    public function byCompany(Request $request): Response|View
    {
        return $this->renderReport($request, 'company');
    }

    public function byName(Request $request): Response|View
    {
        return $this->renderReport($request, 'name');
    }

    public function byCategory(Request $request): Response|View
    {
        return $this->renderReport($request, 'category');
    }

    public function shortStock(Request $request): Response|View
    {
        return $this->renderReport($request, 'short_stock');
    }

    protected function renderReport(Request $request, string $reportType): Response|View
    {
        $config = $this->reportConfig($reportType);
        $filters = $this->resolveFilters($request);

        $query = newProduct::query()->from('product_info');

        if ($filters['company']) {
            $query->where('companyName', $filters['company']);
        }

        if ($filters['product_code']) {
            $query->where('productCode', $filters['product_code']);
        }

        if ($filters['category']) {
            $query->where('catagoryName', $filters['category']);
        }

        if ($reportType === 'short_stock') {
            $query->whereRaw('COALESCE(stockQty, 0) <= COALESCE(lessQty, 0)');
        }

        $rows = $query
            ->orderBy('companyName')
            ->orderBy('productName')
            ->get([
                'productCode',
                'productName',
                'companyName',
                'catagoryName',
                'stockQty',
                'lessQty',
                'saleRate',
                'costRate',
            ])
            ->map(function ($row) {
                $stock = (float) ($row->stockQty ?? 0);
                $threshold = (float) ($row->lessQty ?? 0);
                $row->stockQty = $stock;
                $row->lessQty = $threshold;
                $row->stock_gap = $stock - $threshold;
                $row->stock_status = $stock <= $threshold ? 'Short Stock' : 'In Stock';

                return $row;
            });

        $summary = [
            'total_products' => $rows->count(),
            'short_products' => $rows->where('stock_status', 'Short Stock')->count(),
            'total_stock' => (float) $rows->sum('stockQty'),
            'total_threshold' => (float) $rows->sum('lessQty'),
        ];

        $viewData = [
            'reportType' => $reportType,
            'config' => $config,
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions(),
        ];

        if ($request->boolean('download_pdf')) {
            if (! class_exists(Pdf::class)) {
                return back()->with('error', 'PDF package is not installed. Please run composer install.');
            }

            return Pdf::loadView('reports.products.pdf', $viewData)
                ->setPaper('a4', 'landscape')
                ->download('product-report-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.products.live', $viewData);
        }

        return response()->view('reports.products.report', $viewData);
    }

    protected function filterOptions(): array
    {
        return [
            'companies' => newProduct::query()
                ->whereNotNull('companyName')
                ->where('companyName', '!=', '')
                ->select('companyName')
                ->distinct()
                ->orderBy('companyName')
                ->pluck('companyName'),
            'products' => newProduct::query()
                ->orderBy('productName')
                ->get(['productCode', 'productName', 'companyName', 'catagoryName']),
            'categories' => newProduct::query()
                ->whereNotNull('catagoryName')
                ->where('catagoryName', '!=', '')
                ->select('catagoryName')
                ->distinct()
                ->orderBy('catagoryName')
                ->pluck('catagoryName'),
        ];
    }

    protected function resolveFilters(Request $request): array
    {
        return [
            'company' => $request->string('company')->toString(),
            'product_code' => $request->string('product_code')->toString(),
            'category' => $request->string('category')->toString(),
        ];
    }

    protected function reportConfig(string $reportType): array
    {
        return match ($reportType) {
            'all' => [
                'title' => 'All Products Report',
                'route' => 'reports.products.all',
                'show' => ['company' => true, 'product' => true, 'category' => true],
            ],
            'company' => [
                'title' => 'Product by Company',
                'route' => 'reports.products.by-company',
                'show' => ['company' => true, 'product' => true, 'category' => false],
            ],
            'name' => [
                'title' => 'Product by Name',
                'route' => 'reports.products.by-name',
                'show' => ['company' => true, 'product' => true, 'category' => false],
            ],
            'category' => [
                'title' => 'Product by Category',
                'route' => 'reports.products.by-category',
                'show' => ['company' => false, 'product' => true, 'category' => true],
            ],
            'short_stock' => [
                'title' => 'Short Stock Products',
                'route' => 'reports.products.short-stock',
                'show' => ['company' => true, 'product' => true, 'category' => true],
            ],
            default => [
                'title' => 'Product Report',
                'route' => 'reports.products.all',
                'show' => ['company' => false, 'product' => false, 'category' => false],
            ],
        };
    }
}
