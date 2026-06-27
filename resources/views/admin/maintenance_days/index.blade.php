@extends('adminlte::page')

@section('title', 'RSU JLO - Días de Mantenimiento')

@section('content')
<div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
    <div class="card border-0 shadow-sm custom-crud-card">

        {{-- Header --}}
        <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
            <h4 class="mb-0 font-weight-black text-white">
                <i class="fas fa-calendar-day mr-2"></i>
                {{ strtoupper($schedule->maintenance->name) }} – {{ $schedule->weekday_name }} – {{ $schedule->vehicle->name ?? '-' }}
            </h4>
            <a href="{{ route('admin.maintenance-schedule.index', $schedule->maintenance_id) }}"
               class="btn btn-light font-weight-bold ml-auto">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>

        {{-- Tabla --}}
        <div class="card-body p-4 bg-white">
            <div class="table-responsive">
                <table id="tblDays" class="table table-custom table-hover w-100">
                    <thead>
                        <tr>
                            <th class="text-center">Fecha</th>
                            <th>Observación</th>
                            <th class="text-center">Imagen</th>
                            <th class="text-center">Editar</th>
                            <th class="text-center">Est</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="DayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
            <div class="modal-header text-white py-3" style="background-color:#071D38;">
                <h5 class="modal-title font-weight-bold" id="DayModalTitle">Editar Día</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white" id="DayModalBody"></div>
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

        var table = $('#tblDays').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.maintenance-day.index', $schedule->id) }}",
            columns: [
                { data: 'date_fmt',        className: 'text-center align-middle font-weight-bold text-nowrap' },
                { data: 'observation_fmt', className: 'align-middle' },
                { data: 'image_fmt',       className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'actions',         className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'status_fmt',      className: 'text-center align-middle', orderable: false, searchable: false },
            ],
            ordering: false,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
        });

        // ── Editar día ─────────────────────────────────────────
        $(document).on('click', '.btn-editar', function () {
            var id = $(this).data('id');
            $.get("{{ route('admin.maintenance-day.edit', 'ID') }}".replace('ID', id), function (response) {
                $('#DayModalBody').html(response);
                $('#DayModal').modal('show');
                bindFormSubmit();
            });
        });

        // ── Submit form (multipart con imagen) ─────────────────
        function bindFormSubmit() {
            $('#DayModal').off('submit.form').on('submit.form', '#formDay', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url:  $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#DayModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('¡Actualizado!', res.message, 'success');
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
