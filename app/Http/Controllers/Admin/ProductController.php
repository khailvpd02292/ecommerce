<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{

    protected $product;

    public function __construct(
        Product $product
    ) {
        $this->product = $product;
    }


    public function index(Request $request)
    {

        $products = $this->product->getAll($request->all());

        foreach ($products as $key => $item) {
            
            if($item->image) {
                $item['image'] = config('app.url').'/storage/'.$item->image;
            }
        }

        return $this->sendSuccessResponse($products);

    }

    public function show($id)
    {

        try {

            $product = Product::with('category')->where('id', $id)->first();

            if ($product) {

                if ($product->image) {
                    $product['image'] = config('app.url').'/storage/'.$product->image;
                }

                return $this->sendSuccessResponse($product);
            } else {

                return $this->sendError(__('app.not_found', ['attribute' => __('app.product')]), Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function store(Request $request)
    {

        try {
            $stripe = new \Stripe\StripeClient(config('app.stripe'));
            $path = NULL;

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:500',
                'description' => 'nullable|max:500',
                'price' => 'required|numeric|between:0,9999999999',
                'pre_tax_price' => 'required|numeric|between:0,9999999999',
                'quantity' => 'nullable|numeric|between:0,9999999999',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $requestProduct = $request->all();

            if ($request->hasFile('image')) {
                $path = $this->uploadFile($request->image);

                $requestProduct = array_merge($requestProduct, [
                    'image' => $path
                ]);
            }

            $product = $this->product->create($requestProduct);

           
            $requestProduct = [
                "id" => 'prod_'.$product->id,
                'name' => $request->name,
                'description' => $request->description,
                'default_price_data' => [
                  'unit_amount' => $request->price.'00',
                  'currency' => 'usd',
                ],
            ];
            if ($path) {
                $requestProduct = array_merge($requestProduct, [
                    'images' => [
                        config('app.url').'/storage/'.$path
                    ]
                ]);
            }
            $response = $stripe->products->create($requestProduct);
            
            $product->update([
                'api_id' => $response->default_price
            ]);

            DB::commit();

            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.product')]));

        } catch (\Exception $e) {

            DB::rollBack();

            if(isset($product)) {

                try {

                    $stripe->products->update(
                        'prod_'.$product->id,
                        [
                            "active" => false,
                        ]
                      );
                      
                  } catch (\Exception $ex) {
    
                    logger($ex->getMessage());
                  }

               
            }

            if ($path) {
                if (file_exists(public_path('storage/'.$path))) {
                    @unlink(public_path('storage/'.$path));
                }
            }
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id, Request $request)
    {

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:500',
                'description' => 'nullable|max:500',
                'product_status_id' => 'nullable|integer|min:0|max:1',
                'price' => 'required|numeric|between:0,9999999999',
                'pre_tax_price' => 'required|numeric|between:0,9999999999',
                'quantity' => 'nullable|numeric|between:0,9999999999',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $product = Product::where('id', $id)->where('product_status_id', 1)->first();
            if (empty($product)) {

                return $this->sendError(__('app.not_found', ['attribute' => __('app.product')]), Response::HTTP_NOT_FOUND);
            }

            if($request->is_delete !== 1) {
                $requestProduct = $request->except(['image']);
            } else {
                $requestProduct = $request->all();
            }

            if(empty($request->product_status_id)) {

                $status = $request->product_status_id == 1 ? true : false;
            } else {

                $status = $product->product_status_id == 1 ? true : false;
            }

            $requestStripe = [
                'name' => $request->name,
                'description' => $request->description,
            ];

            if ($request->hasFile('image') && $request->is_delete == 1) {
                $path = $this->uploadFile($request->image);

                $requestProduct = array_merge($requestProduct, [
                    'image' => $path
                ]);

                $requestStripe = array_merge($requestStripe, [
                    'images' => [
                        config('app.url').'/storage/'.$path
                    ]
                ]);

            } else if (empty($request->hasFile('image')) && $request->is_delete == 1) {

                $requestProduct = array_merge($requestProduct, [
                    'image' => ''
                ]);

                $requestStripe = array_merge($requestStripe, [
                    'images' => []
                ]);
            }

            $stripe = new \Stripe\StripeClient(config('app.stripe'));
            try {

                $response = $stripe->prices->create([
                    'unit_amount' => $request->price.'00',
                    'currency' => 'usd',
                    'product' => 'prod_'.$product->id,
                  ]);
                if ($response->id) {

                $requestStripe = array_merge($requestStripe, [
                    'default_price' => $response->id
                ]);
                    
                $stripe->products->update('prod_'.$product->id, $requestStripe);

                $requestProduct = array_merge($requestProduct, [
                    'api_id' => $response->id
                ]);
                
                $product->update($requestProduct);
                    
                }

            } catch (\Exception $ex) {

                DB::rollback();
                logger($e->getMessage());
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::commit();
            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.product')]));

        } catch (\Exception $e) {

            DB::rollback();
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id, Request $request)
    {

        try {

            DB::beginTransaction();
            $product = Product::where('id', $id)->where('product_status_id', 1)->first();

            if ($product) {

                $product->update([
                    'product_status_id' => 0
                ]);

              try {

                $stripe = new \Stripe\StripeClient(config('app.stripe'));
                $stripe->products->update(
                    'prod_'.$product->id,
                    [
                        "active" => false,
                    ]
                  );
              } catch (\Exception $ex) {

                logger($ex->getMessage());
              }
              DB::commit();

                return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.product')]));

            } else {

                return $this->sendError(__('app.not_found', ['attribute' => __('app.product')]), Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {

            DB::rollback();
            logger($e->getMessage());
            return $this->sendSuccessResponse(null, __('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.product')]));
        }
    }

    public function uploadFile($file)
    {
        $name = $file->getClientOriginalName();

        $fileName = time() . '_' . $name;

        $pathStorage = 'app/public/products/';

        // UPLOAD IMAGE
        $file->move(storage_path($pathStorage), $fileName);

        $path = 'products/' . $fileName;

        return $path;
    }


}
