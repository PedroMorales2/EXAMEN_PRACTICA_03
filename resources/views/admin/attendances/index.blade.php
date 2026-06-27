@extends('adminlte::page')

@section('title', 'RSU JLO - Asistencias')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-user-check mr-2"></i> Lista de Asistencias
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo">
                <i class="fas fa-plus mr-1"></i> Agregar nueva asistencia
            </button>
        </div>

        {{-- Filtros --}}
        <div class="card-body border-bottom pb-3 bg-white">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="font-weight-bold">Fecha de Inicio</label>
                    <input type="date" id="filter_start" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Fecha de fin</label>
                    <input type="date" id="filter_end" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Buscar empleado</label>
                    <input type="text" id="filter_employee" class="form-control" placeholder="DNI, nombre o apellido...">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mr-2" id="btn-filter">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <button class="btn btn-secondary" id="btn-clear">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblAttendances" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle">DNI</th>
                            <th class="align-middle">Empleado</th>
                            <th class="align-middle">Fecha y Hora</th>
                            <th class="text-center align-middle">Tipo</th>
                            <th class="text-center align-middle">Estado</th>
                            <th class="align-middle">Turno</th>
                            <th class="align-middle">Notas</th>
                            <th class="text-center align-middle" width="100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="AttendanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="AttendanceModalTitle">
                    <i class="fas fa-user-check mr-1"></i> Formulario de Asistencia
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light-panel"></div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <link class="styles-master" rel="stylesheet" href="{{ asset('custom-crud.css') }}">
    <style>
        #AttendanceModal .select2-container--bootstrap4 .select2-selection--single { height: 38px !important; }
        #AttendanceModal .select2-container { z-index: 1060 !important; }
    </style>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
var table;

$(document).ready(function () {
    table = $('#tblAttendances').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.attendance.index') }}",
            data: function (d) {
                d.start_date       = $('#filter_start').val();
                d.end_date         = $('#filter_end').val();
                d.search_employee  = $('#filter_employee').val();
            }
        },
        columns: [
            { data: 'dni',           className: 'align-middle' },
            { data: 'employee',      className: 'align-middle' },
            { data: 'datetime',      className: 'align-middle', orderable: false },
            { data: 'type',          className: 'text-center align-middle', orderable: false },
            { data: 'status',        className: 'text-center align-middle', orderable: false },
            { data: 'schedule_name', className: 'align-middle', orderable: false },
            { data: 'notes',         className: 'align-middle', defaultContent: '<i class="text-muted">-</i>' },
            { data: 'actions',       className: 'text-center align-middle text-nowrap', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json' },
    });

    $('#btn-filter').click(function () { table.ajax.reload(); });
    $('#btn-clear').click(function () {
        $('#filter_start').val(''); $('#filter_end').val(''); $('#filter_employee').val('');
        table.ajax.reload();
    });

    // Nuevo
    $('#btn-nuevo').click(function () {
        $.ajax({
            url: "{{ route('admin.attendance.create') }}",
            type: "GET",
            success: function (response) {
                $('#AttendanceModal #AttendanceModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nueva Asistencia');
                $('#AttendanceModal .modal-body').html(response);
                $('#AttendanceModal').modal("show");
                bindFormSubmit();
            }
        });
    });

    // Editar
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: "{{ route('admin.attendance.edit', 'id') }}".replace('id', id),
            type: "GET",
            success: function (response) {
                $('#AttendanceModal #AttendanceModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Asistencia');
                $('#AttendanceModal .modal-body').html(response);
                $('#AttendanceModal').modal("show");
                bindFormSubmit();
            },
            error: function () {
                Swal.fire('Error', 'No se pudieron recuperar los datos.', 'error');
            }
        });
    });

    // Eliminar
    $(document).on('submit', '.frmEliminar', function (e) {
        e.preventDefault();
        var form = $(this);
        Swal.fire({
            title: '¿Está seguro de Eliminar?',
            text: '¡Esta acción removerá la asistencia de forma permanente!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#071D38',
            cancelButtonColor: '#a13825',
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function (res) {
                        refreshTable();
                        Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo eliminar.', 'error');
                    }
                });
            }
        });
    });
});

function bindFormSubmit() {
    $('#AttendanceModal form').off('submit').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (res) {
                $('#AttendanceModal').modal('hide');
                refreshTable();
                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Ocurrió un error.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
}

function refreshTable() { table.ajax.reload(null, false); }
</script>
@endsection