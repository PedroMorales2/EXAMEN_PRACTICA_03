{{--
    form_edit.blade.php
    Edición de una programación puntual (una fecha).
    Permite cambiar: turno, vehículo y/o personal de forma independiente,
    cada uno con motivo predefinido + texto libre.
    El status pasa a "Reprogramado" al guardar.
--}}

@php
    $motivosTurno = [
        'Cambio de turno por requerimiento operativo',
        'Falla en el turno original',
        'Solicitud del personal',
        'Optimización de rutas',
        'Otro',
    ];
    $motivosVehiculo = [
        'Falla mecánica del vehículo actual',
        'Vehículo en mantenimiento preventivo',
        'Vehículo en mantenimiento correctivo',
        'Capacidad insuficiente',
        'Otro',
    ];
    $motivosPersonal = [
        'Enfermedad del conductor/ayudante',
        'Inasistencia injustificada',
        'Vacaciones de emergencia',
        'Reasignación por requerimiento operativo',
        'Otro',
    ];
@endphp

<form
    action="{{ route('admin.programacion.update', $prog->id) }}"
    method="POST"
    id="formProgramacion"
>
    @csrf
    @method('PUT')

    {{-- ── Info fija (no editable) ─────────────────────────── --}}
    <div class="row mb-3 p-3 mx-0 rounded" style="background:#F0F4FF;border:1px solid #C7D7FC;">
        <div class="col-3 text-center border-right">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Fecha</small>
            <strong style="font-size:1rem;">{{ $prog->fecha->format('d/m/Y') }}</strong>
            <div style="font-size:.78rem;color:#6B7280;">{{ $prog->fecha->translatedFormat('l') }}</div>
        </div>
        <div class="col-3 text-center border-right">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Zona</small>
            <strong>{{ optional($prog->zone)->name ?? '-' }}</strong>
        </div>
        <div class="col-3 text-center border-right">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Grupo</small>
            <strong>{{ optional($prog->group)->name ?? '-' }}</strong>
        </div>
        <div class="col-3 text-center">
            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Estado actual</small>
            @if($prog->status === 'Reprogramado')
                <span class="badge px-2 py-1" style="background:#8B5CF6;color:#fff;border-radius:20px;">
                    <i class="fas fa-sync-alt mr-1"></i>Reprogramado
                </span>
            @else
                <span class="badge px-2 py-1 bg-primary text-white" style="border-radius:20px;">
                    <i class="fas fa-calendar-check mr-1"></i>Programado
                </span>
            @endif
        </div>
    </div>

    {{-- ── SECCIÓN 1: Turno ─────────────────────────────────── --}}
    <div class="cambio-section card border mb-3" id="section-turno">
        <div class="card-header d-flex align-items-center justify-content-between py-2"
             style="background:#F8FAFC;cursor:pointer;" data-toggle-section="turno">
            <div class="d-flex align-items-center">
                <div class="section-icon mr-3" style="width:32px;height:32px;border-radius:50%;background:#EEF2FF;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-clock" style="color:#4F46E5;font-size:.85rem;"></i>
                </div>
                <div>
                    <strong style="font-size:.9rem;">Cambiar Turno</strong>
                    <div style="font-size:.77rem;color:#6B7280;">
                        Actual: <strong>{{ optional($prog->schedule)->name ?? '-' }}</strong>
                        ({{ optional($prog->schedule)->time_start }} — {{ optional($prog->schedule)->time_end }})
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="cambio-badge d-none mr-2" id="badge-turno">
                    <span class="badge" style="background:#4F46E5;color:#fff;border-radius:20px;font-size:.72rem;">
                        <i class="fas fa-check mr-1"></i>Cambio registrado
                    </span>
                </div>
                <div class="custom-control custom-switch mb-0">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-turno" data-section="turno">
                    <label class="custom-control-label" for="toggle-turno"></label>
                </div>
            </div>
        </div>
        <div class="card-body section-body d-none" id="body-turno">
            <input type="hidden" name="cambiar_turno" value="0" id="hidden-cambiar-turno">
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Nuevo Turno *</label>
                    <select name="schedule_id" class="form-control" id="select-nuevo-turno">
                        <option value="">-- Seleccione turno --</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}"
                                {{ $prog->schedule_id == $schedule->id ? 'disabled style="color:#aaa"' : '' }}>
                                {{ $schedule->name }} ({{ $schedule->time_start }} — {{ $schedule->time_end }})
                                {{ $prog->schedule_id == $schedule->id ? '← actual' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Motivo predefinido *</label>
                    <select name="motivo_turno_predefinido" class="form-control">
                        <option value="">-- Seleccione motivo --</option>
                        @foreach($motivosTurno as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Detalle del motivo</label>
                <textarea name="motivo_turno_detalle" class="form-control" rows="2"
                          placeholder="Descripción adicional del cambio de turno..."></textarea>
            </div>
        </div>
    </div>

    {{-- ── SECCIÓN 2: Vehículo ──────────────────────────────── --}}
    <div class="cambio-section card border mb-3" id="section-vehiculo">
        <div class="card-header d-flex align-items-center justify-content-between py-2"
             style="background:#F8FAFC;cursor:pointer;" data-toggle-section="vehiculo">
            <div class="d-flex align-items-center">
                <div class="section-icon mr-3" style="width:32px;height:32px;border-radius:50%;background:#ECFDF5;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-truck" style="color:#059669;font-size:.85rem;"></i>
                </div>
                <div>
                    <strong style="font-size:.9rem;">Cambiar Vehículo</strong>
                    <div style="font-size:.77rem;color:#6B7280;">
                        Actual: <strong>{{ optional($prog->vehicle)->name ?? '-' }}</strong>
                        — {{ optional($prog->vehicle)->code ?? '' }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="cambio-badge d-none mr-2" id="badge-vehiculo">
                    <span class="badge" style="background:#059669;color:#fff;border-radius:20px;font-size:.72rem;">
                        <i class="fas fa-check mr-1"></i>Cambio registrado
                    </span>
                </div>
                <div class="custom-control custom-switch mb-0">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-vehiculo" data-section="vehiculo">
                    <label class="custom-control-label" for="toggle-vehiculo"></label>
                </div>
            </div>
        </div>
        <div class="card-body section-body d-none" id="body-vehiculo">
            <input type="hidden" name="cambiar_vehiculo" value="0" id="hidden-cambiar-vehiculo">
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Nuevo Vehículo *</label>
                    <select name="vehicle_id" class="form-control" id="select-nuevo-vehiculo">
                        <option value="">-- Seleccione vehículo --</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}"
                                {{ $prog->vehicle_id == $v->id ? 'disabled style="color:#aaa"' : '' }}>
                                {{ $v->code }} — {{ $v->name }} (Cap. {{ $v->occupant_capacity }})
                                {{ $prog->vehicle_id == $v->id ? '← actual' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Motivo predefinido *</label>
                    <select name="motivo_vehiculo_predefinido" class="form-control">
                        <option value="">-- Seleccione motivo --</option>
                        @foreach($motivosVehiculo as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Detalle del motivo</label>
                <textarea name="motivo_vehiculo_detalle" class="form-control" rows="2"
                          placeholder="Descripción adicional del cambio de vehículo..."></textarea>
            </div>
        </div>
    </div>

    {{-- ── SECCIÓN 3: Personal ──────────────────────────────── --}}
    <div class="cambio-section card border mb-3" id="section-personal">
        <div class="card-header d-flex align-items-center justify-content-between py-2"
             style="background:#F8FAFC;cursor:pointer;" data-toggle-section="personal">
            <div class="d-flex align-items-center">
                <div class="section-icon mr-3" style="width:32px;height:32px;border-radius:50%;background:#FFF7ED;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-users" style="color:#EA580C;font-size:.85rem;"></i>
                </div>
                <div>
                    <strong style="font-size:.9rem;">Cambiar Personal</strong>
                    <div style="font-size:.77rem;color:#6B7280;">
                        Conductor: <strong>{{ optional($prog->conductor)->name ?? '-' }}</strong>
                        @if($prog->ayudantes->count())
                            | Ayudantes: {{ $prog->ayudantes->pluck('name')->implode(', ') }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="cambio-badge d-none mr-2" id="badge-personal">
                    <span class="badge" style="background:#EA580C;color:#fff;border-radius:20px;font-size:.72rem;">
                        <i class="fas fa-check mr-1"></i>Cambio registrado
                    </span>
                </div>
                <div class="custom-control custom-switch mb-0">
                    <input type="checkbox" class="custom-control-input section-toggle" id="toggle-personal" data-section="personal">
                    <label class="custom-control-label" for="toggle-personal"></label>
                </div>
            </div>
        </div>
        <div class="card-body section-body d-none" id="body-personal">
            <input type="hidden" name="cambiar_personal" value="0" id="hidden-cambiar-personal">

            {{-- Conductor --}}
            <div class="form-group mb-3">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Conductor *</label>
                <input type="hidden" name="conductor_id" id="conductor_id_edit" value="{{ $prog->conductor_id }}">
                <div class="d-flex align-items-center mb-1" style="gap:.5rem;">
                    <div class="flex-grow-1">
                        <input type="text" id="search-conductor-edit" class="form-control"
                               placeholder="Buscar por nombre o DNI..." autocomplete="off">
                    </div>
                </div>
                <div id="conductor-results-edit" class="search-results-dropdown"></div>
                <div id="selected-conductor-edit">
                    <div class="persona-card-selected mt-2">
                        <i class="fas fa-id-badge text-primary mr-1"></i>
                        <strong id="conductor-name-display">{{ optional($prog->conductor)->name ?? '—' }}</strong>
                        <small class="text-muted ml-2">(conductor actual)</small>
                    </div>
                </div>
            </div>

            {{-- Ayudantes --}}
            <div id="ayudantes-edit-section">
                @foreach($prog->ayudantes->sortBy('pivot.order') as $i => $ayudante)
                    <div class="form-group mb-3 ayudante-edit-slot" data-index="{{ $i }}">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">
                            Ayudante {{ $i + 1 }}{{ $i === 0 ? ' *' : '' }}
                        </label>
                        <input type="hidden" name="ayudantes[]" class="ayudante-hidden-id" value="{{ $ayudante->id }}">
                        <div class="d-flex align-items-center mb-1" style="gap:.5rem;">
                            <div class="flex-grow-1">
                                <input type="text" class="form-control search-ayudante-edit"
                                       data-index="{{ $i }}"
                                       placeholder="Buscar ayudante..." autocomplete="off">
                            </div>
                        </div>
                        <div class="search-ayudante-results-edit search-results-dropdown"></div>
                        <div class="selected-ayudante-display mt-1">
                            <div class="persona-card-selected">
                                <i class="fas fa-id-badge text-warning mr-1"></i>
                                <strong class="ayudante-name-display">{{ $ayudante->name }}</strong>
                                <small class="text-muted ml-2">(ayudante actual)</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Motivo del cambio de personal --}}
            <div class="row mt-2">
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Motivo predefinido *</label>
                    <select name="motivo_personal_predefinido" class="form-control">
                        <option value="">-- Seleccione motivo --</option>
                        @foreach($motivosPersonal as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Detalle del motivo</label>
                    <textarea name="motivo_personal_detalle" class="form-control" rows="2"
                              placeholder="Descripción adicional del cambio de personal..."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── HISTORIAL DE CAMBIOS ─────────────────────────────── --}}
    <div class="card border mb-3" id="section-historial">
        <div class="card-header py-2 d-flex align-items-center justify-content-between"
             style="background:#F8FAFC;cursor:pointer;" id="toggle-historial">
            <div class="d-flex align-items-center">
                <div class="section-icon mr-3" style="width:32px;height:32px;border-radius:50%;background:#F1F5F9;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-history" style="color:#475569;font-size:.85rem;"></i>
                </div>
                <strong style="font-size:.9rem;">Historial de Cambios</strong>
                @if($cambios->count())
                    <span class="badge badge-secondary ml-2">{{ $cambios->count() }}</span>
                @endif
            </div>
            <i class="fas fa-chevron-down text-muted" id="historial-chevron"></i>
        </div>
        <div class="card-body p-0 d-none" id="body-historial">
            @if($cambios->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    Sin cambios registrados para esta programación.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:130px;">Fecha</th>
                                <th>Usuario</th>
                                <th style="width:110px;">Campo</th>
                                <th>Anterior</th>
                                <th>Nuevo</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cambios as $cambio)
                                <tr>
                                    <td class="text-nowrap" style="font-size:.8rem;">
                                        {{ $cambio->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="font-size:.8rem;">
                                        {{ optional($cambio->user)->name ?? '—' }}
                                    </td>
                                    <td>
                                        @php
                                            $badgeColors = [
                                                'turno'     => '#4F46E5',
                                                'vehiculo'  => '#059669',
                                                'conductor' => '#EA580C',
                                                'ayudantes' => '#EA580C',
                                                'status'    => '#6B7280',
                                            ];
                                            $bc = $badgeColors[$cambio->campo] ?? '#6B7280';
                                        @endphp
                                        <span class="badge px-2" style="background:{{ $bc }};color:#fff;border-radius:12px;font-size:.7rem;">
                                            {{ $cambio->campo }}
                                        </span>
                                    </td>
                                    <td style="font-size:.8rem;">
                                        <span class="text-danger">{{ $cambio->valor_anterior ?? '—' }}</span>
                                    </td>
                                    <td style="font-size:.8rem;">
                                        <span class="text-success">{{ $cambio->valor_nuevo ?? '—' }}</span>
                                    </td>
                                    <td style="font-size:.8rem;color:#6B7280;">
                                        {{ $cambio->motivo ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Alerta: ningún cambio seleccionado ─────────────────── --}}
    <div id="alert-sin-cambios" class="alert alert-warning d-none mb-3">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Debes activar al menos una sección de cambio (Turno, Vehículo o Personal).
    </div>

    {{-- ── Botones ──────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end mt-3 pt-2 border-top">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" id="btnGuardarProg" class="btn font-weight-bold"
                style="background:#8B5CF6;color:#fff;">
            <i class="fas fa-sync-alt mr-1"></i> Guardar Reprogramación
        </button>
    </div>
</form>

{{-- ── CSS ──────────────────────────────────────────────────── --}}
<style>
.cambio-section .card-header { border-bottom: none; transition: background .15s; }
.cambio-section .card-header:hover { background: #F1F5F9 !important; }
.cambio-section.activa { border-color: #C7D7FC !important; }
.cambio-section.activa#section-turno    { border-color: #C7D7FC !important; }
.cambio-section.activa#section-vehiculo { border-color: #A7F3D0 !important; }
.cambio-section.activa#section-personal { border-color: #FED7AA !important; }

.persona-card-selected {
    background: #F0FDF4;
    border: 1px solid #86EFAC;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: .85rem;
}

.search-results-dropdown {
    position: absolute;
    z-index: 1055;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
}
.search-result-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f1f1;
    transition: background .1s;
    font-size: .85rem;
}
.search-result-item:hover { background: #f8f9fa; }
</style>

{{-- ── JS ───────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    var SEARCH_URL = "{{ route('admin.programacion.search-users') }}";
    var FECHA      = "{{ $prog->fecha->toDateString() }}";

    // ── Toggle secciones con switch ────────────────────────────
    $('.section-toggle').on('change', function () {
        var section = $(this).data('section');
        var $body   = $('#body-' + section);
        var $hidden = $('#hidden-cambiar-' + section);
        var $card   = $('#section-' + section);
        var $badge  = $('#badge-' + section);

        if ($(this).is(':checked')) {
            $body.removeClass('d-none');
            $hidden.val('1');
            $card.addClass('activa');
            $badge.removeClass('d-none');
        } else {
            $body.addClass('d-none');
            $hidden.val('0');
            $card.removeClass('activa');
            $badge.addClass('d-none');
            // Limpiar inputs de la sección
            $body.find('select').val('');
            $body.find('textarea').val('');
        }
        $('#alert-sin-cambios').addClass('d-none');
    });

    // ── Toggle historial ───────────────────────────────────────
    $('#toggle-historial').on('click', function () {
        var $body = $('#body-historial');
        var $icon = $('#historial-chevron');
        $body.toggleClass('d-none');
        $icon.toggleClass('fa-chevron-down fa-chevron-up');
    });

    // ── Búsqueda conductor ─────────────────────────────────────
    var conductorTimer;
    $('#search-conductor-edit').on('input', function () {
        clearTimeout(conductorTimer);
        var q = $(this).val().trim();
        if (q.length < 2) { $('#conductor-results-edit').hide(); return; }

        conductorTimer = setTimeout(function () {
            searchPersonal(q, 'conductor', '#conductor-results-edit', function (user) {
                $('#conductor_id_edit').val(user.id);
                $('#conductor-name-display').text(user.name);
                $('#selected-conductor-edit small').text('(nuevo conductor)').css('color','#059669');
                $('#conductor-results-edit').hide();
                $('#search-conductor-edit').val('');
            });
        }, 300);
    });

    // ── Búsqueda ayudantes ─────────────────────────────────────
    var ayudanteTimers = {};
    $(document).on('input', '.search-ayudante-edit', function () {
        var $input = $(this);
        var index  = $input.data('index');
        clearTimeout(ayudanteTimers[index]);

        var q = $input.val().trim();
        if (q.length < 2) {
            $input.closest('.ayudante-edit-slot').find('.search-ayudante-results-edit').hide();
            return;
        }

        ayudanteTimers[index] = setTimeout(function () {
            var $slot = $input.closest('.ayudante-edit-slot');
            var $drop = $slot.find('.search-ayudante-results-edit');

            searchPersonal(q, 'ayudante', $drop, function (user) {
                $slot.find('.ayudante-hidden-id').val(user.id);
                $slot.find('.ayudante-name-display').text(user.name);
                $slot.find('.selected-ayudante-display small').text('(nuevo ayudante)').css('color','#059669');
                $drop.hide();
                $input.val('');
            });
        }, 300);
    });

    // ── Función genérica de búsqueda ───────────────────────────
    function searchPersonal(q, rol, dropSelector, onSelect) {
        var excludeIds = [];
        var cid = $('#conductor_id_edit').val();
        if (cid) excludeIds.push(cid);
        $('.ayudante-hidden-id').each(function () {
            if ($(this).val()) excludeIds.push($(this).val());
        });

        $.ajax({
            url: SEARCH_URL,
            data: { q: q, rol: rol, fecha: FECHA, exclude: excludeIds },
            success: function (users) {
                var $drop = typeof dropSelector === 'string' ? $(dropSelector) : dropSelector;
                if (!users.length) {
                    $drop.html('<div class="search-result-item text-muted">Sin resultados.</div>').show();
                    return;
                }
                var html = users.map(function (u) {
                    return '<div class="search-result-item" data-id="' + u.id + '" data-name="' + u.name + '">'
                         + '<strong>' + u.name + '</strong>'
                         + '<div style="font-size:.75rem;color:#6b7280;">DNI ' + u.dni + '</div>'
                         + '</div>';
                }).join('');
                $drop.html(html).show();

                $drop.off('click').on('click', '.search-result-item', function () {
                    onSelect({ id: $(this).data('id'), name: $(this).data('name') });
                });
            }
        });
    }

    // Cerrar dropdowns al clic fuera
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#search-conductor-edit, #conductor-results-edit').length) {
            $('#conductor-results-edit').hide();
        }
        if (!$(e.target).closest('.search-ayudante-edit, .search-ayudante-results-edit').length) {
            $('.search-ayudante-results-edit').hide();
        }
    });

    // ── Validación antes de submit ─────────────────────────────
    $('#formProgramacion').on('submit', function (e) {
        var algunCambio = false;

        // ── Turno ──
        if ($('#toggle-turno').is(':checked')) {
            algunCambio = true;
            if (!$('#select-nuevo-turno').val()) {
                e.preventDefault(); e.stopImmediatePropagation();
                Swal.fire('Atención', 'Seleccione el nuevo turno.', 'warning'); return false;
            }
            if (!$('select[name="motivo_turno_predefinido"]').val()) {
                e.preventDefault(); e.stopImmediatePropagation();
                Swal.fire('Atención', 'Seleccione el motivo predefinido del cambio de turno.', 'warning'); return false;
            }
        }

        // ── Vehículo ──
        if ($('#toggle-vehiculo').is(':checked')) {
            algunCambio = true;
            if (!$('#select-nuevo-vehiculo').val()) {
                e.preventDefault(); e.stopImmediatePropagation();
                Swal.fire('Atención', 'Seleccione el nuevo vehículo.', 'warning'); return false;
            }
            if (!$('select[name="motivo_vehiculo_predefinido"]').val()) {
                e.preventDefault(); e.stopImmediatePropagation();
                Swal.fire('Atención', 'Seleccione el motivo predefinido del cambio de vehículo.', 'warning'); return false;
            }
        }

        // ── Personal ──
        if ($('#toggle-personal').is(':checked')) {
            algunCambio = true;
            if (!$('#conductor_id_edit').val()) {
                e.preventDefault(); e.stopImmediatePropagation();
                Swal.fire('Atención', 'El conductor es requerido.', 'warning'); return false;
            }
            if (!$('select[name="motivo_personal_predefinido"]').val()) {
                e.preventDefault(); e.stopImmediatePropagation();
                Swal.fire('Atención', 'Seleccione el motivo predefinido del cambio de personal.', 'warning'); return false;
            }
        }

        if (!algunCambio) {
            e.preventDefault(); e.stopImmediatePropagation();
            $('#alert-sin-cambios').removeClass('d-none');
            return false;
        }
    });

})();
</script>