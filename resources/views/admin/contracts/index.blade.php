@extends('adminlte::page')

@section('title', 'RSU JLO - Contratos')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-file-contract mr-2"></i> Lista de Contratos
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3 py-2 shadow-sm ml-auto" id="btn-nuevo">
                <i class="fas fa-plus mr-1"></i> Nuevo Contrato
            </button>
        </div>
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblContracts" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle">DNI</th>
                            <th class="align-middle">Empleado</th>
                            <th class="align-middle">Tipo de contrato</th>
                            <th class="align-middle">Inicio</th>
                            <th class="align-middle">Fin</th>
                            <th class="align-middle">Salario</th>
                            <th class="text-center align-middle">Activo</th>
                            <th class="text-center align-middle" width="100">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="ContractModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="ContractModalTitle">
                    <i class="fas fa-file-contract mr-1"></i> Formulario de Contrato
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

    $('#tblContracts').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.contract.index') }}",
        columns: [
            { data: "dni",        className: 'align-middle' },
            { data: "employee",   className: 'align-middle' },
            { data: "type",       className: 'align-middle', orderable: false },
            { data: "start_date", className: 'align-middle' },
            { data: "end_date",   className: 'align-middle' },
            { data: "salary",     className: 'align-middle', orderable: false },
            { data: "active",     className: 'text-center align-middle', orderable: false },
            { data: "actions",    className: 'text-center align-middle text-nowrap', orderable: false, searchable: false },
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
        },
    });

    // ─── NUEVO ────────────────────────────────────────────────────────────────
    $('#btn-nuevo').click(function () {
        $.ajax({
            url: "{{ route('admin.contract.create') }}",
            type: "GET",
            success: function (response) {
                $('#ContractModal #ContractModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nuevo Contrato');
                $('#ContractModal .modal-body').html(response);
                $('#ContractModal').modal("show");
                bindFormSubmit();
            }
        });
    });

    // ─── EDITAR ───────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: "{{ route('admin.contract.edit', 'id') }}".replace('id', id),
            type: "GET",
            success: function (response) {
                $('#ContractModal #ContractModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Contrato');
                $('#ContractModal .modal-body').html(response);
                $('#ContractModal').modal("show");
                bindFormSubmit();
            },
            error: function () {
                Swal.fire('Error', 'No se pudieron recuperar los datos del contrato.', 'error');
            }
        });
    });

    // ─── TOGGLE ACTIVO/INACTIVO ───────────────────────────────────────────────
    $(document).on('click', '.btn-toggle', function () {
        var id = $(this).attr("data-id");
        Swal.fire({
            title: "¿Cambiar estado del contrato?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#071D38",
            cancelButtonColor: "#a13825",
            confirmButtonText: "Sí, cambiar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/contract/' + id + '/toggle',
                    type: "POST",
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        refreshTable();
                        Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON.message, 'error');
                    }
                });
            }
        });
    });

});

function bindFormSubmit() {
    $('#ContractModal form').on("submit", function (e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (res) {
                $('#ContractModal').modal("hide");
                refreshTable();
                Swal.fire('¡Registro Exitoso!', res.message, 'success');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar.';
                Swal.fire('Error de Validación', msg, 'error');
            }
        });
    });
}

function refreshTable() {
    $('#tblContracts').DataTable().ajax.reload(null, false);
}
</script>
@endsection