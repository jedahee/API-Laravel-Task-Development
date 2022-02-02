<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
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
        $categorias = Categoria::get();
        // HACER FOREACH A TODAS LAS CATEGORIAS Y ANADIRLE SUS PRODUCTOS
        return response()->json($categorias);
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
        $data = $request->only('nombre', 'descripcion');
        $validator = Validator::make($data, [
            'nombre' => 'required|max:100|string',
            'descripcion' => 'required|max:100|string',
        ]);
        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' =>
            $validator->messages()], 400);
        }
        //Creamos el producto en la BD
        $categoria = Categoria::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);
        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'message' => 'Categoria creada',
            'data' => $categoria
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
        $categoria = Categoria::find($id);
        //Si el producto no existe devolvemos error no encontrado
        if (!$categoria) {
            return response()->json([
                'message' => 'Categoria no encontrada.'
            ], 404);
        }
        //Si hay producto lo devolvemos
        return response()->json([
            'data' => $categoria
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
        // ------------- SOLO SI NNO HAY PRODUCTOS ASOCIADOS -------------
        
        $products = Producto::where('categoria_id', '=', $id)->get();
        
        if (count($products) <= 0) {
            //Validación de datos
            $data = $request->only('nombre', 'descripcion');
            $validator = Validator::make($data, [
                'nombre' => 'required|max:100|string',
                'descripcion' => 'required|max:100|string',
            ]);
            //Si falla la validación error.
            if ($validator->fails()) {
                return response()->json(['error' =>
                $validator->messages()], 400);
            }
            //Buscamos el producto
            $categoria = Categoria::findOrfail($id);
            //Actualizamos el producto.
            $categoria->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);
            //Devolvemos los datos actualizados.
            return response()->json([
                'message' => 'Categoria actualizada correctamente',
                'data' => $categoria
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'No se puede actualizar una categoria con productos asociados a ella',
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
        $products = Producto::where('categoria_id', '=', $id)->get();
        if (count($products) <= 0) {
            //Buscamos el producto
            $categoria = Categoria::findOrfail($id);
            //Eliminamos el producto
            $categoria->delete();
            //Devolvemos la respuesta
            return response()->json([
                'message' => 'Categoria borrada correctamente'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'No se puede borrar una categoria con productos asociados a ella',
            ], 404);
        }
    }
}