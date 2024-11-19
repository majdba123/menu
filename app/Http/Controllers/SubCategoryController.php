<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class SubCategoryController extends Controller
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
    public function store_for_category(Request $request, $category_id)
    {
        // Find the category by ID, or fail if it doesn't exist
        $category = Category::findOrFail($category_id);

        // Check if the category has any items
        if ($category->items->count() > 0) {
            return response()->json([
                'error' => 'Category has items, cannot create subcategory.'
            ], 422);
        }

        // Validate the request data

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sub_categories,name',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        // Create the subcategory using the category_id from the URL
        $subCategory = SubCategory::create([
            'name' => $request->name,
            'category_id' => $category_id, // Use category_id from the URL
            'parent_id' => $request->parent_id ?? null, // Optional: Include parent_id if provided
            'level' => 1, // Optional: Default level if not provided
        ]);
        // Return the created subcategory with a 201 Created status
        return response()->json($subCategory, 201);
    }



    public function store_for_sub_category(Request $request, $sub_category_id)
    {
        // Find the category by ID, or fail if it doesn't exist
        $sub_category = SubCategory::findOrFail($sub_category_id);

        if ($sub_category->level >= 4) {
            return response()->json([
                'error' => 'Cannot create a subcategory under this level; maximum level is 4.'
            ], 422);
        }

        // Check if the category has any items
        if ($sub_category->items->count() > 0) {
            return response()->json([
                'error' => 'sub_category has items, cannot create subcategory.'
            ], 422);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sub_categories,name',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        // Create the subcategory using the category_id from the URL
        $subCategory = SubCategory::create([
            'name' => $request->name,
            'category_id' => $sub_category->category->id, // Use category_id from the URL
            'parent_id' => $sub_category->id, // Optional: Include parent_id if provided
            'level' => $sub_category->level + 1 , // Optional: Default level if not provided
        ]);
        // Return the created subcategory with a 201 Created status
        return response()->json($subCategory, 201);
    }

    public function getItems($SubCategory_ID)
    {
        try {
            // Retrieve the category with its items
            $SubCategory = SubCategory::with('items')->findOrFail($SubCategory_ID);
            // Check if the category has items
            if ($SubCategory->items->isEmpty()) {
                return response()->json([
                    'message' => 'No items found for this SubCategory.',
                ], 404);
            }
            // Map the items to a custom response format



            // Call the calculate_discount method

            $customItems = $SubCategory->items->map(function ($item) {
                $categoryController = new CategoryController();
                $finalPrice = $categoryController->calculate_discount($item->id);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'discounts' => $finalPrice,
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


    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategory $subCategory)
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
            'name' => 'nullable|string|max:255', // Make name nullable
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the SubCategory instance by ID

        $subCategory = SubCategory::findOrFail($id);


        // Update the SubCategory instance with the validated data
        $subCategory->update($request->only(['name']));

        // Optionally, return a response
        return response()->json([
            'message' => 'SubCategory updated successfully',
            'data' => $subCategory,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the SubCategory instance by ID
        $subCategory = SubCategory::findOrFail($id);
        // Delete the SubCategory instanc
        $subCategory->delete();
        // Optionally, return a response
        return response()->json([
            'message' => 'SubCategory deleted successfully',
        ]);
    }
    public function get_subcategory_of_sub_category($subCategiry_id)
    {
        // Find the category by its ID
        $subCategiry_ = SubCategory::findOrFail($subCategiry_id);
        // Retrieve subcategories
        $subcategories = $subCategiry_->children()->where('parent_id',$subCategiry_id)->get();
        // Check if subcategories exist
        if ($subcategories->isNotEmpty()) {

            return response()->json($subcategories);
        } else {
            return response()->json([
                'message' => 'No subcategories found for this sub_category.',
            ]);
        }


    }
}
