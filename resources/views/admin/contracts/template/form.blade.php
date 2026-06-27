<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="form-group">
    <label for="user_id">Personal <span class="text-danger">*</span></label>
    <select name="user_id" id="user_id" class="form-control select2-users" required style="height: 45px; line-height: 45px; padding-top: 8px; padding-bottom: 8px;">
        <option value="">Seleccione un empleado</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}"
                {{ old('user_id', isset($contract) ? $contract->user_id : '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} - {{ $user->dni }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">
        <i class="fas fa-info-circle"></i> Escriba al menos 2 letras para buscar empleados
    </small>
</div>

<div class="form-group">
    <label for="type">Tipo de Contrato <span class="text-danger">*</span></label>
    <select
        name="type"
        id="type"
        class="form-control"
        style="height: 45px; line-height: 45px; padding-top: 8px; padding-bottom: 8px;"
        required
    >
        <option value="">Seleccione un tipo</option>
        <option value="Permanente" {{ old('type', isset($contract) ? $contract->type : '') == 'Permanente' ? 'selected' : '' }}>Permanente</option>
        <option value="Nombrado" {{ old('type', isset($contract) ? $contract->type : '') == 'Nombrado' ? 'selected' : '' }}>Nombrado</option>
        <option value="Temporal" {{ old('type', isset($contract) ? $contract->type : '') == 'Temporal' ? 'selected' : '' }}>Temporal</option>
    </select>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_date">Fecha de Inicio <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="start_date" class="form-control" required
                   value="{{ old('start_date', isset($contract) ? $contract->start_date?->format('Y-m-d') : '') }}">
        </div>
    </div>
    <div class="col-md-6" id="end_date_container">
        <div class="form-group">
            <label for="end_date">Fecha de Finalización</label>
            <input type="date" name="end_date" id="end_date" class="form-control"
                   value="{{ old('end_date', isset($contract) ? $contract->end_date?->format('Y-m-d') : '') }}">
            <small class="text-muted">Dejar en blanco si es contrato Permanente o Nombrado</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="salary">Salario <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">S/</span>
                </div>
                <input type="number" name="salary" id="salary" class="form-control"
                       placeholder="0.00" required step="0.01" min="0"
                       value="{{ old('salary', isset($contract) ? $contract->salary : '') }}">
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="trial_period">Período de Prueba (meses)</label>
            <input type="number" name="trial_period" id="trial_period" class="form-control"
                   placeholder="0" min="0"
                   value="{{ old('trial_period', isset($contract) ? $contract->trial_period : 0) }}">
            <small class="text-muted">Período de prueba para contrato Permanente</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label>¿Contrato Activo? <span class="text-danger">*</span></label>
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="active" name="active" value="1"
               {{ old('active', isset($contract) ? $contract->active : true) ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" id="active_label" for="active">Activo</label>
    </div>
</div>

<script>
// Mostrar/ocultar fecha fin según tipo de contrato
document.addEventListener('DOMContentLoaded', function () {
    var typeSelect   = document.getElementById('type');
    var endDateInput = document.getElementById('end_date');
    var activeLabel  = document.getElementById('active_label');
    var activeCheck  = document.getElementById('active');

    function toggleEndDate() {
        if (typeSelect.value === 'Temporal') {
            endDateInput.required = true;
            endDateInput.style.backgroundColor = '';
            endDateInput.removeAttribute('disabled');
        } else {
            endDateInput.required = false;
            endDateInput.value    = '';
            endDateInput.setAttribute('disabled', 'disabled');
            endDateInput.style.backgroundColor = '#e9ecef';
        }
    }

    function toggleActiveLabel() {
        activeLabel.textContent = activeCheck.checked ? 'Activo' : 'Inactivo';
        activeLabel.style.color = activeCheck.checked ? '#28a745' : '#dc3545';
    }

    typeSelect.addEventListener('change', toggleEndDate);
    activeCheck.addEventListener('change', toggleActiveLabel);

    toggleEndDate();
    toggleActiveLabel();
});
</script>