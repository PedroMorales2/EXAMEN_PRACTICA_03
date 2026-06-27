@extends('adminlte::page')

@section('title', 'RSU JLO - Motivos de Cambio')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">

        {{-- Header --}}
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-exchange-alt mr-2"></i> Lista de Motivos de Cambio
            </h4>
            <button type="button"
                    class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto"
                    id="btn-nuevo-cambio">
                <i class="fas fa-plus mr-1"></i> Nuevo Motivo
            </button>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblCambios" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Fecha Creación</th>
                            <th class="text-center">Fecha Actualización</th>
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
<div class="modal fade" id="CambioModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header text-white py-3" style="background-color:#071D38;">
                <h5 class="modal-title font-weight-bold" id="CambioModalTitle">
                    Motivo de Cambio
                </h5>
                <button type="button" class="close text-white"
                        data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white" id="CambioModalBody"></div>
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

        // ── DataTable ──────────────────────────────────────────
        var table = $('#tblCambios').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.cambio.index') }}",
            columns: [
                { data: 'name',        className: 'align-middle font-weight-bold' },
                { data: 'description', className: 'align-middle text-muted' },
                { data: 'created_fmt', className: 'text-center align-middle text-nowrap' },
                { data: 'updated_fmt', className: 'text-center align-middle text-nowrap' },
                {
                    data: 'actions',
                    className: 'text-center align-middle text-nowrap',
                    orderable: false,
                    searchable: false,
                },
            ],
            order: [[2, 'desc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ── Nuevo motivo ───────────────────────────────────────
        $('#btn-nuevo-cambio').click(function () {
            $.get("{{ route('admin.cambio.create') }}", function (response) {
                $('#CambioModalTitle').html(
                    '<i class="fas fa-plus-circle mr-1"></i> Nuevo Motivo de Cambio'
                );
                $('#CambioModalBody').html(response);
                $('#CambioModal').modal('show');
                bindFormSubmit(null);
            });
        });

        // ── Editar ─────────────────────────────────────────────
        $(document).on('click', '.btn-editar', function () {
            var id = $(this).data('id');
            $.get("{{ route('admin.cambio.edit', 'ID') }}".replace('ID', id), function (response) {
                $('#CambioModalTitle').html(
                    '<i class="fas fa-edit mr-1"></i> Editar Motivo de Cambio'
                );
                $('#CambioModalBody').html(response);
                $('#CambioModal').modal('show');
                bindFormSubmit(id);
            });
        });

        // ── Eliminar ───────────────────────────────────────────
        $(document).on('submit', '.frmEliminar', function (e) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: '¿Eliminar motivo?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#a13825',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url:  form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
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

        // ── Submit handler ─────────────────────────────────────
        function bindFormSubmit(cambioId) {
            $('#CambioModal').off('submit.cambioForm').on('submit.cambioForm', '#formCambio', function (e) {
                e.preventDefault();
                $.ajax({
                    url:  $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#CambioModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire(
                            cambioId ? '¡Actualizado!' : '¡Registrado!',
                            res.message,
                            'success'
                        );
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