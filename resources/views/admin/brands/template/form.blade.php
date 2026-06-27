<div class="row d-flex align-items-stretch">
    <div class="col-md-7 d-flex flex-column justify-content-between">
        <div class="form-group mb-3">
            {!! Form::label('name', 'Nombre de la Marca *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
            {!! Form::text('name', null, [
                'class' => 'form-control rounded-xl',
                'placeholder' => 'Ej: Toyota, Nissan, Hyundai...',
                'required',
                'maxlength' => '100'
            ]) !!}
        </div>
        
        <div class="form-group mb-3 mb-md-0">
            {!! Form::label('description', 'Descripción de la Marca', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
            {!! Form::textarea('description', null, [
                'class' => 'form-control rounded-xl',
                'placeholder' => 'Agregue detalles o notas de la marca...',
                'rows' => '4'
            ]) !!}
        </div>
    </div>

    <div class="col-md-5 text-center d-flex flex-column justify-content-center align-items-center border-left pl-md-4">
        {!! Form::label('logo', 'Logotipo', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider d-block mb-3']) !!}
        
        <div class="mb-3 d-flex align-items-center justify-content-center bg-white border shadow-sm rounded-xl overflow-hidden p-2 text-center" style="width: 160px; height: 100px;">
            @php
                if(isset($brand) && $brand->logo) {
                    $previewUrl = asset($brand->logo);
                } else {
                    $previewUrl = asset('vendor/adminlte/dist/img/AdminLTELogo.png');
                }
            @endphp
            <img src="{{ $previewUrl }}" id="brandLogoPreview" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.2s ease;">
        </div>

        <div class="custom-file rounded-xl overflow-hidden mb-2" style="max-width: 180px; margin: 0 auto;">
            <input type="file" name="logo" class="custom-file-input" id="fileBrandLogo" accept="image/*" style="cursor: pointer;">
            <label class="custom-file-label text-xs font-weight-bold text-muted rounded-xl text-left" for="fileBrandLogo" data-browse="Buscar">Subir Logo</label>
        </div>
        <small class="text-muted text-xs d-block">Formatos: JPG, PNG, WEBP (Max 2MB).</small>
    </div>
</div>

<style>
    .rounded-xl { border-radius: 10px !important; }
    .text-xs { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.06em; }
</style>

<script>
    $(document).ready(function() {
        // Actualiza el recuadro dinámicamente cuando seleccione archivo nuevo
        $('#fileBrandLogo').on('change', function(event) {
            let inputFile = event.target.files[0];
            let labelElement = $(this).next('.custom-file-label');
            
            if (inputFile) {
                labelElement.text(inputFile.name);
                
                let fileReader = new FileReader();
                fileReader.onload = function(e) {
                    $('#brandLogoPreview').attr('src', e.target.result);
                }
                fileReader.readAsDataURL(inputFile);
            }
        });
    });
</script>