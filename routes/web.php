<?php
use App\Task;

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
Route::get("test", function () {
    $task = new Task;
    $task->name = "Got To store";
    $task->save();
    event(new App\Events\NewTask($task));
});
Route::get('gettask', function () {
    return Task::latest()->get();
});

Route::post("addtask", function () {
    $task = Task::create(['name' => request()->get('name'), 'done' => false]);
    broadcast(new App\Events\NewTask($task))->toOthers();
    return $task;
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
