<div class="form-group">
    <label for="name">Nombre del Modelo <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control"
           placeholder="Ej: Corolla, Civic, Focus" required
           value="{{ old('name', isset($model) ? $model->name : '') }}">
</div>

<div class="form-group">
    <label for="code">Código del Modelo <span class="text-danger">*</span></label>
    <input type="text" name="code" id="code" class="form-control"
           placeholder="Ej: COR-2023, CIV-2024" required
           value="{{ old('code', isset($model) ? $model->code : '') }}">
    <small class="text-muted">Código único para identificar el modelo</small>
</div>

<div class="form-group">
    <label for="brand_id">Marca <span class="text-danger">*</span></label>
    <select name="brand_id" id="brand_id" class="form-control" required>
        <option value="">Seleccione una marca</option>
        @foreach($brands as $id => $name)
            <option value="{{ $id }}"
                {{ old('brand_id', isset($model) ? $model->brand_id : '') == $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="3"
              placeholder="Agregue una descripción del modelo...">{{ old('description', isset($model) ? $model->description : '') }}</textarea>
</div>