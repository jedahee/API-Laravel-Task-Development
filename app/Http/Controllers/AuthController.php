<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
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

        //Intentamos hacer login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                //Credenciales incorrectas.
                return response()->json(['message' => 'Login falló',], 401);
            }
        } catch (JWTException $e) {
            //Error chungo
            return response()->json(['message' => 'Error',], 500);
        }

        //Devolvemos el token
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => Auth::user()
        ]);
    }

    /*

    ###################################################
    #                   DESCONECTAR                   #
    ###################################################

    */
    public function logout(Request $request) {
        //Validamos que se nos envie el token
        $validator = Validator::make($request->only('token'),
            ['token' => 'required']
        );
        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        try {
            //Si el token es valido eliminamos el token desconectando al usuario.

            JWTAuth::invalidate($request->token);
            return response()->json([
                'success' => true,
                'message' => 'Usuario desconectado'
            ]);

        } catch (JWTException $exception) {

            //Error chungo
            return response()->json([
            'success' => false,
            'message' => 'Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*

    ###################################################
    #                  VALIDAR TOKEN                  #
    ###################################################

    */

    public function getUser(Request $request) {
        //Validamos que la request tenga el token
        $this->validate($request, [
            'token' => 'required'
        ]);

        //Realizamos la autentificación
        $user = JWTAuth::authenticate($request->token);

        //Si no hay usuario es que el token no es valido o que ha expirado
        if(!$user)
            return response()->json(['message' => 'Token invalido / token expirado',], 401);

        //Devolvemos los datos del usuario si todo va bien.
        return response()->json(['user' => $user]);
    }
}
