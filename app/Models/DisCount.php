<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisCount extends Model
{
    use HasFactory;

    protected $fillable = ['name' ,'amount'];

    public function discountable()
    {
        return $this->morphTo();
    }
}
