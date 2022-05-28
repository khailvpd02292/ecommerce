<?php

namespace App\Models;

use App\Models\CartItem;
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
        'total_price',
    ];

    public function cartItem()
    {

        return $this->hasMany(CartItem::class);
    }

    public function getCart($user_id)
    {

        $result = Cart::with(['cartItem', 'cartItem.product'])->where('user_id', $user_id)
            ->orderBy('updated_at', 'desc')
            ->first();

        return $result;
    }
}
