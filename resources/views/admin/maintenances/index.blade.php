@extends('adminlte::page')

@section('title', 'RSU JLO - Mantenimientos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">

        {{-- Header --}}
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-tools mr-2"></i> Lista de Mantenimientos
            </h4>
            <button type="button"
                    class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto"
                    id="btn-nuevo">
                <i class="fas fa-plus mr-1"></i> Nuevo Mantenimiento
            </button>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblMaintenances" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="text-center">Inicio</th>
                            <th class="text-center">Fin</th>
                            <th class="text-center">Hor</th>
                            <th class="text-center">Editar</th>
                            <th class="text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="MaintenanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header text-white py-3" style="background-color:#071D38;">
                <h5 class="modal-title font-weight-bold" id="MaintenanceModalTitle">Mantenimiento</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white" id="MaintenanceModalBody"></div>
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

        var table = $('#tblMaintenances').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.maintenance.index') }}",
            columns: [
                { data: 'name',      className: 'align-middle font-weight-bold' },
                { data: 'start_fmt', className: 'text-center align-middle text-nowrap' },
                { data: 'end_fmt',   className: 'text-center align-middle text-nowrap' },
                { data: 'horarios',  className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'edit',      className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'delete',    className: 'text-center align-middle', orderable: false, searchable: false },
            ],
            order: [[1, 'asc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ── Nuevo ──────────────────────────────────────────────
        $('#btn-nuevo').click(function () {
            $.get("{{ route('admin.maintenance.create') }}", function (response) {
                $('#MaintenanceModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Mantenimiento');
                $('#MaintenanceModalBody').html(response);
                $('#MaintenanceModal').modal('show');
                bindFormSubmit(null);
            });
        });

        // ── Editar ─────────────────────────────────────────────
        $(document).on('click', '.btn-editar', function () {
            var id = $(this).data('id');
            $.get("{{ route('admin.maintenance.edit', 'ID') }}".replace('ID', id), function (response) {
                $('#MaintenanceModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Mantenimiento');
                $('#MaintenanceModalBody').html(response);
                $('#MaintenanceModal').modal('show');
                bindFormSubmit(id);
            });
        });

        // ── Eliminar (validada) ────────────────────────────────
        $(document).on('click', '.btn-eliminar', function () {
            var id    = $(this).data('id');
            var name  = $(this).data('name');
            var count = parseInt($(this).data('count')) || 0;

            var text = count > 0
                ? 'El mantenimiento "' + name + '" tiene ' + count + ' horario(s). Se eliminarán también sus horarios y días generados.'
                : 'Se eliminará el mantenimiento "' + name + '".';

            Swal.fire({
                title: '¿Eliminar mantenimiento?',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a13825',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.maintenance.destroy', 'ID') }}".replace('ID', id),
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
            $('#MaintenanceModal').off('submit.form').on('submit.form', '#formMaintenance', function (e) {
                e.preventDefault();
                $.ajax({
                    url:  $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#MaintenanceModal').modal('hide');
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
