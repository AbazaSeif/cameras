<?php

namespace App\Http\Controllers;

use App\Camera;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Camera $camera)
    {
        $cameras = $camera->paginate();
        return view('home', compact('cameras'));
    }
}
