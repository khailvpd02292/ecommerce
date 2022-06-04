<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\CommentProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    protected $product;
    protected $commentProduct;

    public function __construct(
        Product $product,
        CommentProduct $commentProduct,
    ) {
        $this->product = $product;
        $this->commentProduct = $commentProduct;
    }


    public function index(Request $request)
    {

        $products = $this->product->getAll($request);

        foreach ($products as $key => $item) {
            
            if($item->image) {
                $item['image'] = config('app.url').'/storage/'.$item->image;
            }
        }

        return $this->sendSuccessResponse($products);

    }

    public function comment($id, Request $request) {

        try {
            $product = Product::where('id', $id)->first();

            if (!$product) {

                return $this->sendError(__('app.not_found', ['attribute' => __('app.product')]), Response::HTTP_NOT_FOUND);
            } 
           
            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }
    
            $this->commentProduct->create([
                'product_id' => $id,
                'user_id' => auth('users')->user()->id,
                'comment' => $request->comment
            ]);
            DB::commit();

            return $this->sendSuccessResponse(null, __('app.comment_product_success'));

        } catch (\Exception $e) {

            DB::rollBack();
            
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
