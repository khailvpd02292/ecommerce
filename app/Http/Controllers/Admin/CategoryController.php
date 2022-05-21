<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends BaseController
{

    protected $category;

    public function __construct(
        Category $category
    ) {
        $this->category = $category;
    }

    public function index() {

        $categories = Category::all();

        return $this->sendSuccessResponse($categories);

    }

    public function show($id) {

        try {

            $category = Category::find($id);

            if ($category) {
                
                return $this->sendSuccessResponse($category);
            } else {
                
                return $this->sendError(__('app.not_found', ['attribute' => __('app.category')]), Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {

            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($id, Request $request) {

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
            ]);
     
            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }
    
            $category = Category::find($id);

            if (empty($category)) {

                return $this->sendError(__('app.not_found', ['attribute' => __('app.category')]), Response::HTTP_NOT_FOUND);
            }

            $category->update($request->all());
            DB::commit();

            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.update'), 'attribute' => __('app.category')]));

        } catch (\Exception $e) {

            DB::rollBack();
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request) {

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:200',
            ]);
     
            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }
    
            
            $this->category->create($request->all());
            DB::commit();

            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.create'), 'attribute' => __('app.category')]));

        } catch (\Exception $e) {

            DB::rollBack();
            logger($e->getMessage());
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id, Request $request) {

        try {
            DB::beginTransaction();
            $category = Category::find($id);

            if ($category) {

                $category->delete();
                DB::commit();
                return $this->sendSuccessResponse(null, __('app.action_success', ['action' => __('app.delete'), 'attribute' => __('app.category')]));

            } else {
                
                return $this->sendError(__('app.not_found', ['attribute', __('app.category')]), Response::HTTP_NOT_FOUND);
            }


        } catch (\Exception $e) {

            DB::rollBack();
            logger($e->getMessage());
            return $this->sendSuccessResponse(null, __('app.action_failed', ['action' => __('app.delete'), 'attribute' => __('app.category')]));
        }
    }

}
