<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('discounts') // Replace with actual relationships
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    // Return the paginated discounts as a JSON response
        return response()->json($categories);
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
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discryption' => 'required|string',
            'menu_id' => 'required|exists:menus,id', // Validate that menu_id exists in the menus table
        ]);
        // Handle validation failure
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        // Use a transaction to ensure data integrity
        DB::beginTransaction();
        try {
            // Create the category
            $category = Category::create($request->only('name', 'discryption', 'menu_id'));
            // Commit the transaction
            DB::commit();
            return response()->json($category, 201);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            // Log the error for debugging purposes (optiona)
            // Return a generic error response
            return response()->json(['error' => 'An error occurred while creating the category.'], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id); // Find the category
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'discryption' => 'sometimes|required|string',
        ]);
        $category->update($request->only('name', 'discryption')); // Update the category
        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id); // Find the category
        $category->delete(); // Delete the category
        return response()->json([
            'message' => 'Category deleted successfully.'
        ], 200);
    }


    public function getItems($categoryId)
    {
        try {
            // Retrieve the category with its items
            $category = Category::with('items')->findOrFail($categoryId);
            // Check if the category has items
            if ($category->items->isEmpty()) {
                return response()->json([
                    'message' => 'No items found for this category.',
                ], 404);
            }
            // Map the items to a custom response format
            $customItems = $category->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'discounts' => $this->calculate_discount($item->id),
                ];
            });
            // Return the items in a JSON response
            return response()->json($customItems);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function calculate_discount($item_id)
    {
        // Example logic for calculating discounts
        // You can replace this with your actual discount logic
        $item = Item::find($item_id);

        if (!$item) {
            return null; // or handle the case where the item is not found
        }

        $itemDiscount = $item->discounts()->first();

        if ($itemDiscount) {
            // Assuming the discount amount is a fixed amount that needs to be subtracted from the price
            $discountPercentage = $itemDiscount->amount; // This should be the discount percentage
            // Calculate the discount amount based on the percentage
            $discountAmount = ($discountPercentage / 100) * $item->price;
            // Calculate the final price after applying the discoun
            $finalPrice = $item->price - $discountAmount;

            return $finalPrice;
        }

        $itemable = $item->itemable;
        // Check for category discounts if itemable is a category
        if ($itemable instanceof Category) {
            $categoryDiscount = $itemable->discounts()->first();
            if ($categoryDiscount) {
                $discountPercentage = $categoryDiscount->amount; // This should be the discount percentage
                // Calculate the discount amount based on the percentage
                $discountAmount = ($discountPercentage / 100) * $item->price;
                // Calculate the final price after applying the discoun
                $finalPrice = $item->price - $discountAmount;

                return $finalPrice;
            }
        }

        if ($itemable instanceof SubCategory) {
            // Check for subcategory-level discounts
            $subcategoryDiscount = $itemable->discounts()->first();
            if ($subcategoryDiscount) {
                $discountPercentage = $subcategoryDiscount->amount; // This should be the discount percentage
                // Calculate the discount amount based on the percentage
                $discountAmount = ($discountPercentage / 100) * $item->price;
                // Calculate the final price after applying the discoun
                $finalPrice = $item->price - $discountAmount;

                return $finalPrice;
            }
        }

        return null;
    }

    public function get_subcategory_of_category($category_id)
    {
        // Find the category by its ID
        $category = Category::findOrFail($category_id);
        // Retrieve subcategories
        $subcategories = $category->subcategories()->where('level', 1)->get();
        // Check if subcategories exist
        if ($subcategories->isNotEmpty()) {

            return response()->json($subcategories);
        } else {
            return response()->json([
                'message' => 'No subcategories found for this category.',
            ]);
        }
    }
}
