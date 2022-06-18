<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportOrder;
use Validator;
use Symfony\Component\HttpFoundation\Response;


class ExportController extends BaseController
{

    public function export(Request $request) {

        if (isset($request->status)) {
            $validator = Validator::make($request->all(), [
                'status' => 'required|integer|min:0|max:3',
            ]);

            if ($validator->fails()) {

                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }
        }

        return Excel::download(new ExportOrder($request->status), date('Ymd_Hsi').'_order.xlsx');
    }
}
