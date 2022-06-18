<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportOrder implements FromCollection, WithHeadings, WithColumnFormatting
{

    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if (isset($this->request)) {

            $orders = Order::with(['orderItems', 'orderItems.product'])->where('status', $this->request)->orderBy('created_at', 'desc')->get();
        } else {

            $orders = Order::with(['orderItems', 'orderItems.product'])->orderBy('created_at', 'desc')->get();
        }

        $listOrder = collect();
        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                $status = null;

                if ($order->status == 0) {

                    $status = "Đang chờ xác nhận";
                } else if ($order->status == 1) {

                    $status = "Đang giao hàng";
                } else if ($order->status == 2) {

                    $status = "Đã giao hàng thành công";
                } else if ($order->status == 3) {

                    $status = "Đơn hàng đã hủy";
                }

                $listOrder->push((object) [
                    'order_id' => $order->id,
                    'product_name' => $orderItem->product_name,
                    'price' => $orderItem->price,
                    'quantity' => $orderItem->quantity,
                    'total_price' => $order->total,
                    'user_name' => $order->name,
                    'created_at' => date('d/m/yy', strtotime($order->created_at)),
                    'status' => $status,
                ]);
            }
        }

        return $listOrder;

    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => '_(* #,##0_)',
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => '_(* #,##0_)',
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
     * Set header columns
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Mã số đơn đặt hàng',
            'Tên sản phẩm',
            'Giá sản phẩm',
            'Số lượng',
            'Tổng tiền',
            'Tên người mua',
            'Ngày đặt hàng',
            'Trạng thái',
        ];
    }

    /**
     * Mapping data
     *
     * @return array
     */
    public function map($bill): array
    {
        return [
            $listOrder->order_id,
            $listOrder->product_name,
            $listOrder->price,
            $listOrder->quantity,
            $listOrder->total_price,
            $listOrder->user_name,
            $listOrder->created_at,
            $listOrder->status,
        ];
    }
}
