@extends('adminlte::page')

@section('title', 'RSU JLO - Feriados')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    
    <div class="card border-0 shadow-sm custom-crud-card mb-4">
        <div class="card-header custom-crud-header d-flex flex-column flex-md-row align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-calendar-alt mr-2 text-white-75"></i> Lista de feriados
            </h4>
            <div class="d-flex mt-3 mt-md-0 ml-auto">
                <button type="button" class="btn font-weight-bold shadow-sm mr-2 text-white d-flex align-items-center justify-content-center" style="background-color: #6c757d; border-radius: 10px; width: 230px;" id="btn-cargar-peru">
                    <i class="fas fa-cloud-download-alt mr-2"></i> Cargar Feriados Perú
                </button>
                <button type="button" class="btn btn-action-add font-weight-bold shadow-sm text-white d-flex align-items-center justify-content-center" style="border-radius: 10px; width: 230px;" id="btn-nuevo-feriado">
                    <i class="fas fa-plus mr-2"></i> Nuevo Feriado
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #071D38, #0b2e59); border-radius: 12px; color: white;">
                <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                    <div>
                        <h6 class="text-white-50 font-weight-bold text-xs mb-1" style="font-size: 11px; letter-spacing: 0.5px;">TOTAL FERIADOS</h6>
                        <h3 class="mb-0 font-weight-black" id="card-total">{{ $totalHolidays }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5" style="background: rgba(255,255,255,0.1);"><i class="fas fa-globe fa-lg text-white-75"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #28a745, #1e7e34); border-radius: 12px; color: white;">
                <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                    <div>
                        <h6 class="text-white-50 font-weight-bold text-xs mb-1" style="font-size: 11px; letter-spacing: 0.5px;">FERIADOS ACTIVOS</h6>
                        <h3 class="mb-0 font-weight-black" id="card-active">{{ $activeHolidays }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5" style="background: rgba(255,255,255,0.1);"><i class="fas fa-calendar-check fa-lg text-white-75"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #a13825, #7d2617); border-radius: 12px; color: white;">
                <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                    <div>
                        <h6 class="text-white-50 font-weight-bold text-xs mb-1" style="font-size: 11px; letter-spacing: 0.5px;">FERIADOS INACTIVOS</h6>
                        <h3 class="mb-0 font-weight-black" id="card-inactive">{{ $inactiveHolidays }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5" style="background: rgba(255,255,255,0.1);"><i class="fas fa-calendar-times fa-lg text-white-75"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6c757d, #495057); border-radius: 12px; color: white;">
                <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                    <div>
                        <h6 class="text-white-50 font-weight-bold text-xs mb-1" style="font-size: 11px; letter-spacing: 0.5px;">AÑO ACTUAL</h6>
                        <h3 class="mb-0 font-weight-black">{{ $currentYear }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5" style="background: rgba(255,255,255,0.1);"><i class="fas fa-clock fa-lg text-white-75"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body bg-light p-3">
            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-2 mb-md-0">
                    <label for="filterStartDate" class="font-weight-bold text-xs text-secondary mb-1">FECHA INICIO</label>
                    <input type="date" id="filterStartDate" class="form-control form-control-sm rounded-xl">
                </div>
                <div class="col-md-3 form-group mb-2 mb-md-0">
                    <label for="filterEndDate" class="font-weight-bold text-xs text-secondary mb-1">FECHA FIN</label>
                    <input type="date" id="filterEndDate" class="form-control form-control-sm rounded-xl">
                </div>
                <div class="col-md-3 form-group mb-2 mb-md-0">
                    <label for="filterStatus" class="font-weight-bold text-xs text-secondary mb-1">ESTADO</label>
                    <select id="filterStatus" class="form-control form-control-sm rounded-xl">
                        <option value="">Todos</option>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-md-3 text-right">
                    <button type="button" class="btn btn-sm font-weight-bold px-3 rounded-xl shadow-sm mr-1 text-white" id="btn-filtrar-tabla" style="background-color: #071D38;">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-sm font-weight-bold px-3 rounded-xl shadow-sm text-white" id="btn-limpiar-filtros" style="background-color: #a13825;">
                        <i class="fas fa-undo mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblHolidays" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fechas</th>
                            <th>Descripción Detallada</th>
                            <th>Estado</th>
                            <th>Día</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="HolidayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header text-white py-3" style="background-color: #071D38;">
                <h5 class="modal-title font-weight-bold" id="HolidayModalTitle">Formulario de Feriado</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white"></div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
    <style>
        .rounded-xl { border-radius: 8px !important; }
        .text-xs { font-size: 11px !important; letter-spacing: 0.05em; }
    </style>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicialización de la tabla asíncrona mapeada a los filtros
            let table = $('#tblHolidays').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.holiday.index') }}",
                    data: function(d) {
                        d.start_date = $('#filterStartDate').val();
                        d.end_date = $('#filterEndDate').val();
                        d.status = $('#filterStatus').val();
                    }
                },
                columns: [
                    { data: "id", className: "align-middle font-weight-bold text-muted" },
                    { data: "date", className: "align-middle font-weight-bold text-dark-blue" },
                    { data: "description", className: "align-middle text-secondary" },
                    { data: "badge_status", className: "align-middle text-center" },
                    { data: "day_name", className: "align-middle font-weight-bold text-purple" },
                    { data: "actions", orderable: false, searchable: false, className: "text-center align-middle text-nowrap" }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // ACCIÓN FILA 3: Ejecutar el filtro avanzado
            $('#btn-filtrar-tabla').on('click', function() {
                table.draw();
                updateMetricCards();
            });

            // ACCIÓN FILA 3: Limpiar controles y restaurar grilla
            $('#btn-limpiar-filtros').on('click', function() {
                $('#filterStartDate').val('');
                $('#filterEndDate').val('');
                $('#filterStatus').val('');
                table.draw();
                updateMetricCards();
            });

            // Refrescar tarjetas de métricas por JSON
            function updateMetricCards() {
                let start = $('#filterStartDate').val();
                let end = $('#filterEndDate').val();
                let stat = $('#filterStatus').val();
                $.ajax({
                    url: "{{ route('admin.holiday.index') }}",
                    type: "GET",
                    data: { action: 'get_metrics', start_date: start, end_date: end, status: stat },
                    success: function(response) {
                        $('#card-total').text(response.total);
                        $('#card-active').text(response.active);
                        $('#card-inactive').text(response.inactive);
                    }
                });
            }

            // ACCIÓN: ABRIR MODAL CREAR
            $('#btn-nuevo-feriado').click(function() {
                $.ajax({
                    url: "{{ route('admin.holiday.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#HolidayModal #HolidayModalTitle').html('<i class="fas fa-plus-circle"></i> Registrar Día Feriado');
                        $('#HolidayModal .modal-body').html(response);
                        $('#HolidayModal').modal("show");

                        $('#HolidayModal form').off("submit").on("submit", function(e) {
                            e.preventDefault();
                            $.ajax({
                                url: $(this).attr('action'),
                                type: $(this).attr('method'),
                                data: $(this).serialize(),
                                success: function(res) {
                                    $('#HolidayModal').modal("hide");
                                    table.draw();
                                    updateMetricCards();
                                    Swal.fire('¡Registrado!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    Swal.fire('Validación', xhr.responseJSON.message || 'Error', 'error');
                                }
                            });
                        });
                    }
                });
            });
        });

        // Botón Cargar Feriados Perú (Decorativo para la presentación)
        $('#btn-cargar-peru').click(function() {
            Swal.fire('Módulo en Desarrollo', 'Esta funcionalidad estará disponible próximamente en las siguientes versiones.', 'info');
        });

        // ACCIÓN: ABRIR MODAL EDITAR
       // ACCIÓN: ABRIR MODAL EDITAR
        $(document).on('click', '.btn-editar', function() {
            let id = $(this).attr('id');
            $.ajax({
                // 🎯 LA CLAVE: Al poner ':id' con los dos puntos, JavaScript buscará esa cadena exacta al final y no tocará la palabra holiday
                url: "{{ route('admin.holiday.edit', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $('#HolidayModal #HolidayModalTitle').html('<i class="fas fa-edit"></i> Modificar Día Feriado');
                    $('#HolidayModal .modal-body').html(response);
                    $('#HolidayModal').modal("show");

                    $('#HolidayModal form').off("submit").on("submit", function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: $(this).serialize(),
                            success: function(res) {
                                $('#HolidayModal').modal("hide");
                                $('#tblHolidays').DataTable().ajax.reload(null, false);
                                updateMetricCards();
                                Swal.fire('Actualizado', res.message, 'success');
                            },
                            error: function(xhr) { Swal.fire('Validación', xhr.responseJSON.message, 'error'); }
                        });
                    });
                }
            });
        });

        // ACCIÓN: ELIMINAR SOLICITUD
        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            let form = $(this);
            Swal.fire({
                title: '¿Eliminar feriado?',
                text: "El día se removerá permanentemente del calendario de validación.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#071D38',
                cancelButtonColor: '#a13825',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function(res) { 
                            $('#tblHolidays').DataTable().ajax.reload(null, false); 
                            
                            // Forzamos el refresco de las métricas al eliminar un registro
                            let start = $('#filterStartDate').val();
                            let end = $('#filterEndDate').val();
                            let stat = $('#filterStatus').val();
                            $.ajax({
                                url: "{{ route('admin.holiday.index') }}",
                                type: "GET",
                                data: { action: 'get_metrics', start_date: start, end_date: end, status: stat },
                                success: function(response) {
                                    $('#card-total').text(response.total);
                                    $('#card-active').text(response.active);
                                    $('#card-inactive').text(response.inactive);
                                }
                            });
                            
                            Swal.fire('Eliminado', res.message, 'success'); 
                        }
                    });
                }
            });
        });
    </script>
@endsection