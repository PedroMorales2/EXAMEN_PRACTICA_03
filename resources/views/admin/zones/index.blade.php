@extends('adminlte::page')

@section('title', 'RSU JLO - Zonas')

@section('content')
    <div class="container-fluid pt-4 pb-4 content-crud animate-fade-in">

        <div class="card border-0 shadow-sm custom-crud-card">
            <div class="card-header custom-crud-header d-flex align-items-center justify-content-between py-3">
                <h4 class="mb-0 font-weight-black text-white">
                    <i class="fas fa-map-marked-alt mr-2 text-white-75"></i> Lista de Zonas
                </h4>
                <div class="ml-auto d-flex align-items-center" style="gap: 12px;">
                    <button type="button"
                        class="btn btn-success font-weight-bold px-3.5 py-2 shadow-sm d-flex align-items-center"
                        id="btn-ver-mapa" style="border-radius: 8px;">
                        <i class="fas fa-map mr-1.5"></i> Ver Mapa de Zonas
                    </button>
                    <button type="button"
                        class="btn btn-action-add font-weight-bold px-3.5 py-2 shadow-sm d-flex align-items-center"
                        id="btn-nueva-zona" style="border-radius: 8px;">
                        <i class="fas fa-plus mr-1.5"></i> Nueva Zona
                    </button>
                </div>
            </div>

            <div class="card-body p-4 bg-white">
                <div class="table-responsive">
                    <table id="tblZones" class="table table-custom table-hover w-100">
                        <thead>
                            <tr>
                                <th class="align-middle">NOMBRE</th>
                                <th class="align-middle">DISTRITO</th>
                                <th class="align-middle">PROVINCIA</th>
                                <th class="align-middle">DEPARTAMENTO</th>
                                <th class="align-middle">DESCRIPCIÓN</th>
                                <th class="text-center align-middle">COORDENADAS</th>
                                <th class="text-center align-middle">ESTADO</th>
                                <th class="text-center align-middle">FECHA CREACIÓN</th>
                                <th class="text-center align-middle">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL CRUD (crear / editar) ── --}}
    <div class="modal fade" id="ZoneModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg custom-modal-content">
                <div class="modal-header custom-modal-header text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="ZoneModalTitle">Formulario de Zona</h5>
                    <button type="button" class="close text-white opacity-80 hover-opacity-100"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light-panel"></div>
            </div>
        </div>
    </div>

    {{-- ── MODAL DETALLE DE ZONA INDIVIDUAL (btn-mapa por fila) ── --}}
    <div class="modal fade" id="ZoneDetailMapModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header custom-modal-header text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marked-alt mr-2"></i> Mapa de la Zona
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0"></div>
            </div>
        </div>
    </div>

    {{-- ── MODAL EXPLORADOR GLOBAL DE ZONAS ── --}}
    <div class="modal fade" id="ZoneMapModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 96vw;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">

                <div class="modal-header custom-modal-header text-white py-3 px-4"
                    style="background: linear-gradient(135deg, #071D38 0%, #0f3460 100%);">
                    <h5 class="modal-title font-weight-bold d-flex align-items-center"
                        style="font-size: 1rem; letter-spacing: .3px;">
                        <i class="fas fa-globe-americas mr-2" style="font-size: 1.1rem;"></i>
                        Explorador de Zonas Geográficas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"
                        style="opacity:.85; font-size: 1.4rem;">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0 d-flex" style="height: 78vh; background: #f4f6f9;">

                    {{-- Panel lateral --}}
                    <div id="zone-map-sidebar" style="
                        width: 270px; min-width: 270px;
                        background: #fff;
                        border-right: 1px solid #e3e8ef;
                        display: flex; flex-direction: column;
                        overflow-y: auto; overflow-x: hidden;">

                        {{-- Filtros --}}
                        <div class="p-3 border-bottom" style="background: #f8fafc;">
                            <p class="text-uppercase font-weight-bold mb-2"
                                style="font-size: .68rem; letter-spacing: 1px; color: #5a6a85;">
                                <i class="fas fa-filter mr-1"></i> Filtros de Búsqueda
                            </p>

                            <div class="mb-2">
                                <label class="mb-1" style="font-size:.72rem; font-weight:600; color:#3d4f6b;">
                                    <i class="fas fa-map mr-1" style="color:#3b7dd8;"></i> Departamento
                                </label>
                                <select id="map-filter-department" class="form-control form-control-sm"
                                    style="border-radius:6px; font-size:.8rem; border:1px solid #cdd8e8;">
                                    <option value="">— Todos —</option>
                                    @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="mb-1" style="font-size:.72rem; font-weight:600; color:#3d4f6b;">
                                    <i class="fas fa-city mr-1" style="color:#3b7dd8;"></i> Provincia
                                </label>
                                <select id="map-filter-province" class="form-control form-control-sm"
                                    style="border-radius:6px; font-size:.8rem; border:1px solid #cdd8e8;" disabled>
                                    <option value="">— Seleccione departamento —</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="mb-1" style="font-size:.72rem; font-weight:600; color:#3d4f6b;">
                                    <i class="fas fa-map-pin mr-1" style="color:#3b7dd8;"></i> Distrito
                                </label>
                                <select id="map-filter-district" class="form-control form-control-sm"
                                    style="border-radius:6px; font-size:.8rem; border:1px solid #cdd8e8;" disabled>
                                    <option value="">— Seleccione provincia —</option>
                                </select>
                            </div>

                            <button id="btn-apply-zone-filter" class="btn btn-sm btn-block font-weight-bold mt-1"
                                style="background:#071D38; color:#fff; border-radius:6px; font-size:.78rem;">
                                <i class="fas fa-search mr-1"></i> Buscar Zonas
                            </button>
                            <button id="btn-reset-zone-filter" class="btn btn-sm btn-block mt-1"
                                style="background:#f0f4f8; color:#5a6a85; border:1px solid #cdd8e8; border-radius:6px; font-size:.78rem;">
                                <i class="fas fa-redo mr-1"></i> Limpiar Filtros
                            </button>
                        </div>

                        {{-- Estadísticas --}}
                        <div class="p-3 border-bottom">
                            <p class="text-uppercase font-weight-bold mb-2"
                                style="font-size:.68rem; letter-spacing:1px; color:#5a6a85;">
                                <i class="fas fa-chart-bar mr-1"></i> Estadísticas
                            </p>
                            <div class="rounded p-2 mb-2 text-center"
                                style="background:#eaf1fb; border:1px solid #c8dcf5;">
                                <div class="font-weight-bold"
                                    style="font-size:.68rem; color:#3b7dd8; text-transform:uppercase; letter-spacing:.5px;">
                                    Zonas Encontradas</div>
                                <div id="stat-total" class="font-weight-bold"
                                    style="font-size:1.5rem; color:#071D38; line-height:1.2;">0</div>
                            </div>
                            <div class="d-flex" style="gap:8px;">
                                <div class="rounded p-2 text-center flex-fill"
                                    style="background:#eafaf1; border:1px solid #b7e4cc;">
                                    <div style="font-size:.65rem; color:#1a7a45; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Activas</div>
                                    <div id="stat-active" class="font-weight-bold"
                                        style="font-size:1.2rem; color:#155d35;">0</div>
                                </div>
                                <div class="rounded p-2 text-center flex-fill"
                                    style="background:#eaf3fb; border:1px solid #b7d6f5;">
                                    <div style="font-size:.65rem; color:#1565a7; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Total Puntos</div>
                                    <div id="stat-points" class="font-weight-bold"
                                        style="font-size:1.2rem; color:#0d4a8b;">0</div>
                                </div>
                            </div>
                        </div>

                        {{-- Leyenda --}}
                        <div class="p-3">
                            <p class="text-uppercase font-weight-bold mb-2"
                                style="font-size:.68rem; letter-spacing:1px; color:#5a6a85;">
                                <i class="fas fa-layer-group mr-1"></i> Leyenda del Mapa
                            </p>
                            <div class="d-flex align-items-center mb-1" style="gap:8px;">
                                <div style="width:18px; height:12px; background:#28a745; border-radius:3px; border:1px solid #1e7e34; flex-shrink:0;"></div>
                                <span style="font-size:.75rem; color:#3d4f6b;">Zonas Activas</span>
                            </div>
                            <div class="d-flex align-items-center mb-1" style="gap:8px;">
                                <div style="width:18px; height:12px; background:rgba(59,125,216,.25); border:2px solid #3b7dd8; border-radius:3px; flex-shrink:0;"></div>
                                <span style="font-size:.75rem; color:#3d4f6b;">Distrito Seleccionado</span>
                            </div>
                        </div>

                        {{-- Etiqueta de ubicación --}}
                        <div class="mt-auto px-3 pb-3">
                            <div class="d-flex align-items-start"
                                style="font-size:.72rem; color:#5a6a85; gap:6px;">
                                <i class="fas fa-map-marker-alt mt-1" style="color:#e74c3c; flex-shrink:0;"></i>
                                <span id="zone-location-text">Mostrando todas las zonas registradas</span>
                            </div>
                        </div>
                    </div>

                    {{-- Mapa --}}
                    <div style="flex:1; position:relative; overflow:hidden;">
                        <div id="zone-explorer-map" style="width:100%; height:100%;"></div>

                        {{-- Loader --}}
                        <div id="zone-map-loader" style="
                            display:none; position:absolute; inset:0;
                            background:rgba(255,255,255,.75);
                            z-index:500; align-items:center; justify-content:center;">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-2"
                                    style="width:2rem; height:2rem;"></div>
                                <div style="font-size:.8rem; color:#5a6a85; font-weight:600;">
                                    Cargando zonas...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-4"
                    style="background:#f8fafc; border-top:1px solid #e3e8ef;">
                    <small class="text-muted mr-auto" style="font-size:.73rem;">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="zone-footer-count">0 zonas encontradas en esta ubicación</span>
                    </small>
                    <button type="button" class="btn btn-sm font-weight-bold px-4" data-dismiss="modal"
                        style="background:#071D38; color:#fff; border-radius:6px; font-size:.78rem;">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="p-2"></div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="{{ asset('custom-crud.css') }}">
    <style>
        /* ── Fix: evita que Leaflet escape del modal ── */
        #zone-explorer-map { position: relative; z-index: 0; }
        #ZoneMapModal .leaflet-pane,
        #ZoneMapModal .leaflet-top,
        #ZoneMapModal .leaflet-bottom,
        #ZoneMapModal .leaflet-control { z-index: auto !important; }
        #ZoneMapModal .leaflet-popup   { z-index: 700 !important; }
    </style>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
$(document).ready(function () {

    // ════════════════════════════════════════
    // DATATABLE
    // ════════════════════════════════════════
    $('#tblZones').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.zone.index') }}",
        columns: [
            { data: 'name',            className: 'align-middle font-weight-bold text-dark-blue' },
            { data: 'district_name',   name: 'district.name',                          className: 'align-middle' },
            { data: 'province_name',   name: 'district.province.name',                 className: 'align-middle' },
            { data: 'department_name', name: 'district.province.department.name',      className: 'align-middle' },
            { data: 'description',     className: 'align-middle text-muted',
              defaultContent: '<i class="text-muted opacity-60">Sin descripción</i>' },
            { data: 'coords_count',    orderable: false, searchable: false, className: 'text-center align-middle' },
            { data: 'status_badge',    orderable: false, searchable: false, className: 'text-center align-middle' },
            { data: 'formatted_date',  name: 'created_at',                             className: 'text-center align-middle' },
            { data: 'actions',         orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json' },
    });

    // ════════════════════════════════════════
    // CRUD — NUEVA ZONA
    // ════════════════════════════════════════
    $('#btn-nueva-zona').on('click', function () {
        $.ajax({
            url: "{{ route('admin.zone.create') }}",
            type: 'GET',
            success: function (response) {
                $('#ZoneModal #ZoneModalTitle').html('<i class="fas fa-plus-circle mr-1"></i> Nueva Zona');
                $('#ZoneModal .modal-body').html(response);
                $('#ZoneModal').modal('show');

                $('#ZoneModal form').on('submit', function (e) {
                    e.preventDefault();
                    var form = $(this);
                    $.ajax({
                        url:  form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function (res) {
                            $('#ZoneModal').modal('hide');
                            refreshTable();
                            Swal.fire('¡Registro Exitoso!', res.message, 'success');
                        },
                        error: function (xhr) {
                            var res = xhr.responseJSON;
                            var msg = 'Ocurrió un inconveniente al guardar la zona.';
                            if (xhr.status === 422 && res && res.message) msg = res.message;
                            Swal.fire({ title: 'Datos Inválidos', text: msg, icon: 'error' });
                        }
                    });
                });
            }
        });
    });

}); // fin document.ready

// ════════════════════════════════════════
// CRUD — EDITAR ZONA
// ════════════════════════════════════════
$(document).on('click', '.btn-editar', function () {
    var id = $(this).attr('id');
    $.ajax({
        url:  "{{ route('admin.zone.edit', 'id') }}".replace('id', id),
        type: 'GET',
        success: function (response) {
            $('#ZoneModal #ZoneModalTitle').html('<i class="fas fa-edit mr-1"></i> Editar Registro de Zona');
            $('#ZoneModal .modal-body').html(response);
            $('#ZoneModal').modal('show');

            $('#ZoneModal form').on('submit', function (e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    url:  form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function (res) {
                        $('#ZoneModal').modal('hide');
                        refreshTable();
                        Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                    },
                    error: function (xhr) {
                        var res = xhr.responseJSON;
                        var msg = 'Ocurrió un inconveniente al actualizar la zona.';
                        if (xhr.status === 422 && res && res.message) msg = res.message;
                        Swal.fire({ title: 'Datos Inválidos', text: msg, icon: 'error' });
                    }
                });
            });
        }
    });
});

// ════════════════════════════════════════
// CRUD — ELIMINAR ZONA
// ════════════════════════════════════════
$(document).on('submit', '.frmEliminar', function (e) {
    e.preventDefault();
    var form = $(this);

    Swal.fire({
        title: '¿Está seguro de Eliminar?',
        text:  '¡Esta acción removerá la zona y sus configuraciones de forma permanente!',
        icon:  'warning',
        showCancelButton:   true,
        confirmButtonColor: '#071D38',
        cancelButtonColor:  '#a13825',
        confirmButtonText:  'Sí, ¡eliminar!',
        cancelButtonText:   'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.ajax({
                url:  form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function (res) {
                    refreshTable();
                    Swal.fire('¡Proceso Exitoso!', res.message, 'success');
                },
                error: function () {
                    Swal.fire('Error', 'No se pudo eliminar el registro en el servidor.', 'error');
                }
            });
        }
    });
});

