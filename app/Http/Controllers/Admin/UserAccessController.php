<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserModulePermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('role', '!=', User::ROLE_ADMIN)
            ->orderBy('role')
            ->orderBy('name')
            ->withCount([
                'modulePermissions as allowed_modules_count' => fn ($q) => $q->where('allowed', true),
            ])
            ->get();

        return view('admin.user-access.index', compact('users'));
    }

    public function edit(User $user): View|RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()
                ->route('admin.user-access.index')
                ->with('warning', 'Administrators always have full access.');
        }

        $user->load('modulePermissions');
        $modules = $this->groupedModules();
        $accessMap = $user->moduleAccessMap();

        return view('admin.user-access.edit', compact('user', 'modules', 'accessMap'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()
                ->route('admin.user-access.index')
                ->with('warning', 'Administrators always have full access.');
        }

        $moduleKeys = array_keys(config('permissions.modules', []));
        $validated = $request->validate([
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', 'in:'.implode(',', $moduleKeys)],
        ]);

        $allowed = collect($validated['modules'] ?? [])->unique()->values()->all();
        // Home must stay reachable even if admin unchecks every module
        if (! in_array('dashboard', $allowed, true)) {
            $allowed[] = 'dashboard';
        }

        UserModulePermission::query()->where('user_id', $user->id)->delete();

        foreach ($moduleKeys as $key) {
            UserModulePermission::create([
                'user_id' => $user->id,
                'module' => $key,
                'allowed' => in_array($key, $allowed, true),
            ]);
        }

        $user->access_customized = true;
        $user->save();

        return redirect()
            ->route('admin.user-access.edit', $user)
            ->with('success', 'Access updated for '.$user->name.'.');
    }

    public function reset(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('warning', 'Administrators always have full access.');
        }

        UserModulePermission::query()->where('user_id', $user->id)->delete();
        $user->access_customized = false;
        $user->save();

        return redirect()
            ->route('admin.user-access.edit', $user)
            ->with('success', 'Custom access cleared. '.$user->name.' now uses role defaults ('.$user->role.').');
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    protected function groupedModules(): array
    {
        $grouped = [];
        foreach (config('permissions.modules', []) as $key => $module) {
            $grouped[$module['group']][] = [
                'key' => $key,
                'label' => $module['label'],
                'description' => $module['description'],
                'icon' => $module['icon'],
            ];
        }

        return $grouped;
    }
}
