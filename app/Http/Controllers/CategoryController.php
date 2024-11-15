<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    // Middleware konstruksi
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    // CREATE Category
    public function store(Request $request)
    {
        $user = $request->user();

        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'restaurant_id' => 'required_if:user.role,super_admin|exists:restaurants,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tentukan restaurant_id
        $restaurant_id = $user->role == 'restaurant_admin' ? $user->restaurant_id : $request->restaurant_id;

        // Cek apakah kategori sudah ada
        $existing = Category::where('name', $request->name)
            ->where('restaurant_id', $restaurant_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Category already exists for this restaurant.'], 400);
        }

        $category = Category::create([
            'name' => $request->name,
            'restaurant_id' => $restaurant_id,
        ]);

        return response()->json($category, 201);
    }

    // READ All Categories (Public)
    public function index(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id');

        $query = Category::with('restaurant');

        if ($restaurant_id) {
            $query->where('restaurant_id', $restaurant_id);
        }

        $categories = $query->get();

        return response()->json($categories);
    }

    // READ Single Category (Public)
    public function show($id)
    {
        $category = Category::with('restaurant')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    // UPDATE Category
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Hanya pemilik restoran atau super admin yang bisa mengupdate
        if ($user->role == 'restaurant_admin' && $category->restaurant_id != $user->restaurant_id) {
            return response()->json(['message' => 'Forbidden: You can only update your own categories.'], 403);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'restaurant_id' => 'required_if:user.role,super_admin|exists:restaurants,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tentukan restaurant_id
        $restaurant_id = $user->role == 'restaurant_admin' ? $user->restaurant_id : ($request->restaurant_id ?? $category->restaurant_id);

        // Cek apakah nama kategori sudah ada
        if ($request->name) {
            $existing = Category::where('name', $request->name)
                ->where('restaurant_id', $restaurant_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Another category with the same name already exists for this restaurant.'], 400);
            }
            $category->name = $request->name;
        }

        $category->restaurant_id = $restaurant_id;
        $category->save();

        return response()->json($category);
    }

    // DELETE Category
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Hanya pemilik restoran atau super admin yang bisa menghapus
        if ($user->role == 'restaurant_admin' && $category->restaurant_id != $user->restaurant_id) {
            return response()->json(['message' => 'Forbidden: You can only delete your own categories.'], 403);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
