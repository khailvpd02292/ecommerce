<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'pre_order_id',
        'total',
        'status',
        'payment_method',
        'payment_date',
    ];

    public function orderItems() {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function detail($id) {
        $result = Order::with(['orderItems', 'orderItems.product'])->where('id', $id)->first();

        return $result;
    }
}
