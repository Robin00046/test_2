<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $items = User::orderBy('created_at', 'desc')->paginate($perPage);

        $column = [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'number',
                'headerClass' => 'w-20'
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
            [
                'key' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
            [
                'key' => 'role_name',
                'label' => 'Role',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
        ];

        $ItemView = $items->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role_name' => $user->role_name,
            ];
        });
        // Return the index view with the paginated items
        return Inertia::render('Users/Index', [
            'items' => $items,
            'ItemView' => $ItemView,
            'columns' => $column,
            'filters' => $request->all('search', 'trashed'),
            'perPage' => $perPage,
            'meta' => [
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    // store a new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_name' => 'required|string|max:255',
        ]);
        // dd($request->all());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role_name' => $request->role_name,
        ]);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }
    // update an existing user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role_name' => 'required|string|max:255',
        ]);
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $user->password = bcrypt($request->password);
        }
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'role_name' => $request->role_name,
        ]);
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
    // destroy an existing user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
