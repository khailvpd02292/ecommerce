<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Product;

class CartItem extends Model
{
    use HasFactory;
    protected $table = 'cart_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'cart_id',
        'price',
        'quantity',
    ];

    public function cart() {
        
        return $this->hasOne(Cart::class,  'cart_id', 'id');
    }

    public function product() {
        
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function getProduct() {
        return $this->select('*')->join('products', 'cart_items.product_id', '=', 'products.id')->first();
    }

    public function getCart() {
        return $this->select('carts.*')->join('carts', 'cart_items.cart_id', '=', 'carts.id')->first();
    }
}
