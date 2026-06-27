<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="code">Código <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control"
                   placeholder="Ingrese el código (Ej: VEH-ZXIPO)" required
                   value="{{ old('code', isset($vehicle) ? $vehicle->code : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="type_id">Tipo de Vehículo <span class="text-danger">*</span></label>
            <select name="type_id" id="type_id" class="form-control" required>
                <option value="">Seleccione un tipo</option>
                @foreach($types as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('type_id', isset($vehicle) ? $vehicle->type_id : '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Nombre del Vehículo <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control"
                   placeholder="Ingrese el nombre (Ej: VEHICUL001)" required
                   value="{{ old('name', isset($vehicle) ? $vehicle->name : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="plate">Placa <span class="text-danger">*</span></label>
            <input type="text" name="plate" id="plate" class="form-control"
                   placeholder="Ingrese la placa (Ej: ABC-123)" required
                   value="{{ old('plate', isset($vehicle) ? $vehicle->plate : '') }}"
                   style="text-transform: uppercase;">
            <small class="text-muted">Formato: XXXXXX, XX-XXXX o XXX-XXX</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="year">Año <span class="text-danger">*</span></label>
            <input type="number" name="year" id="year" class="form-control"
                   placeholder="Ingrese el año (Ej: 2025)" required
                   min="1900" max="{{ date('Y') + 1 }}"
                   value="{{ old('year', isset($vehicle) ? $vehicle->year : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="color_id">Color <span class="text-danger">*</span></label>
            <select name="color_id" id="color_id" class="form-control" required>
                <option value="">Seleccione un color</option>
                @foreach($colors as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('color_id', isset($vehicle) ? $vehicle->color_id : '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="brand_id">Marca <span class="text-danger">*</span></label>
            <select name="brand_id" id="brand_id" 
                    class="form-control w-100" required
                    style="max-width:100%; box-sizing:border-box;">
                <option value="">Seleccione una marca</option>
                @foreach($brands as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('brand_id', isset($vehicle) ? $vehicle->brand_id : '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="model_id">Modelo <span class="text-danger">*</span></label>
            <select name="model_id" id="model_id" 
                    class="form-control w-100" required
                    style="max-width:100%; box-sizing:border-box;">
                <option value="">Seleccione un modelo</option>
                @foreach($models as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('model_id', isset($vehicle) ? $vehicle->model_id : '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="load_capacity">Capacidad de Carga (Tn) <span class="text-danger">*</span></label>
            <input type="number" name="load_capacity" id="load_capacity" class="form-control"
                   placeholder="Ingrese la capacidad de carga (Ej: 9528)" required step="0.01" min="0"
                   value="{{ old('load_capacity', isset($vehicle) ? $vehicle->load_capacity : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="fuel_capacity">Capacidad de Combustible (L) <span class="text-danger">*</span></label>
            <input type="number" name="fuel_capacity" id="fuel_capacity" class="form-control"
                   placeholder="Ingrese la capacidad de combustible (Ej: 60)" required step="0.01" min="0"
                   value="{{ old('fuel_capacity', isset($vehicle) ? $vehicle->fuel_capacity : '') }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="compaction_capacity">Capacidad de Compactación (Tn)</label>
            <input type="number" name="compaction_capacity" id="compaction_capacity" class="form-control"
                   placeholder="Ingrese la capacidad de compactación (Ej: 180)" step="0.01" min="0"
                   value="{{ old('compaction_capacity', isset($vehicle) ? $vehicle->compaction_capacity : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="occupant_capacity">Capacidad de Personas <span class="text-danger">*</span></label>
            <input type="number" name="occupant_capacity" id="occupant_capacity" class="form-control"
                   placeholder="Ingrese la capacidad de personas (Ej: 3)" required min="1"
                   value="{{ old('occupant_capacity', isset($vehicle) ? $vehicle->occupant_capacity : '') }}">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="3"
              placeholder="Ingrese la descripción">{{ old('description', isset($vehicle) ? $vehicle->description : '') }}</textarea>
</div>