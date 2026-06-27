@extends('adminlte::page')

@section('title', 'RSU JLO - Modelos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">

    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-wrench mr-2 text-white-75"></i> Lista de Modelos de Vehículos
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo">
                <i class="fas fa-plus mr-1"></i> Nuevo Modelo
            </button>
        </div>

        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="datatable" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle">Nombre</th>
                            <th class="align-middle">Código</th>
                            <th class="align-middle">Marca</th>
                            <th class="align-middle">Descripción</th>
                            <th class="align-middle">Creación</th>
                            <th class="align-middle">Actualización</th>
                            <th class="text-center align-middle" width="100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Registro / Edición --}}
<div class="modal fade" id="FormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="FormModalTitle">
                    <i class="fas fa-wrench mr-1"></i> Formulario de Modelos
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

    $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.brandmodel.index') }}",
        columns: [
            { data: "name",        className: 'align-middle' },
            { data: "code",        className: 'align-middle' },
            { data: "brand_id",    className: 'align-middle', orderable: false },
            { data: "description", className: 'align-middle' },
            { data: "created_at",  className: 'align-middle' },
            { data: "updated_at",  className: 'align-middle' },
            { data: "actions",     className: 'text-center align-middle text-nowrap', orderable: false, searchable: false },
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        },
    });

    $(document).on('submit', '.frmEliminar', function (e) {
        e.preventDefault();
        var form = $(this);
        Swal.fire({
            title: "¿Está seguro de Eliminar?",
            text: "¡Esta acción removerá el modelo del catálogo de forma permanente!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#071D38",
            cancelButtonColor: "#a13825",
            confirmButtonText: "Sí, ¡eliminar!",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function (response) {
                        refreshTable();
                        Swal.fire('¡Proceso Exitoso!', response.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('No se puede eliminar', xhr.responseJSON.message, 'error');
                    }
                });
            }
        });
    });

    $('#btn-nuevo').click(function () {
        $.ajax({
            url: "{{ route('admin.brandmodel.create') }}",
            type: "GET",
            success: function (response) {
                $('#FormModal #FormModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Modelo');
                $('#FormModal .modal-body').html(response);
                $('#FormModal').modal("show");
                bindFormSubmit();
            }
        });
    });

    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: "{{ route('admin.brandmodel.edit', 'id') }}".replace('id', id),
            type: "GET",
            success: function (response) {
                $('#FormModal #FormModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Modelo');
                $('#FormModal .modal-body').html(response);
                $('#FormModal').modal("show");
                bindFormSubmit();
            },
            error: function () {
                Swal.fire('Error', 'No se pudieron recuperar los datos del modelo.', 'error');
            }
        });
    });

});

function bindFormSubmit() {
    $('#FormModal form').on("submit", function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#FormModal').modal("hide");
                refreshTable();
                Swal.fire('¡Proceso Exitoso!', response.message, 'success');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Ocurrió un error.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
}

function refreshTable() {
    $('#datatable').DataTable().ajax.reload(null, false);
}
</script>

@if (session('success') != null)
    <script>
        Swal.fire({ title: "Operación exitosa", text: "{{ session('success') }}", icon: "success", draggable: true });
    </script>
@endif

@if (session('error') != null)
    <script>
        Swal.fire({ title: "Ocurrió un error", text: "{{ session('error') }}", icon: "error", draggable: true });
    </script>
@endif

@endsection