<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    protected $cart;
    protected $cartItem;
    protected $order;
    protected $orderItem;
    protected $product;

    public function __construct(
        Cart $cart,
        CartItem $cartItem,
        Order $order,
        OrderItem $orderItem,
        Product $product
    ) {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->order = $order;
        $this->orderItem = $orderItem;
        $this->product = $product;
    }

    public function cancelOrder(Request $request, $id) {
        try {
            
            DB::beginTransaction();

            $order = $this->order->where('id', $id)->first();

            $validator = Validator::make($request->all(), [
                'status' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if ($order->status == 0 || $order->status == 1) {

                $orderItems = $this->orderItem->getOrderItemById($id);

                foreach ($orderItems as $items) {
                    $product = $this->product->where('id', $items->product->id)->first();
                    $product->update([
                        'quantity' => ($items->product->quantity + $items->quantity)
                    ]);
                }
                
                if ($request->status == 3) {
                    if ($order->status == 0) {
                        if (isset($request->reason)) {

                            $order->update([
                                'status' => 3,
                                'reason' => $request->reason,
                                'cancel_by' => 0,
                            ]);
                        } else {
        
                            $order->update([
                                'status' => 3,
                                'cancel_by' => 0,
                            ]);
                        }
                    } else {

                        logger('không thể hủy đơn hàng khi status hiện tại không bằng 0');
                        return $this->sendSuccessResponse(null, __('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.order')]));
                    }
                } else if ($request->status == 2) {

                    if ($order->status == 1) {
                        $order->update([
                            'status' => 2
                        ]);
                    } else {

                        logger('không thể cập nhật status khi status hiện tại không bằng 1');
                        return $this->sendSuccessResponse(null, __('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.order')]));
                    }
                }

                DB::commit();

                return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.order')]));
                
            } else {

                return $this->sendSuccessResponse(null, __('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.order')]));
            }

        } catch (\Exception $e) {

            DB::rollBack();
            
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

                $product = $this->product->where('id', $item->product_id)->first();

                if ($product) {

                    $this->orderItem->create([
                        'product_id' => $item->product_id,
                        'order_id' => $order->id,
                        'product_name' => $item->product->name,
                        'price' => $item->price,
                        'pre_tax_price' => $product->pre_tax_price,
                        'quantity' => $item->quantity,
                    ]);

                    $stock = $product->quantity - $item->quantity;
                    if ($stock <= 0) {
                        $stock = 0;
                    }

                    $product->update([
                        'quantity'  => $stock
                    ]);

                } else {

                    DB::rollBack();
            
                    return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }

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
