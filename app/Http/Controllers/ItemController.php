<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function addItemToCategory(Request $request, $categoryId)
    {
        // Validate the request data


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        // Find the category by ID
        $category = Category::findOrFail($categoryId);

        if ($category->subcategories()->exists()) {
            return response()->json([
                'error' => 'Sorry, this category  has subcategories.'
            ], 422);
        }

        // Create a new item and associate it with the category
        $item = new Item([
            'name' => $request->name,
            'price' => $request->price,
        ]);
        // Use the polymorphic relationship to associate the item with the category
        $category->items()->save($item);
        // Return a response
        return response()->json($item, 201);
    }




    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    public function addItemToSubCategory(Request $request, $subCategoryId)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        // Find the subcategory by ID
        $subCategory = SubCategory::findOrFail($subCategoryId);

        if ($subCategory->children()->exists()) {
            return response()->json([
                'error' => 'Sorry, this subCategory  has subcategories.'
            ], 422);
        }

        $item = new Item([
            'name' => $request->name,
            'price' => $request->price,
        ]);


        // Use the polymorphic relationship to associate the item with the subcategory

        $subCategory->items()->save($item);

        // Return a response

        return response()->json($item, 201);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        // Find the Item instance by ID
        $item = Item::findOrFail($id);
        // Update the Item instance with the validated data
        $item->update($request->only(['name', 'price']));
        // Optionally, return a response
        return response()->json([
            'message' => 'Item updated successfully',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        // Find the Item instance by ID
        $item = Item::findOrFail($id);
        // Delete the Item instance
        $item->delete();
        // Optionally, return a response
        return response()->json([
            'message' => 'Item deleted successfully',
        ]);
    }

    public function getItemsByCategoryOrSubcategory(Request $request)
    {
        // Validate the request parameters
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:1,2', // 1 for category, 2 for subcategory
            'id' => 'sometimes|integer',   // Optional ID for specific category or subcategory
        ]);
        // If validation fails, return a 422 response with the validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $type = $request->input('type');
        $id = $request->input('id');
        switch ($type) {
            case 1:
                // If an ID is provided, get items of that specific category
                if ($id) {
                    $items = Item::where('itemable_type', Category::class)
                                 ->where('itemable_id', $id)
                                 ->with('discounts')
                                 ->get();
                } else {
                    // Get all items of all categories
                    $items = Item::where('itemable_type', Category::class)
                                 ->with('discounts')
                                 ->get();
                }
                return response()->json($items);

            case 2:
                // If an ID is provided, get items of that specific subcategory
                if ($id) {
                    $items = Item::where('itemable_type', Subcategory::class)
                                 ->where('itemable_id', $id)
                                 ->with('discounts')
                                 ->get();
                }
                return response()->json($items);
            default:
                return response()->json(['message' => 'Invalid type'], 400);
        }
    }

    public function getItemsByCategoryOrSubcategory_all(Request $request)
    {

        // Validate the request parameters

        $validator = Validator::make($request->all(), [

            'type' => 'required|in:1,2', // 1 for category, 2 for subcategory

            // No need for 'id' since we're getting all items

        ]);


        // If validation fails, return a 422 response with the validation errors

        if ($validator->fails()) {

            return response()->json(['errors' => $validator->errors()], 422);

        }


        $type = $request->input('type');


        switch ($type) {

            case 1:

                // Get all items of all categories

                $items = Item::where('itemable_type', Category::class)->with('discounts')->get();

                return response()->json($items);


            case 2:

                // Get all items of all subcategories

                $items = Item::where('itemable_type', Subcategory::class)->with('discounts')->get();

                return response()->json($items);


            default:

                return response()->json(['message' => 'Invalid type'], 400);

        }

    }
}
