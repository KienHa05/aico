<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $test = "Hello Backend Laravel";

    return view('index', ['test' => $test]);
});
