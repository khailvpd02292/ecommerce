<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'created_at',
        'updated_at',
        'phone',
        'address',
        'avatar',
        'sex',
        'account_status_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    public function getInfo($id)
    {

        $user = User::with([
            'order' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'order.orderItems', 'order.orderItems.product'])->where('id', $id)->first();

        if (!empty($user)) {

            $ids = [];

            foreach ($user->order as $order) {

                if ($order->orderItems) {

                    foreach ($order->orderItems as $orderItem) {

                        if ($orderItem->product->image) {

                            if (!in_array($orderItem->product->id, $ids)) {

                                array_push($ids, $orderItem->product->id);

                                $orderItem->product->image = config('app.url') . '/storage/' . $orderItem->product->image;
                            }
                        }
                    }
                }
            }

        }

        return $user;
    }
}
