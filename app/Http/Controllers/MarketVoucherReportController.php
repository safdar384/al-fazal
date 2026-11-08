<?php

namespace App\Http\Controllers;

use App\Support\MarketVoucherReport;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketVoucherReportController extends Controller
{
    public function index(Request $request): View
    {
        $range = $this->resolveDateRange($request);
        $areaCode = $request->input('area');
        $generated = $request->boolean('generate');

        $report = null;
        if ($generated && $areaCode) {
            $request->validate([
                'area' => 'required|string',
                'from_date' => 'required|date',
                'to_date' => 'required|date',
            ]);

            $report = MarketVoucherReport::build(
                $areaCode,
                $range['from'],
                $range['to']
            );
        }

        $selectedArea = $areaCode
            ? MarketVoucherReport::areaOptions()->firstWhere('areaCode', $areaCode)
            : null;

        return view('reports.market-voucher.index', [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'areaCode' => $areaCode,
            'selectedAgentName' => $selectedArea?->ajentName ?? '',
            'areas' => MarketVoucherReport::areaOptions(),
            'report' => $report,
            'generated' => $generated && (bool) $areaCode,
            'companyName' => config('app.name', 'TradeGate'),
        ]);
    }

    /**
     * AJAX generate — returns report HTML without full page reload.
     */
    public function data(Request $request): JsonResponse
    {
        $request->validate([
            'area' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        $range = $this->resolveDateRange($request);
        $areaCode = (string) $request->input('area');

        $report = MarketVoucherReport::build(
            $areaCode,
            $range['from'],
            $range['to']
        );

        $html = view('reports.market-voucher.partials.report-panel', [
            'report' => $report,
        ])->render();

        $printUrl = route('reports.market-voucher.print', [
            'area' => $areaCode,
            'from_date' => $range['from'],
            'to_date' => $range['to'],
        ]);

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $report['rows']->count(),
            'print_url' => $printUrl,
            'from_date' => $range['from'],
            'to_date' => $range['to'],
            'area' => $areaCode,
        ]);
    }

    public function print(Request $request): View
    {
        $request->validate([
            'area' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        $range = $this->resolveDateRange($request);
        $report = MarketVoucherReport::build(
            $request->input('area'),
            $range['from'],
            $range['to']
        );

        return view('reports.market-voucher.print', [
            'report' => $report,
            'companyName' => config('app.name', 'TradeGate'),
        ]);
    }

    /**
     * @return array{from: string, to: string}
     */
    protected function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        try {
            $from = Carbon::parse($request->input('from_date', $defaultFrom))->format('Y-m-d');
            $to = Carbon::parse($request->input('to_date', $defaultTo))->format('Y-m-d');
        } catch (\Throwable) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }
}
