<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    // Middleware konstruksi
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            if (Gate::denies('isSuperAdmin')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            return $next($request);
        });
    }

    // CREATE Admin Restoran (Super Admin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,restaurant_admin',
            'restaurant_id' => 'required_if:role,restaurant_admin|exists:restaurants,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'restaurant_id' => $request->role == 'restaurant_admin' ? $request->restaurant_id : null,
        ]);

        return response()->json($user, 201);
    }

    // READ All Users (Super Admin)
    public function index()
    {
        $users = User::with('restaurant')->get();
        return response()->json($users);
    }

    // READ Single User (Super Admin)
    public function show($id)
    {
        $user = User::with('restaurant')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    // UPDATE User (Super Admin)
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:50|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|in:super_admin,restaurant_admin',
            'restaurant_id' => 'required_if:role,restaurant_admin|exists:restaurants,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->username) {
            $user->username = $request->username;
        }
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        if ($request->role) {
            $user->role = $request->role;
            $user->restaurant_id = $request->role == 'restaurant_admin' ? $request->restaurant_id : null;
        }

        $user->save();

        return response()->json($user);
    }

    // DELETE User (Super Admin)
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
