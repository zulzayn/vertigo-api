<?php

namespace App\Http\Controllers\Api;

use App\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validatedData = $request->validate([
            'name'=>'required',
            'email'=>'email|required|unique:users',
            'password'=>'required|confirmed',
            'id_role' => 'required',
        ]);

        $register = New User;
        $register->id = Uuid::uuid4()->getHex();
        $id_user = $register->id;
        $register->name = $request->name;
        $register->email = $request->email;
        $register->password = bcrypt($request->password);
        $register->id_role = $request->id_role;
        $register->created_by = auth()->user()->id;
        $register->status = '';
        $register->save();

        $user = User::find($id_user);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['user' => $user , 'access_token' => $accessToken]);

    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email'=>'email|required',
            'password'=>'required'
        ]);

        if(!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Credentials']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['user' => auth()->user() , 'access_token' => $accessToken]);
    }
}
