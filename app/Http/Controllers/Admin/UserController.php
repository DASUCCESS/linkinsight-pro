<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query
            ->orderByDesc('role')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'   => ['required', Rule::in(['user', 'admin'])],
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        $user->update($data);

        ActivityLogger::log('user.updated', [
            'user_id' => $user->id,
            'role'    => $user->role,
            'status'  => $user->status,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status_user', 'User updated successfully.');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(['user', 'admin'])],
            'status'   => ['required', Rule::in(['active', 'suspended'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'status'   => $data['status'],
            'password' => Hash::make($data['password']),
        ]);

        ActivityLogger::log('user.created', [
            'user_id' => $user->id,
            'role'    => $user->role,
            'status'  => $user->status,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status_user', 'User created successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        $userId = $user->id;
        $user->delete();

        ActivityLogger::log('user.deleted', ['deleted_user_id' => $userId]);

        return redirect()
            ->route('admin.users.index')
            ->with('status_user', 'User deleted.');
    }

    public function suspend(Request $request, User $user)
    {
        if ($user->status === 'suspended') {
            $user->update(['status' => 'active']);
            $message = 'User reactivated.';
            $action = 'user.reactivated';
        } else {
            $user->update(['status' => 'suspended']);
            $message = 'User suspended.';
            $action = 'user.suspended';
        }

        ActivityLogger::log($action, ['user_id' => $user->id]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status_user', $message);
    }
}
