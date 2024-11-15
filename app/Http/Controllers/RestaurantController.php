<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class RestaurantController extends Controller
{
    // Middleware konstruksi
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    // CREATE Restaurant (Super Admin)
    public function store(Request $request)
    {
        if (Gate::denies('isSuperAdmin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // Maks 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads', 'public');
            $imagePath = url(Storage::url($imagePath));
        }

        $restaurant = Restaurant::create([
            'name' => $request->name,
            'address' => $request->address,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json($restaurant, 201);
    }

    // READ All Restaurants (Public)
    public function index()
    {
        $restaurants = Restaurant::all();
        return response()->json($restaurants);
    }

    // READ Single Restaurant (Public)
    public function show($id)
    {
        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }
        return response()->json($restaurant);
    }

    // UPDATE Restaurant (Super Admin)
    public function update(Request $request, $id)
    {
        if (Gate::denies('isSuperAdmin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // Maks 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Hapus gambar lama jika ada gambar baru
        if ($request->hasFile('image')) {
            if ($restaurant->image) {
                $oldImage = str_replace(url('/storage/'), '', $restaurant->image);
                Storage::disk('public')->delete($oldImage);
            }
            $imagePath = $request->file('image')->store('uploads', 'public');
            $restaurant->image = url(Storage::url($imagePath));
        }

        $restaurant->name = $request->name;
        $restaurant->address = $request->address;
        $restaurant->description = $request->description;
        $restaurant->save();

        return response()->json($restaurant);
    }

    // DELETE Restaurant (Super Admin)
    public function destroy($id)
    {
        if (Gate::denies('isSuperAdmin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $restaurant = Restaurant::find($id);
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        // Hapus gambar jika ada
        if ($restaurant->image) {
            $oldImage = str_replace(url('/storage/'), '', $restaurant->image);
            Storage::disk('public')->delete($oldImage);
        }

        $restaurant->delete();
        return response()->json(['message' => 'Restaurant deleted successfully']);
    }
}
