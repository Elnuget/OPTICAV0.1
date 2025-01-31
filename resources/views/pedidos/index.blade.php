@extends('adminlte::page')
@section('title', 'Pedidos')

@section('content_header')
<h1>Pedidos</h1>
<p>Administracion de ventas</p>
@if (session('error'))
    <div class="alert {{ session('tipo') }} alert-dismissible fade show" role="alert">
        <strong>{{ session('mensaje') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif @stop

@section('content')

<div class="card">
    <div class="card-body">
        {{-- Resumen de totales --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Ventas</span>
                        <span class="info-box-number">${{ number_format($totales['ventas'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Saldos</span>
                        <span class="info-box-number">${{ number_format($totales['saldos'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cobrado</span>
                        <span class="info-box-number">${{ number_format($totales['cobrado'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Agregar formulario de filtro --}}
        <form method="GET" class="form-row mb-3" id="filterForm">
            <div class="col-md-2">
                <label for="filtroAno">Seleccionar Año:</label>
                <select name="ano" class="form-control" id="filtroAno">
                    <option value="">Seleccione Año</option>
                    @for ($year = date('Y'); $year >= 2000; $year--)
                        <option value="{{ $year }}" {{ request('ano', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtroMes">Seleccionar Mes:</label>
                <select name="mes" class="form-control" id="filtroMes">
                    <option value="">Seleccione Mes</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('mes', date('n')) == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="button" class="btn btn-primary" id="actualButton">Actual</button>
            </div>
        </form>

        {{-- Botón Añadir pedido --}}
        <div class="btn-group mb-3">
            <a type="button" class="btn btn-success" href="{{ route('pedidos.create') }}">Añadir pedido</a>
        </div>

        {{-- Filtro por mes (removed) --}}
        <!-- Previously here, now removed -->

        <div class="table-responsive">
            <table id="pedidosTable" class="table table-striped table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Orden</th>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th>Paciente</th>
                        <th>Total</th>
                        <th>Saldo</th>
                        <th>Acciones</th>
                        <th>Usuario</th> <!-- ...added Usuario column... -->
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->fecha ? $pedido->fecha->format('Y-m-d') : 'Sin fecha' }}</td>
                            <td>{{ $pedido->numero_orden }}</td>
                            <td>
                                <span
                                    style="color: {{ $pedido->fact == 'Pendiente' ? 'orange' : ($pedido->fact == 'Aprobado' ? 'green' : 'black') }}">
                                    {{ $pedido->fact }}
                                </span>
                            </td>
                            <td>{{ $pedido->cliente }}</td>
                            <td>
                                {{ $pedido->celular }}
                                <a href="https://wa.me/593{{ ltrim($pedido->celular, '0') }}?text=Estimado(a) {{ $pedido->paciente }}, le informamos que sus lentes recetados ya están listos para ser recogidos en ESCLERÓPTICA. Puede pasar a retirarlos cuando le sea más conveniente. ¡Lo esperamos pronto! Muchas gracias por confiar en nosotros.&send=true" target="_blank">
                                    <i class="fab fa-whatsapp" style="color: green;"></i>
                                </a>
                            </td>
                            <td>{{ $pedido->paciente }}</td>
                            <td>{{ $pedido->total }}</td>
                            <td>
                                <span style="color: {{ $pedido->saldo == 0 ? 'green' : 'red' }}">
                                    {{ $pedido->saldo }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('pedidos.show', $pedido->id) }}"
                                        class="btn btn-xs btn-default text-primary mx-1 shadow" title="Ver">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </a>
                                    @can('admin')
                                        <a href="{{ route('pedidos.edit', $pedido->id) }}"
                                            class="btn btn-xs btn-default text-primary mx-1 shadow" title="Editar">
                                            <i class="fa fa-lg fa-fw fa-pen"></i>
                                        </a>
                                        <a class="btn btn-xs btn-default text-danger mx-1 shadow" href="#" data-toggle="modal"
                                            data-target="#confirmarEliminarModal" data-id="{{ $pedido->id }}"
                                            data-url="{{ route('pedidos.destroy', $pedido->id) }}">
                                            <i class="fa fa-lg fa-fw fa-trash"></i>
                                        </a>
                                    @endcan
                                    <!-- Botón de Pago -->
                                    <a href="{{ route('pagos.create', ['pedido_id' => $pedido->id]) }}"
                                        class="btn btn-success btn-sm" title="Añadir Pago">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                    <!-- Botón de Aprobar -->
                                    @can('admin')
                                        @if($pedido->fact == 'Pendiente' && $pedido->saldo == 0 && !empty($pedido->cliente))
                                            <form action="{{ route('pedidos.approve', $pedido->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-warning btn-sm" title="Aprobar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                                <!-- Confirmar Eliminar Modal -->
                                <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Confirmar Eliminación
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                ¿Estás seguro de que deseas eliminar este pedido?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cancelar</button>
                                                <form id="eliminarForm" method="post"
                                                    action="{{ route('pedidos.destroy', $pedido->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </td>
                            <td>{{ $pedido->usuario }}</td> <!-- ...display usuario... -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br />
    </div>
</div>

@stop
@section('js')
@include('atajos')
@parent
<script>
    $(document).ready(function () {
        // Configurar el modal antes de mostrarse
        $('#confirmarEliminarModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Botón que activó el modal
            var url = button.data('url'); // Extraer la URL del atributo data-url
            var modal = $(this);
            modal.find('#eliminarForm').attr('action', url); // Actualizar la acción del formulario
        });

        // Inicializar DataTable con nueva configuración
        var pedidosTable = $('#pedidosTable').DataTable({
            "processing": true,
            "scrollX": true,
            "order": [[1, "desc"]], // Ordenar por número de orden descendente
            "pageLength": 50, // Mostrar 50 registros por página
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'excel',
                    text: 'Excel',
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7,9]
                    },
                    filename: 'Pedidos_' + new Date().toISOString().split('T')[0]
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7,9]
                    },
                    filename: 'Pedidos_' + new Date().toISOString().split('T')[0],
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json",
                "search": "Buscar:",
                "info": "_TOTAL_ registros",
                "infoEmpty": "0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "initComplete": function(settings, json) {
                // Ocultar el indicador de "processing" después de la carga inicial
                $(this).DataTable().processing(false);
            }
        });

        // Manejar cambios en los filtros
        $('#filtroAno, #filtroMes').change(function() {
            $('#filterForm').submit();
        });

        // Botón "Actual"
        $('#actualButton').click(function() {
            const now = new Date();
            $('#filtroAno').val(now.getFullYear());
            $('#filtroMes').val(now.getMonth() + 1);
            $('#filterForm').submit();
        });
    });
</script>
@stop