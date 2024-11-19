<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DisCount;
use App\Models\Item;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class DisCountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function addDiscountToCategory(Request $request, $categoryId)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        // Find the category by ID
        $category = Category::findOrFail($categoryId);

        if ($category->discounts()->exists()) {
            return response()->json([
                'error' => 'This category already has a discount.'
            ], 422);
        }
        // Create a new discount and associate it with the category
        $discount = new DisCount([
            'amount' => $request->amount,
        ]);
        // Use the polymorphic relationship to associate the discount with the category
        $category->discounts()->save($discount);
        // Return a response
        return response()->json($discount, 201);
    }

    public function addDiscountToSubCategory(Request $request, $subCategoryId)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        // Find the subcategory by ID
        $subCategory = SubCategory::findOrFail($subCategoryId);

        // Check if the subcategory already has a discount
        if ($subCategory->discounts()->exists()) {
            return response()->json([
                'error' => 'This subcategory already has a discount.'
            ], 422);
        }

        // Create a new discount and associate it with the subcategory
        $discount = new DisCount([
            'amount' => $request->input('amount'),
        ]);

        // Use the polymorphic relationship to associate the discount with the subcategory
        $subCategory->discounts()->save($discount);

        // Return a response
        return response()->json($discount, 201);
    }



    public function addDiscountToItem(Request $request, $itemId)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        // Find the item by ID
        $item = Item::findOrFail($itemId);

        // Check if the item already has a discount
        if ($item->discounts()->exists()) {
            return response()->json([
                'error' => 'This item already has a discount.'
            ], 422);
        }


        // Create a new discount and associate it with the item
        $discount = new DisCount([
            'name' => $request->name, // Include the name field
            'amount' => $request->amount,
        ]);


        // Use the polymorphic relationship to associate the discount with the item
        $item->discounts()->save($discount);

        // Return a response
        return response()->json($discount, 201);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getDiscounted(Request $request)
    {
        // Validate the 'type' parameter
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:1,2,3', // Ensure 'type' is required and must be one of 1, 2, or 3
        ]);
        // If validation fails, return a 422 response with the validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = $request->input('type'); // Get the validated type from the request
        switch ($type) {
            case 1:
                // Get all items with discounts
                $items = Item::with('discounts')->whereHas('discounts')->get();
                return response()->json($items);
            case 2:
                // Get all categories with discounts
                $categories = Category::with('discounts', 'subcategories.discounts')->whereHas('discounts')->get();
                return response()->json($categories);
            case 3:
                // Get all subcategories with discounts
                $subcategories = Subcategory::with('discounts')->whereHas('discounts')->get();
                return response()->json($subcategories);
            default:
                return response()->json(['message' => 'Invalid type'], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DisCount $disCount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DisCount $disCount)
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
                'amount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        // Find the DisCount instance by ID
        $discount = DisCount::findOrFail($id);
        // Update the DisCount instance with the validated data
        $discount->update($request->only(['amount']));
        // Optionally, return a response
        return response()->json([
            'message' => 'Discount updated successfully',
            'data' => $discount,
        ]);
    }


    public function destroy($id)
    {
        // Find the DisCount instance by ID
        $discount = DisCount::findOrFail($id);
        // Delete the DisCount instance
        $discount->delete();

        // Optionally, return a response
        return response()->json([
            'message' => 'Discount deleted successfully',
        ]);
    }
}
