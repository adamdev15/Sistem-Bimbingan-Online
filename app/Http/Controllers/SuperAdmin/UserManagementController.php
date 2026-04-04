<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

    public function index(Request $request): View
    {
        return view('modules.users.index', [
            'users' => $this->service->adminUserIndex($request),
            'roleOptions' => $this->service->assignableRoleNames(),
            'filters' => $request->only(['search', 'role', 'verified']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'form_context' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->service->assignableRoleNames())],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'form_context' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->service->assignableRoleNames())],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        if ($user->is($request->user())) {
            $data['role'] = (string) ($user->getRoleNames()->first() ?? 'super_admin');
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->email_verified_at = $request->boolean('email_verified') ? now() : null;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return back()->with('status', 'Pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus akun yang sedang login.']);
        }

        $user->delete();

        return back()->with('status', 'Pengguna dihapus.');
    }
}
