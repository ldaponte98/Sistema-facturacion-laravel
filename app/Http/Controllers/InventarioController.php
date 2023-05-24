<?php

namespace App\Http\Controllers;

use App\Caja;
use App\Dominio;
use App\Factura;
use App\FacturaDetalle;
use App\Inventario;
use App\InventarioDetalle;
use App\Licencia;
use App\Permiso;
use App\Producto;
use App\ResolucionFactura;
use Hamcrest\Core\IsNull;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function administrar(Request $request)
    {
        $post        = $request->all();
        $fecha_desde = date('Y-m-d') . " 00:00";
        $fecha_hasta = date('Y-m-d') . " 23:59";
        $fechas      = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";
        if ($post) {
            $post   = (object) $post;
            $fechas = $post->fechas;
            if ($fechas != "") {
                $fecha_desde = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[0]));
                $fecha_hasta = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[1]));
            }
        }
        $inventarios = Inventario::where('id_licencia', session('id_licencia'))
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->orderBy('fecha', 'desc')
            ->get();
        return view('inventario.movimientos', compact(['inventarios', 'fechas']));
    }

    public function guardar(Request $request, $id_inventario = null)
    {
        $post               = $request->all();
        $inventario         = new Inventario;
        $inventario->estado = 1;
        $inventario->fecha  = date('Y-m-d H:i');
        $detalles           = [];
        if ($id_inventario != null) {
            $inventario = Inventario::find($id_inventario);
        }

        $errors = [];
        if ($post) {
            DB::beginTransaction();
            $post = (object) $post;
            $inventario->fill($request->except(['_token']));
            if ($inventario->id_inventario) {
                $inventario->id_usuario_modifica = session('id_usuario');
            } else {
                $inventario->id_usuario_registra = session('id_usuario');
            }

            if (isset($post->detalles)) {
                $detalles                = json_decode($post->detalles);
                $inventario->id_licencia = session('id_licencia');

                if ($inventario->save()) {
                    //AHORA REGISTRAMOS LOS DETALLES
                    DB::delete("DELETE FROM inventario_detalle WHERE id_inventario = " . $inventario->id_inventario);
                    $total = 0;
                    $peso = 0;
                    foreach ($detalles as $detalle) {
                        $detalle                     = (object) $detalle;
                        $producto                    = Producto::find($detalle->id);
                        $item                        = new InventarioDetalle;
                        $item->id_inventario         = $inventario->id_inventario;
                        $item->id_producto           = $producto->id_producto;
                        $item->nombre_producto       = strtoupper($producto->nombre);
                        $item->precio_producto       = $detalle->precio;
                        $item->presentacion_producto = $producto->presentacion->nombre;
                        $item->cantidad              = $detalle->cantidad;
                        if ($item->save()) {
                            $total += $item->cantidad * $item->precio_producto;
                            $peso += $item->cantidad;
                            //AHORA DESCONTAMOS O SUMAMOS A LA CANTIDAD ACTUAL DEL PRODUCTO
                            if ($inventario->id_dominio_tipo_movimiento == 40) {
                                //ENTRADA
                                $producto->cantidad_actual += $detalle->cantidad;
                            }

                            if ($inventario->id_dominio_tipo_movimiento == 41) {
                                //SALIDA
                                $producto->cantidad_actual -= $detalle->cantidad;
                            }
                            $producto->save();
                        }
                    }

                    //SI ES UNA ENTRADA DE INVENTARIO SE REGISTRA UN COMPROBANTE DE EGRESO A NOMBRE DE LA EMPRESA
                    if ($post->id_dominio_tipo_movimiento == 40) {
                        //CREAMOS FACTURA DE COMPROBANTE DE EGRESO
                        $facturacion = $this->facturar_movimiento_inventario("ENTRADA",$total, $peso, $post->id_tercero_proveedor, $detalles);
                        if (!$facturacion->error) {
                            $inventario->id_factura = $facturacion->id_factura;
                            $inventario->save();
                        } else {
                            DB::rollBack();
                            $errors[] = $facturacion->message;
                            return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
                        }
                    }
                    if(isset($post->check_permitir_factura)){
                        $facturacion = $this->facturar_movimiento_inventario("SALIDA", $total, $peso, $post->id_tercero_cliente, $detalles);
                        if (!$facturacion->error) {
                            $inventario->id_factura = $facturacion->id_factura;
                            $inventario->save();
                        } else {
                            DB::rollBack();
                            $errors[] = $facturacion->message;
                            return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
                        }
                    }
                    DB::commit();
                    return redirect()->route('inventario/vista', $inventario->id_inventario);
                } else {
                    DB::rollBack();
                    $errors = $inventario->errors;
                }
            } else {
                DB::rollBack();
                $errors[] = "Debe escoger por lo menos un producto para el movimiento de inventario.";
                return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
            }
        }
        return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
    }

    public function guardare(Request $request, $id_inventario = null)
    {
        $post               = $request->all();
        $inventario         = new Inventario;
        $inventario->estado = 1;
        $inventario->fecha  = date('Y-m-d H:i');
        $detalles           = [];
        if ($id_inventario != null) {
            $inventario = Inventario::find($id_inventario);
        }

        $errors = [];
        if ($post) {
            DB::beginTransaction();
            $post = (object) $post;
            $inventario->fill($request->except(['_token']));
            if ($inventario->id_inventario) {
                $inventario->id_usuario_modifica = session('id_usuario');
            } else {
                $inventario->id_usuario_registra = session('id_usuario');
            }

            if (isset($post->detalles)) {
                $detalles                = json_decode($post->detalles);
                $inventario->id_licencia = session('id_licencia');

                if ($inventario->save()) {
                    //AHORA REGISTRAMOS LOS DETALLES
                    DB::delete("DELETE FROM inventario_detalle WHERE id_inventario = " . $inventario->id_inventario);
                    $total = 0;
                    $peso = 0;
                    foreach ($detalles as $detalle) {
                        $detalle                     = (object) $detalle;
                        $producto                    = Producto::find($detalle->id);
                        $item                        = new InventarioDetalle;
                        $item->id_inventario         = $inventario->id_inventario;
                        $item->id_producto           = $producto->id_producto;
                        $item->nombre_producto       = strtoupper($producto->nombre);
                        $item->precio_producto       = $detalle->precio;
                        $item->presentacion_producto = $producto->presentacion->nombre;
                        $item->cantidad              = $detalle->cantidad;
                        if ($item->save()) {
                            $total += $item->cantidad * $item->precio_producto;
                            $peso += $item->cantidad;
                            //AHORA DESCONTAMOS O SUMAMOS A LA CANTIDAD ACTUAL DEL PRODUCTO
                            if ($inventario->id_dominio_tipo_movimiento == 40) {
                                //ENTRADA
                                $producto->cantidad_actual += $detalle->cantidad;
                            }

                            if ($inventario->id_dominio_tipo_movimiento == 41) {
                                //SALIDA
                                $producto->cantidad_actual -= $detalle->cantidad;
                            }
                            $producto->save();
                        }
                    }

                    //SI ES UNA ENTRADA DE INVENTARIO SE REGISTRA UN COMPROBANTE DE EGRESO A NOMBRE DE LA EMPRESA
                    if ($post->id_dominio_tipo_movimiento == 40) {
                        //CREAMOS FACTURA DE COMPROBANTE DE EGRESO
                        $facturacion = $this->facturar_movimiento_inventario("ENTRADA",$total, $peso, $post->id_tercero_proveedor, $detalles);
                        if (!$facturacion->error) {
                            $inventario->id_factura = $facturacion->id_factura;
                            $inventario->save();
                        } else {
                            DB::rollBack();
                            $errors[] = $facturacion->message;
                            return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
                        }
                    }
                    if(isset($post->check_permitir_factura)){
                        $facturacion = $this->facturar_movimiento_inventario("SALIDA", $total, $peso, $post->id_tercero_cliente, $detalles);
                        if (!$facturacion->error) {
                            $inventario->id_factura = $facturacion->id_factura;
                            $inventario->save();
                        } else {
                            DB::rollBack();
                            $errors[] = $facturacion->message;
                            return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
                        }
                    }
                    DB::commit();
                    return redirect()->route('inventario/vista', $inventario->id_inventario);
                } else {
                    DB::rollBack();
                    $errors = $inventario->errors;
                }
            } else {
                DB::rollBack();
                $errors[] = "Debe escoger por lo menos un producto para el movimiento de inventario.";
                return view('inventario.form', compact(['inventario', 'detalles', 'errors']));
            }
        }
        return view('inventario.forme', compact(['inventario', 'detalles', 'errors']));
    }

    public function vista($id_inventario)
    {
        $inventario = Inventario::find($id_inventario);
        if ($inventario) {
            return view('inventario.view', compact(['inventario']));
        }
        echo "Url invalida";
    }

    public function stock_actual()
    {
        $tipos = Dominio::all()->where('id_padre', 35)->where('id_dominio', '<>', 37);
        return view('inventario.stock_actual', compact('tipos'));
    }

    public function facturar_movimiento_inventario($tipo_movimiento, $valor = 0, $peso = 0, $id_tercero, $detalles)
    {
        // los tipos de movimientos permitidos son ENTRADA, SALIDA
        $error      = true;
        $message    = "";
        $id_factura = null;

        $licencia   = Licencia::find(session('id_licencia'));
        $resolucion = ResolucionFactura::where('id_licencia', session('id_licencia'))->first();

        $caja = Caja::where('id_usuario', session('id_usuario'))
            ->where('estado', 1)
            ->where('fecha_cierre', null)
            ->first();

        if ($caja == null) {
            if (Permiso::validar(3)) {
                $caja = Caja::where('id_licencia', session('id_licencia'))
                    ->where('estado', 1)
                    ->where('fecha_cierre', null)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        if ($resolucion) {
            DB::beginTransaction();
            //RESOLUCION DEL DOCUMENTO
            $factura         = new Factura;
            if($tipo_movimiento=="ENTRADA"){
                $factura->numero = $resolucion->prefijo_comprobante_egreso . "-" . ($resolucion->consecutivo_comprobante_egreso + 1);
            }else{
                $factura->numero = $resolucion->prefijo_factura . "-" . ($resolucion->consecutivo_factura + 1);
            }
            
            if ($caja || session('id_perfil') == '5') {
                $factura->id_tercero              = $id_tercero;
                if (is_null($caja)) {
                    $factura->id_caja                 = null;
                }else{
                    $factura->id_caja                 = $caja->id_caja;
                }

                $factura->peso                    = $peso;
                $factura->valor                   = $valor;
                
                $factura->id_dominio_tipo_factura = $tipo_movimiento == "ENTRADA" ? 53 : 16;
                $factura->observaciones           = $tipo_movimiento == "ENTRADA" ? "Entrada de inventario" : "Salida de inventario";
                $factura->id_usuario_registra     = session('id_usuario');
                $factura->id_licencia             = session('id_licencia');
                $factura->id_dominio_canal        = 49;
                $factura->finalizada              = 1;
                if ($factura->save()) {

                    foreach ($detalles as $detalle) {
                        $detalle                       = (object) $detalle;
                        $producto                       = Producto::find($detalle->id);
                        $factura_detalle                        = new FacturaDetalle;
                        $factura_detalle->id_factura            = $factura->id_factura;
                        $factura_detalle->id_producto           = $producto->id_producto;
                        $factura_detalle->iva_producto          = $producto->iva;
                        $factura_detalle->nombre_producto       = $producto->nombre;
                        $factura_detalle->descripcion_producto  = $producto->descripcion;
                        $factura_detalle->precio_producto       = $detalle->precio;
                        $factura_detalle->cantidad              = $detalle->cantidad;
                        $factura_detalle->descuento_producto    = 0;
                        $factura_detalle->presentacion_producto = $detalle->presentacion;
                        $factura_detalle->save();
                    }

                    if($tipo_movimiento == "ENTRADA"){
                        $resolucion->consecutivo_comprobante_egreso += 1;
                    }else{
                        $resolucion->consecutivo_factura += 1;
                    }
                    $resolucion->save();
                    DB::commit();
                    $id_factura = $factura->id_factura;
                    $error      = false;
                } else {
                    DB::rollBack();
                    $message = $factura->errors[0];
                }
            } else {
                DB::rollBack();
                $message = "No existe caja abierta para este usuario activa";
            }
        } else {
            DB::rollBack();
            $message = "No tiene resolución de factura activa";
        }

        return (object) [
            'error'      => $error,
            'message'    => $message,
            'id_factura' => $id_factura,
        ];
    }

}
