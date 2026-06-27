<div class="form-group">
    <label for="name">Nombre del Turno <span class="text-danger">*</span></label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-clock"></i></span>
        </div>
        <input type="text" name="name" id="name" class="form-control"
               placeholder="Ingrese el nombre del turno" required
               value="{{ old('name', isset($schedule) ? $schedule->name : '') }}">
    </div>
    <small class="text-muted">Ejemplo: Turno Mañana, Turno Tarde, Turno Noche</small>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="time_start">Hora de Inicio <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                </div>
                <input type="time" name="time_start" id="time_start" class="form-control" required
                       value="{{ old('time_start', isset($schedule) ? $schedule->time_start : '') }}">
            </div>
            <small class="text-muted">Formato de 24 horas</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="time_end">Hora de Término <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                </div>
                <input type="time" name="time_end" id="time_end" class="form-control" required
                       value="{{ old('time_end', isset($schedule) ? $schedule->time_end : '') }}">
            </div>
            <small class="text-muted">Formato de 24 horas</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="3"
              placeholder="Ingrese una descripción del turno (opcional)">{{ old('description', isset($schedule) ? $schedule->description : '') }}</textarea>
</div>

<div class="alert alert-info py-2 mb-0">
    <i class="fas fa-info-circle mr-1"></i>
    <strong>Nota:</strong> Configure los horarios de entrada y salida para este turno.
</div>