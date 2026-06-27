@php $editing = isset($cambio); @endphp

<form
    action="{{ $editing ? route('admin.cambio.update', $cambio->id) : route('admin.cambio.store') }}"
    method="POST"
    id="formCambio"
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
            placeholder="Ej. Reprogramación"
            value="{{ $editing ? $cambio->name : '' }}"
            required
        >
    </div>

    {{-- Descripción --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Descripción</label>
        <textarea
            name="description"
            class="form-control"
            rows="3"
            placeholder="Descripción del motivo de cambio..."
        >{{ $editing ? $cambio->description : '' }}</textarea>
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