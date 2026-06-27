<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('name', 'Nombre del Color *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        {!! Form::text('name', null, [
            'class' => 'form-control rounded-xl',
            'placeholder' => 'Ej: Azul, Gris Metálico...',
            'required',
            'maxlength' => '100'
        ]) !!}
    </div>

    <div class="col-md-6 form-group">
        {!! Form::label('code', 'Código Hexadecimal *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        <div class="input-group custom-input-group">
            {!! Form::text('code', isset($color) ? $color->code : '#001548', [
                'class' => 'form-control rounded-left-xl',
                'id' => 'txtColorCode',
                'placeholder' => '#001548',
                'required',
                'maxlength' => '7'
            ]) !!}
            <div class="input-group-append">
                <div class="input-group-text p-0 border-left-0 rounded-right-xl overflow-hidden" style="width: 54px;">
                    <input type="color" id="htmlColorPicker" class="w-100 h-100 custom-color-picker" style="border: none; cursor: pointer;" value="{{ isset($color) ? $color->code : '#001548' }}">
                </div>
            </div>
        </div>
        <small class="text-muted text-xs d-block mt-1">Digita el código o usa el selector de color.</small>
    </div>
</div>

<div class="form-group mt-2">
    {!! Form::label('description', 'Descripción del Color', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control rounded-xl',
        'placeholder' => 'Agregue una descripción de la tonalidad...',
        'rows' => '2'
    ]) !!}
</div>

<div class="form-group mt-3 mb-2">
    <label class="font-weight-bold text-xs uppercase text-secondary tracking-wider mb-2">Muestra previa del color:</label>
    <div id="boxColorPreview" class="text-center font-weight-black shadow-inner rounded-xl p-3 border" 
         style="background-color: {{ isset($color) ? $color->code : '#001548' }}; font-size: 15px; letter-spacing: 1.5px; transition: all 0.25s ease;">
        <i class="fas fa-eye mr-2"></i> <span id="lblColorCodePreview">{{ isset($color) ? $color->code : '#001548' }}</span>
    </div>
</div>

<style>
    .rounded-xl { border-radius: 10px !important; }
    .rounded-left-xl { border-top-left-radius: 10px !important; border-bottom-left-radius: 10px !important; }
    .rounded-right-xl { border-top-right-radius: 10px !important; border-bottom-right-radius: 10px !important; }
    .text-xs { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.06em; }
    .custom-color-picker::-webkit-color-swatch-wrapper { padding: 0; }
    .custom-color-picker::-webkit-color-swatch { border: none; }
    
    
    .custom-input-group:focus-within {
        box-shadow: 0 0 0 4px rgba(46, 94, 150, 0.1);
        border-radius: 10px;
    }
</style>

<script>
    $(document).ready(function() {
        
        function getContrastYIQ(hexcolor){
            hexcolor = hexcolor.replace("#", "");
            if(hexcolor.length === 3) {
                hexcolor = hexcolor[0]+hexcolor[0]+hexcolor[1]+hexcolor[1]+hexcolor[2]+hexcolor[2];
            }
            let r = parseInt(hexcolor.substr(0,2),16);
            let g = parseInt(hexcolor.substr(2,2),16);
            let b = parseInt(hexcolor.substr(4,2),16);
            let yiq = ((r*299)+(g*587)+(b*114))/1000;
            return (yiq >= 128) ? '#071D38' : '#FFFFFF'; // Usa el azul oscuro institucional o blanco
        }

        function updatePreview(hexColor) {
            if (/^#[0-9A-F]{6}$/i.test(hexColor)) {
                let textColor = getContrastYIQ(hexColor);
                $('#boxColorPreview').css({
                    'background-color': hexColor,
                    'color': textColor
                });
                $('#lblColorCodePreview').text(hexColor.toUpperCase());
            }
        }

        updatePreview($('#txtColorCode').val());

        // Selector de tonalidad
        $('#htmlColorPicker').on('input change', function() {
            let selectedColor = $(this).val().toUpperCase();
            $('#txtColorCode').val(selectedColor);
            updatePreview(selectedColor);
        });

        // Entrada manual
        $('#txtColorCode').on('input', function() {
            let enteredColor = $(this).val();
            
            if (enteredColor.length > 0 && !enteredColor.startsWith('#')) {
                enteredColor = '#' + enteredColor;
                $(this).val(enteredColor);
            }
            
            if (enteredColor.length > 7) {
                enteredColor = enteredColor.substring(0, 7);
                $(this).val(enteredColor);
            }

            if (/^#[0-9A-F]{6}$/i.test(enteredColor)) {
                $('#htmlColorPicker').val(enteredColor);
                updatePreview(enteredColor);
            }
        });
    });
</script>