<form action="{{ route('admin.cambios-masivos.store') }}" method="POST" id="formCambioMasivo">
    @csrf

    <div class="p-4">

        {{-- ── Fila 1: Fechas + Zona + Tipo ──────────────────── --}}
        <div class="row">
            <div class="col-md-3 form-group mb-3">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de Inicio *</label>
                <input type="date" name="fecha_inicio" id="cmFechaInicio" class="form-control" required>
            </div>
            <div class="col-md-3 form-group mb-3">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de Fin *</label>
                <input type="date" name="fecha_fin" id="cmFechaFin" class="form-control" required>
            </div>
            <div class="col-md-3 form-group mb-3">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Zonas (Opcional)</label>
                <select name="zone_id" id="cmZona" class="form-control">
                    <option value="">Todas las zonas</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Dejar vacío para aplicar a todas las zonas</small>
            </div>
            <div class="col-md-3 form-group mb-3">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Tipo de Cambio *</label>
                <select name="tipo_cambio" id="cmTipo" class="form-control" required>
                    <option value="">-- Seleccione tipo --</option>
                    <option value="turno">Cambio de Turno</option>
                    <option value="conductor">Cambio de Conductor</option>
                    <option value="ocupante">Cambio de Ocupante</option>
                    <option value="vehiculo">Cambio de Vehículo</option>
                </select>
            </div>
        </div>

        {{-- ── Sección dinámica según tipo ─────────────────────── --}}
        <div id="cm-campos-dinamicos" class="d-none">

            {{-- Loading state --}}
            <div id="cm-loading" class="text-center py-3 d-none">
                <i class="fas fa-spinner fa-spin text-primary mr-2"></i>
                <span class="text-muted">Cargando datos del rango seleccionado...</span>
            </div>

            {{-- Turno --}}
            <div id="cm-section-turno" class="d-none">
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Turno a Reemplazar
                            *</label>
                        {{-- Sin name: el valor se sincroniza vía hiddenAnteriorId --}}
                        <select id="cmTurnoAnterior" class="form-control">
                            <option value="">-- Cargando turnos... --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Nuevo Turno *</label>
                        {{-- Sin name: el valor se sincroniza vía hiddenNuevoId --}}
                        <select id="cmTurnoNuevo" class="form-control">
                            <option value="">-- Seleccione nuevo turno --</option>
                            @foreach($schedules as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->time_start }} - {{ $s->time_end }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Vehículo --}}
            <div id="cm-section-vehiculo" class="d-none">
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Vehículo a Reemplazar
                            *</label>
                        {{-- Sin name: el valor se sincroniza vía hiddenAnteriorId --}}
                        <select id="cmVehiculoAnterior" class="form-control">
                            <option value="">-- Cargando vehículos... --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Nuevo Vehículo *</label>
                        {{-- Sin name: el valor se sincroniza vía hiddenNuevoId --}}
                        <select id="cmVehiculoNuevo" class="form-control">
                            <option value="">-- Seleccione nuevo vehículo --</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->code }} — {{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Conductor --}}
            <div id="cm-section-conductor" class="d-none">
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Conductor a Reemplazar
                            *</label>
                        {{-- Sin name: el valor se sincroniza vía hiddenAnteriorId --}}
                        <select id="cmConductorAnterior" class="form-control">
                            <option value="">-- Cargando conductores... --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Nuevo Conductor *</label>
                        {{-- Renombrado a _val, sin name: el valor se sincroniza vía hiddenNuevoId --}}
                        <input type="hidden" id="cmConductorNuevoId_val">
                        <input type="text" id="cmConductorNuevoSearch" class="form-control"
                            placeholder="Escriba nombre o DNI para buscar..." autocomplete="off">
                        <div id="cmConductorNuevoResults" class="search-results-dropdown-cm"></div>
                        <div id="cmConductorNuevoDisplay" class="mt-2 d-none">
                            <div class="persona-seleccionada-cm">
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                <strong id="cmConductorNuevoName"></strong>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 ml-2"
                                    id="btnLimpiarConductorNuevo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ocupante --}}
            <div id="cm-section-ocupante" class="d-none">
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Ocupante a Reemplazar
                            *</label>
                        {{-- Sin name: el valor se sincroniza vía hiddenAnteriorId --}}
                        <select id="cmOcupanteAnterior" class="form-control">
                            <option value="">-- Cargando ocupantes... --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label class="font-weight-bold text-xs text-secondary text-uppercase">Nuevo Ocupante *</label>
                        {{-- Renombrado a _val, sin name: el valor se sincroniza vía hiddenNuevoId --}}
                        <input type="hidden" id="cmOcupanteNuevoId_val">
                        <input type="text" id="cmOcupanteNuevoSearch" class="form-control"
                            placeholder="Escriba nombre o DNI para buscar..." autocomplete="off">
                        <div id="cmOcupanteNuevoResults" class="search-results-dropdown-cm"></div>
                        <div id="cmOcupanteNuevoDisplay" class="mt-2 d-none">
                            <div class="persona-seleccionada-cm">
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                <strong id="cmOcupanteNuevoName"></strong>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 ml-2"
                                    id="btnLimpiarOcupanteNuevo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Motivo (siempre visible cuando hay tipo) ────── --}}
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Motivo Predefinido *</label>
                    <select name="cambio_id" id="cmMotivo" class="form-control" required>
                        <option value="">-- Seleccione un motivo --</option>
                        @foreach($motivos as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seleccione un motivo predefinido para el cambio</small>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="font-weight-bold text-xs text-secondary text-uppercase">Descripción Adicional
                        (Opcional)</label>
                    <input type="text" name="descripcion" id="cmDescripcion" class="form-control"
                        placeholder="Complemento al motivo predefinido">
                    <small class="text-muted">Complemento al motivo predefinido</small>
                </div>
            </div>

            {{-- Descripción completa (auto generada) --}}
            <div class="form-group mb-3">
                <label class="font-weight-bold text-xs text-secondary text-uppercase">Descripción Completa del Cambio
                    *</label>
                <textarea id="cmDescripcionCompleta" class="form-control" rows="2" readonly
                    style="background:#f8f9fa;"></textarea>
                <small class="text-muted">Este campo se completa automáticamente con el motivo seleccionado + detalles
                    adicionales</small>
            </div>

        </div>

        {{-- ── Hiddens centralizados para valor_anterior_id y valor_nuevo_id ── --}}
        <input type="hidden" name="valor_anterior_id" id="hiddenAnteriorId">
        <input type="hidden" name="valor_nuevo_id" id="hiddenNuevoId">

        {{-- ── Botones ─────────────────────────────────────────── --}}
        <div class="d-flex justify-content-end pt-3 border-top">
            <button type="button" class="btn btn-danger mr-2" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> Cancelar
            </button>
            <button type="button" class="btn btn-primary font-weight-bold" id="btnPreviewCambio" disabled>
                <i class="fas fa-save mr-1"></i> Guardar
            </button>
        </div>

    </div>
</form>

{{-- ── CSS local ──────────────────────────────────────────────── --}}
<style>
    .search-results-dropdown-cm {
        position: absolute;
        z-index: 1060;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
        max-height: 200px;
        overflow-y: auto;
        width: calc(100% - 30px);
        display: none;
    }

    .search-result-item-cm {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
        font-size: .84rem;
        transition: background .1s;
    }

    .search-result-item-cm:hover {
        background: #f8f9fa;
    }

    .persona-seleccionada-cm {
        background: #F0FDF4;
        border: 1px solid #86EFAC;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: .84rem;
    }
</style>

{{-- ── JS ────────────────────────────────────────────────────── --}}
<script>
    (function () {
        'use strict';

        var PERSONAS_URL = "{{ route('admin.cambios-masivos.personas-rango') }}";
        var RECURSOS_URL = "{{ route('admin.cambios-masivos.recursos-rango') }}";
        var SEARCH_URL = "{{ route('admin.cambios-masivos.search-users') }}";
        var STORE_URL = "{{ route('admin.cambios-masivos.store') }}";
        var CSRF = "{{ csrf_token() }}";

        var tipoActual = '';

        // ── Helpers ─────────────────────────────────────────────
        function getFI() { return $('#cmFechaInicio').val(); }
        function getFF() { return $('#cmFechaFin').val(); }
        function getZona() { return $('#cmZona').val(); }
        function getTipo() { return $('#cmTipo').val(); }
        function puedeCargar() { return getFI() && getFF() && getFI() <= getFF() && getTipo(); }

        function actualizarBoton() {
            var ok = puedeCargar() && validarCamposTipo();
            $('#btnPreviewCambio').prop('disabled', !ok);
        }

        function validarCamposTipo() {
            var tipo = getTipo();
            if (!tipo) return false;
            if (!$('#cmMotivo').val()) return false;

            switch (tipo) {
                case 'turno':
                    return $('#cmTurnoAnterior').val() && $('#cmTurnoNuevo').val()
                        && $('#cmTurnoAnterior').val() !== $('#cmTurnoNuevo').val();
                case 'vehiculo':
                    return $('#cmVehiculoAnterior').val() && $('#cmVehiculoNuevo').val()
                        && $('#cmVehiculoAnterior').val() !== $('#cmVehiculoNuevo').val();
                case 'conductor':
                    return $('#cmConductorAnterior').val() && $('#cmConductorNuevoId_val').val()
                        && $('#cmConductorAnterior').val() !== $('#cmConductorNuevoId_val').val();
                case 'ocupante':
                    return $('#cmOcupanteAnterior').val() && $('#cmOcupanteNuevoId_val').val()
                        && $('#cmOcupanteAnterior').val() !== $('#cmOcupanteNuevoId_val').val();
            }
            return false;
        }

        // ── Sincronizar hiddens centralizados ─────────────────
        function sincronizarHiddens() {
            var tipo = getTipo();
            var anteriorId = '', nuevoId = '';

            switch (tipo) {
                case 'turno':
                    anteriorId = $('#cmTurnoAnterior').val() || '';
                    nuevoId    = $('#cmTurnoNuevo').val() || '';
                    break;
                case 'vehiculo':
                    anteriorId = $('#cmVehiculoAnterior').val() || '';
                    nuevoId    = $('#cmVehiculoNuevo').val() || '';
                    break;
                case 'conductor':
                    anteriorId = $('#cmConductorAnterior').val() || '';
                    nuevoId    = $('#cmConductorNuevoId_val').val() || '';
                    break;
                case 'ocupante':
                    anteriorId = $('#cmOcupanteAnterior').val() || '';
                    nuevoId    = $('#cmOcupanteNuevoId_val').val() || '';
                    break;
            }

            $('#hiddenAnteriorId').val(anteriorId);
            $('#hiddenNuevoId').val(nuevoId);
            actualizarBoton();
        }

        // ── Auto-generar descripción completa ─────────────────
        function actualizarDescripcionCompleta() {
            var motivo = $('#cmMotivo option:selected').text();
            var detalle = $('#cmDescripcion').val().trim();
            var texto = motivo !== '-- Seleccione un motivo --' && motivo ? motivo : '';
            if (detalle) texto += (texto ? ' - ' : '') + detalle;
            $('#cmDescripcionCompleta').val(texto);
            sincronizarHiddens();
        }

        $('#cmMotivo, #cmDescripcion').on('change input', actualizarDescripcionCompleta);

        // ── Cargar recursos según tipo + rango ────────────────
        function cargarDinamico() {
            if (!puedeCargar()) return;

            var tipo = getTipo();
            tipoActual = tipo;

            // Ocultar todas las secciones
            $('#cm-section-turno, #cm-section-vehiculo, #cm-section-conductor, #cm-section-ocupante').addClass('d-none');
            $('#cm-campos-dinamicos').removeClass('d-none');
            $('#cm-loading').removeClass('d-none');

            if (tipo === 'turno' || tipo === 'vehiculo') {
                $.get(RECURSOS_URL, { fecha_inicio: getFI(), fecha_fin: getFF(), tipo: tipo, zone_id: getZona() },
                    function (data) {
                        $('#cm-loading').addClass('d-none');
                        var $sel = tipo === 'turno' ? $('#cmTurnoAnterior') : $('#cmVehiculoAnterior');
                        $sel.html('<option value="">-- Seleccione --</option>');
                        if (!data.length) {
                            $sel.html('<option value="">Sin ' + (tipo === 'turno' ? 'turnos' : 'vehículos') + ' en el rango</option>');
                        } else {
                            data.forEach(function (r) {
                                $sel.append('<option value="' + r.id + '">' + r.label + '</option>');
                            });
                        }
                        $('#cm-section-' + tipo).removeClass('d-none');
                        sincronizarHiddens();
                    }
                );
            } else {
                // conductor u ocupante
                var rolParam = tipo === 'conductor' ? 'conductor' : 'ocupante';
                $.get(PERSONAS_URL, { fecha_inicio: getFI(), fecha_fin: getFF(), rol: rolParam, zone_id: getZona() },
                    function (data) {
                        $('#cm-loading').addClass('d-none');
                        var $sel = tipo === 'conductor' ? $('#cmConductorAnterior') : $('#cmOcupanteAnterior');
                        $sel.html('<option value="">-- Seleccione --</option>');
                        if (!data.length) {
                            $sel.html('<option value="">Sin ' + rolParam + 'es en el rango</option>');
                        } else {
                            data.forEach(function (u) {
                                $sel.append('<option value="' + u.id + '">' + u.name + ' — DNI ' + u.dni + '</option>');
                            });
                        }
                        $('#cm-section-' + tipo).removeClass('d-none');
                        sincronizarHiddens();
                    }
                );
            }
        }

        // ── Eventos de cambio de filtros ──────────────────────
        $('#cmTipo').on('change', cargarDinamico);
        $('#cmFechaInicio, #cmFechaFin, #cmZona').on('change', function () {
            if (getTipo()) cargarDinamico();
        });

        // ── Cambio en selects dinámicos ───────────────────────
        $(document).on('change', '#cmTurnoAnterior, #cmTurnoNuevo, #cmVehiculoAnterior, #cmVehiculoNuevo, #cmConductorAnterior, #cmOcupanteAnterior', sincronizarHiddens);

        // ── Búsqueda en vivo: conductor nuevo ────────────────
        var timerConductor;
        $('#cmConductorNuevoSearch').on('input', function () {
            clearTimeout(timerConductor);
            var q = $(this).val().trim();
            if (q.length < 2) { $('#cmConductorNuevoResults').hide(); return; }
            timerConductor = setTimeout(function () {
                buscarPersona(q, 'conductor', '#cmConductorNuevoResults', function (u) {
                    $('#cmConductorNuevoId_val').val(u.id);
                    $('#cmConductorNuevoName').text(u.name);
                    $('#cmConductorNuevoDisplay').removeClass('d-none');
                    $('#cmConductorNuevoSearch').addClass('d-none');
                    $('#cmConductorNuevoResults').hide();
                    sincronizarHiddens();
                }, [$('#cmConductorAnterior').val()]);
            }, 300);
        });

        $('#btnLimpiarConductorNuevo').on('click', function () {
            $('#cmConductorNuevoId_val').val('');
            $('#cmConductorNuevoDisplay').addClass('d-none');
            $('#cmConductorNuevoSearch').val('').removeClass('d-none').focus();
            sincronizarHiddens();
        });

        // ── Búsqueda en vivo: ocupante nuevo ─────────────────
        var timerOcupante;
        $('#cmOcupanteNuevoSearch').on('input', function () {
            clearTimeout(timerOcupante);
            var q = $(this).val().trim();
            if (q.length < 2) { $('#cmOcupanteNuevoResults').hide(); return; }
            timerOcupante = setTimeout(function () {
                buscarPersona(q, 'ayudante', '#cmOcupanteNuevoResults', function (u) {
                    $('#cmOcupanteNuevoId_val').val(u.id);
                    $('#cmOcupanteNuevoName').text(u.name);
                    $('#cmOcupanteNuevoDisplay').removeClass('d-none');
                    $('#cmOcupanteNuevoSearch').addClass('d-none');
                    $('#cmOcupanteNuevoResults').hide();
                    sincronizarHiddens();
                }, [$('#cmOcupanteAnterior').val()]);
            }, 300);
        });

        $('#btnLimpiarOcupanteNuevo').on('click', function () {
            $('#cmOcupanteNuevoId_val').val('');
            $('#cmOcupanteNuevoDisplay').addClass('d-none');
            $('#cmOcupanteNuevoSearch').val('').removeClass('d-none').focus();
            sincronizarHiddens();
        });

        function buscarPersona(q, rol, dropSelector, onSelect, excluir) {
            $.ajax({
                url: SEARCH_URL,
                data: { q: q, rol: rol, exclude: excluir || [] },
                success: function (users) {
                    var $drop = $(dropSelector);
                    if (!users.length) {
                        $drop.html('<div class="search-result-item-cm text-muted">Sin resultados.</div>').show();
                        return;
                    }
                    $drop.html(users.map(function (u) {
                        return '<div class="search-result-item-cm" data-id="' + u.id + '" data-name="' + u.name + '">'
                            + '<strong>' + u.name + '</strong>'
                            + '<div style="font-size:.74rem;color:#9CA3AF;">DNI ' + u.dni + '</div>'
                            + '</div>';
                    }).join('')).show();
                    $drop.off('click').on('click', '.search-result-item-cm', function () {
                        onSelect({ id: $(this).data('id'), name: $(this).data('name') });
                    });
                }
            });
        }

        // Cerrar dropdowns al clic fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#cmConductorNuevoSearch, #cmConductorNuevoResults').length) {
                $('#cmConductorNuevoResults').hide();
            }
            if (!$(e.target).closest('#cmOcupanteNuevoSearch, #cmOcupanteNuevoResults').length) {
                $('#cmOcupanteNuevoResults').hide();
            }
        });

        // ── Guardar → mostrar modal de confirmación ───────────
        $('#btnPreviewCambio').on('click', function () {
            var tipo = getTipo();
            var tipoLabel = { turno: 'Cambio de Turno', conductor: 'Cambio de Conductor', ocupante: 'Cambio de Ocupante', vehiculo: 'Cambio de Vehículo' }[tipo];
            var tipoColor = { turno: '#F59E0B', conductor: '#3B82F6', ocupante: '#8B5CF6', vehiculo: '#10B981' }[tipo];

            var anteriorLabel = '', nuevoLabel = '';
            switch (tipo) {
                case 'turno':
                    anteriorLabel = $('#cmTurnoAnterior option:selected').text();
                    nuevoLabel = $('#cmTurnoNuevo option:selected').text();
                    break;
                case 'vehiculo':
                    anteriorLabel = $('#cmVehiculoAnterior option:selected').text();
                    nuevoLabel = $('#cmVehiculoNuevo option:selected').text();
                    break;
                case 'conductor':
                    anteriorLabel = $('#cmConductorAnterior option:selected').text();
                    nuevoLabel = $('#cmConductorNuevoName').text();
                    break;
                case 'ocupante':
                    anteriorLabel = $('#cmOcupanteAnterior option:selected').text();
                    nuevoLabel = $('#cmOcupanteNuevoName').text();
                    break;
            }

            var zonaLabel = $('#cmZona option:selected').text() || 'Todas las zonas';
            var motivoLabel = $('#cmDescripcionCompleta').val();

            $('#ConfirmModalBody').html(`
            <p class="text-center text-muted mb-4" style="font-size:.85rem;">
                Revise cuidadosamente los detalles antes de proceder
            </p>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 h-100" style="background:#071D38;border-radius:12px;color:#fff;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-cogs mr-2" style="font-size:1.1rem;"></i>
                            <strong>Configuración General</strong>
                        </div>
                        <div class="mb-2">
                            <small style="color:#94A3B8;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Tipo de Cambio</small>
                            <div><span style="background:${tipoColor};color:#fff;padding:2px 10px;border-radius:20px;font-size:.78rem;font-weight:700;">${tipoLabel}</span></div>
                        </div>
                        <div class="mb-2 mt-2">
                            <small style="color:#94A3B8;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Período</small>
                            <div style="font-size:.85rem;">
                                Inicio: <strong style="color:#34D399;">${getFI()}</strong><br>
                                Fin: <strong style="color:#34D399;">${getFF()}</strong>
                            </div>
                        </div>
                        <div>
                            <small style="color:#94A3B8;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Ámbito de Aplicación</small>
                            <div style="font-size:.85rem;color:#FCD34D;">${zonaLabel}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 h-100" style="background:#1D4ED8;border-radius:12px;color:#fff;">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users mr-2" style="font-size:1.1rem;"></i>
                            <strong>Gestión de Recursos</strong>
                        </div>
                        <div class="mb-2">
                            <small style="color:#BFDBFE;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Recurso a Reemplazar</small>
                            <div style="font-size:.88rem;color:#FCA5A5;font-weight:600;">${anteriorLabel}</div>
                        </div>
                        <div class="mb-2 mt-2">
                            <small style="color:#BFDBFE;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Nuevo Recurso</small>
                            <div style="font-size:.88rem;color:#86EFAC;font-weight:600;">${nuevoLabel}</div>
                        </div>
                        <div>
                            <small style="color:#BFDBFE;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Motivo Predefinido</small>
                            <div style="font-size:.82rem;">
                                <i class="fas fa-quote-left mr-1" style="font-size:.65rem;"></i>${motivoLabel}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 mb-4" style="background:#FEF3C7;border:1.5px solid #FDE68A;border-radius:12px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-warning mr-2" style="font-size:1.1rem;"></i>
                    <div>
                        <strong style="color:#92400E;">Advertencia del Sistema</strong>
                        <div style="font-size:.8rem;color:#78350F;">
                            Esta operación modificará múltiples programaciones existentes. La acción es irreversible en bloque y requiere confirmación expresa.
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center" style="gap:1rem;">
                <button type="button" class="btn btn-primary font-weight-bold px-4" id="btnConfirmarCambio">
                    <i class="fas fa-check-circle mr-1"></i> Confirmar
                </button>
                <button type="button" class="btn btn-outline-danger font-weight-bold px-4" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i> Cancelar
                </button>
            </div>
        `);

            $('#ConfirmModal').modal('show');
        });

        // ── Confirmar y enviar ────────────────────────────────
        $(document).on('click', '#btnConfirmarCambio', function () {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Aplicando...');

            // Construir formData manualmente para manejar los inputs ocultos
            var formData = $('#formCambioMasivo').serialize();

            $.ajax({
                url: STORE_URL,
                type: 'POST',
                data: formData,
                success: function (res) {
                    $('#ConfirmModal').modal('hide');
                    $('#CambioModal').modal('hide');
                    if (typeof table !== 'undefined') table.ajax.reload(null, false);
                    Swal.fire({
                        title: '¡Cambio aplicado!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#071D38',
                    });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Confirmar');
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al aplicar cambio', 'error');
                }
            });
        });

        $('#cmTurnoAnterior').on('change', function () {
            var anteriorId = $(this).val();
            $('#cmTurnoNuevo option').each(function () {
                $(this).prop('disabled', $(this).val() == anteriorId);
            });
            if ($('#cmTurnoNuevo').val() == anteriorId) {
                $('#cmTurnoNuevo').val('');
            }
            sincronizarHiddens();
        });

        $('#cmVehiculoAnterior').on('change', function () {
            var anteriorId = $(this).val();
            $('#cmVehiculoNuevo option').each(function () {
                $(this).prop('disabled', $(this).val() == anteriorId);
            });
            if ($('#cmVehiculoNuevo').val() == anteriorId) {
                $('#cmVehiculoNuevo').val('');
            }
            sincronizarHiddens();
        });

    })();
</script>