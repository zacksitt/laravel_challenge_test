<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $req)
    {
        // dd(Auth::user());

        if(Auth::user()){
            return array("tasks" => []);
        }else{
            return redirect('/login');
        }
        
    }
    public function create(Request $req){
        
        return response(["tasks" => []],201);
    }
}
