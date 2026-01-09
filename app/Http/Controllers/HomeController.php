<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
/**
 * write code and method
 * 
 * @return response()
 */

    public function index(Request $requests){
            return view("home");   
         }
}
