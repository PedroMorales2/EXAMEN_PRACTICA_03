<form
    action="{{ route('admin.maintenance-day.update', $day->id) }}"
    method="POST"
    id="formDay"
    enctype="multipart/form-data"
>
    @csrf

    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Fecha</label>
        <input type="text" class="form-control" value="{{ $day->date->format('d/m/Y') }}" disabled>
    </div>

    {{-- Observación --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Observación</label>
        <textarea name="observation" class="form-control" rows="3"
                  placeholder="Ej. Todo conforme / No se realizó...">{{ $day->observation }}</textarea>
    </div>

    {{-- Imagen --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">Imagen</label>
        @if($day->image_path)
            <div class="mb-2">
                <a href="{{ \Storage::url($day->image_path) }}" target="_blank">
                    <img src="{{ \Storage::url($day->image_path) }}" style="height:90px;border-radius:8px;">
                </a>
            </div>
        @endif
        <input type="file" name="image" class="form-control-file" accept="image/*">
        <small class="text-muted">Formatos: JPG, PNG, WEBP. Máx. 4MB.</small>
    </div>

    {{-- Estado realizado --}}
    <div class="form-group mb-3">
        <label class="font-weight-bold text-xs text-secondary text-uppercase">¿Se realizó el mantenimiento?</label>
        <select name="completed" class="form-control" required>
            <option value="1" {{ $day->completed ? 'selected' : '' }}>Sí, realizado</option>
            <option value="0" {{ ! $day->completed ? 'selected' : '' }}>No realizado</option>
        </select>
    </div>

    {{-- Botones --}}
    <div class="d-flex justify-content-end mt-3 pt-2 border-top">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-save mr-1"></i> Actualizar
        </button>
    </div>
</form>
