<div class="row d-flex align-items-stretch">
    {{-- Columna Izquierda: Datos del Formulario --}}
    <div class="col-md-7 d-flex flex-column justify-content-between">
        
        {{-- Personal --}}
        <div class="form-group mb-3">
            {!! Form::label('user_id', 'Personal / Empleado *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
            <select name="user_id" id="user_id" class="form-control rounded-xl select2" required style="width: 100%;">
                <option value="">-- Seleccione un Empleado --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ (isset($attendance) && $attendance->user_id == $u->id) ? 'selected' : '' }}>
                        {{ $u->dni }} - {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- CONTENEDOR DINÁMICO: Tarjeta de Historial e Información del Empleado --}}
        <div id="attendance_history_box" class="mb-3" style="display: none;">
            <div class="p-3 border rounded-xl shadow-sm" style="background-color: #e8f4fd; border-color: #bce0fd !important;">
                <div class="row text-secondary mb-2" style="font-size: 0.85rem;">
                    <div class="col-6">
                        <i class="fas fa-user text-primary mr-1"></i> <span class="font-weight-bold text-primary">Nombre completo:</span> <span id="hist_name">...</span>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-id-card text-primary mr-1"></i> <span class="font-weight-bold text-primary">DNI:</span> <span id="hist_dni">...</span>
                    </div>
                </div>
                <div class="row text-secondary mb-2" style="font-size: 0.85rem;">
                    <div class="col-6">
                        <i class="fas fa-envelope text-primary mr-1"></i> <span class="font-weight-bold text-primary">Email:</span> <span id="hist_email">...</span>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-phone text-primary mr-1"></i> <span class="font-weight-bold text-primary">Teléfono:</span> <span id="hist_phone">...</span>
                    </div>
                </div>
                
                {{-- Bloque de Registros del Día --}}
                <div class="border-top pt-2 mt-2" style="border-color: #bce0fd !important;">
                    <span class="text-xs font-weight-bold text-secondary uppercase tracking-wider d-block mb-1">
                        <i class="fas fa-history text-primary mr-1"></i> Registros del día:
                    </span>
                    <p id="hist_logs_text" class="text-muted m-0 mb-2" style="font-size: 0.85rem;">Cargando historial...</p>
                    
                    {{-- Alerta dinámica de sugerencia --}}
                    <div id="hist_suggestion_badge" class="alert alert-warning text-dark font-weight-bold m-0 p-2 text-xs rounded-xl d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2 text-dark"></i> <span id="hist_suggestion_text">Calculando siguiente marcación...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Fecha --}}
            <div class="col-md-6 form-group mb-3">
                {!! Form::label('date', 'Fecha de Registro *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::date('date', isset($attendance) ? $attendance->date->format('Y-m-d') : $now->format('Y-m-d'), [
                    'class' => 'form-control rounded-xl',
                    'id' => 'date',
                    'required'
                ]) !!}
            </div>

            {{-- Hora --}}
            <div class="col-md-6 form-group mb-3">
                {!! Form::label('time', 'Hora de Registro *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::time('time', isset($attendance) ? substr($attendance->time, 0, 5) : $now->setTimezone('America/Lima')->format('H:i'), [
                    'class' => 'form-control rounded-xl',
                    'id' => 'time',
                    'required'
                ]) !!}
            </div>

        </div>

        {{-- Estado --}}
        <div class="form-group mb-3">
            {!! Form::label('status', 'Estado del Personal *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
            {!! Form::select('status', ['Presente' => 'Presente', 'Ausente' => 'Ausente'], null, [
                'class' => 'form-control rounded-xl custom-select-fix',
                'id' => 'status',
                'required'
            ]) !!}
        </div>
        
        {{-- Notas --}}
        <div class="form-group mb-3 mb-md-0">
            {!! Form::label('notes', 'Notas Adicionales', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
            {!! Form::textarea('notes', null, [
                'class' => 'form-control rounded-xl',
                'placeholder' => 'Ej: Reemplazo por descanso médico, retraso por tráfico, etc...',
                'rows' => '2'
            ]) !!}
        </div>
    </div>

    {{-- Columna Derecha: Automatizaciones Visuales y Alternador --}}
    <div class="col-md-5 text-center d-flex flex-column justify-content-center align-items-center border-left pl-md-4">
        
        {{-- Bloque de Turno Detectado --}}
        <div class="w-100 mb-4">
            {!! Form::label('schedule_text', 'Turno Detectado', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider d-block mb-2']) !!}
            {!! Form::text('schedule_text', isset($attendance) && $attendance->schedule ? $attendance->schedule->name : 'Calculando...', [
                'class' => 'form-control rounded-xl bg-light text-center font-weight-bold border-0 shadow-sm text-secondary',
                'id' => 'schedule_text',
                'readonly'
            ]) !!}
            <input type="hidden" name="schedule_id" id="schedule_id" value="{{ isset($attendance) ? $attendance->schedule_id : '' }}">
        </div>

        {{-- Bloque de Tipo de Marcación (Pestañas en Editar / Badge en Crear) --}}
        <div class="w-100">
            {!! Form::label('type', 'Tipo de Marcación *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider d-block mb-2']) !!}
            
            @if(isset($attendance))
                {{-- MODO EDICIÓN --}}
                <div class="btn-group btn-group-toggle w-100 shadow-sm rounded-xl overflow-hidden mb-1" data-toggle="buttons" style="height: 50px;">
                    <label class="btn btn-outline-success font-weight-black d-flex align-items-center justify-content-center w-50 m-0 btn-type-toggle {{ $attendance->type == 'Entrada' ? 'active' : '' }}" style="font-size: 1rem; border-width: 2px;">
                        {!! Form::radio('type', 'Entrada', null, ['id' => 'option_entrada', 'autocomplete' => 'off', 'required']) !!} 
                        <i class="fas fa-sign-in-alt mr-2"></i> ENTRADA
                    </label>
                    <label class="btn btn-outline-info font-weight-black d-flex align-items-center justify-content-center w-50 m-0 btn-type-toggle {{ $attendance->type == 'Salida' ? 'active' : '' }}" style="font-size: 1rem; border-width: 2px;">
                        {!! Form::radio('type', 'Salida', null, ['id' => 'option_salida', 'autocomplete' => 'off', 'required']) !!} 
                        <i class="fas fa-sign-out-alt mr-2"></i> SALIDA
                    </label>
                </div>
                <small class="text-muted text-xs d-block mt-2">Haz clic para alternar libremente el tipo de registro.</small>
            @else
                {{-- MODO CREACIÓN --}}
                {!! Form::hidden('type', null, ['id' => 'type_hidden']) !!}
                <div id="type_badge" class="alert alert-secondary text-center rounded-xl shadow-sm font-weight-black m-0 d-flex align-items-center justify-content-center" style="height: 50px; font-size: 1.15rem; letter-spacing: 0.05em; transition: all 0.3s ease;">
                    AUTOMÁTICO
                </div>
                <small class="text-muted text-xs d-block mt-2">Determinado por el servidor según el histórico diario.</small>
            @endif
        </div>

    </div>
</div>

<style>
    .rounded-xl { border-radius: 10px !important; }
    .text-xs { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.06em; }
    .font-weight-black { font-weight: 900 !important; }
    
    /* SOLUCIÓN AL TEXTO OCULTO EN SELECTS: Restablece el padding y fuerza la altura de línea */
    .custom-select-fix {
        height: calc(2.5rem + 2px) !important;
        padding: 0.375rem 1rem !important;
        line-height: 1.5 !important;
    }
    
    .btn-group-toggle .btn input[type="radio"] { position: absolute; clip: rect(0,0,0,0); pointer-events: none; }
    .btn-outline-success.active { background-color: #28a745 !important; color: #ffffff !important; }
    .btn-outline-info.active { background-color: #17a2b8 !important; color: #ffffff !important; }
</style>