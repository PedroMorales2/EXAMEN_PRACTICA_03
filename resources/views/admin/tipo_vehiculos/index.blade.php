@extends('adminlte::page')

@section('title', 'RSU JLO - Tipos de Vehículos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">

    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-truck mr-2 text-white-75"></i> Lista de Tipos de Vehículos
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo-tipo">
                <i class="fas fa-plus mr-1"></i> Nuevo Tipo
            </button>
        </div>

        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblTipos" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle" width="30%">Nombre</th>
                            <th class="align-middle" width="55%">Descripción</th>
                            <th class="text-center align-middle" width="15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="TipoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="TipoModalTitle">
                    <i class="fas fa-truck mr-1"></i> Formulario de Tipo de Vehículo
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
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    $('#tblTipos').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.tipo-vehiculo.index') }}",
        columns: [
            { data: 'name',        className: 'align-middle' },
            { data: 'description', className: 'align-middle', defaultContent: '<i class="text-muted">Sin descripción</i>' },
            { data: 'actions',     className: 'text-center align-middle text-nowrap', orderable: false, searchable: false },
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        },
    });

    // ─── NUEVO ────────────────────────────────────────────────────────────────
    $('#btn-nuevo-tipo').click(function () {
        $.ajax({
            url: "{{ route('admin.tipo-vehiculo.create') }}",
            type: 'GET',
            success: function (response) {
                $('#TipoModal #TipoModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Tipo de Vehículo');
                $('#TipoModal .modal-body').html(response);
                $('#TipoModal').modal('show');
                bindFormSubmit();
            }
        });
    });

    // ─── EDITAR ───────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr('id');
        $.ajax({
            url: "{{ route('admin.tipo-vehiculo.edit', 'id') }}".replace('id', id),
            type: 'GET',
            success: function (response) {
                $('#TipoModal #TipoModalTitle').html('<i class="fas fa-edit mr-1"></i> Modificar Tipo de Vehículo');
                $('#TipoModal .modal-body').html(response);
                $('#TipoModal').modal('show');
                bindFormSubmit();
            },
            error: function () {
                Swal.fire('Error', 'No se pudieron recuperar los datos del tipo.', 'error');
            }
        });
    });

    // ─── ELIMINAR ─────────────────────────────────────────────────────────────
    $(document).on('submit', '.frmEliminar', function (e) {
        e.preventDefault();
        var form = $(this);
        Swal.fire({
            title: '¿Está seguro de Eliminar?',
            text: '¡Esta acción removerá el tipo de vehículo del catálogo de forma permanente!',
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
    $('#TipoModal form').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (res) {
                $('#TipoModal').modal('hide');
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

function refreshTable() {
    $('#tblTipos').DataTable().ajax.reload(null, false);
}
</script>
@endsection