<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'description',
        'price',
        'is_stock',
        'product_status_id', // 0: draft, 1: public
        'category_id',
    ];

    public function category() {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}