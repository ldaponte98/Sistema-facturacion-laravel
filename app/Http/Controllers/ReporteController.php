<?php

namespace App\Http\Controllers;

use App\AuditoriaInventario;
use App\Caja;
use App\Dominio;
use App\Factura;
use App\Permiso;
use App\Producto;
use App\Tercero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function facturas(Request $request)
    {
        $post           = $request->all();
        $fecha_desde    = date('Y-m-d') . " 00:00";
        $fecha_hasta    = date('Y-m-d') . " 23:59";
        $id_perfil      = session('id_perfil');
        $id_permiso     = 1;
        $permiso_anular = Permiso::validar($id_permiso, $id_perfil);
        $fechas         = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";

        $canales = [];
        if ($post) {
            $post   = (object) $post;
            $fechas = $post->fechas;
            if ($fechas != "") {
                $fecha_desde = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[0]));
                $fecha_hasta = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[1]));
            }

            if (isset($post->canales)) {
                $canales = $post->canales;
            }
        }

        // $facturas = Factura::where('id_licencia', session('id_licencia'))
        //     ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
        //     ->where('id_factura_cruce', null);

        $facturas = Factura::join('inventario', 'factura.id_factura', '=', 'inventario.id_factura')
        ->where('factura.id_licencia', session('id_licencia'))
        ->whereBetween('inventario.fecha', [$fecha_desde, $fecha_hasta])
        ->where('factura.id_factura_cruce', null);

        if (count($canales) > 0) {
            $facturas = $facturas->whereIn('factura.id_dominio_canal', $canales);
        }

        $facturas = $facturas->orderBy('factura.fecha', 'desc');
        $facturas = $facturas->get();

        $total_ventas_fecha    = 0;
        $total_facturas_ventas = 0;
        $total_egresos         = 0;

        foreach ($facturas as $factura) {
            if ($factura->estado == 1 and $factura->id_dominio_tipo_factura == 16) {
                $total_ventas_fecha += $factura->valor;
            }
            if ($factura->estado == 1 and $factura->id_dominio_tipo_factura == 56) {
                $total_ventas_fecha += $factura->valor;
            }
            if ($factura->id_dominio_tipo_factura == 16 || $factura->id_dominio_tipo_factura == 56) {
                $total_facturas_ventas += 1;
            }
            if ($factura->estado == 1 and $factura->id_dominio_tipo_factura == 53) {
                $total_egresos += $factura->valor;
            }
        }

        return view('reportes.facturas', compact([
            'facturas',
            'total_ventas_fecha',
            'total_facturas_ventas',
            'total_egresos',
            'fechas',
            'canales',
            'permiso_anular',
        ]));
    }

    public function cajas(Request $request)
    {
        $post        = $request->all();
        $fecha_desde = date('Y-m-d') . " 00:00";
        $fecha_hasta = date('Y-m-d') . " 23:59";
        $fechas      = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";
        $formas_pago = Dominio::where('id_padre', 19)->get();
        $usuarios    = [];
        if ($post) {
            $post   = (object) $post;
            $fechas = $post->fechas;
            if ($fechas != "") {
                $fecha_desde = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[0]));
                $fecha_hasta = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[1]));
            }
            if (isset($post->usuarios)) {
                $usuarios = $post->usuarios;
            }
        }

        $cajas = Caja::where('id_licencia', session('id_licencia'))
            ->whereBetween('created_at', [$fecha_desde, $fecha_hasta]);

        if (count($usuarios) > 0) {
            $cajas = $cajas->whereIn('id_usuario', $usuarios);
        }

        $cajas = $cajas->orderBy('created_at', 'asc');
        $cajas = $cajas->get();

        return view('reportes.caja', compact([
            'cajas',
            'fechas',
            'usuarios',
            'formas_pago',
        ]));
    }

    public function auditoria_interna(Request $request)
    {
        $post        = $request->all();
        $fecha_desde = date('Y-m-d') . " 00:00";
        $fecha_hasta = date('Y-m-d') . " 23:59";
        $fechas      = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";

        $_productos = Producto::where('id_licencia', session('id_licencia'))
            ->where('id_dominio_tipo_producto', '<>', 37)
            ->get();

        $productos = [];
        if ($post) {
            $post   = (object) $post;
            $fechas = $post->fechas;
            if ($fechas != "") {
                $fecha_desde = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[0]));
                $fecha_hasta = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[1]));
            }
            if (isset($post->productos)) {
                $productos = $post->productos;
            }
        }

        $auditorias = AuditoriaInventario::where('id_licencia', session('id_licencia'))
            ->whereBetween('created_at', [$fecha_desde, $fecha_hasta]);

        if (count($productos) > 0) {
            $auditorias = $auditorias->whereIn('id_producto', $productos);
        }

        $auditorias = $auditorias->orderBy('created_at', 'desc');
        $auditorias = $auditorias->get();

        return view('reportes.auditoria_interna', compact([
            'auditorias',
            'fechas',
            'productos',
            '_productos',
        ]));
    }

    public function facturas_pendientes_pagar(Request $request)
    {
        $post           = $request->all();
        $fecha_desde    = "";
        $fecha_hasta    = "";
        $id_perfil      = session('id_perfil');
        $id_permiso     = 5;
        $permiso_pagar  = Permiso::validar($id_permiso, $id_perfil);
        $fechas         = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";
        $fechas         = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";
        $search_tercero = "";
        $formas_pago    = Dominio::where('id_padre', 19)
            ->where('id_dominio', '<>', Dominio::get('Credito (Saldo pendiente)'))
            ->get();

        $canales = [];
        if ($post) {
            $post   = (object) $post;
            $fechas = $post->fechas;
            if ($fechas != "") {
                $fecha_desde = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[0]));
                $fecha_hasta = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[1]));
            }

            $search_tercero = $post->search_tercero;

            if (isset($post->canales)) {
                $canales = $post->canales;
            }
        }

        $facturas = Factura::where('id_licencia', session('id_licencia'))
            ->where('estado', 1)
            ->where('id_dominio_tipo_factura', 56);
        if ($fechas != "") {
            $facturas = $facturas->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);
        }
        if ($search_tercero != "") {
            $search   = Tercero::where('identificacion', $search_tercero)->first();
            $search   = $search ? $search : new Tercero;
            $facturas = $facturas->where('id_tercero', $search->id_tercero);
        }

        if (count($canales) > 0) {
            $facturas = $facturas->whereIn('id_dominio_canal', $canales);
        }

        $facturas = $facturas->orderBy('fecha', 'desc');
        $facturas = $facturas->get();

        $total_ventas_fecha    = 0;
        $total_facturas_ventas = 0;
        $total_egresos         = 0;

        foreach ($facturas as $factura) {
            if ($factura->estado == 1 and $factura->id_dominio_tipo_factura == 16) {
                $total_ventas_fecha += $factura->valor;
            }
            if ($factura->id_dominio_tipo_factura == 16) {
                $total_facturas_ventas += 1;
            }
            if ($factura->estado == 1 and $factura->id_dominio_tipo_factura == 53) {
                $total_egresos += $factura->valor;
            }
        }

        return view('reportes.facturas_pendientes_pagar', compact([
            'facturas',
            'total_ventas_fecha',
            'total_facturas_ventas',
            'total_egresos',
            'fechas',
            'canales',
            'permiso_pagar',
            'search_tercero',
            'formas_pago',
        ]));
    }

    public function ventas_por_producto(Request $request)
    {
        $post           = $request->all();
        $fecha_desde    = date('Y-m-d') . " 00:00";
        $fecha_hasta    = date('Y-m-d') . " 23:59";
        $fechas         = date('Y/m/d') . " 00:00 - " . date('Y/m/d') . " 23:59";
        $id_tipo_factura = 16;

        if ($post) {
            $post   = (object) $post;
            $fechas = $post->fechas;
            if ($fechas != "") {
                $fecha_desde = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[0]));
                $fecha_hasta = date('Y-m-d H:i', strtotime(explode('-', $post->fechas)[1]));
            }
            $id_tipo_factura = $post->id_tipo_factura;
        }
        $id_licencia = session('id_licencia');
        $sql = "SELECT p.nombre as producto, 
        fd.presentacion_producto, 
        SUM(fd.cantidad) as cantidad, 
        SUM(fd.cantidad * fd.precio_producto) as total 
        FROM factura_detalle fd 
        LEFT JOIN producto p USING(id_producto) 
        LEFT JOIN factura f USING(id_factura) 
        WHERE f.fecha BETWEEN '$fecha_desde' AND '$fecha_hasta' 
        AND f.id_licencia = $id_licencia
        AND f.id_dominio_tipo_factura = $id_tipo_factura
        GROUP BY 1, 2
        ORDER BY 3 DESC";

        $reporte = DB::select($sql);
        return view("reportes.ventas_por_producto", compact(["reporte", "fechas", "id_tipo_factura"]));
    }
}
