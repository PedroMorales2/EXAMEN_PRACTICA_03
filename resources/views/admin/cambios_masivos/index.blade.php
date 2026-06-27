@extends('adminlte::page')

@section('title', 'RSU JLO - Cambios de Programaciones')

@section('content')
    <div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">
        <div class="card border-0 shadow-sm custom-crud-card">

            {{-- Header --}}
            <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
                <h4 class="mb-0 font-weight-black text-white">
                    <i class="fas fa-exchange-alt mr-2"></i> Cambios de Programaciones
                </h4>
                <button type="button" class="btn btn-warning font-weight-bold px-3 py-2 shadow-sm" id="btn-nuevo-cambio">
                    <i class="fas fa-sync-alt mr-1"></i> Nuevo Cambio Masivo
                </button>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom bg-light py-3">
                <div class="row align-items-end">
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de inicio</label>
                        <input type="date" id="filtroStart" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de fin</label>
                        <input type="date" id="filtroEnd" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Tipo de cambio</label>
                        <select id="filtroTipo" class="form-control form-control-sm">
                            <option value="">Todos los tipos</option>
                            <option value="turno">Turno</option>
                            <option value="conductor">Conductor</option>
                            <option value="ocupante">Ocupante</option>
                            <option value="vehiculo">Vehículo</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-0">
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
                    <table id="tblCambios" class="table table-custom table-hover w-100">
                        <thead>
                            <tr>
                                <th>Tipo de Cambio</th>
                                <th>Fecha Cambio</th>
                                <th>Período</th>
                                <th>Antes</th>
                                <th>Después</th>
                                <th>Realizado Por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Nuevo Cambio Masivo --}}
    <div class="modal fade" id="CambioModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3 text-white" style="background:#071D38;">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-sync-alt mr-2"></i> Cambio Masivo
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0" id="CambioModalBody"></div>
            </div>
        </div>
    </div>

    {{-- Modal: Confirmar cambio masivo --}}
    <div class="modal fade" id="ConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3 text-white" style="background:#071D38;">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Resumen de la operación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4" id="ConfirmModalBody"></div>
            </div>
        </div>
    </div>

    {{-- Modal: Detalle del cambio --}}
    <div class="modal fade" id="DetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px;overflow:hidden;">
                <div class="modal-header py-3 text-white" id="DetalleModalHeader" style="background:#071D38;">
                    <h5 class="modal-title font-weight-bold" id="DetalleModalTitle">
                        <i class="fas fa-info-circle mr-2"></i> Detalles del Cambio
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4" id="DetalleModalBody" style="max-height:80vh;overflow-y:auto;"></div>
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
                ajax: {
                    url: "{{ route('admin.cambios-masivos.index') }}",
                    data: function (d) {
                        d.start_date = $('#filtroStart').val();
                        d.end_date = $('#filtroEnd').val();
                        d.tipo_cambio = $('#filtroTipo').val();
                    }
                },
                columns: [
                    { data: 'tipo_badge', className: 'align-middle', orderable: false },
                    { data: 'fecha_cambio', className: 'align-middle' },
                    { data: 'periodo', className: 'align-middle', orderable: false },
                    { data: 'antes_col', className: 'align-middle', orderable: false },
                    { data: 'despues_col', className: 'align-middle', orderable: false },
                    { data: 'ejecutado_col', className: 'align-middle', orderable: false },
                    { data: 'actions', className: 'text-center align-middle', orderable: false, searchable: false },
                ],
                order: [[1, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            // ── Filtros ────────────────────────────────────────────
            $('#btnFiltrar').click(function () { table.ajax.reload(); });
            $('#btnLimpiar').click(function () {
                $('#filtroStart, #filtroEnd').val('');
                $('#filtroTipo').val('');
                table.ajax.reload();
            });

            // ── Abrir form cambio masivo ───────────────────────────
            $('#btn-nuevo-cambio').click(function () {
                $.get("{{ route('admin.cambios-masivos.create-form') }}", function (html) {
                    $('#CambioModalBody').html(html);
                    $('#CambioModal').modal('show');
                });
            });

            // ── Ver detalle ────────────────────────────────────────
            $(document).on('click', '.btn-ver-cambio', function () {
                var id = $(this).data('id');
                $.get("{{ url('admin/cambios-masivos') }}/" + id, function (data) {

                    var revertidoHtml = data.revertido
                        ? '<span class="badge badge-secondary px-2"><i class="fas fa-check mr-1"></i>Ya revertido</span>'
                        : '<button class="btn btn-sm btn-outline-warning btn-revertir-fila mt-2" data-id="' + data.id + '">'
                        + '<i class="fas fa-undo mr-1"></i>Revertir este cambio</button>';

                    var masivoHtml = data.es_masivo
                        ? '<span class="badge ml-1" style="background:#8B5CF6;color:#fff;border-radius:20px;font-size:.72rem;">'
                        + '<i class="fas fa-layer-group mr-1"></i>Lote #' + data.lote_id + '</span>'
                        : '';

                    $('#DetalleModalHeader').css('background', data.tipo_color);
                    $('#DetalleModalTitle').html(
                        '<i class="fas fa-info-circle mr-2"></i>Detalles del Cambio #' + data.id
                        + ' <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:.8rem;">'
                        + data.tipo_label + '</span>' + masivoHtml
                    );

                    $('#DetalleModalBody').html(`
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="p-3 h-100" style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:12px;">
                                <div class="d-flex align-items-center mb-3">
                                    <div style="width:32px;height:32px;border-radius:50%;background:#EF4444;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                        <i class="fas fa-arrow-left text-white" style="font-size:.8rem;"></i>
                                    </div>
                                    <div>
                                        <strong style="color:#991B1B;">Valor Anterior</strong>
                                        <div style="font-size:.72rem;color:#B91C1C;">Estado previo al cambio</div>
                                    </div>
                                </div>
                                <div style="font-size:.95rem;font-weight:600;color:#DC2626;">${data.valor_anterior}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 h-100" style="background:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:12px;">
                                <div class="d-flex align-items-center mb-3">
                                    <div style="width:32px;height:32px;border-radius:50%;background:#10B981;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                        <i class="fas fa-arrow-right text-white" style="font-size:.8rem;"></i>
                                    </div>
                                    <div>
                                        <strong style="color:#065F46;">Valor Nuevo</strong>
                                        <div style="font-size:.72rem;color:#047857;">Estado después del cambio</div>
                                    </div>
                                </div>
                                <div style="font-size:.95rem;font-weight:600;color:#059669;">${data.valor_nuevo}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3" style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:12px;">
                        <div class="row text-center">
                            <div class="col-3 border-right">
                                <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Tipo</small>
                                <span style="background:${data.tipo_color};color:#fff;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">${data.tipo_label}</span>
                            </div>
                            <div class="col-3 border-right">
                                <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Realizado Por</small>
                                <strong style="font-size:.82rem;">${data.ejecutado_por}</strong>
                            </div>
                            <div class="col-3 border-right">
                                <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Fecha</small>
                                <strong style="font-size:.82rem;">${data.fecha_cambio}</strong>
                            </div>
                            <div class="col-3">
                                <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Programación</small>
                                <strong style="font-size:.82rem;">#${data.prog_id} — ${data.fecha_prog}</strong>
                            </div>
                        </div>
                        <div class="border-top mt-3 pt-3">
                            <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Motivo</small>
                            <div style="font-size:.85rem;background:#fff;border-radius:8px;padding:8px 12px;border:1px solid #BFDBFE;margin-top:4px;">
                                <i class="fas fa-quote-left text-primary mr-1" style="font-size:.7rem;"></i>${data.motivo}
                            </div>
                        </div>
                        <div class="mt-3 text-center">${revertidoHtml}</div>
                    </div>
                `);

                    $('#DetalleModal').modal('show');
                });
            });

            // ── Render detalle ─────────────────────────────────────
            function renderDetalle(data) {
                $('#DetalleModalHeader').css('background', data.tipo_color);
                $('#DetalleModalTitle').html(
                    '<i class="fas fa-info-circle mr-2"></i> Detalles del Cambio #' + data.id +
                    ' <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:.8rem;margin-left:8px;">' + data.tipo_label + '</span>'
                );

                // Filas de programaciones afectadas
                var filasHtml = '';
                if (data.filas && data.filas.length) {
                    filasHtml = data.filas.map(function (f) {
                        var revertBtn = f.revertido
                            ? '<span class="badge badge-secondary px-2 py-1" style="font-size:.7rem;"><i class="fas fa-check mr-1"></i>Revertido</span>'
                            : '<button class="btn btn-xs btn-outline-warning btn-revertir-fila" data-id="' + f.id + '" style="font-size:.72rem;padding:2px 8px;">'
                            + '<i class="fas fa-undo mr-1"></i>Revertir</button>';
                        return '<tr>'
                            + '<td style="font-size:.8rem;"><span class="badge badge-light border">#' + f.programacion_id + '</span></td>'
                            + '<td style="font-size:.8rem;">' + f.fecha_prog + '</td>'
                            + '<td style="font-size:.8rem;color:#dc3545;">' + f.valor_anterior + '</td>'
                            + '<td style="font-size:.8rem;color:#198754;">' + f.valor_nuevo + '</td>'
                            + '<td class="text-center">' + revertBtn + '</td>'
                            + '</tr>';
                    }).join('');
                } else {
                    filasHtml = '<tr><td colspan="5" class="text-center text-muted py-3">Sin registros de filas afectadas.</td></tr>';
                }

                $('#DetalleModalBody').html(`
                        {{-- Valores Anteriores / Nuevos --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="p-3 h-100" style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:12px;">
                                    <div class="d-flex align-items-center mb-3">
                                        <div style="width:32px;height:32px;border-radius:50%;background:#EF4444;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                            <i class="fas fa-arrow-left text-white" style="font-size:.8rem;"></i>
                                        </div>
                                        <div>
                                            <strong style="color:#991B1B;">Valores Anteriores</strong>
                                            <div style="font-size:.72rem;color:#B91C1C;">Estado previo al cambio</div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3">
                                        <div class="mb-2">
                                            <small class="text-uppercase font-weight-bold" style="font-size:.68rem;color:#9CA3AF;letter-spacing:.05em;">${data.tipo_label}</small>
                                            <div style="font-size:.92rem;font-weight:600;color:#DC2626;">${data.valor_anterior}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 h-100" style="background:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:12px;">
                                    <div class="d-flex align-items-center mb-3">
                                        <div style="width:32px;height:32px;border-radius:50%;background:#10B981;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                            <i class="fas fa-arrow-right text-white" style="font-size:.8rem;"></i>
                                        </div>
                                        <div>
                                            <strong style="color:#065F46;">Valores Nuevos</strong>
                                            <div style="font-size:.72rem;color:#047857;">Estado después del cambio</div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3">
                                        <div class="mb-2">
                                            <small class="text-uppercase font-weight-bold" style="font-size:.68rem;color:#9CA3AF;letter-spacing:.05em;">${data.tipo_label}</small>
                                            <div style="font-size:.92rem;font-weight:600;color:#059669;">${data.valor_nuevo}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Info del cambio --}}
                        <div class="p-3 mb-4" style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:12px;">
                            <div class="d-flex align-items-center mb-3">
                                <div style="width:32px;height:32px;border-radius:50%;background:#3B82F6;display:flex;align-items:center;justify-content:center;margin-right:10px;">
                                    <i class="fas fa-info text-white" style="font-size:.8rem;"></i>
                                </div>
                                <strong style="color:#1E40AF;">Información del Cambio</strong>
                            </div>
                            <div class="row text-center">
                                <div class="col-3 border-right">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;letter-spacing:.05em;">Tipo de Cambio</small>
                                    <span style="background:${data.tipo_color};color:#fff;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">${data.tipo_label}</span>
                                </div>
                                <div class="col-3 border-right">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;letter-spacing:.05em;">Realizado Por</small>
                                    <strong style="font-size:.82rem;">${data.ejecutado_por}</strong>
                                </div>
                                <div class="col-3 border-right">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;letter-spacing:.05em;">Fecha del Cambio</small>
                                    <strong style="font-size:.82rem;">${data.fecha_ejecucion}</strong>
                                </div>
                                <div class="col-3">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;letter-spacing:.05em;">Programaciones</small>
                                    <strong style="font-size:.82rem;">${data.afectadas} afectada(s)</strong>
                                </div>
                            </div>
                            <div class="border-top mt-3 pt-3 row">
                                <div class="col-6">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Período</small>
                                    <strong style="font-size:.82rem;">${data.fecha_inicio} — ${data.fecha_fin}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Zona</small>
                                    <strong style="font-size:.82rem;">${data.zona}</strong>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-uppercase font-weight-bold d-block" style="font-size:.65rem;color:#6B7280;">Motivo del Cambio</small>
                                    <div style="font-size:.82rem;background:#fff;border-radius:8px;padding:8px 12px;border:1px solid #BFDBFE;margin-top:4px;">
                                        <i class="fas fa-quote-left text-primary mr-1" style="font-size:.7rem;"></i>
                                        ${data.descripcion}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabla de programaciones afectadas --}}
                        <div>
                            <strong class="d-block mb-2" style="font-size:.88rem;">
                                <i class="fas fa-list mr-1 text-secondary"></i>
                                Programaciones Afectadas
                                <span class="badge badge-secondary ml-1">${data.afectadas}</span>
                            </strong>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="font-size:.75rem;"># Prog.</th>
                                            <th style="font-size:.75rem;">Fecha</th>
                                            <th style="font-size:.75rem;">Valor Anterior</th>
                                            <th style="font-size:.75rem;">Valor Nuevo</th>
                                            <th style="font-size:.75rem;" class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>${filasHtml}</tbody>
                                </table>
                            </div>
                        </div>
                    `);
            }

            // ── Revertir fila individual ───────────────────────────
            $(document).on('click', '.btn-revertir-fila', function () {
                var filaId = $(this).data('id');
                var $btn = $(this);

                Swal.fire({
                    title: '¿Revertir este cambio?',
                    text: 'Solo se revertirá esta programación específica, no el lote completo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F59E0B',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, revertir',
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ url('admin/cambios-masivos') }}/" + filaId + "/revertir",
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            $btn.replaceWith(
                                '<span class="badge badge-secondary px-2 py-1" style="font-size:.7rem;"><i class="fas fa-check mr-1"></i>Revertido</span>'
                            );
                            table.ajax.reload(null, false);
                            Swal.fire('Revertido', res.message, 'success');
                        },
                        error: function (xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Error al revertir', 'error');
                        }
                    });
                });
            });

        });
    </script>
@endsection