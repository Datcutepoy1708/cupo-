<?php

namespace App\Http\Controllers;

class CartController extends Controller
{
    public function index()
    {
        return view('cart.index'); // hoặc logic lấy giỏ hàng của bạn
    }
}
