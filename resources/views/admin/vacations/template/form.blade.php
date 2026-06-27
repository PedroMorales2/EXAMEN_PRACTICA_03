@php
    // Calculamos dinámicamente los límites visuales conforme a las reglas de tiempo por año
    $minFechaHtml = \Carbon\Carbon::now()->startOfMonth()->toDateString(); // Primer día del mes actual
    $maxFechaHtml = \Carbon\Carbon::now()->addYear()->endOfYear()->toDateString(); // Último día del año siguiente
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">

<div class="row">
    <div class="col-md-12 form-group mb-3">
        {!! Form::label('selectUserId', 'Seleccionar Personal Apto con Contrato Activo *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        <select name="user_id" id="selectUserId" class="form-control rounded-xl" required {{ isset($vacation) ? 'disabled' : '' }}>
            <option value="">-- Seleccione un usuario --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ (isset($vacation) && $vacation->user_id == $user->id) ? 'selected' : '' }}>
                    {{ $user->dni ?? 'S/D' }} - {{ $user->name }}
                </option>
            @endforeach
        </select>
        @if(isset($vacation))
            <input type="hidden" name="user_id" id="hiddenUserId" value="{{ $vacation->user_id }}">
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group mb-3">
        {!! Form::label('litepickerInput', 'Seleccionar Periodo de Vacaciones (Rango en un Solo Cuadro) *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text bg-light rounded-left-xl"><i class="fas fa-calendar-alt"></i></span>
            </div>
            <input type="text" name="range_picker" id="litepickerInput" class="form-control rounded-right-xl" placeholder="Haz click aquí para abrir el calendario de días..." required autocomplete="off">
        </div>
    </div>
</div>

<input type="hidden" name="start_date" id="dtStart" value="{{ isset($vacation) ? $vacation->start_date : '' }}">
<input type="hidden" name="end_date" id="dtEnd" value="{{ isset($vacation) ? $vacation->end_date : '' }}">
<input type="hidden" name="vacation_id" id="vacationId" value="{{ isset($vacation) ? $vacation->id : '' }}">

<div class="form-group mb-3">
    {!! Form::label('notesInput', 'Notas Adicionales o Justificación', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
    {!! Form::textarea('notes', null, ['id' => 'notesInput', 'class' => 'form-control rounded-xl', 'placeholder' => 'Detalles de la solicitud...', 'rows' => '2']) !!}
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div id="boxDaysCounter" class="text-center font-weight-black shadow-inner rounded-xl p-3 border bg-light h-100 d-flex align-items-center justify-content-center" style="font-size: 14px; color: #071D38; letter-spacing: 0.5px;">
            <div>
                <i class="fas fa-calculator mr-2"></i> Días Seleccionados:<br>
                <span id="lblDaysCount" class="badge badge-dark px-2.5 py-1.5 mt-1" style="font-size: 13px;">0 días</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div id="boxLiveReviewer" class="text-center font-weight-bold shadow-sm rounded-xl p-3 border alert alert-info mb-0 h-100 d-flex align-items-center justify-content-center" style="font-size: 13px; letter-spacing: 0.2px; transition: all 0.3s ease;">
            <div>
                <i class="fas fa-info-circle mr-1"></i> <span id="lblLiveMessage">Esperando selección de usuario...</span>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-xl { border-radius: 10px !important; }
    .rounded-left-xl { border-top-left-radius: 10px !important; border-bottom-left-radius: 10px !important; }
    .rounded-right-xl { border-top-right-radius: 10px !important; border-bottom-right-radius: 10px !important; }
    .text-xs { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.06em; }
</style>

<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

<script>
    $(document).ready(function() {
        let picker = null;

        // Inicialización controlada de Litepicker dentro del DOM de AdminLTE
        setTimeout(function() {
            if (document.getElementById('litepickerInput')) {
                picker = new Litepicker({
                    element: document.getElementById('litepickerInput'),
                    singleMode: false,
                    numberOfMonths: 2,
                    numberOfColumns: 2,
                    minDate: "{{ $minFechaHtml }}",
                    maxDate: "{{ $maxFechaHtml }}",
                    format: 'DD/MM/YYYY',
                    lang: 'es-ES',
                    setup: (picker) => {
                        picker.on('selected', (date1, date2) => {
                            let startFormatted = date1.format('YYYY-MM-DD');
                            let endFormatted = date2.format('YYYY-MM-DD');
                            
                            $('#dtStart').val(startFormatted);
                            $('#dtEnd').val(endFormatted);
                            
                            calcularDias(startFormatted, endFormatted);
                            checkFormBusinessRules(startFormatted, endFormatted);
                        });
                    }
                });

                // Si es edición, cargamos las fechas guardadas previamente dentro del cuadro de rango
                if($('#dtStart').val() && $('#dtEnd').val()) {
                    let sDate = $('#dtStart').val();
                    let eDate = $('#dtEnd').val();
                    picker.setDateRange(new Date(sDate + "T00:00:00"), new Date(eDate + "T00:00:00"));
                    calcularDias(sDate, eDate);
                    checkFormBusinessRules(sDate, eDate);
                } else {
                    checkFormBusinessRules('', '');
                }
            }
        }, 250);

        function checkFormBusinessRules(startDate, endDate) {
            let userId = $('#selectUserId').val();
            let vacationId = $('#vacationId').val(); 

            if (!userId) {
                updateReviewerBox('alert-info', 'Esperando selección de usuario...');
                return;
            }

            $.ajax({
                url: "{{ route('admin.vacation.checkLive') }}",
                type: "GET",
                data: { user_id: userId, start_date: startDate, end_date: endDate, vacation_id: vacationId },
                success: function(response) {
                    if (response.status === 'user_selected') {
                        updateReviewerBox('alert-info', response.message);
                    } 
                    else if (response.status === 'suggest_end') {
                        if (response.suggested_end_date && picker) {
                            let today = new Date();
                            picker.setDateRange(today, new Date(response.suggested_end_date + "T00:00:00"));
                        }
                    } 
                    else if (response.status === 'error') {
                        updateReviewerBox('alert-danger', response.message);
                    } 
                    else if (response.status === 'success') {
                        updateReviewerBox('alert-success', response.message);
                    }
                }
            });
        }

        function updateReviewerBox(alertClass, message) {
            $('#boxLiveReviewer').removeClass('alert-info alert-success alert-danger alert-warning').addClass(alertClass);
            $('#lblLiveMessage').html(message);
        }

        function calcularDias(startVal, endVal) {
            if(startVal && endVal) {
                let partsStart = startVal.split('-');
                let partsEnd = endVal.split('-');
                let s = new Date(partsStart[0], partsStart[1] - 1, partsStart[2]);
                let e = new Date(partsEnd[0], partsEnd[1] - 1, partsEnd[2]);
                
                if(e >= s) {
                    let diff = Math.floor((e - s) / (1000 * 60 * 60 * 24)) + 1;
                    $('#lblDaysCount').text(diff + ' día(s)');
                    return;
                }
            }
            $('#lblDaysCount').text('0 días');
        }

        $('#selectUserId').on('change', function() {
            if(picker) { picker.clearSelection(); }
            $('#litepickerInput').val('');
            $('#dtStart').val('');
            $('#dtEnd').val('');
            $('#lblDaysCount').text('0 días');
            checkFormBusinessRules('', '');
        });
    });
</script>