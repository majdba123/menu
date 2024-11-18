<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name' ,'discryption'];


    public function subcategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function items()
    {
        return $this->morphMany(Item::class, 'itemable');
    }

    public function discounts()
    {
        return $this->morphMany(DisCount::class, 'discountable');
    }
}
