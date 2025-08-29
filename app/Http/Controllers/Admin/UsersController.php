<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $users = User::query()
            ->when($q !== '', function($query) use ($q) {
                $query->where(function($qq) use ($q) {
                    $qq->where('name', 'like', "%$q%")
                       ->orWhere('email', 'like', "%$q%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->appends(['q' => $q]);

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function create()
    {
        $user = new User();
        return view('admin.users.create', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $plainPassword = $data['password'];

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($plainPassword);
        $user->role = $data['role'];
        $user->save();

        // Email the credentials to the created user
        try {
            Mail::to($user->email)->queue(new UserCredentialsMail($user, $plainPassword));
        } catch (\Throwable $e) {
            // Log but do not block creation
            \Log::warning('Failed to queue credentials email for user '.$user->id.': '.$e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('status', 'User created and credentials emailed');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'min:6'],
            'role' => ['required', 'in:admin,user'],
        ]);

        if (isset($data['name'])) $user->name = $data['name'];
        if (isset($data['email'])) $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        $user->role = $data['role'];
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'User updated');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself for safety
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'User deleted');
    }
}

