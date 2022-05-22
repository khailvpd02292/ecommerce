<?php

namespace App\Http\Controllers\User;

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


    public function index()
    {

        $products = Product::with('category')->get();

        return $this->sendSuccessResponse($products);

    }
}
