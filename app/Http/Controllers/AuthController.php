<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Autenticação e obtenção de token de acesso
     */
    public function login(AuthRequest $request){
        $credentials = $request->returnData();

        if(!Auth::attempt($credentials)){
            return response()->json(['error' => 'Unauthorized'], 401);
        };

        $user = Auth::user();
        $token = $user->createToken('token');

        return response()->json($token->plainTextToken);
    }
}
