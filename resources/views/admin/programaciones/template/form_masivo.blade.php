{{-- Formulario: Programación Masiva --}}
<form action="{{ route('admin.programacion.store-masivo') }}" method="POST" id="formMasivo">
    @csrf

    {{-- Fechas + Validar --}}
    <div class="row align-items-end mb-3">
        <div class="col-md-4 form-group mb-0">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de inicio *</label>
            <input type="date" name="fecha_inicio" id="masivoFechaInicio" class="form-control" required>
        </div>
        <div class="col-md-4 form-group mb-0">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de fin *</label>
            <input type="date" name="fecha_fin" id="masivoFechaFin" class="form-control" required>
        </div>
        <div class="col-md-4 form-group mb-0">
            <button type="button" class="btn btn-outline-primary font-weight-bold w-100" id="btnValidarMasivo">
                <i class="fas fa-check-circle mr-1"></i> Validar Disponibilidad
            </button>
        </div>
    </div>

    {{-- Filtro por turno --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Filtrar por Turno:</label>
        <div class="d-flex flex-wrap" style="gap:.4rem;">
            <button type="button" class="btn btn-primary btn-sm btn-turno active" data-turno="">
                Todos los Turnos
            </button>
            @foreach($schedules as $schedule)
                <button type="button" class="btn btn-outline-secondary btn-sm btn-turno"
                        data-turno="{{ $schedule->id }}">
                    {{ $schedule->name }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Feriados --}}
    <div class="card border mb-3">
        <div class="card-header bg-light py-2">
            <i class="fas fa-calendar-times mr-1 text-danger"></i>
            <strong class="text-sm">Días Feriados en el Rango Seleccionado:</strong>
        </div>
        <div class="card-body py-2">
            <small class="text-muted d-block mb-2">Feriados encontrados:
                <em>Seleccione los que NO desea programar</em>
            </small>
            <div id="feriadosContainer">
                <p class="text-muted text-sm mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Seleccione un rango de fechas para ver los feriados
                </p>
            </div>
            <small class="text-info mt-2 d-block">
                <i class="fas fa-info-circle mr-1"></i>
                Los feriados seleccionados NO serán programados, incluso si el grupo trabaja ese día.
            </small>
        </div>
    </div>

    {{-- Grupos de Trabajo --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase d-block mb-2">
            Grupos de Trabajo
        </label>
        <div id="gruposContainer" class="row">
            @foreach($groups as $group)
                <div class="col-md-4 mb-3 grupo-card-wrap"
                     data-group-id="{{ $group->id }}"
                     data-schedule-id="{{ $group->schedule_id }}">
                    <div class="card border h-100 grupo-card" style="border-radius:10px;">
                        <div class="card-body p-3">
                            {{-- Header grupo --}}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong class="text-uppercase" style="font-size:.82rem;">
                                    {{ $group->name }}
                                </strong>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger p-0 px-1 btn-remove-group"
                                        data-group-id="{{ $group->id }}"
                                        title="Quitar grupo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div style="font-size:.8rem;" class="mb-2">
                                <div><strong>Zona:</strong> {{ optional($group->zone)->name ?? '-' }}</div>
                                <div class="mt-1">
                                    <strong>Turno:</strong>
                                    @if($group->schedule)
                                        @php
                                            $sc = $group->schedule;
                                            $color = str_contains(strtolower($sc->name),'noch') ? '#64748B'
                                                   : (str_contains(strtolower($sc->name),'tard') ? '#8B5CF6' : '#F59E0B');
                                        @endphp
                                        <span class="badge px-2" style="background:{{ $color }};color:#fff;">
                                            {{ $sc->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <strong>Días:</strong>
                                    {{ implode(', ', $group->days ?? []) ?: '-' }}
                                </div>
                                <div class="mt-1">
                                    <strong>Vehículo:</strong>
                                    @if($group->vehicle)
                                        <span class="badge badge-info px-2">
                                            {{ $group->vehicle->code }}
                                            (Cap. {{ $group->vehicle->occupant_capacity }})
                                        </span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            {{-- Conductor --}}
                            <div class="form-group mb-2">
                                <label class="font-weight-bold text-xs text-secondary text-uppercase">
                                    Conductor:
                                </label>
                                @php
                                    $conductor = $group->members->firstWhere('pivot.role','conductor');
                                    $ayudantes = $group->members->where('pivot.role','ayudante')->values();
                                @endphp
                                <select name="grupos[{{ $group->id }}][conductor_id]"
                                        class="form-control form-control-sm sel-conductor"
                                        data-group-id="{{ $group->id }}">
                                    @foreach($conductores as $c)
                                        <option value="{{ $c->id }}"
                                            {{ $conductor && $conductor->id == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="aviso-disponibilidad mt-1" data-group-id="{{ $group->id }}"
                                     data-rol="conductor" style="font-size:.73rem;"></div>
                            </div>

                            {{-- Ayudantes --}}
                            @foreach($ayudantes as $i => $ay)
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold text-xs text-secondary text-uppercase">
                                        Ayudante {{ $i + 1 }}{{ $i === 0 ? ':' : ':' }}
                                    </label>
                                    <select name="grupos[{{ $group->id }}][ayudantes][]"
                                            class="form-control form-control-sm sel-ayudante"
                                            data-group-id="{{ $group->id }}">
                                        <option value="">-- Opcional --</option>
                                        @foreach($ayudantesAll as $a)
                                            <option value="{{ $a->id }}"
                                                {{ $ay->id == $a->id ? 'selected' : '' }}>
                                                {{ $a->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="aviso-disponibilidad mt-1" data-group-id="{{ $group->id }}"
                                         data-rol="ayudante{{ $i }}" style="font-size:.73rem;"></div>
                                </div>
                            @endforeach

                            {{-- Input hidden para dias del grupo --}}
                            @foreach($group->days ?? [] as $day)
                                <input type="hidden" name="grupos[{{ $group->id }}][dias][]" value="{{ $day }}">
                            @endforeach

                            {{-- Resultado de validación por grupo --}}
                            <div class="grupo-validacion-result mt-2 d-none" data-group-id="{{ $group->id }}"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Resultado de validación general --}}
    <div id="resultadoValidacionGeneral" class="d-none mb-3">
        <h6 class="font-weight-bold text-danger mb-2">
            <i class="fas fa-exclamation-triangle mr-1"></i> Resultado de Validación General
        </h6>
        <div id="resultadoValidacionBody"></div>
    </div>

    {{-- Botones --}}
    <div class="d-flex justify-content-end mt-3 pt-2 border-top">
        <button type="button" class="btn btn-danger mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" id="btnGuardarMasivo" class="btn btn-primary font-weight-bold" disabled>
            <i class="fas fa-save mr-1"></i> Guardar
        </button>
    </div>
</form>

<script>
(function () {
    'use strict';

    var VALIDATE_MASIVO_URL = "{{ route('admin.programacion.validate-masivo') }}";
    var FERIADOS_URL        = "{{ route('admin.programacion.feriados') }}";
    var validationPassed    = false;
    var scheduleFilter      = ''; // ID del turno activo, '' = todos

    // ── Filtro por turno ───────────────────────────────────────
    $(document).on('click', '.btn-turno', function () {
        $('.btn-turno').removeClass('btn-primary active').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
        scheduleFilter = $(this).data('turno');
        filterGrupos();
        markDirty();
    });

    function filterGrupos() {
        $('.grupo-card-wrap').each(function () {
            if (!scheduleFilter || $(this).data('schedule-id') == scheduleFilter) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // ── Quitar grupo ───────────────────────────────────────────
    $(document).on('click', '.btn-remove-group', function () {
        var gid = $(this).data('group-id');
        $('.grupo-card-wrap[data-group-id="' + gid + '"]').hide();
        markDirty();
    });

    // ── Cambio de fechas → cargar feriados ────────────────────
    $('#masivoFechaInicio, #masivoFechaFin').on('change', function () {
        var fi = $('#masivoFechaInicio').val();
        var ff = $('#masivoFechaFin').val();
        if (fi && ff && fi <= ff) {
            loadFeriados(fi, ff);
        }
        markDirty();
    });

    function loadFeriados(fi, ff) {
        $.get(FERIADOS_URL, { fecha_inicio: fi, fecha_fin: ff }, function (data) {
            if (!data.length) {
                $('#feriadosContainer').html(
                    '<p class="text-muted text-sm mb-0"><i class="fas fa-check-circle text-success mr-1"></i>No hay feriados en este rango.</p>'
                );
                return;
            }
            var html = data.map(function (f) {
                return '<div class="form-check form-check-inline mb-1">' +
                       '<input class="form-check-input feriado-check" type="checkbox" ' +
                       'name="feriados_excluir[]" id="feriado_' + f.id + '" value="' + f.date + '">' +
                       '<label class="form-check-label" for="feriado_' + f.id + '" style="font-size:.82rem;">' +
                       '<strong>' + f.date_fmt + '</strong> — ' + f.description +
                       '</label></div>';
            }).join('');
            $('#feriadosContainer').html(html);
        });
    }

    $(document).on('change', '.feriado-check, .sel-conductor, .sel-ayudante', markDirty);

    function markDirty() {
        validationPassed = false;
        $('#btnGuardarMasivo').prop('disabled', true);
        $('#resultadoValidacionGeneral').addClass('d-none');
        $('.aviso-disponibilidad').html('');
        $('.grupo-validacion-result').addClass('d-none').html('');
    }

    // ── Validar Masivo ─────────────────────────────────────────
    $('#btnValidarMasivo').on('click', function () {
        var fi = $('#masivoFechaInicio').val();
        var ff = $('#masivoFechaFin').val();

        if (!fi || !ff) {
            Swal.fire('Atención', 'Seleccione las fechas de inicio y fin.', 'warning');
            return;
        }
        if (fi > ff) {
            Swal.fire('Atención', 'La fecha de fin debe ser mayor o igual a la fecha de inicio.', 'warning');
            return;
        }

        // Recopilar grupos visibles
        var grupos = [];
        $('.grupo-card-wrap:visible').each(function () {
            var gid       = $(this).data('group-id');
            var conductor = $('select[name="grupos[' + gid + '][conductor_id]"]').val();
            var ayudantes = [];
            $('select[name="grupos[' + gid + '][ayudantes][]"]').each(function () {
                if ($(this).val()) ayudantes.push($(this).val());
            });
            var dias = [];
            $('input[name="grupos[' + gid + '][dias][]"]').each(function () {
                dias.push($(this).val());
            });
            grupos.push({
                group_id:     gid,
                conductor_id: conductor,
                ayudantes:    ayudantes,
                dias:         dias,
            });
        });

        if (!grupos.length) {
            Swal.fire('Atención', 'No hay grupos de trabajo visibles para validar.', 'warning');
            return;
        }

        var feriadosExcluir = [];
        $('.feriado-check:checked').each(function () {
            feriadosExcluir.push($(this).val());
        });

        $('#btnValidarMasivo').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin mr-1"></i> Validando...'
        );

        $.ajax({
            url:  VALIDATE_MASIVO_URL,
            type: 'POST',
            data: {
                _token:           '{{ csrf_token() }}',
                fecha_inicio:     fi,
                fecha_fin:        ff,
                grupos:           grupos,
                feriados_excluir: feriadosExcluir,
            },
            success: function (res) {
                $('#btnValidarMasivo').prop('disabled', false).html(
                    '<i class="fas fa-check-circle mr-1"></i> Validar Disponibilidad'
                );
                renderValidationResults(res);
            },
            error: function (xhr) {
                $('#btnValidarMasivo').prop('disabled', false).html(
                    '<i class="fas fa-check-circle mr-1"></i> Validar Disponibilidad'
                );
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al validar', 'error');
            }
        });
    });

    // ── Renderizar resultados de validación ────────────────────
    function renderValidationResults(res) {
        var hayErrores = false;

        // Limpiar avisos previos
        $('.aviso-disponibilidad').html('');
        $('.grupo-validacion-result').addClass('d-none').html('');

        var bodyHtml = '';

        res.grupos.forEach(function (g) {
            var gid        = g.group_id;
            var tieneError = g.errores && g.errores.length > 0;
            var tieneAdv   = g.advertencias && g.advertencias.length > 0;
            if (tieneError) hayErrores = true;

            // Badge de estado en el header de la tarjeta
            var $card = $('.grupo-card-wrap[data-group-id="' + gid + '"] .grupo-card');

            // Avisos por persona
            if (g.avisos_persona) {
                g.avisos_persona.forEach(function (av) {
                    var color  = av.tipo === 'error' ? '#dc3545' : '#0dcaf0';
                    var icon   = av.tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
                    var $aviso = $('.aviso-disponibilidad[data-group-id="' + gid + '"][data-rol="' + av.rol + '"]');
                    $aviso.html('<span style="color:' + color + ';"><i class="fas ' + icon + ' mr-1"></i>' + av.mensaje + '</span>');
                    // Resaltar select
                    var selName = av.rol === 'conductor'
                        ? 'select[name="grupos[' + gid + '][conductor_id]"]'
                        : 'select[name="grupos[' + gid + '][ayudantes][]"]:eq(' + av.rol.replace('ayudante','') + ')';
                    $(selName).css('border-color', av.tipo === 'error' ? '#dc3545' : '#0dcaf0');
                });
            }

            // Bloque de resultado por grupo en la sección general
            var badgesHtml = '';
            if (tieneError) badgesHtml += '<span class="badge badge-danger ml-1">Con Errores</span>';
            if (tieneAdv)   badgesHtml += '<span class="badge badge-warning ml-1">Con Advertencias</span>';
            if (!tieneError && !tieneAdv) badgesHtml += '<span class="badge badge-success ml-1">OK</span>';

            var errHtml = '';
            if (tieneError) {
                errHtml += '<div class="mt-2"><strong class="text-danger"><i class="fas fa-times-circle mr-1"></i>Errores:</strong><ul class="mb-1 mt-1">';
                g.errores.forEach(function (e) { errHtml += '<li style="font-size:.82rem;">' + e + '</li>'; });
                errHtml += '</ul></div>';
            }

            var advHtml = '';
            if (tieneAdv) {
                advHtml += '<div class="mt-2"><strong class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Advertencias:</strong><ul class="mb-1 mt-1">';
                g.advertencias.forEach(function (a) { advHtml += '<li style="font-size:.82rem;" class="text-warning">' + a + '</li>'; });
                advHtml += '</ul></div>';
            }

            var diasNoCubiertos = '';
            if (g.dias_no_cubiertos && g.dias_no_cubiertos > 0) {
                diasNoCubiertos = '<div class="mt-1" style="font-size:.8rem;">'
                    + '<i class="fas fa-calendar-times text-secondary mr-1"></i>'
                    + '<strong>' + g.dias_no_cubiertos + ' día(s) no cubiertos</strong>'
                    + ' (el grupo solo trabaja: ' + (g.dias_grupo || []).join(', ') + ')'
                    + '</div>';
            }

            var bgColor = tieneError ? '#fff5f5' : (tieneAdv ? '#fffbeb' : '#f0fdf4');
            var borderC = tieneError ? '#fca5a5' : (tieneAdv ? '#fde68a' : '#86efac');

            bodyHtml += '<div class="mb-3 p-3 rounded" style="background:' + bgColor + ';border:1px solid ' + borderC + ';">'
                + '<div class="d-flex justify-content-between align-items-center">'
                + '<strong>' + g.group_name + '</strong>'
                + '<div>' + badgesHtml + '</div></div>'
                + diasNoCubiertos + errHtml + advHtml
                + '</div>';
        });

        $('#resultadoValidacionBody').html(bodyHtml);
        $('#resultadoValidacionGeneral').removeClass('d-none');

        if (!hayErrores) {
            validationPassed = true;
            $('#btnGuardarMasivo').prop('disabled', false);
        } else {
            validationPassed = false;
            $('#btnGuardarMasivo').prop('disabled', true);
        }
    }

    // ── Submit ─────────────────────────────────────────────────
    $('#formMasivo').on('submit', function (e) {
        if (!validationPassed) {
            e.preventDefault();
            e.stopImmediatePropagation();
            Swal.fire('Atención', 'Debe validar la disponibilidad antes de guardar.', 'warning');
            return false;
        }
    });

})();
</script>