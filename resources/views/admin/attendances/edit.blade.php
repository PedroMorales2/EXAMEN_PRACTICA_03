{!! Form::model($attendance, ['route' => ['admin.attendance.update', $attendance->id], 'method' => 'PUT', 'id' => 'formAttendanceUpdate']) !!}
    
    @include('admin.attendances.template.form')

    <div class="modal-footer p-0 pt-3 border-top-0 d-flex justify-content-end">
        <button type="button" class="btn btn-sm text-white mr-2" data-dismiss="modal" style="background-color: #a13825; border-color: #a13825;">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-sm text-white" style="background-color: #071D38; border-color: #071D38;">
            <i class="fas fa-sync"></i> Actualizar
        </button>
    </div>

{!! Form::close() !!}

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4' });

    function loadHistoryData() {
        let u = $('#user_id').val();
        let d = $('#date').val();

        if (u) {
            $.get("{{ route('admin.attendance.type') }}", { user_id: u, date: d }, function(resData) {
                $('#hist_name').text(resData.user_name);
                $('#hist_dni').text(resData.user_dni);
                $('#hist_email').text(resData.user_email);
                $('#hist_phone').text(resData.user_phone);
                $('#hist_logs_text').text(resData.historial);
                $('#hist_suggestion_text').text(resData.sugerencia);
                $('#attendance_history_box').show();
            });
        }
    }

    // Listener para actualizar el turno en base a la hora modificada
    $('#time').on('change', function() {
        let t = $(this).val();
        if (!t) return;
        
        $.get("{{ route('admin.attendance.scheduleByTime') }}", { time: t }, function(res) {
            if(res) {
                $('#schedule_text').val(res.name);
                $('#schedule_id').val(res.id);
            } else {
                $('#schedule_text').val('Fuera de Rango / Sin Turno');
                $('#schedule_id').val('');
            }
        });
    });

    // Escuchar cambios de usuario o fecha en la edición para re-renderizar el historial
    $('#user_id, #date').on('change', loadHistoryData);
    
    // Cargar los datos iniciales al abrir la edición
    loadHistoryData();
});
</script>