@extends('adminlte::page')

@section('title', 'RSU JLO - Tipos de Personal')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-user-tag mr-2 text-white-75"></i> Lista de Tipos de Personal
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm ml-auto" id="btn-nuevo-tipo">
                <i class="fas fa-plus mr-1.5"></i> Nuevo Tipo
            </button>
        </div>
                
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblUserTypes" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="align-middle" width="40%">Nombre del Tipo de Personal</th>
                            <th class="align-middle" width="45%">Descripción</th>
                            <th class="text-center align-middle" width="15%">Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="UserTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="UserTypeModalTitle">Formulario de Tipo de Personal</h5>
                <button type="button" class="close text-white opacity-80 hover-opacity-100" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light-panel">
            </div>
        </div>
    </div>
</div>

<div class="p-2"></div>
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
        $(document).ready(function() {

            // Inicialización de DataTable (Renderiza solo las 3 columnas visibles)
            $('#tblUserTypes').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.usertype.index') }}",
                columns: [
                    { 
                        data: "name",
                        className: 'align-middle font-weight-bold text-dark-blue'
                    },
                    { 
                        data: "description", 
                        className: 'align-middle text-muted',
                        defaultContent: '<i class="text-muted opacity-60">Sin descripción registrada</i>' 
                    },
                    { 
                        data: "actions", 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center align-middle text-nowrap' 
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                }
            });

            // Acción: Presionar Botón Nuevo Tipo
            $('#btn-nuevo-tipo').click(function() {
                $.ajax({
                    url: "{{ route('admin.usertype.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#UserTypeModal #UserTypeModalTitle').html('<i class="fas fa-plus-circle mr-1.5"></i> Nuevo Tipo de Personal');
                        $('#UserTypeModal .modal-body').html(response);
                        $('#UserTypeModal').modal("show");

                        $('#UserTypeModal form').on("submit", function(e) {
                            e.preventDefault();
                            var form = $(this);

                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: form.serialize(),
                                success: function(res) {
                                    $('#UserTypeModal').modal("hide");
                                    refreshTable();
                                    Swal.fire('¡Registro Exitoso!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al guardar el tipo de personal.';
                                    if (xhr.status === 422 && res.message) { msg = res.message; }
                                    Swal.fire({ title: 'Dato Duplicado o Inválido', text: msg, icon: 'error' });
                                }
                            });
                        });
                    }
                });
            });

            // Acción: Presionar Botón Editar Tipo (Lanza el Modal con la data cargada)
            $(document).on('click', '.btn-editar', function() {
                var id = $(this).attr("id");
                $.ajax({
                    url: "{{ route('admin.usertype.edit', 'id') }}".replace('id', id),
                    type: "GET",
                    success: function(response) {
                        $('#UserTypeModal #UserTypeModalTitle').html('<i class="fas fa-edit mr-1.5"></i> Editar Tipo de Personal');
                        $('#UserTypeModal .modal-body').html(response);
                        $('#UserTypeModal').modal("show");

                        $('#UserTypeModal form').on("submit", function(e) {
                            e.preventDefault();
                            var form = $(this);

                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: form.serialize(),
                                success: function(res) {
                                    $('#UserTypeModal').modal("hide");
                                    refreshTable(); 
                                    Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al actualizar el registro.';
                                    if (xhr.status === 422 && res.message) { msg = res.message; }
                                    Swal.fire({ title: 'Dato Duplicado o Inválido', text: msg, icon: 'error' });
                                }
                            });
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron recuperar los datos desde el servidor.', 'error');
                    }
                });
            });

            // Acción: Enviar Formulario de Eliminación (SweetAlert2)
            $(document).on('submit', '.frmEliminar', function(e) {
                e.preventDefault();
                var form = $(this); 
                
                Swal.fire({
                    title: "¿Está seguro de Eliminar?",
                    text: "¡Esta acción removerá el tipo de personal de forma permanente!",
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
                            success: function(res) {
                                refreshTable(); 
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function(xhr) {
                                var res = xhr.responseJSON;
                                var msg = 'No se pudo eliminar el registro en el servidor.';
                                if (xhr.status === 422 && res.message) { msg = res.message; }
                                Swal.fire('Restricción de Integridad', msg, 'error');
                            }
                        });
                    }
                });
            });

            // Función Global para recargar de forma asíncrona la Tabla
            function refreshTable() {
                $('#tblUserTypes').DataTable().ajax.reload(null, false);
            }
        });
    </script>
@endsection