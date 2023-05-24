@extends('layout.main')
@section('menu')
    <div class="fab-container">
        <div class="fab fab-icon-holder">
            <i class="fa fa-bars"></i>
        </div>
        <ul class="fab-options">
            <li onclick="exportar_excel()">
                <span class="fab-label"><b>Exportar a excel</b></span>
                <div class="fab-icon-holder">
                    <i class="ti-agenda"></i>
                </div>
            </li>
        </ul>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Stock actual</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                @php $cont = 0; @endphp
                                @foreach ($tipos as $item)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $cont == 0 ? 'active' : '' }}"
                                            id="nav-tipo-{{ $item->id_dominio }}-tab" data-toggle="pill"
                                            href="#nav-tipo-{{ $item->id_dominio }}" role="tab"
                                            aria-controls="nav-tipo-{{ $item->id_dominio }}"
                                            aria-selected="true">{{ $item->nombre }}</a>
                                    </li>
                                    @php $cont++; @endphp
                                @endforeach
                            </ul>
                            <hr>
                            <div class="tab-content" id="pills-tabContent">
                                @php $cont = 0; @endphp
                                @foreach ($tipos as $item)
                                    <div class="tab-pane fade {{ $cont == 0 ? 'show active' : '' }}"
                                        id="nav-tipo-{{ $item->id_dominio }}" role="tabpanel"
                                        aria-labelledby="nav-tipo-{{ $item->id_dominio }}-tab">
                                        @php
                                            $productos = \App\Producto::all()
                                                ->where('id_licencia', session('id_licencia'))
                                                ->where('id_dominio_tipo_producto', $item->id_dominio)
                                                ->where('estado', 1);
                                            
                                        @endphp

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <select
                                                            onchange="ValidarEstado(this.value, {{ $item->id_dominio }})"
                                                            data-placeholder="Estado" class="form-control select2">
                                                            <option value=" ">Todos los estados</option>
                                                            <option value="AGOTADO">Agotado</option>
                                                            <option value="POR_AGOTARSE">Por_agotarse</option>
                                                            <option value="ESTABLE">Estable</option>
                                                            <option value="FULL">Full</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-6"></div>
                                                    <div class="col-sm-3" style="text-align: right !important;">
                                                        <input id="filtro-productos-{{ $item->id_dominio }}" type="text"
                                                            class="form-control" placeholder="Consulte aqui..."
                                                            autocomplete="on">
                                                    </div>
                                                </div>
                                            </div><br><br>
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="table-stats order-table ov-h table-responsive">
                                                        <table class="table" id="table-productos-{{ $item->id_dominio }}">
                                                            <thead>
                                                                <tr>
                                                                    <th>
                                                                        <center><b>#</b></center>
                                                                    </th>
                                                                    <th class="serial">
                                                                        <center><i class="fa fa-laptop"></i></center>
                                                                    </th>
                                                                    <th>
                                                                        <center><b>Producto</b></center>
                                                                    </th>
                                                                    <th>
                                                                        <center><b>Precio Compra</b></center>
                                                                    </th>
                                                                    <th>
                                                                        <center><b>Cantidad actual KG</b></center>
                                                                    </th>
                                                                    <th>
                                                                        <center><b>Valor Total</b></center>
                                                                    </th>
                                                                    <th>
                                                                        <center><b>Estado</b></center>
                                                                    </th>
                                                                    <th>
                                                                        <center><b>Acciones</b></center>
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php $cont = 1; @endphp
                                                                @foreach ($productos as $producto)
                                                                    @if ($producto->descontado == 1 or $producto->alerta == 1)
                                                                        <tr>
                                                                            <td>
                                                                                <center>{{ $cont }}</center>
                                                                            </td>
                                                                            <td>
                                                                                <center><img class="rounded-circle"
                                                                                        src="{{ $producto->get_imagen() }}"
                                                                                        width="45" height="45"
                                                                                        alt="tercero"></center>
                                                                            </td>
                                                                            <td>
                                                                                <center>{{ $producto->nombre }}</center>
                                                                            </td>
                                                                            <td>
                                                                                <center>{{ $producto->precio_compra }}
                                                                                </center>
                                                                            </td>
                                                                            <td>
                                                                                <center>{{ $producto->cantidad_actual }}
                                                                                </center>
                                                                            </td>
                                                                            <td>
                                                                                <center>
                                                                                    ${{ floatval($producto->precio_compra * $producto->cantidad_actual) }}
                                                                                </center>
                                                                                {{-- <center>
                                                                                    {{ $producto->alerta == 1 ? $producto->cantidad_minimo_alerta : "No aplica" }}
                                                                                </center> --}}
                                                                            </td>
                                                                            <td>
                                                                                <center>
                                                                                    @if ($producto->cantidad_actual <= 0)
                                                                                        <span
                                                                                            class="badge badge-danger"><b>Agotado</b></span>
                                                                                    @else
                                                                                        @if ($producto->cantidad_actual >= ($producto->cantidad_minimo_alerta*2))
                                                                                            <span
                                                                                                class="badge badge-success"><b>Full</b></span>
                                                                                        @else
                                                                                            @if ($producto->cantidad_actual > $producto->cantidad_minimo_alerta && $producto->cantidad_actual <= ($producto->cantidad_minimo_alerta*2))
                                                                                                <span
                                                                                                    class="badge badge-info text-black"><b>Estable</b></span>
                                                                                            @else
                                                                                                <span
                                                                                                    class="badge badge-warning text-black"><b>Por_agotarse</b></span>
                                                                                            @endif
                                                                                        @endif
                                                                                    @endif

                                                                                </center>
                                                                            </td>
                                                                            <td>
                                                                                <center>
                                                                                    <a
                                                                                        href="{{ route('producto/view', $producto->id_producto) }}">Ver</a>
                                                                                </center>
                                                                            </td>
                                                                        </tr>
                                                                    @endif

                                                                    @php $cont++; @endphp
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php $cont++; @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table border="1" id="tabla_excel" style="display: none">
        <tr>
            <td colspan='12' rowspan='2'>
                <center>
                    <b>REPORTE DE STOCK</b>
                </center>
            </td>
        </tr>
        <tr></tr>
        @if ($fechas = date('Y-m-d'))
            <tr colspan='12' rowspan='2'>
                <td>
                    <center><b>Fechas: </b>{{ $fechas }}</center>
                </td>
            </tr>
        @endif

        <tr>
            <th>
                <center><b>Producto</b></center>
            </th>
            <th>
                <center><b>Precio Compra</b></center>
            </th>
            <th>
                <center><b>Cantidad actual KG</b></center>
            </th>
            <th>
                <center><b>Valor Total</b></center>
            </th>
            <th>
                <center><b>Estado</b></center>
            </th>
        </tr>
        <tbody bodytable_excel>
            @foreach ($productos as $producto)
                @if ($producto->descontado == 1 or $producto->alerta == 1)
                    <tr>
                        <td>
                            <center>{{ $producto->nombre }}</center>
                        </td>
                        <td>
                            <center>${{ floatval($producto->precio_compra) }}
                            </center>
                        </td>
                        <td>
                            <center>{{ number_format($producto->cantidad_actual, 2, ',', '.') }}
                            </center>
                        </td>
                        <td>
                            <center>
                                ${{ floatval($producto->precio_compra * $producto->cantidad_actual) }}
                            </center>
                        </td>
                        <td>
                            <center>
                                @if ($producto->cantidad_actual <= 0)
                                    <span class="badge badge-danger"><b>Agotado</b></span>
                                @else
                                    @if ($producto->cantidad_actual >= $producto->cantidad_minimo_alerta)
                                        <span class="badge badge-success"><b>Full</b></span>
                                    @else
                                        @if ($producto->cantidad_actual >= 500 && $producto->cantidad_actual <= $producto->cantidad_minimo_alerta)
                                            <span class="badge badge-info text-black"><b>Estable</b></span>
                                        @else
                                            <span class="badge badge-warning text-black"><b>Por_agotarse</b></span>
                                        @endif
                                    @endif
                                @endif

                            </center>
                        </td>
                    </tr>
                @endif

                @php $cont++; @endphp
            @endforeach
        </tbody>


    </table>

    <script type="text/javascript">
        function exportar_excel() {
            tableToExcel('tabla_excel', 'Reporte de Stock inventario Productos químicos mi fragancia')
        }
        @foreach ($tipos as $item)
            setFiltro('filtro-productos-{{ $item->id_dominio }}', 'table-productos-{{ $item->id_dominio }}')
        @endforeach

        function ValidarEstado(estado, tipo) {
            $("#filtro-productos-" + tipo).val(estado)
            $("#filtro-productos-" + tipo).keyup()
            $("#filtro-productos-" + tipo).val("")
        }
    </script>
@endsection
