<?php

$router->get('/', function () use ($router) {
    return 'OK';
});

$router->post('login', function (Illuminate\Http\Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    $result = 0;
    $token = generateRandomString();

    if (app('db')->table('users')->where('email', $email)->exists()) {
        try {
            $result = app('db')->table('email')->where('password', md5($password))->update(
                [
                    'token' => $token,
                    'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
                ]
            );

        } catch (\Illuminate\Database\QueryException $e) {
            $reason = $e->errorInfo[1] . " - " . $e->errorInfo[2];
        }
    } else {
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
    }

    return ($result) ? ['response' => '1', 'auth_token' => $token] : ['response' => '0', 'reason' => $reason];
});

$router->get('/predefinedCategories', function () {
    return app('db')->table('predefined_categories')->select()->get()->toJson();
});

$router->get('/userCategories/{token}', function ($token) {
    if (app('db')->table('users')->where('token', $token)->exists()) {
        $id = json_decode(app('db')->table('users')->where('token', $token)->get()->toJson(), true)[0]['id'];
        return app('db')->table('user_categories')->where('user_id', $id)->get()->toJson();
    } else {
        return ['response' => '0', 'reason' => 'Not authorized'];
    }
});

$router->post('/userCategories/{token}/add', function (Illuminate\Http\Request $request, $token) {
    if (app('db')->table('users')->where('token', $token)->exists()) {
        $id = json_decode(app('db')->table('users')->where('token', $token)->get()->toJson(), true)[0]['id'];
        $cat_name = $request->input('cat_name');

        try {
            $result = app('db')->table('user_categories')->insert(
                [   
                    'user_id' => $id,
                    'name' => $cat_name,
                    'created_at' => Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
                ]
            );

            return ['response' => '1'];
    
        } catch (\Illuminate\Database\QueryException $e) {
            $reason = $e->errorInfo[1] . " - " . $e->errorInfo[2];
        }   
    } else {
        return ['response' => '0', 'reason' => 'Not authorized'];
    }
});

$router->post('/userCategories/{token}/remove', function (Illuminate\Http\Request $request, $token) {
    if (app('db')->table('users')->where('token', $token)->exists()) {
        $id = $request->input('cat_id');

        try {
            $result = app('db')->table('user_categories')->where('id', $id)->delete();
            return ['response' => '1'];
        } catch (\Illuminate\Database\QueryException $e) {
            $reason = $e->errorInfo[1] . " - " . $e->errorInfo[2];
        }   
    } else {
        return ['response' => '0', 'reason' => 'Not authorized'];
    }
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