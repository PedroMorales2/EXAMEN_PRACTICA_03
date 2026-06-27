@extends('adminlte::page')

@section('title', 'RSU JLO - Grupos de Personal')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-users mr-2"></i> Grupos de Personal
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo-grupo">
                <i class="fas fa-plus mr-1"></i> Nuevo Grupo
            </button>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblGroups" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Zona</th>
                            <th>Turno</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Ayudantes</th>
                            <th>Días</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="GroupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header text-white py-3" style="background-color:#071D38;">
                <h5 class="modal-title font-weight-bold" id="GroupModalTitle">Grupo de Personal</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white" id="GroupModalBody"></div>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
    <style>
        .badge-day {
            display: inline-block;
            color: #fff;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: .7rem;
            font-weight: 700;
            margin: 1px;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function () {

        // ── DataTable ──────────────────────────────────────────
        var table = $('#tblGroups').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.personal-group.index') }}",
            columns: [
                { data: 'id',              className: 'align-middle text-muted' },
                { data: 'name',            className: 'align-middle font-weight-bold' },
                { data: 'zona_name',       className: 'align-middle' },
                { data: 'schedule_badge',  className: 'align-middle', orderable: false },
                { data: 'vehicle_name',    className: 'align-middle' },
                { data: 'conductor_name',  className: 'align-middle', orderable: false },
                { data: 'ayudantes_names', className: 'align-middle', orderable: false },
                { data: 'days_badges',     className: 'align-middle', orderable: false },
                { data: 'badge_status',    className: 'text-center align-middle', orderable: false },
                { data: 'actions',         className: 'text-center align-middle text-nowrap', orderable: false, searchable: false },
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ── Nuevo grupo ────────────────────────────────────────
        $('#btn-nuevo-grupo').click(function () {
            $.get("{{ route('admin.personal-group.create') }}", function (response) {
                $('#GroupModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Grupo de Personal');
                $('#GroupModalBody').html(response);
                $('#GroupModal').modal('show');
                initForm(null);
            });
        });

        // ── Editar ─────────────────────────────────────────────
        $(document).on('click', '.btn-editar', function () {
            var id = $(this).data('id');
            $.get("{{ route('admin.personal-group.edit', 'ID') }}".replace('ID', id), function (response) {
                $('#GroupModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Grupo de Personal');
                $('#GroupModalBody').html(response);
                $('#GroupModal').modal('show');
                initForm(id);
            });
        });

        // ── Eliminar ───────────────────────────────────────────
        $(document).on('submit', '.frmEliminar', function (e) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: '¿Eliminar grupo?',
                text: 'Se eliminará permanentemente el grupo y sus asignaciones de personal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a13825',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function (res) { table.ajax.reload(null, false); Swal.fire('Eliminado', res.message, 'success'); },
                        error:   function (xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error'); }
                    });
                }
            });
        });

        // ── Init form submit ───────────────────────────────────
        // FIX #1: eliminada recolección de ayudantes desde '#selected-ayudantes .selected-user'
        //         (selector inexistente). El form.blade ya inyecta input[name="ayudantes[]"]
        //         antes del submit, así que $form.serialize() los captura solos.
        //
        // FIX #2: se usa un namespace de evento ('submit.groupForm') con off() previo
        //         para evitar que se acumulen listeners al abrir el modal múltiples veces,
        //         y se elimina la competencia con el submit handler del form.blade.
        function initForm(groupId) {
            $('#GroupModal').off('submit.groupForm').on('submit.groupForm', '#formGroup', function (e) {
                e.preventDefault();
                var $form = $(this);
                var url   = $form.attr('action');

                // Respetar el method spoofing de Laravel (PUT vía _method hidden)
                // El form siempre envía POST; Laravel interpreta _method=PUT como UPDATE.
                $.ajax({
                    url:  url,
                    type: 'POST',
                    data: $form.serialize(), // incluye _token, _method y ayudantes[]
                    success: function (res) {
                        $('#GroupModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire(groupId ? '¡Actualizado!' : '¡Registrado!', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error de validación', xhr.responseJSON?.message || 'Error al guardar', 'error');
                    }
                });
            });
        }

    });
    </script>
@endsection