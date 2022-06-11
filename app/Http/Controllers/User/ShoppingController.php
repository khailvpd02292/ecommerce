<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class ShoppingController extends BaseController
{
    protected $cart;
    protected $cartItem;
    protected $product;

    public function __construct(
        Cart $cart,
        CartItem $cartItem,
        Product $product
    ) {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->product = $product;
    }

    public function index()
    {

        try {

            $user_id = auth('users')->user()->id;
            $carts = $this->cart->getCart($user_id);
            if ($carts) {
                foreach ($carts->cartItem as $item) {

                    if ($item->product->product_status_id == 0 || $item->product->quantity == 0) {
                        $this->destroy($item->id);
                    }

                    if ($item->product->quantity < $item->quantity) {

                        $prod = $this->product->where('id', $item->product_id)->first();

                        if ($prod) {

                            $cartIt = $this->cartItem->where('id', $item->id)->first();

                            if ($cartIt) {
                                DB::beginTransaction();

                                $cartIt->update([
                                    "quantity" => $item->product->quantity,
                                ]);

                                DB::commit();
                                $carts = $this->cart->getCart($user_id);

                            }

                        }
                    }
                }
            }

            $total = 0;
            foreach ($carts->cartItem as $item) {
                $total += ($item->price) * ($item->quantity);
            }

            $carts->update([
                'total_price' => $total,
            ]);

            $carts = $this->cart->getCart($user_id);

            return $this->sendSuccessResponse($carts, null);

        } catch (\Exception $e) {
            
            DB::rollBack();

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (empty($request->id_product)) {

                logger('Not found id product');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $product = Product::where('id', $request->id_product)->first();

            if (empty($product)) {

                logger('Not found product');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            } else if ($product->quantity <= 0) {

                return $this->sendError(__('app.sold_out'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $price = $product->price * $request->quantity;

            if ($request->quantity > $product->quantity) {

                return $this->sendError(__('app.sold_out'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::beginTransaction();

            $existCart = $this->cart->where('user_id', auth('users')->user()->id)->first();

            if (empty($existCart)) {
                $cart = $this->cart->create([
                    'user_id' => auth('users')->user()->id,
                    'total_price' => $price,
                ]);

                $this->cartItem->create([
                    'product_id' => $product->id,
                    'cart_id' => $cart->id,
                    'price' => $product->price,
                    'quantity' => $request->quantity,
                ]);
            } else {

                $cartItem = $this->cartItem->where('cart_id', $existCart->id)->where('product_id', $product->id)->first();

                if (empty($cartItem)) {

                    $this->cartItem->create([
                        'product_id' => $product->id,
                        'cart_id' => $existCart->id,
                        'price' => $product->price,
                        'quantity' => $request->quantity,
                    ]);

                } else {

                    $cartItem->update([
                        'price' => $product->price,
                        'quantity' => ($request->quantity) + ($cartItem->quantity),
                    ]);

                }

                $carts = $this->cartItem->where('cart_id', $existCart->id)->get();

                $price = 0;
                foreach ($carts as $value) {
                    $price += $value->quantity * $value->price;
                }
                $existCart->update([
                    'total_price' => $price,
                ]);

            }

            DB::commit();

            return $this->sendSuccessResponse(null, __('app.add_cart_success'));

        } catch (\Exception$e) {

            DB::rollBack();

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (empty($request->id_cart_item)) {

                logger('Not found id cart item');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::beginTransaction();

            $cartItem = $this->cartItem->where('id', $request->id_cart_item)->first();

            if (empty($cartItem)) {

                logger('Not found cart items');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);

            } else {
                $product = $cartItem->getProduct();

                if ($request->quantity > $product->quantity) {

                    return $this->sendError(__('app.sold_out'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                $cartItem->update([
                    'price' => $product->price,
                    'quantity' => $request->quantity,
                ]);

                $cart = $this->cart->where('id', $cartItem->getCart()->id)->first();

                $items = $this->cartItem->where('cart_id', $cart->id)->get();

                $total = 0;
                foreach ($items as $item) {
                    $total += ($item->price) * ($item->quantity);
                }

                $cart->update([
                    'total_price' => $total,
                ]);
            }

            DB::commit();

            return $this->sendSuccessResponse(null, __('app.update_cart_success'));

        } catch (\Exception$e) {

            DB::rollBack();

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {

            if (empty($id)) {

                logger('Not found id cart item');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::beginTransaction();

            $cartItem = $this->cartItem->where('id', $id)->first();

            if (empty($cartItem)) {

                logger('Not found cart items');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);

            }
            $idCart = $cartItem->cart_id;
            $total = ($cartItem->price) * ($cartItem->quantity);
            $cartItem->delete();

            $item = $this->cart->where('id', $idCart)->first();

            if (empty($cartItem)) {
                $this->cart->where('id', $idCart)->first()->delete();
            } else {

                $item->update([
                    'total_price' => ($item->total_price) - $total,
                ]);
            }

            DB::commit();

            return $this->sendSuccessResponse(null, __('app.delete_cart_success'));

        } catch (\Exception$e) {

            DB::rollBack();

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkStock(Request $request)
    {

        try {

            DB::beginTransaction();

            $user = auth('users')->user();
            $carts = $this->cart->getCart($user->id);

            foreach ($carts->cartItem as $item) {

                $product = $this->product->where('id', $item->product_id)->first();

                if ($product) {

                    if ($product->quantity < $item->quantity || $product->quantity == 0) {

                        return $this->sendError(__('app.sold_out'), Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }

            }

            DB::commit();

            return $this->sendSuccessResponse(null, __('app.transaction_success'));

        } catch (\Exception$e) {

            DB::rollBack();

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
