<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = [
            [
                'id' => 1,
                'name' => 'Laptop Macbook Air M1 2020',
                'brand' => 'Apple',
                'warranty_status' => 'Off',
                'buying_date' => '2020-09-12',
                'image' => "https://image1ws.indotrading.com/s3/productimages/jpeg/co262721/p1279886/w600-h600/887c1220-bbf3-44d3-a0c7-3b105c6a64bc.jpg"
            ],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Office assets retrieved successfully',
            'data' => $assets
        ], 200);
    }
}
