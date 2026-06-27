@php
    $editing = isset($group);
    $selectedDays = $editing ? $group->days : [];

    // FIX #1: $group->ayudantes no existe como relación.
    //         Se filtra members por pivot.role === 'ayudante'
    $existingConductor = null;
    $existingAyudantes = collect();
    if ($editing) {
        $existingConductor = $group->conductor()->first();
        $existingAyudantes = $group->members->where('pivot.role', 'ayudante')->values();
    }
@endphp

<form
    action="{{ $editing ? route('admin.personal-group.update', $group->id) : route('admin.personal-group.store') }}"
    method="POST"
    id="formGroup"
>
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    {{-- Nombre + Estado --}}
    <div class="row">
        <div class="col-md-{{ $editing ? '8' : '12' }} form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Nombre del Grupo *</label>
            <input type="text" name="name" class="form-control"
                   placeholder="Ej. Grupo A"
                   value="{{ $editing ? $group->name : '' }}" required>
        </div>
        @if($editing)
        <div class="col-md-4 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Estado *</label>
            <select name="status" class="form-control" required>
                <option value="Activo"   {{ $group->status === 'Activo'   ? 'selected' : '' }}>Activo</option>
                <option value="Inactivo" {{ $group->status === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        @endif
    </div>

    {{-- Zona + Turno --}}
    <div class="row">
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Zona *</label>
            <select name="zone_id" class="form-control" required>
                <option value="">-- Seleccione zona --</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ ($editing && $group->zone_id == $zone->id) ? 'selected' : '' }}>
                        {{ $zone->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Turno *</label>
            <select name="schedule_id" class="form-control" required>
                <option value="">-- Seleccione turno --</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->id }}" {{ ($editing && $group->schedule_id == $schedule->id) ? 'selected' : '' }}>
                        {{ $schedule->name }} ({{ $schedule->time_start }} - {{ $schedule->time_end }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Vehículo --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Vehículo *</label>
        <select name="vehicle_id" class="form-control" id="select-vehicle" required>
            <option value="">-- Seleccione vehículo --</option>
            @foreach($vehicles as $vehicle)
                <option
                    value="{{ $vehicle->id }}"
                    data-capacity="{{ $vehicle->occupant_capacity }}"
                    {{ ($editing && $group->vehicle_id == $vehicle->id) ? 'selected' : '' }}
                >
                    {{ $vehicle->code }} - {{ $vehicle->name }} (Cap. {{ $vehicle->occupant_capacity }})
                </option>
            @endforeach
        </select>
        <div id="vehicle-capacity-info" class="mt-1 text-sm" style="font-size:.82rem;"></div>
    </div>

    {{-- Días de trabajo --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase d-block">Días de trabajo *</label>
        <div class="d-flex flex-wrap gap-2" id="days-container">
            @php
                $dayColors = [
                    'Lun' => '#3B82F6', 'Mar' => '#8B5CF6', 'Mié' => '#10B981',
                    'Jue' => '#F59E0B', 'Vie' => '#EF4444', 'Sáb' => '#EC4899', 'Dom' => '#6366F1',
                ];
            @endphp
            @foreach($days as $day)
                @php $checked = in_array($day, $selectedDays); $color = $dayColors[$day] ?? '#6B7280'; @endphp
                <label class="day-checkbox-label {{ $checked ? 'checked' : '' }}"
                       data-day="{{ $day }}"
                       style="--day-color:{{ $color }};">
                    <input type="checkbox" name="days[]" value="{{ $day }}" {{ $checked ? 'checked' : '' }} style="display:none;">
                    {{ $day }}
                </label>
            @endforeach
        </div>
        <small class="text-muted">Selecciona al menos un día. Los días afectan la validación de disponibilidad del personal.</small>
    </div>

    <hr class="my-3">
    <p class="text-muted text-xs mb-3" style="font-size:.8rem;">
        <i class="fas fa-info-circle mr-1"></i>
        Los datos de personal se validan según el vehículo seleccionado y los días de trabajo.
    </p>

    {{-- Conductor --}}
    <div class="form-group mb-3 position-relative" id="conductor-section">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Conductor *</label>
        <input type="hidden" name="conductor_id" id="conductor_id" value="{{ $editing && $existingConductor ? $existingConductor->id : '' }}" required>
        <input type="text" id="search-conductor" class="form-control"
               placeholder="Escriba nombre, apellido o DNI para buscar conductores..."
               autocomplete="off">
        <div id="conductor-results" class="search-results-dropdown"></div>
        <div id="selected-conductor">
            @if($editing && $existingConductor)
                <div class="selected-user-card mt-2" data-id="{{ $existingConductor->id }}">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            <strong>{{ $existingConductor->name }}</strong>
                            <br>
                            <small class="text-muted ml-3">DNI {{ $existingConductor->dni }} | {{ optional($existingConductor->usertype)->name }}</small>
                            <br>
                            <small class="text-success ml-3"><i class="fas fa-check mr-1"></i>Disponible</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-conductor">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Ayudantes (dinámicos según capacidad del vehículo) --}}
    <div id="ayudantes-section">
        {{-- Se genera dinámicamente por JS --}}
    </div>

    {{-- Botones --}}
    <div class="d-flex justify-content-end mt-3 pt-2 border-top">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Actualizar' : 'Guardar' }}
        </button>
    </div>
</form>

{{-- Datos para JS (edición) --}}
@if($editing)
    @php
        $ayudantesJs = $existingAyudantes->map(function ($a) {
            return [
                'id'         => $a->id,
                'name'       => $a->name,
                'dni'        => $a->dni,
                'role_label' => optional($a->usertype)->name ?? 'Ayudante',
                'available'  => true,
                'conflict'   => null,
            ];
        })->values();
    @endphp

    <script>
        window._editingGroupId = {{ $group->id }};
        window._existingAyudantes = {!! json_encode($ayudantesJs) !!};
    </script>
@else
    <script>
        window._editingGroupId = null;
        window._existingAyudantes = [];
    </script>
@endif

<style>
/* ── Day checkboxes ─────────────────────────── */
.day-checkbox-label {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    border: 2px solid var(--day-color);
    color: var(--day-color);
    font-weight: 700;
    font-size: .78rem;
    cursor: pointer;
    transition: all .15s;
    margin: 3px 3px 3px 0;
    user-select: none;
}
.day-checkbox-label.checked,
.day-checkbox-label:hover {
    background: var(--day-color);
    color: #fff;
}

/* ── Search dropdown ────────────────────────── */
.search-results-dropdown {
    position: absolute;
    z-index: 1055;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    max-height: 220px;
    overflow-y: auto;
    width: calc(100% - 30px);
    display: none;
}
.search-result-item {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f1f1;
    transition: background .1s;
}
.search-result-item:hover { background: #f8f9fa; }
.search-result-item.unavailable { opacity: .55; cursor: not-allowed; }
.search-result-item .conflict-msg { color: #dc3545; font-size: .73rem; margin-top:2px; }

/* ── Selected user card ─────────────────────── */
.selected-user-card {
    background: #d1fae5;
    border: 1.5px solid #10b981;
    border-radius: 10px;
    padding: 10px 14px;
    margin-top: 6px;
}
.selected-user-card.unavailable {
    background: #fee2e2;
    border-color: #ef4444;
}

/* ── Capacity bar ────────────────────────────── */
#vehicle-capacity-info .cap-badge {
    display: inline-block;
    background: #EFF6FF;
    color: #1D4ED8;
    border: 1px solid #BFDBFE;
    border-radius: 20px;
    padding: 2px 12px;
    font-size: .78rem;
    font-weight: 600;
}
</style>

<script>
(function () {
    'use strict';

    var SEARCH_URL  = "{{ route('admin.personal-group.search-users') }}";
    var groupId     = window._editingGroupId;

    // ── Estado ─────────────────────────────────────────────────
    var selectedConductor = null;
    var selectedAyudantes = []; // [{id, name, dni, role_label}]
    var vehicleCapacity   = 0;

    // ── Ayudantes iniciales (edición) ──────────────────────────
    if (window._existingAyudantes && window._existingAyudantes.length) {
        selectedAyudantes = window._existingAyudantes;
    }

    // ── Capacidad inicial (vehículo pre-seleccionado) ──────────
    var $selVehicle = $('#select-vehicle');
    if ($selVehicle.val()) {
        vehicleCapacity = parseInt($selVehicle.find('option:selected').data('capacity')) || 0;
        updateCapacityInfo();
        renderAyudanteSlots();
    }

    // ── Días: toggle visual ────────────────────────────────────
    $(document).on('click', '.day-checkbox-label', function () {
        var $lbl = $(this);
        var $cb  = $lbl.find('input[type=checkbox]');
        $cb.prop('checked', !$cb.prop('checked'));
        $lbl.toggleClass('checked', $cb.prop('checked'));
    });

    // ── Vehículo: cambio de capacidad ─────────────────────────
    $selVehicle.on('change', function () {
        vehicleCapacity = parseInt($(this).find('option:selected').data('capacity')) || 0;
        updateCapacityInfo();

        // Truncar ayudantes si exceden la nueva capacidad
        var maxAyudantes = Math.max(0, vehicleCapacity - 1); // -1 por el conductor
        if (selectedAyudantes.length > maxAyudantes) {
            selectedAyudantes = selectedAyudantes.slice(0, maxAyudantes);
        }
        renderAyudanteSlots();
    });

    function updateCapacityInfo() {
        var $info = $('#vehicle-capacity-info');
        if (!vehicleCapacity) { $info.html(''); return; }
        var maxAy = vehicleCapacity - 1;
        $info.html(
            '<span class="cap-badge"><i class="fas fa-users mr-1"></i>' +
            'Capacidad: ' + vehicleCapacity + ' persona(s) — 1 conductor + ' + maxAy + ' ayudante(s)</span>'
        );
    }

    // ── Conductor: búsqueda ────────────────────────────────────
    var conductorTimer;
    $('#search-conductor').on('input', function () {
        clearTimeout(conductorTimer);
        var q = $(this).val().trim();
        if (q.length < 2) { $('#conductor-results').hide(); return; }
        conductorTimer = setTimeout(function () { searchUsers(q, 'conductor', '#conductor-results', selectConductor); }, 300);
    });

    $(document).on('click', '.btn-remove-conductor', function () {
        selectedConductor = null;
        $('#conductor_id').val('');
        $('#selected-conductor').empty();
        $('#search-conductor').val('').show();
    });

    function selectConductor(user) {
        selectedConductor = user;
        $('#conductor_id').val(user.id);
        $('#conductor-results').hide();
        $('#search-conductor').val('');

        var cardHtml = buildUserCard(user, 'btn-remove-conductor');
        $('#selected-conductor').html(cardHtml);
    }

    // ── Ayudantes: renderizado dinámico ───────────────────────
    function renderAyudanteSlots() {
        var $section = $('#ayudantes-section');
        $section.empty();

        if (!vehicleCapacity) {
            $section.html('<p class="text-muted text-sm"><i class="fas fa-info-circle mr-1"></i>Selecciona un vehículo para habilitar los campos de ayudantes.</p>');
            return;
        }

        var maxAyudantes = vehicleCapacity - 1;

        if (maxAyudantes <= 0) {
            $section.html('<p class="text-warning text-sm"><i class="fas fa-exclamation-triangle mr-1"></i>El vehículo seleccionado no tiene capacidad para ayudantes (solo conductor).</p>');
            return;
        }

        // Renderizar ayudantes ya seleccionados
        selectedAyudantes.forEach(function (ay, i) {
            $section.append(buildAyudanteSlot(i, ay));
        });

        // Botón agregar (si no se alcanzó el límite)
        if (selectedAyudantes.length < maxAyudantes) {
            $section.append(
                '<div id="btn-add-ayudante-wrap" class="mt-2">' +
                '<button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-ayudante">' +
                '<i class="fas fa-plus mr-1"></i> Agregar ayudante ' + (selectedAyudantes.length + 1) +
                ' <small class="text-muted">(' + selectedAyudantes.length + '/' + maxAyudantes + ')</small>' +
                '</button></div>'
            );
        } else {
            $section.append(
                '<div class="text-muted text-sm mt-1"><i class="fas fa-lock mr-1"></i>Capacidad máxima de ayudantes alcanzada (' + maxAyudantes + '/' + maxAyudantes + ').</div>'
            );
        }
    }

    // FIX #2: agregado position-relative a los slots para que el dropdown
    //         se posicione respecto al slot y no al viewport
    function buildAyudanteSlot(index, user) {
        return '<div class="form-group mb-2 ayudante-slot position-relative" data-index="' + index + '">' +
               '<label class="font-weight-bold text-xs text-secondary text-uppercase">Ayudante ' + (index + 1) + (index === 0 ? ' *' : '') + '</label>' +
               (user ? buildUserCard(user, 'btn-remove-ayudante', index) :
                       '<div class="search-ayudante-wrap position-relative" data-index="' + index + '">' +
                       '<input type="text" class="form-control search-ayudante" data-index="' + index + '" placeholder="Buscar ayudante..." autocomplete="off">' +
                       '<div class="search-ayudante-results search-results-dropdown"></div>' +
                       '</div>') +
               '</div>';
    }

    function buildUserCard(user, removeBtnClass, index) {
        var conflictHtml = user.conflict
            ? '<br><small class="conflict-msg"><i class="fas fa-exclamation-circle mr-1"></i>' + user.conflict + '</small>'
            : '<br><small class="text-success ml-3"><i class="fas fa-check mr-1"></i>Disponible para los días seleccionados</small>';
        var cardClass = user.conflict ? 'selected-user-card unavailable' : 'selected-user-card';
        var dataIndex = (index !== undefined) ? ' data-index="' + index + '"' : '';
        return '<div class="' + cardClass + ' mt-2 selected-user" data-id="' + user.id + '">' +
               '<div class="d-flex align-items-start justify-content-between">' +
               '<div>' +
               '<i class="fas fa-check-circle ' + (user.conflict ? 'text-danger' : 'text-success') + ' mr-1"></i>' +
               '<strong>' + user.name + '</strong>' +
               '<br><small class="text-muted ml-3">DNI ' + user.dni + ' | ' + user.role_label + '</small>' +
               conflictHtml +
               '</div>' +
               '<button type="button" class="btn btn-sm btn-link text-danger p-0 ' + removeBtnClass + '"' + dataIndex + '>' +
               '<i class="fas fa-times"></i></button>' +
               '</div></div>';
    }

    // ── Click en "Agregar ayudante" ────────────────────────────
    $(document).on('click', '#btn-add-ayudante', function () {
        var index = selectedAyudantes.length;
        $('#btn-add-ayudante-wrap').replaceWith(buildAyudanteSlotSearch(index));
    });

    // FIX #2: position-relative también en buildAyudanteSlotSearch
    function buildAyudanteSlotSearch(index) {
        var maxAyudantes = vehicleCapacity - 1;
        return '<div class="form-group mb-2 ayudante-slot position-relative" data-index="' + index + '">' +
               '<label class="font-weight-bold text-xs text-secondary text-uppercase">Ayudante ' + (index + 1) + (index === 0 ? ' *' : '') + '</label>' +
               '<div class="search-ayudante-wrap position-relative" data-index="' + index + '">' +
               '<input type="text" class="form-control search-ayudante" data-index="' + index + '" placeholder="Buscar ayudante..." autocomplete="off">' +
               '<div class="search-ayudante-results search-results-dropdown"></div>' +
               '</div></div>' +
               (index + 1 < maxAyudantes ?
                   '<div id="btn-add-ayudante-wrap"><button type="button" class="btn btn-sm btn-outline-primary mt-1" id="btn-add-ayudante">' +
                   '<i class="fas fa-plus mr-1"></i> Agregar ayudante ' + (index + 2) +
                   ' <small class="text-muted">(' + (index + 1) + '/' + maxAyudantes + ')</small>' +
                   '</button></div>' : '');
    }

    // ── Búsqueda ayudantes en tiempo real ─────────────────────
    var ayudanteTimers = {};
    $(document).on('input', '.search-ayudante', function () {
        var $input = $(this);
        var index  = $input.data('index');
        var q      = $input.val().trim();
        var $drop  = $input.siblings('.search-ayudante-results');

        clearTimeout(ayudanteTimers[index]);
        if (q.length < 2) { $drop.hide(); return; }

        ayudanteTimers[index] = setTimeout(function () {
            var excludeIds = selectedAyudantes.map(function (a) { return a.id; });
            if (selectedConductor) excludeIds.push(selectedConductor.id);
            searchUsers(q, 'ayudante', $drop, function (user) { selectAyudante(index, user); }, excludeIds);
        }, 300);
    });

    function selectAyudante(index, user) {
        selectedAyudantes[index] = user;

        // Reemplazar el slot con la tarjeta seleccionada
        var $slot = $('.ayudante-slot[data-index="' + index + '"]');
        $slot.find('.search-ayudante-wrap').replaceWith(buildUserCard(user, 'btn-remove-ayudante', index));

        // Actualizar el botón agregar
        var maxAyudantes = vehicleCapacity - 1;
        $('#btn-add-ayudante-wrap').remove();
        if (selectedAyudantes.filter(Boolean).length < maxAyudantes) {
            var nextIndex = selectedAyudantes.filter(Boolean).length;
            $('#ayudantes-section').append(
                '<div id="btn-add-ayudante-wrap"><button type="button" class="btn btn-sm btn-outline-primary mt-1" id="btn-add-ayudante">' +
                '<i class="fas fa-plus mr-1"></i> Agregar ayudante ' + (nextIndex + 1) +
                ' <small class="text-muted">(' + nextIndex + '/' + maxAyudantes + ')</small>' +
                '</button></div>'
            );
        }
    }

    // ── Quitar ayudante ────────────────────────────────────────
    $(document).on('click', '.btn-remove-ayudante', function () {
        var index = parseInt($(this).data('index'));
        selectedAyudantes.splice(index, 1);
        renderAyudanteSlots();
    });

    // ── Búsqueda genérica ──────────────────────────────────────
    function searchUsers(q, role, dropSelector, onSelect, excludeIds) {
        var days = [];
        $('input[name="days[]"]:checked').each(function () { days.push($(this).val()); });

        $.ajax({
            url: SEARCH_URL,
            data: {
                q: q,
                role: role,
                days: days,
                group_id: groupId || '',
                exclude: excludeIds || [],
            },
            success: function (users) {
                var $drop = typeof dropSelector === 'string' ? $(dropSelector) : dropSelector;
                if (!users.length) {
                    $drop.html('<div class="search-result-item text-muted">No se encontraron resultados.</div>').show();
                    return;
                }
                var html = users.map(function (u) {
                    var cls = u.available ? '' : ' unavailable';
                    var conflictHtml = u.conflict
                        ? '<div class="conflict-msg"><i class="fas fa-exclamation-circle mr-1"></i>' + u.conflict + '</div>'
                        : '<div style="color:#10b981;font-size:.73rem;"><i class="fas fa-check mr-1"></i>Disponible</div>';
                    return '<div class="search-result-item' + cls + '" ' +
                           'data-id="' + u.id + '" data-name="' + u.name + '" ' +
                           'data-dni="' + u.dni + '" data-role="' + u.role_label + '" ' +
                           'data-available="' + u.available + '" data-conflict="' + (u.conflict || '') + '">' +
                           '<strong>' + u.name + '</strong>' +
                           '<div style="font-size:.78rem;color:#6b7280;">DNI ' + u.dni + ' — ' + u.role_label + '</div>' +
                           conflictHtml +
                           '</div>';
                }).join('');
                $drop.html(html).show();

                // Click en resultado
                $drop.off('click').on('click', '.search-result-item', function () {
                    if ($(this).hasClass('unavailable')) {
                        Swal.fire('No disponible', $(this).find('.conflict-msg').text() || 'Usuario con conflicto de horario.', 'warning');
                        return;
                    }
                    var u = {
                        id:         $(this).data('id'),
                        name:       $(this).data('name'),
                        dni:        $(this).data('dni'),
                        role_label: $(this).data('role'),
                        available:  true,
                        conflict:   null,
                    };
                    $drop.hide();
                    onSelect(u);
                });
            }
        });
    }

    // ── Cerrar dropdowns al clic fuera ─────────────────────────
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#search-conductor, #conductor-results').length) {
            $('#conductor-results').hide();
        }
        if (!$(e.target).closest('.search-ayudante, .search-ayudante-results').length) {
            $('.search-ayudante-results').hide();
        }
    });

    // ── Init: renderizar ayudantes si estamos en edición ──────
    renderAyudanteSlots();

    // ── Validación y serialización antes de enviar ────────────
    $('#formGroup').on('submit', function (e) {

        // FIX #3: los ayudantes viven solo en memoria JS (selectedAyudantes[]).
        //         Sin este bloque el backend recibe ayudantes = null siempre.
        //         Limpiamos inputs previos e inyectamos los actuales.
        $(this).find('input[name="ayudantes[]"]').remove();
        selectedAyudantes.forEach(function (ay) {
            if (ay && ay.id) {
                $('<input>').attr({ type: 'hidden', name: 'ayudantes[]', value: ay.id })
                            .appendTo('#formGroup');
            }
        });

        // Validar conductor requerido
        if (!$('#conductor_id').val()) {
            e.preventDefault();
            e.stopImmediatePropagation();
            Swal.fire('Campo requerido', 'Debes seleccionar un conductor.', 'warning');
            return false;
        }

        // Validar días requeridos
        var checkedDays = $('input[name="days[]"]:checked').length;
        if (!checkedDays) {
            e.preventDefault();
            e.stopImmediatePropagation();
            Swal.fire('Campo requerido', 'Debes seleccionar al menos un día de trabajo.', 'warning');
            return false;
        }
    });

})();
</script>