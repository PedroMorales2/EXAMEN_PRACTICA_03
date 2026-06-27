{{-- Formulario: Nueva Programación --}}
<form
    action="{{ route('admin.programacion.store') }}"
    method="POST"
    id="formProgramacion"
>
    @csrf

    {{-- Fechas + Validar --}}
    <div class="row">
        <div class="col-md-4 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de inicio *</label>
            <input type="date" name="fecha_inicio" id="progFechaInicio" class="form-control" required>
        </div>
        <div class="col-md-4 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de fin *</label>
            <input type="date" name="fecha_fin" id="progFechaFin" class="form-control" required>
        </div>
        <div class="col-md-4 form-group mb-3 d-flex flex-column justify-content-end">
            <button type="button" class="btn btn-outline-primary font-weight-bold" id="btnValidarDisp">
                <i class="fas fa-check-circle mr-1"></i> Validar disponibilidad
            </button>
        </div>
    </div>

    {{-- Grupo de Personal --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Grupo de Personal *</label>
        <select name="personal_group_id" id="selGroup" class="form-control" required>
            <option value="">-- Seleccione un grupo --</option>
            @foreach($groups as $group)
                <option value="{{ $group->id }}">
                    {{ $group->name }}
                    @if($group->zone) — {{ $group->zone->name }} @endif
                    @if($group->schedule) | {{ $group->schedule->name }} @endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Busque por nombre, zona o turno</small>
    </div>

    {{-- Alerta: datos cambiados --}}
    <div id="alertDatosCambiados" class="alert alert-info d-none mb-3">
        <i class="fas fa-info-circle mr-1"></i> Los datos han cambiado. Valide la disponibilidad nuevamente.
    </div>

    {{-- Alerta: errores --}}
    <div id="alertErrors" class="alert alert-danger d-none mb-3">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Hay errores que corregir</strong>
        <ul id="listErrors" class="mb-2 mt-2"></ul>
        <div id="blockSuggestions" class="d-none">
            <i class="fas fa-lightbulb text-warning mr-1"></i> <strong>Sugerencias:</strong>
            <ul id="listSuggestions" class="mb-0 mt-1"></ul>
        </div>
    </div>

    {{-- Alerta: validación OK --}}
    <div id="alertSuccess" class="alert alert-success d-none mb-3">
        <i class="fas fa-check-circle mr-1"></i>
        <strong>Todo está correcto. Puede guardar la programación.</strong>
    </div>

    {{-- Info del grupo seleccionado --}}
    <div id="groupInfoRow" class="d-none mb-3">
        <div class="row text-center border rounded p-2 bg-light mx-0">
            <div class="col-3">
                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Grupo</small>
                <strong id="infoGrupo">-</strong>
            </div>
            <div class="col-3">
                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Zona</small>
                <strong id="infoZona">-</strong>
            </div>
            <div class="col-3">
                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Turno</small>
                <strong id="infoTurno">-</strong>
            </div>
            <div class="col-3">
                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size:10px;">Vehículo</small>
                <strong id="infoVehiculo">-</strong>
            </div>
        </div>
    </div>

    {{-- Personal (se llena automáticamente, es editable) --}}
    <div id="personalSection" class="d-none">
        <div class="row" id="personalRow">
            {{-- Se genera dinámicamente según los miembros del grupo --}}
        </div>
    </div>

    {{-- Días de trabajo --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase d-block">Días de trabajo *</label>
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            @php
                $diasOpciones = [
                    ['label' => 'Lunes',     'value' => 'Lun'],
                    ['label' => 'Martes',    'value' => 'Mar'],
                    ['label' => 'Miércoles', 'value' => 'Mié'],
                    ['label' => 'Jueves',    'value' => 'Jue'],
                    ['label' => 'Viernes',   'value' => 'Vie'],
                    ['label' => 'Sábado',    'value' => 'Sáb'],
                    ['label' => 'Domingo',   'value' => 'Dom'],
                ];
            @endphp
            @foreach($diasOpciones as $dia)
                <div class="form-check form-check-inline">
                    <input class="form-check-input dia-check" type="checkbox"
                           name="dias[]" id="dia_{{ $dia['value'] }}"
                           value="{{ $dia['value'] }}">
                    <label class="form-check-label" for="dia_{{ $dia['value'] }}">{{ $dia['label'] }}</label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Observaciones --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Observaciones adicionales..."></textarea>
    </div>

    {{-- Botones --}}
    <div class="d-flex justify-content-end mt-3 pt-2 border-top">
        <button type="button" class="btn btn-danger mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" id="btnGuardarProg" class="btn btn-primary font-weight-bold" disabled>
            <i class="fas fa-save mr-1"></i> Guardar
        </button>
    </div>
</form>

<script>
(function () {
    'use strict';

    var GROUP_DATA_URL  = "{{ route('admin.personal-group.data', 'ID') }}";
    var VALIDATE_URL    = "{{ route('admin.programacion.validate') }}";
    var SEARCH_USER_URL = "{{ route('admin.programacion.search-users') }}";

    var validationPassed = false;

    // Estado del personal seleccionado
    // { conductor_id, ayudantes: [{id, name}] }
    var personal = { conductor_id: null, ayudantes: [] };

    // ── Helpers ────────────────────────────────────────────────
    function getFechaInicio() { return $('#progFechaInicio').val(); }
    function getFechaFin()    { return $('#progFechaFin').val(); }
    function getDias()        { return $('input.dia-check:checked').map(function(){ return this.value; }).get(); }

    function markDirty() {
        validationPassed = false;
        $('#btnGuardarProg').prop('disabled', true);
        $('#alertSuccess').addClass('d-none');
        $('#alertErrors').addClass('d-none');
        if (getFechaInicio() && getFechaFin() && $('#selGroup').val()) {
            $('#alertDatosCambiados').removeClass('d-none');
        }
    }

    // ── Selección de grupo ─────────────────────────────────────
    $('#selGroup').on('change', function () {
        var groupId = $(this).val();
        if (!groupId) {
            $('#groupInfoRow').addClass('d-none');
            $('#personalSection').addClass('d-none');
            personal = { conductor_id: null, ayudantes: [] };
            markDirty();
            return;
        }

        $.get(GROUP_DATA_URL.replace('ID', groupId), function (data) {
            // Info del grupo
            $('#infoGrupo').text(data.name);
            $('#infoZona').text(data.zone ? data.zone.name : '-');
            $('#infoTurno').text(data.schedule
                ? data.schedule.name + ' (' + data.schedule.time_start + ' - ' + data.schedule.time_end + ')'
                : '-');
            $('#infoVehiculo').text(data.vehicle
                ? data.vehicle.name + ' — ' + data.vehicle.code
                : '-');
            $('#groupInfoRow').removeClass('d-none');

            // Personal por defecto
            personal.conductor_id = data.conductor ? data.conductor.id : null;
            personal.ayudantes    = data.ayudantes ? data.ayudantes : [];

            // Días por defecto del grupo
            if (data.days && data.days.length) {
                $('input.dia-check').prop('checked', false);
                data.days.forEach(function (d) {
                    $('#dia_' + d).prop('checked', true);
                });
            }

            renderPersonalSection(data);
            $('#personalSection').removeClass('d-none');
        });

        markDirty();
    });

    // ── Renderiza la sección de personal ───────────────────────
    function renderPersonalSection(groupData) {
        var $row = $('#personalRow');
        $row.empty();

        // Conductor
        var conductorName = groupData.conductor ? groupData.conductor.name : '—';
        $row.append(buildPersonCard(
            'Conductor *',
            groupData.conductor,
            'conductor',
            true
        ));

        // Ayudantes
        if (groupData.ayudantes && groupData.ayudantes.length) {
            groupData.ayudantes.forEach(function (ay, i) {
                $row.append(buildPersonCard(
                    'Ayudante ' + (i + 1) + (i === 0 ? ' *' : ''),
                    ay,
                    'ayudante',
                    i === 0,
                    i
                ));
            });
        }
    }

    function buildPersonCard(label, user, tipo, required, ayIndex) {
        var userId   = user ? user.id : '';
        var userName = user ? user.name : '—';
        var dataAttr = tipo === 'conductor'
            ? 'data-tipo="conductor"'
            : 'data-tipo="ayudante" data-ay-index="' + ayIndex + '"';

        return '<div class="col-md-4 form-group mb-3 personal-card" ' + dataAttr + '>' +
               '<label class="font-weight-bold text-xs text-secondary text-uppercase">' + label + '</label>' +
               '<div class="input-group">' +
               '<div class="form-control d-flex align-items-center justify-content-between person-display" ' +
               'style="cursor:pointer;background:#f8f9fa;" ' + dataAttr + '>' +
               '<span class="person-name">' + userName + '</span>' +
               '<small class="text-primary"><i class="fas fa-exchange-alt"></i> Cambiar</small>' +
               '</div>' +
               '</div>' +
               '<input type="hidden" name="' + (tipo === 'conductor' ? 'conductor_id' : 'ayudantes[]') +
               '" class="person-hidden-id" value="' + userId + '">' +
               // Búsqueda inline (oculta por defecto)
               '<div class="person-search-wrap mt-1 d-none">' +
               '<input type="text" class="form-control form-control-sm person-search-input" ' +
               'placeholder="Buscar por nombre o DNI..." autocomplete="off" ' + dataAttr + '>' +
               '<div class="person-search-results search-dropdown-results"></div>' +
               '</div>' +
               '</div>';
    }

    // ── Clic en "Cambiar" persona ──────────────────────────────
    $(document).on('click', '.person-display', function () {
        var $card = $(this).closest('.personal-card');
        $card.find('.person-search-wrap').toggleClass('d-none');
        $card.find('.person-search-input').focus();
    });

    // ── Búsqueda de persona ────────────────────────────────────
    var searchTimers = {};
    $(document).on('input', '.person-search-input', function () {
        var $input = $(this);
        var tipo   = $input.data('tipo');
        var key    = tipo + ($input.data('ay-index') !== undefined ? $input.data('ay-index') : '');
        clearTimeout(searchTimers[key]);

        var q = $input.val().trim();
        if (q.length < 2) {
            $input.closest('.person-search-wrap').find('.person-search-results').hide();
            return;
        }

        searchTimers[key] = setTimeout(function () {
            var excludeIds = getAssignedIds();

            $.ajax({
                url: SEARCH_USER_URL,
                data: {
                    q:            q,
                    rol:          tipo,
                    fecha_inicio: getFechaInicio(),
                    fecha_fin:    getFechaFin(),
                    exclude:      excludeIds,
                },
                success: function (users) {
                    var $results = $input.closest('.person-search-wrap').find('.person-search-results');
                    if (!users.length) {
                        $results.html('<div class="search-result-item text-muted">Sin resultados disponibles.</div>').show();
                        return;
                    }
                    var html = users.map(function (u) {
                        return '<div class="search-result-item" data-id="' + u.id + '" data-name="' + u.name + '">' +
                               '<strong>' + u.name + '</strong>' +
                               '<div style="font-size:.78rem;color:#6b7280;">DNI ' + u.dni + '</div>' +
                               '</div>';
                    }).join('');
                    $results.html(html).show();

                    $results.off('click').on('click', '.search-result-item', function () {
                        var uid   = $(this).data('id');
                        var uname = $(this).data('name');
                        var $card = $input.closest('.personal-card');

                        // Actualizar display y hidden
                        $card.find('.person-name').text(uname);
                        $card.find('.person-hidden-id').val(uid);

                        // Actualizar estado interno
                        if (tipo === 'conductor') {
                            personal.conductor_id = uid;
                        } else {
                            var idx = parseInt($input.data('ay-index'));
                            personal.ayudantes[idx] = { id: uid, name: uname };
                        }

                        $card.find('.person-search-wrap').addClass('d-none');
                        $results.hide();
                        $input.val('');
                        markDirty();
                    });
                }
            });
        }, 300);
    });

    // Cerrar dropdown al clic fuera
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.personal-card').length) {
            $('.person-search-results').hide();
            $('.person-search-wrap').addClass('d-none');
        }
    });

    function getAssignedIds() {
        var ids = [];
        if (personal.conductor_id) ids.push(personal.conductor_id);
        personal.ayudantes.forEach(function (a) { if (a && a.id) ids.push(a.id); });
        return ids;
    }

    // ── Cambio de fechas y días → marcar dirty ─────────────────
    $('#progFechaInicio, #progFechaFin').on('change', markDirty);
    $(document).on('change', '.dia-check', markDirty);

    // ── Validar disponibilidad ─────────────────────────────────
    $('#btnValidarDisp').on('click', function () {
        var fechaInicio = getFechaInicio();
        var fechaFin    = getFechaFin();
        var dias        = getDias();
        var conductorId = $('input[name="conductor_id"]').val();

        if (!fechaInicio || !fechaFin) {
            Swal.fire('Atención', 'Complete las fechas de inicio y fin.', 'warning');
            return;
        }
        if (!$('#selGroup').val()) {
            Swal.fire('Atención', 'Seleccione un grupo de personal.', 'warning');
            return;
        }
        if (!conductorId) {
            Swal.fire('Atención', 'Debe haber un conductor asignado.', 'warning');
            return;
        }
        if (!dias.length) {
            Swal.fire('Atención', 'Seleccione al menos un día de trabajo.', 'warning');
            return;
        }

        var ayudantesIds = $('input[name="ayudantes[]"]').map(function () {
            return $(this).val();
        }).get().filter(function (v) { return v !== ''; });

        $.ajax({
            url:  VALIDATE_URL,
            type: 'POST',
            data: {
                _token:       '{{ csrf_token() }}',
                fecha_inicio: fechaInicio,
                fecha_fin:    fechaFin,
                conductor_id: conductorId,
                ayudantes:    ayudantesIds,
                dias:         dias,
            },
            success: function (res) {
                $('#alertDatosCambiados').addClass('d-none');
                if (res.status === 'success') {
                    $('#alertErrors').addClass('d-none');
                    $('#alertSuccess').removeClass('d-none');
                    validationPassed = true;
                    $('#btnGuardarProg').prop('disabled', false);
                } else {
                    showErrors(res.errors, res.suggestions);
                    validationPassed = false;
                    $('#btnGuardarProg').prop('disabled', true);
                }
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al validar', 'error');
            }
        });
    });

    function showErrors(errors, suggestions) {
        $('#alertSuccess').addClass('d-none');
        var errHtml = errors.map(function (e) { return '<li>' + e + '</li>'; }).join('');
        $('#listErrors').html(errHtml);

        if (suggestions && suggestions.length) {
            var sugHtml = suggestions.map(function (s) { return '<li>' + s + '</li>'; }).join('');
            $('#listSuggestions').html(sugHtml);
            $('#blockSuggestions').removeClass('d-none');
        } else {
            $('#blockSuggestions').addClass('d-none');
        }
        $('#alertErrors').removeClass('d-none');
    }

    // ── Submit ─────────────────────────────────────────────────
    // (El ajax lo maneja bindFormSubmit en index.blade, aquí solo validamos)
    $('#formProgramacion').on('submit', function (e) {
        if (!validationPassed) {
            e.preventDefault();
            e.stopImmediatePropagation();
            Swal.fire('Atención', 'Debe validar la disponibilidad antes de guardar.', 'warning');
            return false;
        }
        if (!getDias().length) {
            e.preventDefault();
            e.stopImmediatePropagation();
            Swal.fire('Atención', 'Debe seleccionar al menos un día de trabajo.', 'warning');
            return false;
        }
    });

})();
</script>

<style>
.search-dropdown-results {
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
}
.search-result-item:hover { background: #f8f9fa; }
.person-display:hover { background: #e9ecef !important; }
</style>