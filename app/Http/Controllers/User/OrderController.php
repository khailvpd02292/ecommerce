<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    protected $cart;
    protected $cartItem;
    protected $order;
    protected $orderItem;

    public function __construct(
        Cart $cart,
        CartItem $cartItem,
        Order $order,
        OrderItem $orderItem
    ) {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->order = $order;
        $this->orderItem = $orderItem;
    }

    public function store(Request $request) {
        try {
            
            DB::beginTransaction();

            $user = auth('users')->user();
            $carts = $this->cart->getCart($user->id);

            $payment_method = $request->payment_method ?? 'COD';

            $order = $this->order->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'total' => $carts->total_price,
                'payment_method' => $payment_method,
                'payment_date' => now(),
            ]);

            foreach ($carts->cartItem as  $item) {
                $this->orderItem->create([
                    'product_id' => $item->product_id,
                    'order_id' => $order->id,
                    'product_name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ]);
            }

            $cartItem = $this->cartItem->where('cart_id', $carts->id)->get();

            foreach ($cartItem as $value) {
                $value->delete();
            }

            $carts->delete();

            DB::commit();

            return $this->sendSuccessResponse(null, __('app.transaction_success'));

        } catch (\Exception $e) {

            DB::rollBack();
            
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
