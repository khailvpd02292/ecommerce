<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{

    protected $order;

    public function __construct(
        Order $order
    ) {
        $this->order = $order;
    }

    public function index(Request $request) {

        $orders = Order::all();

        return $this->sendSuccessResponse($orders, null);

    }


    public function update(Request $request, $id) {
        try {
            
            DB::beginTransaction();

            $order = $this->order->where('id', $id)->first();

            if ($order->status == 0) {
                $order->update([
                    'status' => 1
                ]);

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
}
