<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends BaseController
{
    public function index() {

        $totalOrder = DB::table('orders')
            ->select(DB::raw('YEAR(payment_date) year, MONTH(payment_date) month'), DB::raw('count(*) as total'))
            ->groupby('year','month')
            ->orderBy('month', 'asc')
            ->get();

        $totalMoney = DB::table('orders')
            ->select(DB::raw('YEAR(payment_date) year, MONTH(payment_date) month'), DB::raw('SUM(total) as total'))
            ->groupby('year','month')
            ->orderBy('month', 'asc')
            ->get();

        $totalProduct = DB::table('order_items')
            ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(quantity) as total'))
            ->groupby('year','month')
            ->orderBy('month', 'asc')
            ->get();


        $listMonth = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        $data = [
            "total_order" => [],
            "total_money" => [],
            "total_product" => [],
        ];

        foreach ($listMonth as $key => $item) {
            
            $month = null;
            foreach ($totalOrder as  $value) {

                $exitMonth = false;
                
                if(empty($month)) {
                    $month = $item;
                }
                if ($value->year == date('Y')) {

                    if ($value->month == ($key + 1)) {
                        
                        array_push($data['total_order'], 
                        [
                            $item => $value->total
                        ]);

                        $exitMonth = true;
                        break;

                    } 

                }

            }


            if (isset($exitMonth) && !$exitMonth) {

                array_push($data['total_order'], 
                    [
                        $month => 0
                    ]);
            }
           
        }

        foreach ($listMonth as $key => $item) {
            
            $month = null;
            foreach ($totalMoney as  $value) {

                $exitMonth = false;
                
                if(empty($month)) {
                    $month = $item;
                }
                if ($value->year == date('Y')) {

                    if ($value->month == ($key + 1)) {
                        
                        array_push($data['total_money'], 
                        [
                            $item => $value->total
                        ]);

                        $exitMonth = true;
                        break;

                    } 

                }

            }


            if (isset($exitMonth) && !$exitMonth) {

                array_push($data['total_money'], 
                    [
                        $month => 0
                    ]);
            }
           
        }

        foreach ($listMonth as $key => $item) {
            
            $month = null;
            foreach ($totalProduct as  $value) {

                $exitMonth = false;
                
                if(empty($month)) {
                    $month = $item;
                }
                if ($value->year == date('Y')) {

                    if ($value->month == ($key + 1)) {
                        
                        array_push($data['total_product'], 
                        [
                            $item => $value->total
                        ]);

                        $exitMonth = true;
                        break;

                    } 

                }

            }


            if (isset($exitMonth) && !$exitMonth) {

                array_push($data['total_product'], 
                    [
                        $month => 0
                    ]);
            }
           
        }


        return $this->sendSuccessResponse($data, null);
    }
}
