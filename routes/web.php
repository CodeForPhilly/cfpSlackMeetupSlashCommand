<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route::get('meetup','MeetupController@hello'); 

Route::get('posts','MeetupController@posts'); 

Route::get('meetup','MeetupController@slack'); 

Route::get('test','MeetupSlackController@test'); 

