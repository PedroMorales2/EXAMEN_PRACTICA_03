@extends('adminlte::page')

@section('title', 'Dashboard RSU')

@section('content_header')
@stop

@section('content')
<div class="container-fluid pt-4 pb-5 content-dashboard animate-fade-in">
    
    <div class="card border-0 mb-4 shadow-sm position-relative overflow-hidden custom-header-card">
        <div class="card-body p-4 position-relative z-index-10">
            <div class="row align-items-center">
                <div class="col-md-9 text-center text-md-left">
                    <span class="badge badge-pill text-uppercase px-3 py-1.5 font-weight-bold tracking-wider label-badge mb-2">
                        <i class="fas fa-shield-alt mr-1"></i> Plataforma Oficial • Entorno Seguro
                    </span>
                    <h1 class="font-weight-black text-white mb-1 tracking-tight" style="font-size: 2rem;">
                        ¡Bienvenido al Panel de Control Operativo!
                    </h1>
                    <p class="lead text-white-50 font-weight-normal mb-0" style="font-size: 1.05rem;">
                        Municipalidad Distrital de José Leonardo Ortiz — Gestión de Residuos Sólidos Urbanos
                    </p>
                </div>
                <div class="col-md-3 d-none d-md-flex justify-content-end pr-3">
                    <div class="logo-wrapper p-2 bg-white shadow rounded-xl">
                        <img src="{{ asset('images/logoJLO.png') }}" alt="Logo Municipalidad JLO" class="img-fluid" style="max-height: 60px; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
        <div class="glow-sphere"></div>
    </div>

    <div class="row mb-4">
        <!-- Cardview con Color: Vehículos -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow h-100 kpi-card bg-gradient-dark-blue text-white">
                <div class="card-body d-flex align-items-center p-3.5">
                    <div class="kpi-icon-container bg-white-20 text-white">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="ml-3">
                        <small class="text-white-50 text-uppercase font-weight-bold text-xs tracking-wider d-block mb-1">Vehículos Totales</small>
                        <h3 class="font-weight-black text-white mb-0">{{ number_format($totalVehicles) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center p-3.5">
                    <div class="kpi-icon-container bg-teal-soft text-teal">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="ml-3">
                        <small class="text-muted text-uppercase font-weight-bold text-xs tracking-wider d-block mb-1">Personal Activos</small>
                        <h3 class="font-weight-black text-dark-blue mb-0">{{ number_format($totalPersonal) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow h-100 kpi-card bg-gradient-mid-blue text-white">
                <div class="card-body d-flex align-items-center p-3.5">
                    <div class="kpi-icon-container bg-white-20 text-white">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="ml-3">
                        <small class="text-white-50 text-uppercase font-weight-bold text-xs tracking-wider d-block mb-1">Rutas Programadas</small>
                        <h3 class="font-weight-black text-white mb-0">{{ number_format($totalZones) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center p-3.5">
                    <div class="kpi-icon-container bg-danger-soft text-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="ml-3">
                        <small class="text-muted text-uppercase font-weight-bold text-xs tracking-wider d-block mb-1">Alertas Sistema</small>
                        <h3 class="font-weight-black text-dark-blue mb-0">5</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 custom-card mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="icon-shape bg-primary-soft text-primary mr-3 rounded-lg">
                    <i class="fas fa-landmark"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold text-dark-blue">Identidad Local</h5>
                    <small class="text-muted">Gobierno Local y Gestión Territorial</small>
                </div>
            </div>
            <span class="badge bg-success-soft text-success font-weight-bold px-3 py-2 rounded-pill small">
                <i class="fas fa-check-circle mr-1"></i> Operaciones Activas
            </span>
        </div>
        
        <div class="card-body pt-2">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="position-relative overflow-hidden rounded-xl shadow-sm border custom-image-container">
                        <img src="{{ asset('images/palacio.jpg') }}" alt="Municipalidad JLO" class="img-fluid w-100 h-100 object-cover">
                    </div>
                </div>
                <div class="col-md-8">
                    <h4 class="text-dark-blue font-weight-bold mb-2">Control Operativo del Distrito</h4>
                    <p class="text-secondary-muted text-justify text-sm mb-3" style="line-height: 1.6;">
                        Entorno digital centralizado para la supervisión de rutas de recolección, administración de cuadrillas y monitoreo logístico. El procesamiento sistemático de datos optimiza la eficiencia del servicio en toda la jurisdicción de José Leonardo Ortiz.
                    </p>
                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <span class="custom-badge bg-green"><i class="fas fa-leaf mr-1"></i> Eco-Friendly</span>
                        <span class="custom-badge bg-blue"><i class="fas fa-sync mr-1"></i> Sostenible</span>
                        <span class="custom-badge bg-orange"><i class="fas fa-lock mr-1"></i> Centralizado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-xl-7 col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0 custom-card">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-info-soft text-info mr-3 rounded-lg">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark-blue">Ejes de Operación</h5>
                            <small class="text-muted">Alcance funcional y navegación del sistema</small>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-1">
                   <div class="row">
                        <div class="col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.user.index') }}" class="shortcut-link">
                                <div class="module-shortcut-card">
                                    <div class="shortcut-icon text-indigo bg-indigo-soft"><i class="fas fa-users-cog"></i></div>
                                    <span class="shortcut-title">Personal</span>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.vehicle.index') }}" class="shortcut-link">
                                <div class="module-shortcut-card">
                                    <div class="shortcut-icon text-primary bg-primary-soft"><i class="fas fa-truck"></i></div>
                                    <span class="shortcut-title">Vehículos</span>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.zone.index') }}" class="shortcut-link">
                                <div class="module-shortcut-card">
                                    <div class="shortcut-icon text-warning bg-warning-soft"><i class="fas fa-map-marked-alt"></i></div>
                                    <span class="shortcut-title">Rutas</span>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-4 col-6 mb-3 mb-sm-0">
                            <a href="{{ route('admin.attendance.index') }}" class="shortcut-link">
                                <div class="module-shortcut-card">
                                    <div class="shortcut-icon text-success bg-success-soft"><i class="fas fa-user-clock"></i></div>
                                    <span class="shortcut-title">Asistencia</span>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-4 col-6 mb-3 mb-sm-0">
                            <a href="{{ route('admin.contract.index') }}" class="shortcut-link">
                                <div class="module-shortcut-card">
                                    <div class="shortcut-icon text-danger bg-danger-soft"><i class="fas fa-file-signature"></i></div>
                                    <span class="shortcut-title">Contratos</span>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-4 col-6">
                            <a href="{{ route('admin.vacation.index') }}" class="shortcut-link">
                                <div class="module-shortcut-card">
                                    <div class="shortcut-icon text-purple bg-purple-soft"><i class="fas fa-umbrella-beach"></i></div>
                                    <span class="shortcut-title">Vacaciones</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-6 mb-4">
            <div class="card h-100 shadow-sm border-0 custom-card">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-orange-soft text-orange mr-3 rounded-lg">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark-blue">Monitoreo de Eventos</h5>
                            <small class="text-muted">Últimas acciones registradas en el sistema</small>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="timeline-custom">
                        <div class="timeline-item-custom">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-info">
                                <span class="timeline-time">Hace 5 min</span>
                                <p class="timeline-text font-weight-bold text-dark-blue mb-0">Ruta recolectora iniciada</p>
                                <small class="text-muted">Unidad JLO-200 asignada a Sector Principal.</small>
                            </div>
                        </div>
                        <div class="timeline-item-custom">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-info">
                                <span class="timeline-time">Hace 1 hora</span>
                                <p class="timeline-text font-weight-bold text-dark-blue mb-0">Actualización de cuadrilla</p>
                                <small class="text-muted">Personal de asistencia sincronizado con éxito.</small>
                            </div>
                        </div>
                        <div class="timeline-item-custom border-0 pb-0">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-info">
                                <span class="timeline-time">Ayer 18:20</span>
                                <p class="timeline-text font-weight-bold text-dark-blue mb-0">Alerta de mantenimiento</p>
                                <small class="text-muted">Vehículo ingresado a control preventivo técnico.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
    <style>
        :root {
            --dark-blue: #071D38;
            --mid-blue: #123F75;
            --soft-blue: #2E5E96;
            --text-dark: #1e293b;
            --blue-soft: rgba(18, 63, 117, 0.06);
            --teal-soft: rgba(32, 201, 151, 0.06);
            --warning-soft: rgba(255, 193, 7, 0.07);
            --danger-soft: rgba(220, 53, 69, 0.06);
            --purple-soft: rgba(111, 66, 193, 0.06);
            --orange-soft: rgba(253, 126, 20, 0.07);
        }

        .content-dashboard {
            background-color: #F5F7FA;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .text-dark-blue { color: var(--dark-blue); }
        .text-secondary-muted { color: #5a6e85; }
        .z-index-10 { position: relative; z-index: 10; }
        .object-cover { object-fit: cover; }
        .gap-2 { gap: 0.5rem; }
        .font-weight-black { font-weight: 900; }

        .custom-image-container {
            height: 155px;
        }

        .custom-header-card {
            background: linear-gradient(135deg, var(--dark-blue) 0%, var(--mid-blue) 60%, var(--soft-blue) 100%);
            border-radius: 14px !important;
            box-shadow: 0 8px 25px rgba(7, 29, 56, 0.15) !important;
        }
        .label-badge {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            backdrop-filter: blur(4px);
        }
        .logo-wrapper {
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: white;
        }

        .kpi-card {
            border-radius: 14px !important;
            border: 1px solid rgba(0, 0, 0, 0.02) !important;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(7, 29, 56, 0.08) !important;
        }
        .bg-gradient-dark-blue {
            background: linear-gradient(135deg, var(--dark-blue) 0%, #0e2f56 100%);
        }
        .bg-gradient-mid-blue {
            background: linear-gradient(135deg, var(--mid-blue) 0%, var(--soft-blue) 100%);
        }
        .bg-white-20 {
            background-color: rgba(255, 255, 255, 0.15);
        }
        .text-white-50 {
            color: rgba(255, 255, 255, 0.65) !important;
        }
        .kpi-icon-container {
            width: 46px;
            height: 46px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .custom-card {
            border-radius: 14px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02) !important;
            border: 1px solid rgba(0, 0, 0, 0.01) !important;
        }
        .icon-shape {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .bg-primary-soft { background-color: var(--blue-soft); color: var(--mid-blue) !important; }
        .bg-info-soft { background-color: rgba(18, 63, 117, 0.06); color: var(--soft-blue) !important; }
        .bg-success-soft { background-color: rgba(40, 167, 69, 0.06); color: #28a745 !important; }

        .custom-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.85rem;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .custom-badge.bg-green { background-color: #e6f7ed; color: #1e7e34; }
        .custom-badge.bg-blue { background-color: #e8f2ff; color: var(--mid-blue); }
        .custom-badge.bg-orange { background-color: #fff4e6; color: #b28500; }

        .module-shortcut-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.1rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            height: 100%;
            transition: all 0.2s ease;
        }
        .module-shortcut-card:hover {
            background-color: #ffffff;
            border-color: var(--soft-blue);
            box-shadow: 0 6px 15px rgba(7, 29, 56, 0.06);
            transform: translateY(-2px);
        }
        .shortcut-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .shortcut-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }

        .timeline-custom {
            padding-left: 0.5rem;
            position: relative;
            border-left: 2px solid #eef2f6;
            margin-left: 0.5rem;
        }
        .timeline-item-custom {
            position: relative;
            padding-bottom: 1.2rem;
            padding-left: 1.25rem;
        }
        .timeline-marker {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            position: absolute;
            left: -17.5px;
            top: 5px;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.03);
        }
        .timeline-time {
            float: right;
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
        }
        .timeline-text {
            font-size: 0.85rem;
        }

        .text-indigo { color: #6610f2; }
        .bg-indigo-soft { background-color: var(--purple-soft); }
        .bg-blue-soft { background-color: var(--blue-soft); }
        .text-blue { color: var(--mid-blue); }
        .bg-teal-soft { background-color: var(--teal-soft); }
        .text-teal { color: #15a178; }
        .bg-warning-soft { background-color: var(--warning-soft); }
        .text-warning-dark { color: #997300; }
        .text-purple { color: #6f42c1; }
        .bg-purple-soft { background-color: var(--purple-soft); }
        .bg-danger-soft { background-color: var(--danger-soft); }
        .text-orange { color: #fd7e14; }
        .bg-orange-soft { background-color: var(--orange-soft); }

        .glow-sphere {
            position: absolute;
            top: -60px;
            right: -60px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }
    </style>
@stop

@section('js')
    <script> 
        console.log("Dashboard RSU: Versión Homogénea JLO Cargada Correctamente."); 
    </script>
@stop