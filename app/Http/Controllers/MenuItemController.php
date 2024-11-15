<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class MenuItemController extends Controller
{
    // Middleware konstruksi
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    // CREATE Menu Item
    public function store(Request $request)
    {
        $user = $request->user();

        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'restaurant_id' => 'required_if:user.role,super_admin|exists:restaurants,id',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tentukan restaurant_id
        $restaurant_id = $user->role == 'restaurant_admin' ? $user->restaurant_id : $request->restaurant_id;

        // Cek kategori milik restoran
        $category = Category::where('id', $request->category_id)
            ->where('restaurant_id', $restaurant_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Invalid category_id or category does not belong to your restaurant.'], 400);
        }

        // Upload gambar
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads', 'public');
            $imagePath = url(Storage::url($imagePath));
        }

        $menuItem = MenuItem::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'category_id' => $request->category_id,
            'restaurant_id' => $restaurant_id,
        ]);

        return response()->json($menuItem, 201);
    }

    // READ Menu Items (Public)
    public function index(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id');

        $query = MenuItem::with(['category', 'restaurant']);

        if ($restaurant_id) {
            $query->where('restaurant_id', $restaurant_id);
        }

        $menuItems = $query->get();

        return response()->json($menuItems);
    }

    // READ Single Menu Item (Public)
    public function show($id)
    {
        $menuItem = MenuItem::with(['category', 'restaurant'])->find($id);

        if (!$menuItem) {
            return response()->json(['message' => 'Menu item not found'], 404);
        }

        return response()->json($menuItem);
    }

    // UPDATE Menu Item
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json(['message' => 'Menu item not found'], 404);
        }

        // Hanya pemilik restoran atau super admin yang bisa mengupdate
        if ($user->role == 'restaurant_admin' && $menuItem->restaurant_id != $user->restaurant_id) {
            return response()->json(['message' => 'Forbidden: You can only update your own menu items.'], 403);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'restaurant_id' => 'required_if:user.role,super_admin|exists:restaurants,id',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tentukan restaurant_id
        $restaurant_id = $user->role == 'restaurant_admin' ? $user->restaurant_id : ($request->restaurant_id ?? $menuItem->restaurant_id);

        // Cek kategori jika diubah
        if ($request->category_id) {
            $category = Category::where('id', $request->category_id)
                ->where('restaurant_id', $restaurant_id)
                ->first();

            if (!$category) {
                return response()->json(['message' => 'Invalid category_id or category does not belong to your restaurant.'], 400);
            }
            $menuItem->category_id = $request->category_id;
        }

        // Upload gambar baru jika ada
        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                $oldImage = str_replace(url('/storage/'), '', $menuItem->image);
                Storage::disk('public')->delete($oldImage);
            }
            $imagePath = $request->file('image')->store('uploads', 'public');
            $menuItem->image = url(Storage::url($imagePath));
        }

        // Update data lain
        $menuItem->name = $request->name ?? $menuItem->name;
        $menuItem->price = $request->price ?? $menuItem->price;
        $menuItem->description = $request->description ?? $menuItem->description;
        $menuItem->restaurant_id = $restaurant_id;

        $menuItem->save();

        return response()->json($menuItem);
    }

    // DELETE Menu Item
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json(['message' => 'Menu item not found'], 404);
        }

        // Hanya pemilik restoran atau super admin yang bisa menghapus
        if ($user->role == 'restaurant_admin' && $menuItem->restaurant_id != $user->restaurant_id) {
            return response()->json(['message' => 'Forbidden: You can only delete your own menu items.'], 403);
        }

        // Hapus gambar jika ada
        if ($menuItem->image) {
            $oldImage = str_replace(url('/storage/'), '', $menuItem->image);
            Storage::disk('public')->delete($oldImage);
        }

        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
    }
}
