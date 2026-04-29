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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->service->assignableRoleNames())],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('status', 'Pengguna "' . $user->name . '" berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->service->assignableRoleNames())],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        // Prevent changing own role
        if ($user->id === auth()->id()) {
            $data['role'] = $user->getRoleNames()->first() ?: 'super_admin';
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if ($request->has('email_verified')) {
             $user->email_verified_at = $request->boolean('email_verified') ? ($user->email_verified_at ?: now()) : null;
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return back()->with('status', 'Data pengguna "' . $user->name . '" telah diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('status', 'Pengguna "' . $userName . '" berhasil dihapus.');
    }
}
