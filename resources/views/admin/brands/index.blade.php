@extends('adminlte::page')

@section('title', 'RSU JLO - Marcas')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    
    <div class="card border-0 shadow-sm custom-crud-card">
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-copyright mr-2 text-white-75"></i> Lista de Marcas
            </h4>
            <button type="button" class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm ml-auto" id="btn-nueva-marca">
                <i class="fas fa-plus mr-1.5"></i> Nueva Marca
            </button>
        </div>
                
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblBrands" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" width="15%">Logo</th>
                            <th class="align-middle" width="25%">Nombre</th>
                            <th class="align-middle" width="50%">Descripción Detallada</th>
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

<div class="modal fade" id="BrandModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg custom-modal-content" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header custom-modal-header text-white py-3" style="background-color: #071D38;">
                <h5 class="modal-title font-weight-bold" id="BrandModalTitle">Formulario de Marca</h5>
                <button type="button" class="close text-white opacity-80 hover-opacity-100" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-white">
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
            $('#tblBrands').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.brand.index') }}",
                columns: [
                    { data: "logo", orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: "name", className: 'align-middle font-weight-bold text-dark-blue' },
                    { 
                        data: "description", 
                        className: 'align-middle text-muted',
                        defaultContent: '<i class="text-muted opacity-60">Sin descripción registrada</i>' 
                    },
                    { data: "actions", orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' }, 
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                },
            });

            $('#btn-nueva-marca').click(function() {
                $.ajax({
                    url: "{{ route('admin.brand.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#BrandModal #BrandModalTitle').html('<i class="fas fa-plus-circle mr-1.5"></i> Nueva Marca');
                        $('#BrandModal .modal-body').html(response);
                        $('#BrandModal').modal("show");

                        $('#BrandModal form').on("submit", function(e) {
                            e.preventDefault();
                            var formData = new FormData(this);

                            $.ajax({
                                url: $(this).attr('action'),
                                type: $(this).attr('method'),
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(res) {
                                    $('#BrandModal').modal("hide");
                                    refreshBrandTable();
                                    Swal.fire('¡Registro Exitoso!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    var res = xhr.responseJSON;
                                    var msg = 'Ocurrió un inconveniente al guardar la marca.';
                                    if (xhr.status === 422 && res.message) { msg = res.message; }
                                    Swal.fire({ title: 'Marca Inválida o Duplicada', text: msg, icon: 'error' });
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
                url: "{{ route('admin.brand.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#BrandModal #BrandModalTitle').html('<i class="fas fa-edit mr-1.5"></i> Editar Marca');
                    $('#BrandModal .modal-body').html(response);
                    $('#BrandModal').modal("show");

                    $('#BrandModal form').on("submit", function(e) {
                        e.preventDefault();
                        var formData = new FormData(this);
                        if($(this).find('input[name="_method"]').val() === 'PUT') {
                            formData.append('_method', 'PUT');
                        }

                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST', 
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                $('#BrandModal').modal("hide");
                                refreshBrandTable(); 
                                Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                            },
                            error: function(xhr) {
                                var res = xhr.responseJSON;
                                var msg = 'Ocurrió un inconveniente al actualizar la marca.';
                                if (xhr.status === 422 && res.message) { msg = res.message; }
                                Swal.fire({ title: 'Marca Inválida o Duplicada', text: msg, icon: 'error' });
                            }
                        });
                    });
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron recuperar los datos de la marca.', 'error');
                }
            });
        });

        $(document).on('submit', '.frmEliminar', function(e) {
            e.preventDefault();
            var form = $(this); 
            
            Swal.fire({
                title: "¿Está seguro de Eliminar?",
                text: "¡Esta acción removerá la marca de forma permanente!",
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
                            refreshBrandTable(); 
                            Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                        },
                        error: function(xhr) {
                            var res = xhr.responseJSON;
                            var msg = 'No se pudo eliminar el registro en el servidor.';
                            
                            if (xhr.status === 422 && res.message) { msg = res.message; }
                            Swal.fire('Restricción de Borrado', msg, 'error');
                        }
                    });
                }
            });
        });

        function refreshBrandTable() {
            $('#tblBrands').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection