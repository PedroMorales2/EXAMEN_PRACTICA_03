@extends('adminlte::page')

@section('title', 'RSU JLO - Gestión de Personal')

@section('content')
    <div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
        <div class="card border-0 shadow-sm custom-crud-card">
            <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
                <h4 class="mb-0 font-weight-black text-white">
                    <i class="fas fa-users mr-2 text-white-75"></i> Lista de Personal
                </h4>
                <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm ml-auto"
                    id="btn-nuevo-usuario">
                    <i class="fas fa-plus mr-1.5"></i> Nuevo Personal
                </button>
            </div>

            <div class="card-body p-4 bg-white">
                <div class="table-responsive">
                    <table id="tblUsers" class="table table-custom table-hover w-100">
                        <thead>
                            <tr>
                                <th class="align-middle" width="5%">Foto</th>
                                <th class="align-middle" width="10%">DNI</th>
                                <th class="align-middle" width="25%">Nombres y Apellidos</th>
                                <th class="align-middle" width="20%">Email</th>
                                <th class="align-middle" width="15%">Tipo</th>
                                <th class="align-middle" width="10%">Estado</th>
                                <th class="align-middle" width="10%">Creación</th>
                                <th class="text-center align-middle" width="5%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg custom-modal-content">
                <div class="modal-header custom-modal-header text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="UserModalTitle">Formulario de Personal</h5>
                    <button type="button" class="close text-white opacity-80 hover-opacity-100" data-dismiss="modal"
                        aria-label="Close">
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
            $('#tblUsers').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.user.index') }}",
                columns: [
                    { data: "photo", orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: "dni", className: 'align-middle font-weight-bold text-secondary' },
                    { data: "name", className: 'align-middle font-weight-bold text-dark-blue' },
                    { data: "email", className: 'align-middle' },
                    { data: "type_name", className: 'align-middle' },
                    { data: "status", className: 'align-middle text-center' },
                    { data: "created_at_formatted", className: 'align-middle text-muted' },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json' }
            });

            // Registrar Nuevo
            $('#btn-nuevo-usuario').click(function () {
                $.ajax({
                    url: "{{ route('admin.user.create') }}",
                    type: "GET",
                    success: function (response) {
                        $('#UserModal #UserModalTitle').html('<i class="fas fa-user-plus mr-1.5"></i> Registrar Nuevo Personal');
                        $('#UserModal .modal-body').html(response);
                        $('#UserModal').modal("show");
                        attachFormSubmit();
                    }
                });
            });

            // Editar
            $(document).on('click', '.btn-editar', function () {
                var id = $(this).attr("id");
                $.ajax({
                    url: "{{ route('admin.user.edit', 'id') }}".replace('id', id),
                    type: "GET",
                    success: function (response) {
                        $('#UserModal #UserModalTitle').html('<i class="fas fa-user-edit mr-1.5"></i> Editar Datos de Personal');
                        $('#UserModal .modal-body').html(response);
                        $('#UserModal').modal("show");
                        attachFormSubmit();
                    }
                });
            });

            // CORREGIDO: Envío de datos preparado para soportar archivos multimedia (Imágenes)
            function attachFormSubmit() {
                $('#UserModal form').off("submit").on("submit", function (e) {
                    e.preventDefault();
                    var form = $(this);
                    
                    // Empaquetamos todo el formulario de forma nativa incluyendo archivos binarios
                    var formData = new FormData(this);

                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        processData: false,  // CRUCIAL: Le dice a jQuery que no transforme el objeto en string
                        contentType: false,  // CRUCIAL: Le dice a jQuery que no use "application/x-www-form-urlencoded"
                        success: function (res) {
                            $('#UserModal').modal("hide");
                            $('#tblUsers').DataTable().ajax.reload(null, false);
                            Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON?.message || 'Ocurrió un error inesperado.';
                            Swal.fire({ title: 'Error de Validación', text: msg, icon: 'error' });
                        }
                    });
                });
            }

            // Eliminar
            $(document).on('submit', '.frmEliminar', function (e) {
                e.preventDefault();
                var form = $(this);
                Swal.fire({
                    title: "¿Está seguro de remover a este miembro del personal?",
                    text: "¡Esta acción no se puede deshacer!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#071D38",
                    cancelButtonColor: "#a13825",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(), // En eliminar sí dejamos serialize porque solo viajan tokens de texto
                            success: function (res) {
                                $('#tblUsers').DataTable().ajax.reload(null, false);
                                Swal.fire('¡Eliminado!', res.message, 'success');
                            },
                            error: function (xhr) {
                                Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection