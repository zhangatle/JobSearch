<?php


namespace App\Http\Controllers;


class SearchController extends Controller
{
    /**
     * 搜索首页
     */
    public function index() {
        return view("index");
    }

    public function search() {
        return view("result");
    }
}
