@extends('adminlte::page')

@section('title', 'RSU JLO - Colores')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-palette mr-2 text-white-75"></i> Lista de colores
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm ml-auto" id="btn-nuevo-color">
                <i class="fas fa-plus mr-1.5"></i> Nuevo Color
            </button>
        </div>
                
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblColors" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" width="12%">Muestra</th>
                            <th class="align-middle" width="23%">Nombre</th>
                            <th class="text-center align-middle" width="15%">Código HEX</th>
                            <th class="align-middle" width="40%">Descripción Detallada</th>
                            <th class="text-center align-middle" width="10%">Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ColorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content">
            <div class="modal-header custom-modal-header text-white py-3">
                <h5 class="modal-title font-weight-bold" id="ColorModalTitle">Formulario de Color</h5>
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
 
            $('#tblColors').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.color.index') }}",
                columns: [
                    { 
                        data: "code",
                        orderable: false, 
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function(data) {
                            return `<div class="color-preview-circle mx-auto" style="background-color: ${data};" data-toggle="tooltip" title="${data}"></div>`;
                        }
                    },
                    { 
                        data: "name",
                        className: 'align-middle font-weight-bold text-dark-blue'
                    },
                    { 
                        data: "code",
                        className: 'text-center align-middle',
                        render: function(data) {
                            return `<span class="badge custom-hex-badge px-2.5 py-1.5 shadow-sm">${data}</span>`;
                        }
                    },
                    { 
                        data: "description", 
                        className: 'align-middle text-muted',
                        defaultContent: '<i class="text-muted opacity-60">Sin descripción registrada</i>' 
                    },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }, 
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                },
            });

            
            $('#btn-nuevo-color').click(function() {
                $.ajax({
                    url: "{{ route('admin.color.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#ColorModal #ColorModalTitle').html('<i class="fas fa-plus-circle mr-1.5"></i> Nuevo Color');
                        $('#ColorModal .modal-body').html(response);
                        $('#ColorModal').modal("show");

                        $('#ColorModal form').on("submit", function(e) {
                            e.preventDefault();
                            var form = $(this);

                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: form.serialize(),
                                success: function(res) {
                                    $('#ColorModal').modal("hide");
                                    refreshTable();
                                    Swal.fire('¡Registro Exitoso!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al guardar el color.';
                                    if (xhr.status === 422 && res.message) { msg = res.message; }
                                    Swal.fire({ title: 'Color Duplicado o Inválido', text: msg, icon: 'error' });
                                }
                            });
                        });
                    }
                });
            });
        });

        
        $(document).on('click', '.btn-editar', function() {
            var id = $(this).attr("id");
            $.ajax({
                url: "{{ route('admin.color.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#ColorModal #ColorModalTitle').html('<i class="fas fa-edit mr-1.5"></i> Editar Registro');
                    $('#ColorModal .modal-body').html(response);
                    $('#ColorModal').modal("show");

                    $('#ColorModal form').on("submit", function(e) {
                        e.preventDefault();
                        var form = $(this);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            success: function(res) {
                                $('#ColorModal').modal("hide");
                                refreshTable(); 
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function(xhr) {
                                var res = xhr.responseJSON;
                                var msg = 'Ocurrió un inconveniente al actualizar el color.';
                                if (xhr.status === 422 && res.message) { msg = res.message; }
                                Swal.fire({ title: 'Color Duplicado o Inválido', text: msg, icon: 'error' });
                            }
                        });
                    });
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron recuperar los datos del color.', 'error');
                }
            });
        });

        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            var form = $(this); 
            
            Swal.fire({
                title: "¿Está seguro de Eliminar?",
                text: "¡Esta acción removerá el color del catálogo de forma permanente!",
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
                            Swal.fire('Error', 'No se pudo eliminar el registro en el servidor.', 'error');
                        }
                    });
                }
            });
        });

        function refreshTable() {
            $('#tblColors').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection