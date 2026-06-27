@php
    use App\Models\MaintenanceSchedule;
    $editing = isset($schedule);
@endphp

<form
    action="{{ $editing
        ? route('admin.maintenance-schedule.update', $schedule->id)
        : route('admin.maintenance-schedule.store', $maintenance->id) }}"
    method="POST"
    id="formSchedule"
>
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="row">
        {{-- Vehículo --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Vehículo *</label>
            <select name="vehicle_id" class="form-control" required>
                <option value="">— Seleccione —</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}"
                        {{ $editing && $schedule->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->name }}{{ $vehicle->plate ? ' (' . $vehicle->plate . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Responsable --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Responsable *</label>
            <select name="responsible_id" class="form-control" required>
                <option value="">— Seleccione —</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ $editing && $schedule->responsible_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        {{-- Tipo --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Tipo de mantenimiento *</label>
            <select name="type" class="form-control" required>
                <option value="">— Seleccione —</option>
                @foreach(MaintenanceSchedule::TYPES as $type)
                    <option value="{{ $type }}"
                        {{ $editing && $schedule->type == $type ? 'selected' : '' }}>
                        {{ ucfirst(strtolower($type)) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Día de la semana --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Día de la semana *</label>
            <select name="weekday" class="form-control" required>
                <option value="">— Seleccione —</option>
                @foreach(MaintenanceSchedule::WEEKDAYS as $num => $label)
                    <option value="{{ $num }}"
                        {{ $editing && $schedule->weekday == $num ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @if($editing)
                <small class="text-muted">Si cambia el día, se regenerarán los días de detalle.</small>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Hora inicio --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Hora de inicio *</label>
            <input type="time" name="start_time" id="start_time" class="form-control"
                   value="{{ $editing ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '' }}" required>
        </div>

        {{-- Hora fin --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Hora de fin *</label>
            <input type="time" name="end_time" id="end_time" class="form-control"
                   value="{{ $editing ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '' }}" required>
        </div>
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

<script>
// Validación frontend: la hora de fin debe ser mayor a la de inicio.
(function () {
    var start = document.getElementById('start_time');
    var end   = document.getElementById('end_time');

    function sync() {
        if (start.value && end.value && end.value <= start.value) {
            end.setCustomValidity('La hora de fin debe ser mayor a la hora de inicio.');
        } else {
            end.setCustomValidity('');
        }
    }
    start.addEventListener('change', sync);
    end.addEventListener('change', sync);
    sync();
})();
</script>
