<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class OrderController extends BaseController
{

    protected $order;
    protected $orderItem;
    protected $product;

    public function __construct(
        Order $order,
        OrderItem $orderItem,
        Product $product
    ) {
        $this->order = $order;
        $this->orderItem = $orderItem;
        $this->product = $product;
    }

    public function index(Request $request)
    {

        try {
            if (isset($request->status)) {
                $validator = Validator::make($request->all(), [
                    'status' => 'required|integer|min:0|max:3',
                ]);

                if ($validator->fails()) {
                    return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
                }

                $orders = $this->order->getOrderByStatus($request->status);

            } else {

                $orders = Order::with(['orderItems', 'orderItems.product'])->get();
            }

            return $this->sendSuccessResponse($orders, null);
        } catch (\Exception$e) {

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function show($id)
    {
        $order = $this->order->detail($id);

        if (empty($order)) {

            return $this->sendError(__('app.not_found', ['attribute' => __('app.order')]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendSuccessResponse($order, null);
    }

    public function update(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $order = $this->order->where('id', $id)->first();

            $validator = Validator::make($request->all(), [
                'status' => 'required|integer|min:0|max:3',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (($order->status < 2 && $request->status != $order->status)) {

                if ($request->status == 3) {

                    $validator = Validator::make($request->all(), [
                        'reason' => 'required|max:500',
                    ]);
                    if ($validator->fails()) {
                        return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
                    }

                    $orderItems = $this->orderItem->getOrderItemById($id);

                    foreach ($orderItems as $items) {
                        $product = $this->product->where('id', $items->product->id)->first();
                        $product->update([
                            'quantity' => ($items->product->quantity + $items->quantity),
                        ]);
                    }
                    $order->update([
                        'status' => 3,
                        'reason' => $request->reason,
                        'cancel_by' => 1,
                    ]);

                } else {

                    $order->update([
                        'status' => $request->status,
                    ]);
                }

                DB::commit();

                return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.order')]));

            } else {

                return $this->sendSuccessResponse(null, __('app.action_failed', ['action' => __('app.update'), 'attribute' => __('app.order')]));
            }

        } catch (\Exception$e) {

            DB::rollBack();

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
