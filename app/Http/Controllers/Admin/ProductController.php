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

        return $this->sendSuccessResponse($products);

    }

    public function show($id)
    {

        try {

            $product = Product::with('category')->where('id', $id)->first();

            if ($product) {

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
            $path = NULL;
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:500',
                'description' => 'nullable|max:500',
                'price' => 'required|numeric|between:0,9999999999',
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


            $this->product->create($requestProduct);
            DB::commit();

            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.product')]));

        } catch (\Exception $e) {

            DB::rollBack();
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
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
                'image' => 'nullable|mimes:jpg,jpeg,png|max:500',
                'description' => 'nullable|max:500',
                'price' => 'required|numeric|between:0,9999999999',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $product = Product::find($id);
            if (empty($product)) {

                return $this->sendError(__('app.not_found', ['attribute' => __('app.product')]), Response::HTTP_NOT_FOUND);
            }

            if($request->is_delete !== 1) {
                $requestProduct = $request->except(['image']);
            } else {
                $requestProduct = $request->all();
            }
           
            if ($request->hasFile('image') && $request->is_delete == 1) {
                $path = $this->uploadFile($request->image);

                $requestProduct = array_merge($requestProduct, [
                    'image' => $path
                ]);
            } else if (empty($request->hasFile('image')) && $request->is_delete == 1) {

                $requestProduct = array_merge($requestProduct, [
                    'image' => ''
                ]);
            }

            $product->update($requestProduct);

            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.product')]));

        } catch (\Exception $e) {

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id, Request $request)
    {

        try {

            $product = Product::find($id);

            if ($product) {

                $product->update([
                    'product_status_id' => 0
                ]);
                return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.product')]));

            } else {

                return $this->sendError(__('app.not_found', ['attribute', __('app.product')]), Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {

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
