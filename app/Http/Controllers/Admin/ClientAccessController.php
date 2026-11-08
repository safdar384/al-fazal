<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientUrlRestriction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientAccessController extends Controller
{
    public function index(): View
    {
        $this->ensureDefaultsExist();

        $restrictions = ClientUrlRestriction::query()->orderBy('pattern')->get();
        $byPattern = $restrictions->keyBy('pattern');

        $modules = $this->buildModuleMatrix($byPattern);
        $catalogPatterns = $this->allCatalogPatterns();
        $customRestrictions = $restrictions->reject(
            fn (ClientUrlRestriction $r) => in_array($r->pattern, $catalogPatterns, true)
        )->values();

        $blockedCount = $restrictions->where('is_active', true)->count();
        $allowedCount = $restrictions->where('is_active', false)->count();

        return view('admin.client-access.index', compact(
            'modules',
            'customRestrictions',
            'restrictions',
            'blockedCount',
            'allowedCount'
        ));
    }

    public function syncModule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', Rule::in(array_keys(config('client_access.modules', [])))],
            'allow' => ['required', 'in:0,1'],
        ]);

        $module = config('client_access.modules.'.$validated['module']);
        $allow = (bool) (int) $validated['allow'];
        // allow=true → restriction inactive; allow=false → block client
        $isActive = ! $allow;

        foreach ($module['patterns'] as $pattern) {
            ClientUrlRestriction::updateOrCreate(
                ['pattern' => $pattern],
                ['is_active' => $isActive]
            );
        }

        $label = $module['label'];
        $msg = $allow
            ? "Client access allowed for “{$label}”."
            : "Client access blocked for “{$label}”.";

        return back()->with('success', $msg);
    }

    public function bulkSync(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'allow' => ['required', 'in:0,1'],
        ]);

        $allow = (bool) (int) $validated['allow'];
        $isActive = ! $allow;

        foreach (config('client_access.modules', []) as $module) {
            foreach ($module['patterns'] as $pattern) {
                ClientUrlRestriction::updateOrCreate(
                    ['pattern' => $pattern],
                    ['is_active' => $isActive]
                );
            }
        }

        return back()->with(
            'success',
            $allow
                ? 'All listed modules are now allowed for clients.'
                : 'All listed modules are now blocked for clients.'
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pattern' => [
                'required',
                'string',
                'max:191',
                Rule::unique('client_url_restrictions', 'pattern'),
                'regex:/^[a-zA-Z0-9_\-\.\*\/]+$/',
            ],
            'is_active' => ['nullable', 'in:0,1'],
        ], [
            'pattern.regex' => 'Use letters, numbers, and * / - _ . only (Laravel path patterns).',
        ]);

        ClientUrlRestriction::create([
            'pattern' => $validated['pattern'],
            'is_active' => ! isset($validated['is_active']) || (bool) (int) $validated['is_active'],
        ]);

        return back()->with('success', 'Custom access rule added.');
    }

    public function update(Request $request, ClientUrlRestriction $restriction): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'in:0,1'],
        ]);

        $restriction->update(['is_active' => (bool) (int) $validated['is_active']]);

        return back()->with('success', 'Access rule updated.');
    }

    public function destroy(ClientUrlRestriction $restriction): RedirectResponse
    {
        $restriction->delete();

        return back()->with('success', 'Access rule removed.');
    }

    public function seedDefaults(): RedirectResponse
    {
        foreach (config('client_access.admin_only_patterns', []) as $pattern) {
            ClientUrlRestriction::firstOrCreate(
                ['pattern' => $pattern],
                ['is_active' => true]
            );
        }

        return back()->with('success', 'Default rules applied (existing rows unchanged).');
    }

    protected function ensureDefaultsExist(): void
    {
        if (ClientUrlRestriction::query()->exists()) {
            return;
        }

        foreach (config('client_access.admin_only_patterns', []) as $pattern) {
            ClientUrlRestriction::create([
                'pattern' => $pattern,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  Collection<string, ClientUrlRestriction>  $byPattern
     * @return array<string, list<array<string, mixed>>>
     */
    protected function buildModuleMatrix(Collection $byPattern): array
    {
        $grouped = [];

        foreach (config('client_access.modules', []) as $key => $module) {
            $patternStates = [];
            foreach ($module['patterns'] as $pattern) {
                $row = $byPattern->get($pattern);
                $patternStates[] = [
                    'pattern' => $pattern,
                    'exists' => (bool) $row,
                    'blocked' => $row ? (bool) $row->is_active : true,
                ];
            }

            $blockedCount = collect($patternStates)->where('blocked', true)->count();
            $total = count($patternStates);
            $allBlocked = $blockedCount === $total;
            $allAllowed = $blockedCount === 0;

            $group = $module['group'];
            $grouped[$group][] = [
                'key' => $key,
                'label' => $module['label'],
                'description' => $module['description'],
                'icon' => $module['icon'],
                'patterns' => $module['patterns'],
                'allowed' => $allAllowed,
                'mixed' => ! $allBlocked && ! $allAllowed,
                'blocked_count' => $blockedCount,
                'total' => $total,
            ];
        }

        return $grouped;
    }

    /**
     * @return list<string>
     */
    protected function allCatalogPatterns(): array
    {
        $patterns = [];
        foreach (config('client_access.modules', []) as $module) {
            foreach ($module['patterns'] as $pattern) {
                $patterns[] = $pattern;
            }
        }

        return array_values(array_unique($patterns));
    }
}
