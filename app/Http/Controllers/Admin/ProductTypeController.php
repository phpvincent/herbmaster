<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductTypeController extends Controller
{
    public function index()
    {
        return code_response(10,'获取产品分类成功！', 200, ['data' => ProductType::all()]);
    }
}
