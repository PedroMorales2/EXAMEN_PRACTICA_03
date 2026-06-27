<div class="row no-gutters text-dark" style="font-family: 'Poppins', sans-serif;">
    
    <div class="col-md-4 bg-light p-3 border-right style-scroll" style="max-height: 75vh; overflow-y: auto;">
        
        <div class="card border-0 text-white mb-3" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="p-2.5 rounded-circle mr-3 text-center d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: rgba(255,255,255,0.2);">
                    <i class="fas fa-map-marked-alt fa-lg"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold text-truncate" style="max-width: 220px;">{{ $zone->name }}</h5>
                    <small class="opacity-80"><i class="fas fa-map-marker-alt mr-1"></i> {{ $zone->district->name ?? 'No asignado' }}</small>
                </div>
            </div>
        </div>

        <div class="row no-gutters mx-n1">
            <div class="col-6 p-1">
                <div class="card border-0 shadow-sm p-2 text-white text-center" style="background-color: #0f172a; border-radius: 10px;">
                    <small class="text-uppercase font-weight-bold opacity-75 d-block text-truncate" style="font-size: 10px;">📍 Puntos</small>
                    <span class="h4 font-weight-black mb-0 d-block py-1">{{ $zone->zonecoords->count() }}</span>
                </div>
            </div>
            <div class="col-6 p-1">
                <div class="card border-0 shadow-sm p-2 text-white text-center" style="background-color: #10b981; border-radius: 10px;">
                    <small class="text-uppercase font-weight-bold opacity-75 d-block text-truncate" style="font-size: 10px;">🗑️ Residuos</small>
                    <span class="h4 font-weight-black mb-0 d-block py-1">
                        {{ isset($zone->average_waste) ? number_format($zone->average_waste, 2) : 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="col-6 p-1">
                <div class="card border-0 shadow-sm p-2 text-white text-center" style="background-color: #f59e0b; border-radius: 10px;">
                    <small class="text-uppercase font-weight-bold opacity-75 d-block text-truncate" style="font-size: 10px;">🏢 Dpto</small>
                    <span class="font-weight-bold mb-0 d-block py-1 text-truncate" style="font-size: 13px;">
                        {{ $zone->district->province->department->name ?? 'Lambayeque' }}
                    </span>
                </div>
            </div>
            <div class="col-6 p-1">
                <div class="card border-0 shadow-sm p-2 text-white text-center" style="background-color: #06b6d4; border-radius: 10px;">
                    <small class="text-uppercase font-weight-bold opacity-75 d-block text-truncate" style="font-size: 10px;">📐 Área</small>
                    <span class="font-weight-bold mb-0 d-block py-1 text-truncate" style="font-size: 12px;">
                        {{ isset($zone->area) ? number_format($zone->area, 2).' M²' : '0.00 KM²' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <label class="text-uppercase font-weight-bold text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                <i class="fas fa-align-left mr-1"></i> Descripción de la Zona
            </label>
            <div class="bg-white p-2.5 rounded border text-secondary" style="font-size: 13px; border-radius: 8px;">
                {{ $zone->description ?? 'Zona para recojo manual / Sin descripción registrada.' }}
            </div>
        </div>

        <div class="mt-3">
            <label class="text-uppercase font-weight-bold text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                <i class="fas fa-list-ol mr-1"></i> Coordenadas del Polígono
            </label>
            <div class="table-responsive border rounded bg-white" style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-striped mb-0 custom-table-inner" style="font-size: 12px;">
                    <thead class="bg-dark text-white" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th class="text-center py-1">#</th>
                            <th class="py-1">LATITUD</th>
                            <th class="py-1">LONGITUD</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zone->zonecoords as $index => $coord)
                            <tr>
                                <td class="text-center font-weight-bold text-muted py-1">{{ $index + 1 }}</td>
                                <td class="py-1 text-monospace font-mono">{{ number_format($coord->latitude, 6, '.', '') }}</td>
                                <td class="py-1 text-monospace font-mono">{{ number_format($coord->longitude, 6, '.', '') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-2">Sin coordenadas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8 position-relative">
        <div class="bg-dark text-white font-weight-bold px-3 py-2 d-flex align-items-center" style="font-size: 11px; background-color: #1e293b !important; letter-spacing: 0.05em;">
            <i class="fas fa-globe-americas mr-2 text-info"></i> VISUALIZACIÓN EN MAPA
        </div>
        <div id="mapaDetalleIndividual" style="height: 500px; min-height: 500px; width: 100%; background-color: #f8fafc; position: relative;"></div>
    </div>

</div>

<script>
    // 1. Mapeo de variables nativas desde Eloquent
    var currentZoneCoords = @json($zone->zonecoords);
    var currentZoneName = "{{ $zone->name }}";
    var currentDistrictName = "{{ $zone->district->name ?? 'No asignado' }}";
    var currentAverageWaste = "{{ isset($zone->average_waste) ? number_format($zone->average_waste, 2) : 'N/A' }}";

    // 2. Registro global de la función de inicio
    window.inicializarMapaDetalle = function() {
        if (!currentZoneCoords || currentZoneCoords.length === 0) {
            $('#mapaDetalleIndividual').html(
                '<div class="h-100 d-flex align-items-center justify-content-center text-muted">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i> Esta zona no cuenta con puntos geográficos para trazar.</div>'
            );
            return;
        }

        var latLngs = currentZoneCoords.map(function(c) {
            return [parseFloat(c.latitude), parseFloat(c.longitude)];
        });

        // Limpieza de instancias antiguas para evitar fugas de memoria
        if (window.leafletDetalleInstance) {
            window.leafletDetalleInstance.off();
            window.leafletDetalleInstance.remove();
            window.leafletDetalleInstance = null;
        }
        $('#mapaDetalleIndividual').html(''); 

        // Inicialización del mapa con Leaflet
        var map = L.map('mapaDetalleIndividual', {
            center: latLngs[0],
            zoom: 15,
            zoomControl: true
        });

        window.leafletDetalleInstance = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Trazado del Polígono RSU
        var zonePolygon = L.polygon(latLngs, {
            color: '#3b82f6',        
            fillColor: '#60a5fa',    
            fillOpacity: 0.4,       
            weight: 3                
        }).addTo(map);

        // Añadir vértices físicos
        latLngs.forEach(function(coord) {
            L.circleMarker(coord, {
                radius: 4,
                color: '#1e3a8a',
                fillColor: '#ffffff',
                fillOpacity: 1,
                weight: 2
            }).addTo(map);
        });

        // Popup descriptivo del polígono
        zonePolygon.bindPopup(`
            <div style="font-family: 'Poppins', sans-serif; font-size: 12px; min-width: 160px;">
                <strong class="text-primary d-block mb-0.5"><i class="fas fa-dumpster mr-1"></i> ` + currentZoneName + `</strong>
                <span class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt mr-1"></i> ` + currentDistrictName + `</span>
                <hr class="my-1">
                <small class="d-block"><b>🗑️ Prom. Residuos:</b> ` + currentAverageWaste + `</small>
                <small class="d-block"><b>📍 Vértices:</b> ` + latLngs.length + ` puntos</small>
            </div>
        `).openPopup();

        // Recalcular dimensiones y forzar encuadre de forma segura
        map.invalidateSize();
        map.fitBounds(zonePolygon.getBounds());

        // Seguro asíncrono secundario por si se genera retraso de hilos en el navegador
        setTimeout(function() {
            if (window.leafletDetalleInstance) {
                window.leafletDetalleInstance.invalidateSize();
                window.leafletDetalleInstance.fitBounds(zonePolygon.getBounds());
            }
        }, 250);
    };
</script>

<style>
    .style-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .style-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .style-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .style-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .custom-table-inner td, .custom-table-inner th {
        vertical-align: middle !important;
    }
    .font-mono {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    }
</style>