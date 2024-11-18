<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category_id', 'parent_id' ,'level'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function parent()
    {
        return $this->belongsTo(SubCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SubCategory::class, 'parent_id');
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
