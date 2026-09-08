<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /*

    ###################################################
    #                    REGISTRO                     #
    ###################################################

    */
    public function register(Request $request) {
        //Indicamos que solo queremos recibir name, email y password de la request
        $data = $request->only('nombre', 'apellidos', 'email', 'password');

        //Realizamos las validaciones
        $validator = Validator::make($data, [
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50',
        ]);

        //Devolvemos un error si fallan las validaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Creamos el nuevo usuario
        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'apellidos' => $request->apellidos,
            'password' => bcrypt($request->password)
        ]);

        //Devolvemos la respuesta con el token del usuario
        return response()->json([
            'message' => 'Usuario creado',
            'user' => $user,
        ], Response::HTTP_OK);
    }

    /*

    ###################################################
    #                     LOGEARSE                    #
    ###################################################

    */
    public function authenticate(Request $request) {
        //Indicamos que solo queremos recibir email y password de la request
        $credentials = $request->only('email', 'password');

        //Validaciones
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Devolvemos un error de validación en caso de fallo en las verificaciones
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Login falló'], 401);
        }

        //Devolvemos el token
        return response()->json([
            'success' => true,
            'token' => $user->createToken('auth-token')->plainTextToken,
            'user' => $user
        ]);
    }

    /*

    ###################################################
    #                   DESCONECTAR                   #
    ###################################################

    */
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario desconectado'
        ], Response::HTTP_OK);
    }

    /*

    ###################################################
    #                  VALIDAR TOKEN                  #
    ###################################################

    */

    public function getUser(Request $request) {
        //Realizamos la autentificación
        $user = $request->user('sanctum');

        //Si no hay usuario es que el token no es valido o que ha expirado
        if(!$user)
            return response()->json(['message' => 'Token invalido / token expirado',], 401);

        //Devolvemos los datos del usuario si todo va bien.
        return response()->json(['user' => $user]);
    }
}