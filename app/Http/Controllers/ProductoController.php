<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Puja;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{

    protected $user;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != '')
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        //Listamos todos los productos
        $productos = Producto::get();
        return response()->json($productos);
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        //Validamos los datos
        $data = $request->only('nombre', 'descripcion', 'precioInicial', 'numMax', 'categoria_id');

        $validator = Validator::make($data, [
            'nombre' => 'required|min:5|max:100|string',
            'descripcion' => 'required|max:100|string',
            'precioInicial' => 'required|min:0|integer',
            'numMax' => 'required|min:10|max:1000|integer',
            'categoria_id' => 'required|integer',
        ]);
        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' =>
            $validator->messages()], 400);
        }
        //Creamos el producto en la BD
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precioInicial' => $request->precioInicial,
            'numMax' => $request->numMax,
            'categoria_id' => $request->categoria_id,
            'user_id' => $this->user->id,
        ]);
        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'message' => 'Producto creado',
            'data' => $producto
        ], Response::HTTP_OK);
    }
    /**
    * Display the specified resource.
    *
    * @param \App\Models\Product $product
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        //Buscamos el producto
        $producto = Producto::find($id);
        //Si el producto no existe devolvemos error no encontrado
        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado.'
            ], 404);
        }
        //Si hay producto lo devolvemos
        return response()->json([
            'data' => $producto
            ], Response::HTTP_OK);
    }
    /**
    * Update the specified resource in storage.
    *
    * @param \Illuminate\Http\Request $request
    * @param \App\Models\Product $product
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        // ------------- SOLO SI NO HAY PUJAS ASOCIADAS -------------
        //Validación de datos
        $data = $request->only('nombre', 'descripcion', 'precioInicial', 'numMax', 'categoria_id');

        $validator = Validator::make($data, [
            'nombre' => 'required|min:5|max:100|string',
            'descripcion' => 'required|max:100|string',
            'precioInicial' => 'required|min:0|integer',
            'numMax' => 'required|min:10|max:1000|integer',
            'categoria_id' => 'required|integer',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' =>
            $validator->messages()], 400);
        }

        //Buscamos el producto
        $producto = Producto::findOrfail($id);
        $pujasProducto = Puja::where('prod_id', '=', $id)->get();

        if (count($pujasProducto) <= 0) {
            if ($this->user->id == $producto->user_id) {

                //Actualizamos el producto.
                $producto->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'precioInicial' => $request->precioInicial,
                    'numMax' => $request->numMax,
                    'categoria_id' => $request->categoria_id,
                ]);
                //Devolvemos los datos actualizados.
                return response()->json([
                    'message' => 'Producto actualizado correctamente',
                    'data' => $producto
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'El producto no se puede actualizar porque no eres el dueño',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'El producto no se puede actualizar porque tiene pujas asociadas',
            ], 404);
        }


    }
    /**
    * Remove the specified resource from storage.
    *
    * @param \App\Models\Product $product
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        // ------------- SOLO SI NNO HAY PRODUCTOS ASOCIADOS -------------
        $producto = Producto::findOrfail($id);
        $pujasProducto = Puja::where('prod_id', '=', $id)->get();

        if (count($pujasProducto) <= 0) {
            if ($this->user->id == $producto->user_id) {
                //Buscamos el producto
                $producto = Producto::findOrfail($id);
                //Eliminamos el producto
                $producto->delete();
                //Devolvemos la respuesta
                return response()->json([
                    'message' => 'Producto borrado correctamente'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'El producto no se puede borrar porque no eres el dueño',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'El producto no se puede borrar porque tiene pujas asociadas',
            ], 404);
        }

    }
}
