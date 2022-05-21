<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total_price'
    ];

    
    public function getCart($user_id) {
        $result = $this->select('*', 'cart_items.*')
                        ->join('cart_items', 'cart.id', '=', 'cart_items.cart_id')
                        ->where('user_id' , $user_id)
                        ->orderBy('updated_at', 'desc')
                        ->get();

        return $result;
    }
}
