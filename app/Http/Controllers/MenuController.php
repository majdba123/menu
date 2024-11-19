<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // You can set the number of items per page
        $perPage = $request->input('per_page', 10); // Default to 10 items per page

        // Retrieve paginated menus
        $menus = Menu::paginate($perPage);

        // Return menus as JSON, including pagination metadata
        return response()->json($menus);
    }


    public function all_category_of_menu($menu_id)
    {
        // Validate that the menu_id exists in the menus table
        if (!Menu::find($menu_id)) {
            return response()->json(['error' => 'Menu not found.'], 404);
        }

        // Retrieve categories associated with the specified menu_id
        $categories = Category::where('menu_id', $menu_id)->get();

        // Check if categories are found
        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No categories found for this menu.'], 404);
        }

        // Return the categories as a JSON response
        return response()->json($categories);
    }



    public function menu_with_categories($menu_id)
    {
        // Validate that the menu_id exists in the menus table
        $menu = Menu::with(['categories.items', 'categories.subcategories.items', 'categories.subcategories.children.items'])
                    ->find($menu_id);

        if (!$menu) {
            return response()->json(['error' => 'Menu not found.'], 404);
        }

        // Transform the menu data
        $formattedMenu = $this->transformMenu($menu);

        // Return the structured menu as a JSON response
        return response()->json($formattedMenu);
    }

    private function transformMenu($menu)
    {
        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'type' => $menu->type,
            'created_at' => $menu->created_at,
            'updated_at' => $menu->updated_at,
            'categories' => $menu->categories->map(function ($category) {
                return $this->transformCategory($category);
            }),
        ];
    }

    private function transformCategory($category)
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->discryption, // Corrected spelling

            'items' => $category->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                ];
            }),
            'subcategories' => $category->subcategories->map(function ($subcategory) {
                return $this->transformSubcategory($subcategory);
            }),
        ];
    }

    private function transformSubcategory($subcategory)
    {
        return [
            'id' => $subcategory->id,
            'name' => $subcategory->name,
            'level' => $subcategory->level,
            'category_id' => $subcategory->category_id,
            'parent_id' => $subcategory->parent_id,
            'items' => $subcategory->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                ];
            }),
            'children' => $subcategory->children->map(function ($child) {
                return $this->transformSubcategory($child);
            }),
        ];
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:breakfast,lunch,dinner',
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        // Create a new menu
        $menu = menu::create($request->all());
        // Return the created menu as JSON with a 201 status code
        return response()->json($menu, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(menu $menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(menu $menu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $menu_id)
    {
        // Find the menu item by ID
        $menu = menu::find($menu_id);
        // Check if the menu item exists
        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255', // Make name nullable
            'type' => 'nullable|string|in:breakfast,lunch,dinner', // Make type nullable
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        // Update the menu with the request data
        // Only update fields that are present in the request
        $menu->update($request->only(['name', 'type']));
        // Return the updated menu as JSON
        return response()->json($menu, 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($menu_id)
    {
        // Find the menu item by ID
        $menu = menu::find($menu_id);
        // Check if the menu item exists
        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }
        // Delete the menu item
        $menu->delete();
        // Return a success response
        return response()->json(['message' => 'Menu deleted successfully'], 200);
    }
}
