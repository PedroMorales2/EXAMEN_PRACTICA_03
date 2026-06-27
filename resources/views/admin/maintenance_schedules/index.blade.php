@extends('adminlte::page')

@section('title', 'RSU JLO - Horarios de Mantenimiento')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">

        {{-- Header --}}
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-calendar-alt mr-2"></i> {{ strtoupper($maintenance->name) }}
            </h4>
            <div class="ml-auto">
                <a href="{{ route('admin.maintenance.index') }}" class="btn btn-light font-weight-bold mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm" id="btn-nuevo">
                    <i class="fas fa-plus mr-1"></i> Nuevo Horario
                </button>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblSchedules" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Vehículo</th>
                            <th>Responsable</th>
                            <th>Tipo</th>
                            <th class="text-center">Inicio</th>
                            <th class="text-center">Fin</th>
                            <th class="text-center">Ver</th>
                            <th class="text-center">Editar</th>
                            <th class="text-center">Del</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="ScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header text-white py-3" style="background-color:#071D38;">
                <h5 class="modal-title font-weight-bold" id="ScheduleModalTitle">Horario</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white" id="ScheduleModalBody"></div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function () {

        var table = $('#tblSchedules').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.maintenance-schedule.index', $maintenance->id) }}",
            columns: [
                { data: 'weekday_name',     className: 'align-middle font-weight-bold' },
                { data: 'vehicle_name',     className: 'align-middle' },
                { data: 'responsible_name', className: 'align-middle' },
                { data: 'type_fmt',         className: 'align-middle' },
                { data: 'start_fmt',        className: 'text-center align-middle text-nowrap' },
                { data: 'end_fmt',          className: 'text-center align-middle text-nowrap' },
                { data: 'ver',              className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'edit',             className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'delete',           className: 'text-center align-middle', orderable: false, searchable: false },
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ── Nuevo ──────────────────────────────────────────────
        $('#btn-nuevo').click(function () {
            $.get("{{ route('admin.maintenance-schedule.create', $maintenance->id) }}", function (response) {
                $('#ScheduleModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Horario');
                $('#ScheduleModalBody').html(response);
                $('#ScheduleModal').modal('show');
                bindFormSubmit(null);
            });
        });

        // ── Editar ─────────────────────────────────────────────
        $(document).on('click', '.btn-editar', function () {
            var id = $(this).data('id');
            $.get("{{ route('admin.maintenance-schedule.edit', 'ID') }}".replace('ID', id), function (response) {
                $('#ScheduleModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Horario');
                $('#ScheduleModalBody').html(response);
                $('#ScheduleModal').modal('show');
                bindFormSubmit(id);
            });
        });

        // ── Eliminar (con confirmación; elimina días generados) ─
        $(document).on('click', '.btn-eliminar', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Eliminar horario?',
                text: 'Se eliminarán también todos los días generados de este horario.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a13825',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.maintenance-schedule.destroy', 'ID') }}".replace('ID', id),
                        type: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            table.ajax.reload(null, false);
                            Swal.fire('Eliminado', res.message, 'success');
                        },
                        error: function (xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                        }
                    });
                }
            });
        });

        // ── Submit form ────────────────────────────────────────
        function bindFormSubmit(id) {
            $('#ScheduleModal').off('submit.form').on('submit.form', '#formSchedule', function (e) {
                e.preventDefault();
                $.ajax({
                    url:  $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#ScheduleModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire(id ? '¡Actualizado!' : '¡Registrado!', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                    }
                });
            });
        }
    });
    </script>
@endsection