// ════════════════════════════════════════
// MAPA DETALLE POR FILA (btn-mapa)
// Usa ZoneDetailMapModal — independiente del explorador global
// ════════════════════════════════════════
$(document).on('click', '.btn-mapa', function () {
    var id = $(this).attr('id');

    $.ajax({
        url:  "{{ route('admin.zones.mapDetails', 'id') }}".replace('id', id),
        type: 'GET',
        success: function (response) {
            $('#ZoneDetailMapModal .modal-body').empty().html(response);

            $('#ZoneDetailMapModal').one('shown.bs.modal', function () {
                if (typeof window.inicializarMapaDetalle === 'function') {
                    window.inicializarMapaDetalle();
                }
            });

            $('#ZoneDetailMapModal').modal('show');
        },
        error: function () {
            Swal.fire({ title: 'Error', text: 'No se pudo cargar la información del mapa.', icon: 'error' });
        }
    });
});

// ════════════════════════════════════════
// HELPER
// ════════════════════════════════════════
function refreshTable() {
    $('#tblZones').DataTable().ajax.reload(null, false);
}

// ════════════════════════════════════════
// EXPLORADOR GLOBAL DE ZONAS
// ════════════════════════════════════════
(function () {
    'use strict';

    var explorerMap        = null;
    var zoneLayerGroup     = null;
    var districtLayerGroup = null;

    var ZONE_COLORS = [
        '#e74c3c', '#3498db', '#2ecc71', '#f39c12',
        '#9b59b6', '#1abc9c', '#e67e22', '#34495e',
        '#e91e63', '#00bcd4'
    ];

    // Abrir explorador — si ZoneModal está abierto, esperar a que cierre primero
    $('#btn-ver-mapa').on('click', function () {
        if ($('#ZoneModal').hasClass('show')) {
            $('#ZoneModal').one('hidden.bs.modal', function () {
                $('#ZoneMapModal').modal('show');
            });
            $('#ZoneModal').modal('hide');
        } else {
            $('#ZoneMapModal').modal('show');
        }
    });

    // Al terminar la animación de apertura: reiniciar mapa limpio
    $('#ZoneMapModal').on('shown.bs.modal', function () {
        // Destruir instancia previa si existe
        if (explorerMap !== null) {
            explorerMap.remove();
            explorerMap        = null;
            zoneLayerGroup     = null;
            districtLayerGroup = null;
        }
        $('#zone-explorer-map').empty();
        initExplorerMap();

        // Doble invalidateSize: el primero estabiliza el contenedor,
        // el segundo corrige el tamaño real tras cualquier transición
        // pendiente de un modal previo (ej: cerrar ZoneModal justo antes)
        explorerMap.invalidateSize();
        setTimeout(function () {
            if (explorerMap) {
                explorerMap.invalidateSize();
                loadZones();
            }
        }, 120);
    });

    // Al cerrar: destruir mapa y resetear UI
    $('#ZoneMapModal').on('hidden.bs.modal', function () {
        if (explorerMap !== null) {
            explorerMap.remove();
            explorerMap        = null;
            zoneLayerGroup     = null;
            districtLayerGroup = null;
        }
        $('#map-filter-department').val('');
        $('#map-filter-province').html('<option value="">— Seleccione departamento —</option>').prop('disabled', true);
        $('#map-filter-district').html('<option value="">— Seleccione provincia —</option>').prop('disabled', true);
        updateLocationLabel(null);
        updateStats(0, 0, 0);
        updateFooter(0);
    });

    // ── Inicializar Leaflet ──
    function initExplorerMap() {
        explorerMap = L.map('zone-explorer-map', {
            center: [-6.7714, -79.8409],
            zoom: 13,
            zoomControl: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(explorerMap);

        zoneLayerGroup     = L.layerGroup().addTo(explorerMap);
        districtLayerGroup = L.layerGroup().addTo(explorerMap);
    }

    // ── Cargar zonas ──
    function loadZones(districtId) {
        showLoader(true);
        var url = "{{ route('admin.zone.mapdata') }}";
        if (districtId) url += '?district_id=' + districtId;

        $.ajax({
            url:  url,
            type: 'GET',
            success:  function (data) { renderZones(data); },
            error:    function ()     { Swal.fire('Error', 'No se pudieron cargar las zonas del mapa.', 'error'); },
            complete: function ()     { showLoader(false); }
        });
    }

    // ── Renderizar polígonos ──
    function renderZones(zones) {
        zoneLayerGroup.clearLayers();

        var totalPoints = 0, activeCount = 0, bounds = [];

        if (!zones || zones.length === 0) {
            updateStats(0, 0, 0);
            updateFooter(0);
            return;
        }

        zones.forEach(function (zone, idx) {
            if (!zone.coords || zone.coords.length < 3) return;

            var color    = ZONE_COLORS[idx % ZONE_COLORS.length];
            var isActive = (zone.status || '').toUpperCase() === 'ACTIVO';

            var latlngs = zone.coords.map(function (c) {
                return [parseFloat(c[0]), parseFloat(c[1])];
            });

            var polygon = L.polygon(latlngs, {
                color:       isActive ? color : '#95a5a6',
                weight:      2.5,
                opacity:     0.9,
                fillColor:   isActive ? color : '#bdc3c7',
                fillOpacity: isActive ? 0.3 : 0.15
            });

            var popupHtml =
                '<div style="min-width:160px;">' +
                '<div style="font-weight:700;color:#071D38;font-size:.9rem;margin-bottom:4px;">' + zone.name + '</div>' +
                '<div style="display:flex;gap:6px;align-items:center;margin-bottom:4px;">' +
                (isActive
                    ? '<span style="background:#d4edda;color:#155724;padding:1px 8px;border-radius:50px;font-size:.7rem;font-weight:600;">Activo</span>'
                    : '<span style="background:#f8d7da;color:#721c24;padding:1px 8px;border-radius:50px;font-size:.7rem;font-weight:600;">Inactivo</span>') +
                (zone.district_name ? '<span style="font-size:.72rem;color:#666;">· ' + zone.district_name + '</span>' : '') +
                '</div>' +
                (zone.description ? '<div style="font-size:.75rem;color:#555;margin-top:2px;">' + zone.description + '</div>' : '') +
                '<div style="font-size:.72rem;color:#888;margin-top:4px;">' + latlngs.length + ' puntos</div>' +
                '</div>';

            polygon.bindPopup(popupHtml, { maxWidth: 220 });
            polygon.on('mouseover', function () { this.setStyle({ weight: 4, fillOpacity: isActive ? 0.5 : 0.3 }); });
            polygon.on('mouseout',  function () { this.setStyle({ weight: 2.5, fillOpacity: isActive ? 0.3 : 0.15 }); });

            zoneLayerGroup.addLayer(polygon);
            latlngs.forEach(function (ll) { bounds.push(ll); });
            totalPoints += latlngs.length;
            if (isActive) activeCount++;
        });

        if (bounds.length > 0) {
            explorerMap.fitBounds(L.latLngBounds(bounds), { padding: [30, 30] });
        }

        updateStats(zones.length, activeCount, totalPoints);
        updateFooter(zones.length);
    }

    // ── Filtros encadenados ──

    $('#map-filter-department').on('change', function () {
        var deptId = $(this).val();
        var $prov  = $('#map-filter-province');
        var $dist  = $('#map-filter-district');

        $dist.html('<option value="">— Seleccione provincia —</option>').prop('disabled', true);

        if (!deptId) {
            $prov.html('<option value="">— Seleccione departamento —</option>').prop('disabled', true);
            return;
        }

        $prov.html('<option value="">— Cargando... —</option>').prop('disabled', true);

        $.ajax({
            url:  "{{ route('admin.locations.provinces', 'DEPT_ID') }}".replace('DEPT_ID', deptId),
            type: 'GET',
            success: function (data) {
                var opts = '<option value="">— Todas —</option>';
                $.each(data, function (i, p) { opts += '<option value="' + p.id + '">' + p.name + '</option>'; });
                $prov.html(opts).prop('disabled', false);
            },
            error: function () {
                $prov.html('<option value="">— Error al cargar —</option>').prop('disabled', true);
            }
        });
    });

    $('#map-filter-province').on('change', function () {
        var provId = $(this).val();
        var $dist  = $('#map-filter-district');

        if (!provId) {
            $dist.html('<option value="">— Seleccione provincia —</option>').prop('disabled', true);
            return;
        }

        $dist.html('<option value="">— Cargando... —</option>').prop('disabled', true);

        $.ajax({
            url:  "{{ route('admin.locations.districts', 'PROV_ID') }}".replace('PROV_ID', provId),
            type: 'GET',
            success: function (data) {
                var opts = '<option value="">— Todos —</option>';
                $.each(data, function (i, d) { opts += '<option value="' + d.id + '">' + d.name + '</option>'; });
                $dist.html(opts).prop('disabled', false);
            },
            error: function () {
                $dist.html('<option value="">— Error al cargar —</option>').prop('disabled', true);
            }
        });
    });

    $('#btn-apply-zone-filter').on('click', function () {
        var districtId   = $('#map-filter-district').val();
        var districtName = $('#map-filter-district option:selected').text().trim();
        var provName     = $('#map-filter-province option:selected').text().trim();
        var deptName     = $('#map-filter-department option:selected').text().trim();

        districtLayerGroup.clearLayers();

        if (districtId) {
            updateLocationLabel(districtName + ', ' + provName + ', ' + deptName);
        } else if ($('#map-filter-province').val()) {
            updateLocationLabel(provName + ', ' + deptName);
        } else if ($('#map-filter-department').val()) {
            updateLocationLabel(deptName);
        } else {
            updateLocationLabel(null);
        }

        loadZones(districtId || null);
    });

    $('#btn-reset-zone-filter').on('click', function () {
        $('#map-filter-department').val('');
        $('#map-filter-province').html('<option value="">— Seleccione departamento —</option>').prop('disabled', true);
        $('#map-filter-district').html('<option value="">— Seleccione provincia —</option>').prop('disabled', true);
        districtLayerGroup.clearLayers();
        updateLocationLabel(null);
        loadZones();
    });

    // ── Helpers ──
    function showLoader(show) {
        $('#zone-map-loader').css('display', show ? 'flex' : 'none');
    }
    function updateStats(total, active, points) {
        $('#stat-total').text(total);
        $('#stat-active').text(active);
        $('#stat-points').text(points);
    }
    function updateFooter(count) {
        $('#zone-footer-count').text(
            count + ' zona' + (count !== 1 ? 's' : '') + ' encontrada' + (count !== 1 ? 's' : '') + ' en esta ubicación'
        );
    }
    function updateLocationLabel(label) {
        $('#zone-location-text').text(label || 'Mostrando todas las zonas registradas');
    }

})();
</script>
@endsection