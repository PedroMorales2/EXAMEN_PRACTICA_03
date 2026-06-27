<div class="row">
    <div class="col-md-6 form-group mb-3">
        {!! Form::label('dateInput', 'Fecha del Feriado *', ['class' => 'font-weight-bold text-dark', 'style' => 'font-size: 14px;']) !!}
        {!! Form::date('date', null, ['id' => 'dateInput', 'class' => 'form-control', 'required']) !!}
        <small class="form-text text-muted mt-1" style="font-size: 12px;">
            Día: <strong id="lblDayName" style="color: #007bff;">Seleccione una fecha</strong>
        </small>
    </div>

    <div class="col-md-6 form-group mb-4">
        {!! Form::label('activeSelect', 'Estado *', ['class' => 'font-weight-bold text-dark', 'style' => 'font-size: 14px;']) !!}
        {!! Form::select('active', [1 => 'Activo', 0 => 'Inactivo'], null, [
            'id' => 'activeSelect', 
            'class' => 'form-control', 
            'required'
        ]) !!}
        <small class="form-text text-muted mt-1" style="font-size: 12px;">
            Los feriados inactivos no se considerarán en las validaciones de programación.
        </small>
    </div>
</div>

<div class="form-group mb-3">
    {!! Form::label('descInput', 'Descripción *', ['class' => 'font-weight-bold text-dark', 'style' => 'font-size: 14px;']) !!}
    {!! Form::text('description', null, [
        'id' => 'descInput', 
        'class' => 'form-control', 
        'placeholder' => 'Descripción del día feriado. Ejm: Navidad, Año Nuevo, etc.', 
        'required',
        'maxlength' => '255'
    ]) !!}
</div>

<div class="alert mb-0" style="background-color: #f4f9fd; border: 1px solid #85c1e9; border-radius: 6px; color: #0275d8; padding: 15px;">
    <h6 class="font-weight-bold mb-2" style="font-size: 14px;">
        <i class="fas fa-info-circle mr-1"></i> Información:
    </h6>
    <ul class="mb-0 pl-4" style="font-size: 13px; color: #0084d1;">
        <li class="mb-1">Los días feriados afectan la programación de rutas</li>
        <li class="mb-1">Puede cargar los feriados oficiales de Perú usando el botón "Cargar Feriados Perú"</li>
        <li>Los feriados inactivos no se consideran en las validaciones</li>
    </ul>
</div>

<input type="hidden" id="holidayId" value="{{ isset($holiday) ? $holiday->id : '' }}">

<script>
    $(document).ready(function() {
        // 1. Mostrar el nombre del día en texto azul
        function updateDayName() {
            let dateVal = $('#dateInput').val();
            if (dateVal) {
                let parts = dateVal.split('-');
                let dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
                let options = { weekday: 'long' };
                let dayName = dateObj.toLocaleDateString('es-ES', options);
                $('#lblDayName').text(dayName.charAt(0).toUpperCase() + dayName.slice(1));
                $('#lblDayName').css('color', '#007bff');
            } else {
                $('#lblDayName').text('Seleccione una fecha');
                $('#lblDayName').css('color', '#007bff');
            }
        }

        // 2. Validación en caliente (Solo pinta el borde de rojo si se encuentra duplicado)
        function checkHolidayLive() {
            let dateVal = $('#dateInput').val();
            let holidayId = $('#holidayId').val();

            if (!dateVal) return;

            $.ajax({
                url: "{{ route('admin.holiday.index') }}",
                type: "GET",
                data: { check_date: dateVal, holiday_id: holidayId },
                success: function(response) {
                    if (response.status === 'duplicated') {
                        // Alerta visual inmediata en el campo
                        $('#dateInput').addClass('is-invalid');
                    } else {
                        $('#dateInput').removeClass('is-invalid');
                    }
                }
            });
        }

        // Detectores de eventos
        $('#dateInput').on('change input', function() {
            updateDayName();
            checkHolidayLive();
        });

        // 🎯 SOLUCIÓN AL DOBLE SWEETALERT: 
        // Hemos removido por completo la función $(document).on('submit', ...) de este archivo.
        // Ahora, al presionar "Guardar", el formulario viajará directamente por la petición AJAX principal 
        // de la vista index. Si hay un error de validación, Laravel responderá con el estado 422 
        // y se abrirá una única e impecable ventana de SweetAlert en pantalla.

        // Ejecución inicial por si es el modal de Editar
        if($('#dateInput').val()) {
            updateDayName();
            checkHolidayLive();
        }
    });
</script>