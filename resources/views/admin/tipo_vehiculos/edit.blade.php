<form action="{{ route('admin.tipo-vehiculo.update', $type->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="name">Nombre del Tipo de Vehículo <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control"
               id="name"
               name="name"
               value="{{ $type->name }}"
               maxlength="100"
               required>
    </div>

    <div class="form-group">
        <label for="description">Descripción</label>
        <textarea class="form-control"
                  id="description"
                  name="description"
                  rows="3">{{ $type->description }}</textarea>
    </div>

    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</form>