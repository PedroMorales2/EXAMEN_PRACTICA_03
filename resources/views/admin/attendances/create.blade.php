{!! Form::open(['route' => 'admin.attendance.store', 'method' => 'POST', 'id' => 'formAttendanceRegister']) !!}
    
    @include('admin.attendances.template.form')

    <div class="modal-footer p-0 pt-3 border-top-0 d-flex justify-content-end">
        <button type="button" class="btn btn-sm text-white mr-2" data-dismiss="modal" style="background-color: #a13825; border-color: #a13825;">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-sm text-white" style="background-color: #071D38; border-color: #071D38;">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>

{!! Form::close() !!}

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    function asyncCalculations() {
        let t = $('#time').val();
        let u = $('#user_id').val();
        let d = $('#date').val();

        if (!t) return;

        // 1. Detectar turno por hora
        $.get("{{ route('admin.attendance.scheduleByTime') }}", { time: t }, function(res) {
            if(res) {
                $('#schedule_text').val(res.name);
                $('#schedule_id').val(res.id);
            } else {
                $('#schedule_text').val('Fuera de Rango / Sin Turno');
                $('#schedule_id').val('');
            }

            // 2. Consulta de secuencia por día e historial
            if(u) {
                $.get("{{ route('admin.attendance.type') }}", { user_id: u, date: d }, function(resData) {
                    
                    // Renderizar datos en la tarjeta de historial
                    $('#hist_name').text(resData.user_name);
                    $('#hist_dni').text(resData.user_dni);
                    $('#hist_email').text(resData.user_email);
                    $('#hist_phone').text(resData.user_phone);
                    $('#hist_logs_text').text(resData.historial);
                    $('#hist_suggestion_text').text(resData.sugerencia);
                    
                    // Mostrar la tarjeta animada
                    $('#attendance_history_box').slideDown(250);

                    // Setear el valor en el input oculto para pasar la validación
                    $('#type_hidden').val(resData.type);

                    // Cambiar el color del badge lateral derecho grande
                    if(resData.type === 'Entrada') {
                        $('#type_badge').text('ENTRADA').removeClass('alert-info alert-secondary').addClass('alert-success');
                    } else {
                        $('#type_badge').text('SALIDA').removeClass('alert-success alert-secondary').addClass('alert-info');
                    }
                });
            } else {
                $('#attendance_history_box').slideUp(200);
                $('#type_badge').text('AUTOMÁTICO').removeClass('alert-success alert-info').addClass('alert-secondary');
                $('#type_hidden').val('');
            }
        });
    }

    // Escuchar cambios en los inputs clave para refrescar la información
    $('#time, #date, #user_id').on('change', asyncCalculations);
    asyncCalculations();
});
</script>