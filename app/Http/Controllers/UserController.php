<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // List users by role (optional)
    public function index(Request $request)
    {
        $role = $request->query('role'); // admin, doctor, staff, patient

        $query = User::query()->whereNull('deleted_at');

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->get();

        return response()->json($users);
    }

    // Create a new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
            'role'       => 'required|in:admin,staff,doctor,patient',
            'department_id' => 'required_if:role,doctor|exists:departments,id',
            'specialization' => 'sometimes|string|max:255',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => bcrypt($validated['password']),
            'role'       => $validated['role'],
        ]);

        if ($validated['role'] === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
                'department_id' => $validated['department_id'],
                'specialization' => $validated['specialization'] ?? null,
            ]);
        }

        return response()->json($user, 201);
    }


    // Show a specific user
    public function show($id)
    {
        $user = User::whereNull('deleted_at')->findOrFail($id);
        return response()->json($user);
    }

    // Update user
    public function update(Request $request, $id)
    {
        $user = User::whereNull('deleted_at')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:users,email,' . $user->id,
            'password'   => 'sometimes|min:6',
            'role'       => 'sometimes|in:admin,doctor,staff,patient',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    // Soft delete user
    public function destroy($id)
    {
        $user = User::whereNull('deleted_at')->findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
