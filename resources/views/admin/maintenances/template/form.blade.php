@php $editing = isset($maintenance); @endphp

<form
    action="{{ $editing ? route('admin.maintenance.update', $maintenance->id) : route('admin.maintenance.store') }}"
    method="POST"
    id="formMaintenance"
>
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    {{-- Nombre --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Nombre *</label>
        <input
            type="text"
            name="name"
            class="form-control"
            placeholder="Ej. MANT. DICIEMBRE 2025"
            value="{{ $editing ? $maintenance->name : '' }}"
            maxlength="150"
            required
        >
    </div>

    <div class="row">
        {{-- Fecha inicio --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de inicio *</label>
            <input
                type="date"
                name="start_date"
                id="start_date"
                class="form-control"
                value="{{ $editing ? $maintenance->start_date->format('Y-m-d') : '' }}"
                required
            >
        </div>

        {{-- Fecha fin --}}
        <div class="col-md-6 form-group mb-3">
            <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha de fin *</label>
            <input
                type="date"
                name="end_date"
                id="end_date"
                class="form-control"
                value="{{ $editing ? $maintenance->end_date->format('Y-m-d') : '' }}"
                required
            >
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
// Validación frontend: la fecha de inicio no puede ser mayor a la de fin.
(function () {
    var start = document.getElementById('start_date');
    var end   = document.getElementById('end_date');

    function sync() {
        if (start.value) { end.min = start.value; }
        if (end.value && start.value && end.value < start.value) {
            end.setCustomValidity('La fecha de inicio no puede ser mayor a la fecha de fin.');
        } else {
            end.setCustomValidity('');
        }
    }
    start.addEventListener('change', sync);
    end.addEventListener('change', sync);
    sync();
})();
</script>
