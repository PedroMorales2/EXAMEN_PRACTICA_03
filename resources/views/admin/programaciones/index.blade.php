@extends('adminlte::page')

@section('title', 'RSU JLO - Lista de Programaciones')

@section('content')
    <div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
        <div class="card border-0 shadow-sm custom-crud-card">

            {{-- Header --}}
            <div class="card-header custom-crud-header d-flex align-items-center justify-content-between flex-wrap py-3"
                style="gap:.5rem;">
                <h4 class="mb-0 font-weight-black text-white">
                    <i class="far fa-calendar-alt mr-2"></i> Lista de Programaciones
                </h4>
                <div class="ml-auto d-flex" style="gap:.5rem;">
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-tachometer-alt mr-1"></i> Ir al Dashboard
                    </a>
                    <button type="button" class="btn btn-success font-weight-bold" id="btn-nueva-programacion">
                        <i class="fas fa-plus mr-1"></i> Nueva Programación
                    </button>
                    <button type="button" class="btn btn-danger font-weight-bold" id="btn-programacion-masiva">
                        <i class="fas fa-layer-group mr-1"></i> Programación Masiva
                    </button>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom bg-light py-3">
                <div class="row align-items-end">
                    <div class="col-md-2 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de Inicio</label>
                        <input type="date" id="filtroStart" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de Fin</label>
                        <input type="date" id="filtroEnd" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Zona</label>
                        <select id="filtroZone" class="form-control form-control-sm">
                            <option value="">Todas las zonas</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Turno</label>
                        <select id="filtroSchedule" class="form-control form-control-sm">
                            <option value="">Todos los turnos</option>
                            @foreach($schedules as $schedule)
                                <option value="{{ $schedule->id }}">{{ $schedule->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-0">
                        <button id="btnFiltrar" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <button id="btnLimpiar" class="btn btn-outline-secondary btn-sm w-100 mt-1">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body p-4 bg-white">
                <div class="table-responsive">
                    <table id="tblProgramaciones" class="table table-custom table-hover w-100">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Estado</th>
                                <th>Zona</th>
                                <th>Turno</th>
                                <th>Vehículo</th>
                                <th>Conductor</th>
                                <th>Ayudantes</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Nueva / Editar Programación --}}
    <div class="modal fade" id="ProgramacionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3" style="background-color:#F5A623;">
                    <h5 class="modal-title font-weight-bold text-dark" id="ProgramacionModalTitle">
                        <i class="fas fa-calendar-plus mr-1"></i> Nueva Programación
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body p-4 bg-white" id="ProgramacionModalBody"></div>
            </div>
        </div>
    </div>

    {{-- 2. MODAL Programación Masiva --}}
    <div class="modal fade" id="MasivoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3" style="background-color:#071D38;">
                    <h5 class="modal-title font-weight-bold text-white">
                        <i class="fas fa-layer-group mr-1"></i> Programación Masiva
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4 bg-white" id="MasivoModalBody" style="max-height:85vh;overflow-y:auto;"></div>
            </div>
        </div>
    </div>

    {{-- Modal Ver Detalle --}}
    <div class="modal fade" id="DetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3" style="background-color:#071D38;">
                    <h5 class="modal-title font-weight-bold text-white">
                        <i class="fas fa-info-circle mr-1"></i> Detalle de Programación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4" id="DetalleModalBody"></div>
            </div>
        </div>
    </div>

    {{-- Modal Historial --}}
    <div class="modal fade" id="HistorialModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3" style="background-color:#4B5563;">
                    <h5 class="modal-title font-weight-bold text-white">
                        <i class="fas fa-history mr-1"></i> Historial de Cambios
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4" id="HistorialModalBody"></div>
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
        let tableProg;

        $(document).ready(function () {

            // ── DataTable ──────────────────────────────────────────
            tableProg = $('#tblProgramaciones').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.programacion.index') }}",
                    data: function (d) {
                        d.start_date = $('#filtroStart').val();
                        d.end_date = $('#filtroEnd').val();
                        d.zone_id = $('#filtroZone').val();
                        d.schedule_id = $('#filtroSchedule').val();
                    }
                },
                columns: [
                    { data: 'fecha_fmt', className: 'align-middle font-weight-bold text-nowrap' },
                    { data: 'badge_status', className: 'align-middle', orderable: false },
                    { data: 'zona_name', className: 'align-middle' },
                    { data: 'turno_name', className: 'align-middle', orderable: false },
                    { data: 'vehicle_name', className: 'align-middle', orderable: false },
                    { data: 'conductor_name', className: 'align-middle' },
                    { data: 'ayudantes_names', className: 'align-middle', orderable: false },
                    { data: 'actions', className: 'text-center align-middle text-nowrap', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // ── Filtros ────────────────────────────────────────────
            $('#btnFiltrar').click(function () { tableProg.ajax.reload(); });
            $('#btnLimpiar').click(function () {
                $('#filtroStart, #filtroEnd').val('');
                $('#filtroZone, #filtroSchedule').val('');
                tableProg.ajax.reload();
            });

            // ── Nueva Programación ─────────────────────────────────
            $('#btn-nueva-programacion').click(function () {
                $.get("{{ route('admin.programacion.create') }}", function (response) {
                    $('#ProgramacionModalTitle').html('<i class="fas fa-calendar-plus mr-1"></i> Nueva Programación');
                    $('#ProgramacionModalBody').html(response);
                    $('#ProgramacionModal').modal('show');
                    bindFormSubmit(null);
                });
            });

            // Abrir modal de Programación Masiva
            $('#btn-programacion-masiva').click(function () {
                $.get("{{ route('admin.programacion.create-masivo') }}", function (response) {
                    $('#MasivoModalBody').html(response);
                    $('#MasivoModal').modal('show');

                    // Submit masivo
                    $('#MasivoModal').off('submit.masivoForm').on('submit.masivoForm', '#formMasivo', function (e) {
                        e.preventDefault();
                        var $btn = $('#btnGuardarMasivo');
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

                        $.ajax({
                            url: $(this).attr('action'),
                            type: 'POST',
                            data: $(this).serialize(),
                            success: function (res) {
                                $('#MasivoModal').modal('hide');
                                tableProg.ajax.reload(null, false);
                                Swal.fire('¡Guardado!', res.message, 'success');
                            },
                            error: function (xhr) {
                                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar');
                                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                            }
                        });
                    });
                });
            });


            // ── Ver Detalle ────────────────────────────────────────
            $(document).on('click', '.btn-ver', function () {
                let id = $(this).data('id');

                $.get("{{ route('admin.programacion.show', 'ID') }}".replace('ID', id), function (data) {

                    // ── Status badge ──
                    const statusColors = {
                        'Programado': '#3B82F6',
                        'Reprogramado': '#8B5CF6',
                        'Finalizado': '#10B981',
                        'Cancelado': '#EF4444',
                    };

                    const statusColor = statusColors[data.status] || '#6B7280';

                    const statusBadge = `
                    <span style="background:${statusColor};color:#fff;padding:3px 12px;border-radius:20px;font-size:.78rem;font-weight:700;">
                        ${data.status}
                    </span>`;

                    // ── Ayudantes rows ──
                    const ayudantesRows = (data.ayudantes || []).map((a, i) => `
                    <tr>
                        <td class="bg-light font-weight-bold" style="width:38%;font-size:.82rem;">
                            Ayudante ${i + 1}
                        </td>
                        <td style="font-size:.82rem;">
                            ${a.name}
                        </td>
                    </tr>
                `).join('');

                    // ── Historial rows ──
                    const campoColors = {
                        turno: '#4F46E5',
                        vehiculo: '#059669',
                        conductor: '#EA580C',
                        ayudantes: '#EA580C',
                        status: '#6B7280'
                    };

                    const historialRows = (data.cambios || []).length
                        ? data.cambios.map(c => {
                            const bc = campoColors[c.campo] || '#6B7280';

                            return `
                            <tr>
                                <td style="font-size:.75rem;white-space:nowrap;">${c.fecha}</td>
                                <td style="font-size:.75rem;">${c.usuario}</td>
                                <td>
                                    <span style="background:${bc};color:#fff;padding:1px 8px;border-radius:10px;font-size:.7rem;">
                                        ${c.campo}
                                    </span>
                                </td>
                                <td style="font-size:.75rem;color:#dc3545;">
                                    ${c.valor_anterior ?? '—'}
                                </td>
                                <td style="font-size:.75rem;color:#198754;">
                                    ${c.valor_nuevo ?? '—'}
                                </td>
                                <td style="font-size:.75rem;color:#6B7280;">
                                    ${c.motivo ?? '—'}
                                </td>
                            </tr>`;
                        }).join('')
                        : `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3" style="font-size:.82rem;">
                                <i class="fas fa-inbox mr-1"></i>Sin cambios registrados.
                            </td>
                        </tr>`;

                    const html = `
                    <table class="table table-bordered table-sm mb-3">
                        <tr>
                            <td class="bg-light font-weight-bold" style="width:38%;font-size:.82rem;">Fecha</td>
                            <td style="font-size:.82rem;"><strong>${data.fecha}</strong></td>
                        </tr>

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Estado</td>
                            <td>${statusBadge}</td>
                        </tr>

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Grupo</td>
                            <td style="font-size:.82rem;">${data.group?.name ?? '-'}</td>
                        </tr>

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Zona</td>
                            <td style="font-size:.82rem;">${data.zone?.name ?? '-'}</td>
                        </tr>

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Turno</td>
                            <td style="font-size:.82rem;">
                                ${data.schedule?.name ?? '-'}
                                (${data.schedule?.time_start ?? ''} — ${data.schedule?.time_end ?? ''})
                            </td>
                        </tr>

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Vehículo</td>
                            <td style="font-size:.82rem;">
                                ${data.vehicle?.name ?? '-'} — ${data.vehicle?.code ?? ''}
                            </td>
                        </tr>

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Conductor</td>
                            <td style="font-size:.82rem;">
                                ${data.conductor?.name ?? '-'}
                            </td>
                        </tr>

                        ${ayudantesRows}

                        <tr>
                            <td class="bg-light font-weight-bold" style="font-size:.82rem;">Observaciones</td>
                            <td style="font-size:.82rem;">
                                ${data.observaciones ?? '-'}
                            </td>
                        </tr>
                    </table>

                    <div class="font-weight-bold mb-2" style="font-size:.85rem;">
                        <i class="fas fa-history mr-1 text-secondary"></i>
                        Historial de Cambios
                        ${data.cambios?.length
                            ? `<span class="badge badge-secondary ml-1">${data.cambios.length}</span>`
                            : ''}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="border:1px solid #dee2e6;border-radius:8px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="font-size:.75rem;">Fecha</th>
                                    <th style="font-size:.75rem;">Usuario</th>
                                    <th style="font-size:.75rem;">Campo</th>
                                    <th style="font-size:.75rem;">Anterior</th>
                                    <th style="font-size:.75rem;">Nuevo</th>
                                    <th style="font-size:.75rem;">Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${historialRows}
                            </tbody>
                        </table>
                    </div>`;

                    $('#DetalleModalBody').html(html);
                    $('#DetalleModal').modal('show');
                });
            });

            // ── Editar ─────────────────────────────────────────────
            $(document).on('click', '.btn-editar', function () {
                let id = $(this).data('id');
                $.get("{{ route('admin.programacion.edit', 'ID') }}".replace('ID', id), function (response) {
                    $('#ProgramacionModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Programación');
                    $('#ProgramacionModalBody').html(response);
                    $('#ProgramacionModal').modal('show');
                    bindFormSubmit(id);
                });
            });

            // ── Historial ──────────────────────────────────────────
            $(document).on('click', '.btn-historial', function () {
                let id = $(this).data('id');
                $.get("{{ url('admin/programacion') }}/" + id + "/historial", function (data) {
                    if (!data.cambios.length) {
                        $('#HistorialModalBody').html(
                            '<p class="text-muted text-center py-3"><i class="fas fa-inbox mr-1"></i>Sin cambios registrados.</p>'
                        );
                    } else {
                        let rows = data.cambios.map(c => `
                                            <tr>
                                                <td class="text-nowrap">${c.fecha}</td>
                                                <td>${c.usuario}</td>
                                                <td><span class="badge badge-secondary">${c.campo}</span></td>
                                                <td><small class="text-danger">${c.valor_anterior ?? '—'}</small></td>
                                                <td><small class="text-success">${c.valor_nuevo ?? '—'}</small></td>
                                                <td><small>${c.motivo ?? '—'}</small></td>
                                            </tr>`).join('');
                        $('#HistorialModalBody').html(`
                                            <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Fecha</th><th>Usuario</th><th>Campo</th>
                                                        <th>Anterior</th><th>Nuevo</th><th>Motivo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>${rows}</tbody>
                                            </table></div>`);
                    }
                    $('#HistorialModal').modal('show');
                });
            });

            // ── Finalizar ──────────────────────────────────────────
            $(document).on('click', '.btn-finalizar', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: '¿Finalizar programación?',
                    text: 'El estado cambiará a Finalizado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, finalizar'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/programacion') }}/" + id + "/finalizar",
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: res => { tableProg.ajax.reload(null, false); Swal.fire('Finalizado', res.message, 'success'); },
                            error: xhr => { Swal.fire('Error', xhr.responseJSON?.message, 'error'); }
                        });
                    }
                });
            });

            // ── Eliminar ───────────────────────────────────────────
            $(document).on('submit', '.frmEliminar', function (e) {
                e.preventDefault();
                let form = $(this);
                Swal.fire({
                    title: '¿Eliminar programación?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#a13825',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: res => { tableProg.ajax.reload(null, false); Swal.fire('Eliminado', res.message, 'success'); },
                            error: xhr => { Swal.fire('Error', xhr.responseJSON?.message || 'Error', 'error'); }
                        });
                    }
                });
            });

            // ── Submit handler compartido (nueva y edición) ────────
            function bindFormSubmit(progId) {
                $('#ProgramacionModal').off('submit.progForm').on('submit.progForm', '#formProgramacion', function (e) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: $(this).serialize(),
                        success: res => {
                            $('#ProgramacionModal').modal('hide');
                            tableProg.ajax.reload(null, false);
                            Swal.fire(progId ? '¡Actualizado!' : '¡Guardado!', res.message, 'success');
                        },
                        error: xhr => {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                        }
                    });
                });
            }
        });
    </script>
@endsection