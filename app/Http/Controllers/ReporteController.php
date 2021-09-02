<?php

namespace App\Http\Controllers;

use App\AuditoriaInventario;
use App\Factura;
use App\Permiso;
use App\Producto;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function facturas(Request $request)
    {
        $post           = $request->all();
        $fecha_desde    = date('Y-m-d') . " 00:00";
        $fecha_hasta    = date('Y-m-d') . " 23:59";
        $id_perfil      = session('id_perfil');
        $id_permiso     = 1;
        $permiso_anular = Permiso::validar($id_perfil, $id_permiso);
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

        $facturas = Factura::where('id_licencia', session('id_licencia'))
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);

        if (count($canales) > 0) {
            $facturas = $facturas->whereIn('id_dominio_canal', $canales);
        }

        $facturas = $facturas->orderBy('fecha', 'desc');
        $facturas = $facturas->get();

        $total_ventas_fecha    = 0;
        $total_facturas_ventas = 0;
        $total_cotizaciones    = 0;

        foreach ($facturas as $factura) {
            if ($factura->estado == 1) {
                $total_ventas_fecha += $factura->valor;
            }
            if ($factura->id_dominio_tipo_factura == 16) {
                $total_facturas_ventas += 1;
            }
            if ($factura->id_dominio_tipo_factura == 17) {
                $total_facturas_ventas += 1;
            }
        }

        return view('reportes.facturas', compact([
            'facturas',
            'total_ventas_fecha',
            'total_facturas_ventas',
            'total_cotizaciones',
            'fechas',
            'canales',
            'permiso_anular',
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
}
