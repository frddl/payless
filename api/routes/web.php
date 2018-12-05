<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('register', function (Illuminate\Http\Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    $result = 0;
    $token = generateRandomString();

    try {
        $result = app('db')->table('users')->insert(
            [
                'email' => $email,
                'password' => md5($password),
                'token' => $token,
                'created_at' => Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
            ]
        );

    } catch (\Illuminate\Database\QueryException $e) {
        $reason = $e->errorInfo[1] . " - " . $e->errorInfo[2];
    }

    return ($result) ? ['response' => '1', 'auth_token' => $token] : ['response' => '0', 'reason' => $reason];
});

$router->post('login', function (Illuminate\Http\Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    $result = 0;
    $token = generateRandomString();

    if (app('db')->table('users')->where('email', $email)->exists()) {
        try {
            $result = app('db')->table('email')->where('password', md5($token))->update(
                [
                    'token' => $token,
                    'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
                ]
            );

        } catch (\Illuminate\Database\QueryException $e) {
            $reason = $e->errorInfo[1] . " - " . $e->errorInfo[2];
        }
    }

    return ($result) ? ['response' => '1', 'auth_token' => $token] : ['response' => '0', 'reason' => $reason];
});

function generateRandomString($length = 50)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}