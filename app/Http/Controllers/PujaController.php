<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Puja;
use Illuminate\Http\Request;
use DB;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class PujaController extends Controller
{
    protected $user;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != '')
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function listarPujasDeProducto($id, $num = -1) {

        if ($num == -1) {
            $pujas = DB::table('pujas')->where('prod_id', $id)->orderBy('created_at', 'desc')->get();
        } else {
            $pujas = DB::table('pujas')->where('prod_id', $id)->orderBy('created_at', 'desc')->limit($num)->get();
        }

        return response()->json([
            'data' => $pujas
            ], Response::HTTP_OK);
    }

    public function listarPujasDeUsuario() {
        $pujas = DB::table('pujas')->where('user_id', $this->user->id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $pujas
            ], Response::HTTP_OK);
    }

    public function listarUltimaPujaProducto() {
        $productos = Producto::get();
        $pujas = [];
        $i = 0;

        foreach ($productos as $producto) {
            $cantidadPujasProducto = Puja::where('prod_id', $producto->id)->count();
            $numMaxPujas = Producto::where('user_id', $producto->user_id)->where('id', $producto->id)->get()->last()->numMax;            
            
            $pujas[] = DB::table('pujas')->where('prod_id', $producto->id)->get()->last();
            $pujas[$i]->puja_abierta = ($cantidadPujasProducto < ($numMaxPujas * 0.1));
            $i++;
        }

        return response()->json($pujas);


    }

    /**
    * Store a newly created resource in storage.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request, $id)
    {
        //Validamos los datos
        $data = $request->only('dineroPujado');

        $validator = Validator::make($data, [
            'dineroPujado' => 'required|regex:/^\d+(.\d{1,2})?$/|min:0|integer',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' =>
            $validator->messages()], 400);
        }

        $hayDatosEnLaTabla = Puja::where('prod_id', $id)->count();

        if (!((bool)$hayDatosEnLaTabla)) {
            //Creamos la puja en la BD
            $puja = Puja::create([
                'dineroPujado' => $request->dineroPujado,
                'user_id' => $this->user->id,
                'prod_id' => $id
            ]);
            //Respuesta en caso de que todo vaya bien.
            return response()->json([
                'message' => 'Puja creada',
                'data' => $puja
            ], Response::HTTP_OK);
        } else {
            // Recoger ultima puja del producto pasado por id para comprobar
            // si la puja añadida es mayor a la anterior
            $ultimoDineroPujado = DB::table('pujas')->where('prod_id', $id)->get()->last()->dineroPujado;

            // Pujas que ha realizado el usuario
            $pujasRealizadas = DB::table('pujas')->where('user_id', $this->user->id)->get();

            // Obtener numero maximo de pujas
            $numMaxPujas = Producto::where('user_id', $this->user->id)->where('id', $id)->get()->last()->numMax;

            if (($request->dineroPujado) > (int)($ultimoDineroPujado)) {
                if (count($pujasRealizadas) < ($numMaxPujas * 0.1)) {
                    //Creamos la puja en la BD
                    $puja = Puja::create([
                        'dineroPujado' => $request->dineroPujado,
                        'user_id' => $this->user->id,
                        'prod_id' => $id
                    ]);
                    //Respuesta en caso de que todo vaya bien.
                    return response()->json([
                        'message' => 'Puja creada',
                        'data' => $puja
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'No se puede crear la puja porque ya has llegado a tu limite',
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'No se puede crear la puja porque debe ingresar mas dinero que la ultima puja',
                ], 400);
            }
        }
    }
}
