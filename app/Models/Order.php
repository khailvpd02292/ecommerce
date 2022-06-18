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
        'reason',
        'cancel_by',
        'payment_date',
    ];

    public function orderItems() {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function detail($id) {
        $result = Order::with(['orderItems', 'orderItems.product'])->where('id', $id)->first();

        return $result;
    }

    public function getOrderByStatus($status) {
        $result = Order::with(['orderItems', 'orderItems.product'])->where('status', $status)->orderBy('created_at', 'desc')->get();

        return $result;
    }
}
