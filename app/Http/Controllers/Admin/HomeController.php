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

        if(count($totalOrder) <= 0) {
            $totalOrder = [
                [
                    "year" => 0 
                ]
            ];
        }

        $totalMoney = DB::table('orders')
            ->select(DB::raw('YEAR(payment_date) year, MONTH(payment_date) month'), DB::raw('SUM(total) as total'), 'status')
            ->groupby('year', 'month', 'status')
            ->having('status', 2)
            ->orderBy('month', 'asc')
            ->get();

        if(count($totalMoney) <= 0) {
            $totalMoney = [
                [
                    "year" => 0 
                ]
            ];
        }

        $totalProfit = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select('orders.status', DB::raw('YEAR(order_items.created_at) year, MONTH(order_items.created_at) month'), DB::raw('SUM(order_items.quantity * order_items.price) as total_price'), DB::raw('SUM(order_items.quantity * order_items.pre_tax_price) as total_pre_tax_price'))
            ->groupby('year', 'month', 'orders.status')
            ->having('orders.status', 2)
            ->orderBy('month', 'asc')
            ->get();

        if(count($totalProfit) <= 0) {
            $totalProfit = [
                [
                    "year" => 0 
                ]
            ];
        }


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
                if (isset($value->year) && ($value->year == date('Y'))) {

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
                if (isset($value->year) && ($value->year == date('Y'))) {

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
            foreach ($totalProfit as  $value) {

                $exitMonth = false;
                
                if(empty($month)) {
                    $month = $item;
                }
                if (isset($value->year) && ($value->year == date('Y'))) {

                    if ($value->month == ($key + 1)) {
                        
                        array_push($data['total_product'], 
                        [
                            $item => ($value->total_price - $value->total_pre_tax_price )
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
