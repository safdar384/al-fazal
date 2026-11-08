<?php



namespace App\Http\Controllers;



use App\Models\CustomerCheque;

use App\Models\NewCustomer;

use App\Models\Order;

use App\Models\newProduct;

use App\Models\SaleInvoice;

use App\Support\DashboardService;

use Carbon\Carbon;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\View\View;



class HomeController extends Controller

{

    public function __construct(

        protected DashboardService $dashboard

    ) {}



    public function index(Request $request): View
    {
        $user = $request->user();

        // Business KPI dashboard is admin-only. Staff / client / manager get a clean home.
        if ($user && ! $user->isAdmin()) {
            return view('workspace', [
                'user' => $user,
                'shortcuts' => $this->workspaceShortcuts($user),
            ]);
        }

        // Cache-only first paint so login → dashboard never hangs on heavy SQL.
        [$data, $needsWarm] = $this->dashboard->summaryViewDataFast();
        if ($needsWarm) {
            app()->terminating(function () {
                app(DashboardService::class)->warmSummaryCache();
            });
        }

        return view('home', $data);
    }

    public function chartData(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($this->dashboard->chartSeries());
    }

    public function recentActivityData(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'html' => view('components.dashboard.recent-activity', [
                'recentActivity' => $this->dashboard->recentActivity(),
            ])->render(),
        ]);
    }

    /**
     * Shortcut cards for non-admin home (only pages the user can open).
     *
     * @return list<array{key: string, label: string, description: string, icon: string, url: string}>
     */
    protected function workspaceShortcuts($user): array
    {
        $items = [];
        foreach (config('permissions.modules', []) as $key => $module) {
            if ($key === 'dashboard') {
                continue;
            }
            if (! $user->hasPermission($key)) {
                continue;
            }

            $url = null;
            if (! empty($module['route']) && \Illuminate\Support\Facades\Route::has($module['route'])) {
                $url = route($module['route']);
            } elseif (! empty($module['url'])) {
                $url = url($module['url']);
            }

            if (! $url) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => $module['label'],
                'description' => $module['description'],
                'icon' => $module['icon'],
                'url' => $url,
            ];
        }

        return $items;
    }



    public function navbarSearch(Request $request): JsonResponse

    {

        $query = trim((string) $request->get('q', ''));



        if (mb_strlen($query) < 2) {

            return response()->json(['items' => []]);

        }



        $products = newProduct::query()

            ->select(['productCode', 'productName'])

            ->where('productCode', 'like', "%{$query}%")

            ->orWhere('productName', 'like', "%{$query}%")

            ->limit(5)

            ->get()

            ->map(fn ($item) => [

                'type' => 'Product',

                'title' => $item->productName,

                'subtitle' => $item->productCode,

                'url' => route('product.index'),

                'icon' => 'box-seam',

            ]);



        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->select(['custCode', 'custName', 'bussnessName'])
            ->where(function ($q) use ($query) {
                $q->where('custCode', 'like', "%{$query}%")
                    ->orWhere('custName', 'like', "%{$query}%")
                    ->orWhere('bussnessName', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()

            ->map(fn ($item) => [

                'type' => 'Customer',

                'title' => $item->custName ?: $item->bussnessName,

                'subtitle' => $item->custCode,

                'url' => route('Customer.index'),

                'icon' => 'person',

            ]);



        $sales = SaleInvoice::query()

            ->select(['invoiceNo', 'customerName', 'invoiceDate'])

            ->where('invoiceNo', 'like', "%{$query}%")

            ->orWhere('customerName', 'like', "%{$query}%")

            ->limit(5)

            ->get()

            ->map(fn ($item) => [

                'type' => 'Sale Invoice',

                'title' => $item->invoiceNo,

                'subtitle' => $item->customerName,

                'url' => route('saleSummary'),

                'icon' => 'receipt',

            ]);



        $orders = Order::query()

            ->select(['orderNo', 'customerName', 'orderDate', 'status'])

            ->where('orderNo', 'like', "%{$query}%")

            ->orWhere('customerName', 'like', "%{$query}%")

            ->orWhere('status', 'like', "%{$query}%")

            ->limit(5)

            ->get()

            ->map(fn ($item) => [

                'type' => 'Order',

                'title' => $item->orderNo,

                'subtitle' => ($item->customerName ?: 'Unknown').' - '.($item->status ?: 'Open'),

                'url' => route('order.summary'),

                'icon' => 'journal-check',

            ]);



        $items = collect()

            ->concat($products)

            ->concat($customers)

            ->concat($sales)

            ->concat($orders)

            ->take(12)

            ->values();



        return response()->json(['items' => $items]);

    }



    public function navbarNotifications(): JsonResponse
    {
        $payload = \Illuminate\Support\Facades\Cache::store('file')->remember(
            'navbar.notifications.'.config('app.env', 'local'),
            45,
            function () {
                $items = [];

                // Today pending cheques (due today or earlier still pending)
                $todayPendingCheques = CustomerCheque::query()
                    ->where('status', CustomerCheque::STATUS_PENDING)
                    ->whereDate('due_date', '<=', now()->format('Y-m-d'))
                    ->count();

                if ($todayPendingCheques > 0 && \Illuminate\Support\Facades\Route::has('partyCheques.index')) {
                    $items[] = [
                        'title' => 'Today Pending Cheques',
                        'message' => (string) $todayPendingCheques,
                        'time' => 'today',
                        'url' => route('partyCheques.index'),
                        'priority' => 'danger',
                        'status' => (string) $todayPendingCheques,
                        'icon' => 'bank2',
                    ];
                }

                $pendingStatuses = ['pending', 'open', 'new', 'hold'];

                $pendingOrders = Order::query()
                    ->select(['orderNo', 'customerName', 'orderDate', 'status', 'grandTotal'])
                    ->where(function ($query) use ($pendingStatuses) {
                        $query->whereNull('status')
                            ->orWhereIn('status', $pendingStatuses);
                    })
                    ->orderByDesc('orderDate')
                    ->limit(8)
                    ->get();

                foreach ($pendingOrders as $order) {
                    $items[] = [
                        'title' => 'Order '.($order->orderNo ?: '-'),
                        'message' => ($order->customerName ?: 'Unknown customer').' | PKR '.number_format((float) $order->grandTotal, 2),
                        'time' => $order->orderDate ? Carbon::parse($order->orderDate)->diffForHumans() : 'recently',
                        'url' => route('order.summary'),
                        'priority' => 'warning',
                        'status' => $order->status ?: 'Open',
                        'icon' => 'journal-check',
                    ];
                }

                return [
                    'count' => count($items),
                    'today_pending_cheques' => $todayPendingCheques,
                    'items' => $items,
                ];
            }
        );

        return response()->json($payload);
    }

}

