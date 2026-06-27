<form id="frmMassiveAssignment" action="{{ route('admin.assignment.massiveStore') }}" method="POST">
    @csrf

    {{-- Sección Rango de Fechas --}}
    <div class="row align-items-end bg-white p-3 rounded shadow-sm mb-4">
        <div class="col-md-3 form-group">
            <label class="font-weight-bold text-sm text-secondary">Fecha de inicio: *</label>
            <input type="date" name="start_date" id="mass_start_date" class="form-control" required>
        </div>
        <div class="col-md-3 form-group">
            <label class="font-weight-bold text-sm text-secondary">Fecha de fin: *</label>
            <input type="date" name="end_date" id="mass_end_date" class="form-control" required>
        </div>
        <div class="col-md-3 form-group">
            <button type="button" id="btnValidarDisponibilidad" class="btn btn-outline-success font-weight-bold w-100 py-2">
                <i class="fas fa-check-circle mr-1"></i> Validar Disponibilidad
            </button>
        </div>
    </div>

    {{-- Filtrar por Turno Real de la Base de Datos --}}
    <div class="mb-4">
        <label class="font-weight-bold text-secondary d-block mb-2">Filtrar por Turno:</label>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-primary active btn-turno-filter" data-turno-id="todos">
                <input type="radio" name="options" checked> Todos los Turnos
            </label>
            @foreach($schedules as $schedule)
                <label class="btn btn-outline-primary btn-turno-filter" data-turno-id="{{ $schedule->id }}">
                    <input type="radio" name="options"> {{ $schedule->name }}
                </label>
            @endforeach
        </div>
    </div>

    {{-- Sección Feriados de BD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-white border-left border-info" style="border-left-width: 4px !important;">
            <h6 class="font-weight-bold text-secondary"><i class="far fa-calendar-times mr-1"></i> Días Feriados en el Rango Seleccionado:</h6>
            <div id="feriadosAlertContainer" class="p-2 border rounded bg-light text-muted text-sm mt-2">
                Seleccione un rango de fechas para ver los feriados
            </div>
            <small class="text-primary d-block mt-2 font-weight-bold">
                <i class="fas fa-info-circle mr-1"></i> Los feriados seleccionados <strong>NO</strong> serán programados, incluso si el grupo trabaja ese día.
            </small>
        </div>
    </div>

    {{-- Sección Grupos de Trabajo --}}
    <h5 class="font-weight-black text-secondary mb-3">Grupos de Trabajo</h5>
    <div class="row" id="containerGruposTarjetas">
        @foreach($groups as $index => $group)
            @php
                $dias_array = [];
                if(!empty($group->work_days)) {
                    $decoded = is_array($group->work_days) ? $group->work_days : json_decode($group->work_days, true);
                    if(is_array($decoded)) {
                        $dias_array = $decoded;
                    }
                }
                $txt_dias = count($dias_array) > 0 ? implode(', ', $dias_array) : 'No especificados';
            @endphp
            
            <div class="col-md-4 card-grupo-item mb-4" data-schedule-id="{{ $group->schedule_id }}">
                <div class="card h-100 shadow-sm border-0 position-relative card-grupo-inner" style="border-radius: 8px;">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute btn-remover-card-grupo" style="top: 10px; right: 10px; border-radius: 5px; padding: .15rem .4rem;">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="card-body p-3">
                        <input type="hidden" name="groups[{{ $index }}][group_id]" value="{{ $group->id }}">
                        
                        <h6 class="font-weight-black text-uppercase text-dark mb-3 pr-4">{{ $group->name }}</h6>
                        
                        <div class="text-sm text-secondary mb-1"><strong>Zona:</strong> {{ $group->zone->name ?? '-' }}</div>
                        <div class="text-sm text-secondary mb-1">
                            <strong>Turno:</strong> <span class="badge bg-info text-white px-2 py-0.5">{{ $group->schedule->name ?? '-' }}</span>
                        </div>
                        <div class="text-sm text-secondary mb-1"><strong>Días:</strong> {{ $txt_dias }}</div>
                        <div class="text-sm text-secondary mb-3">
                            <strong>Vehículo:</strong> <span class="badge badge-light border text-primary font-weight-bold">{{ $group->vehicle->name ?? '-' }}</span>
                        </div>

                        {{-- Input Selector: Conductor --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-xs mb-1 text-secondary">Conductor:</label>
                            <select name="groups[{{ $index }}][conductor_id]" class="form-control form-control-sm select-personal-trigger" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $group->conductor_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="feedback-disponibilidad mt-1 text-xs px-2 py-1 rounded border bg-light text-secondary d-flex align-items-center" style="gap: .25rem;">
                                <i class="fas fa-info-circle"></i> <span>Disponible o sin evaluar</span>
                            </div>
                        </div>

                        {{-- Input Selector: Ayudante 1 --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-xs mb-1 text-secondary">Ayudante 1:</label>
                            <select name="groups[{{ $index }}][ayudante1_id]" class="form-control form-control-sm select-personal-trigger" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $group->ayudante1_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="feedback-disponibilidad mt-1 text-xs px-2 py-1 rounded border bg-light text-secondary d-flex align-items-center" style="gap: .25rem;">
                                <i class="fas fa-info-circle"></i> <span>Disponible o sin evaluar</span>
                            </div>
                        </div>

                        {{-- Input Selector: Ayudante 2 --}}
                        <div class="form-group mb-1">
                            <label class="font-weight-bold text-xs mb-1 text-secondary">Ayudante 2:</label>
                            <select name="groups[{{ $index }}][ayudante2_id]" class="form-control form-control-sm select-personal-trigger">
                                <option value="">-- Seleccionar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $group->ayudante2_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="feedback-disponibilidad mt-1 text-xs px-2 py-1 rounded border bg-light text-secondary d-flex align-items-center" style="gap: .25rem;">
                                <i class="fas fa-info-circle"></i> <span>Disponible o sin evaluar</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Observaciones Generales --}}
    <div class="form-group bg-white p-3 rounded shadow-sm mb-4">
        <label class="font-weight-bold text-sm text-secondary">Observaciones Generales (Opcional):</label>
        <textarea name="observations" class="form-control" rows="2" placeholder="Escriba anotaciones para esta programación..."></textarea>
    </div>

    {{-- Sección Resultados de Validación General --}}
    <div id="seccionValidacionGeneral" class="mt-4 d-none">
        <h5 class="font-weight-black text-danger mb-3"><i class="fas fa-exclamation-triangle mr-1"></i> Resultado de Validación General</h5>
        <div id="containerInconsistenciasAcordeon"></div>
    </div>

    {{-- Botones de Acción --}}
    <div class="d-flex align-items-center justify-content-center mt-4 mb-2" style="gap: 1rem;">
        <button type="button" class="btn btn-danger font-weight-bold px-4" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
        <button type="submit" id="btnGuardarMasivo" class="btn btn-info font-weight-bold px-4 text-white" style="background-color: #4ea3e3; border-color: #4ea3e3;"><i class="fas fa-save mr-1"></i> Guardar</button>
    </div>
</form>

<script>
$(document).ready(function() {
    
    // 1. Filtrado dinámico visual por ID del Turno Real de la BD
    $('.btn-turno-filter').click(function() {
        $('.btn-turno-filter').removeClass('btn-primary text-white').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
        
        let scheduleId = $(this).data('turno-id');
        if(scheduleId === 'todos') {
            $('.card-grupo-item').fadeIn(200);
        } else {
            $('.card-grupo-item').hide();
            $(`.card-grupo-item[data-schedule-id="${scheduleId}"]`).fadeIn(200);
        }
    });

    // 2. Consulta de Feriados Reales de la BD mediante Rango de Fechas
    $('#mass_start_date, #mass_end_date').change(function() {
        let start = $('#mass_start_date').val();
        let end = $('#mass_end_date').val();
        
        // Limpiar inputs ocultos generados previamente
        $('.hidden-feriado-input').remove();

        if(start && end) {
            $.get("{{ route('admin.assignment.massiveHolidays') }}", { start_date: start, end_date: end }, function(feriados) {
                if(feriados.length === 0) {
                    $('#feriadosAlertContainer').html('<span class="text-muted"><i class="fas fa-info-circle"></i> No se encontraron feriados registrados en este rango de fechas.</span>');
                    return;
                }
                
                let htmlFeriados = '<div class="d-flex flex-wrap" style="gap:.5rem;">';
                feriados.forEach((f, i) => {
                    let fFmt = f.date.split('-').reverse().slice(0,2).join('/');
                    htmlFeriados += `
                        <div class="custom-control custom-checkbox bg-white border p-2 rounded shadow-sm">
                            <input type="checkbox" class="custom-control-input checkbox-feriado" id="feriado_${i}" value="${f.date}" checked>
                            <label class="custom-control-label text-xs font-weight-bold text-dark" style="cursor:pointer;" for="feriado_${i}">
                                <span class="text-danger">[${fFmt}]</span> ${f.description}
                            </label>
                        </div>
                    `;
                });
                htmlFeriados += '</div>';
                $('#feriadosAlertContainer').html(htmlFeriados);
                
                // Sincronizar inputs para el request inicial
                sincronizarFeriados();
            }).fail(function() {
                $('#feriadosAlertContainer').text('Error al cargar los feriados.');
            });
        }
    });

    // Sincronizar cuando cambie un checkbox de feriado
    $(document).on('change', '.checkbox-feriado', function() {
        sincronizarFeriados();
    });

    function sincronizarFeriados() {
        $('.hidden-feriado-input').remove();
        $('.checkbox-feriado:checked').each(function() {
            $('#frmMassiveAssignment').append(`<input type="hidden" class="hidden-feriado-input" name="excluded_holidays[]" value="${$(this).val()}">`);
        });
    }

    // 3. Remover Tarjeta de Grupo
    $(document).on('click', '.btn-remover-card-grupo', function() {
        let item = $(this).closest('.card-grupo-item');
        item.fadeOut(300, function() { $(this).remove(); });
    });

    // 4. Validación Real contra Base de Datos mediante AJAX
    $('#btnValidarDisponibilidad').click(function() {
        let start = $('#mass_start_date').val();
        let end = $('#mass_end_date').val();

        if(!start || !end) {
            Swal.fire('Atención', 'Por favor complete el rango de fechas para validar.', 'warning');
            return;
        }

        // Resetear estados visuales previos
        $('.select-personal-trigger').removeClass('is-invalid').css({'background-color': '', 'border-color': ''});
        $('.feedback-disponibilidad')
            .removeClass('bg-danger text-white border-0')
            .addClass('bg-light text-secondary')
            .html(`<i class="fas fa-info-circle"></i> <span>Disponible o sin evaluar</span>`);
        
        $('#seccionValidacionGeneral').addClass('d-none');
        $('#containerInconsistenciasAcordeon').empty();

        $.ajax({
            url: "{{ route('admin.assignment.massiveValidate') }}",
            method: "POST",
            data: $('#frmMassiveAssignment').serialize(),
            success: function(response) {
                if (response.isValid) {
                    Swal.fire('¡Excelente!', 'No se detectaron cruces de vehículos ni de personal en el rango.', 'success');
                    return;
                }

                // 1. Marcar selectores conflictivos específicos en las tarjetas
                if (response.individualConflicts) {
                    Object.keys(response.individualConflicts).forEach(groupId => {
                        let userConflicts = response.individualConflicts[groupId];
                        let cardBody = $(`input[value="${groupId}"]`).closest('.card-body');
                        
                        Object.keys(userConflicts).forEach(userId => {
                            let dates = userConflicts[userId].join(', ');
                            let select = cardBody.find('select').filter(function() {
                                return $(this).val() == userId;
                            });

                            select.addClass('is-invalid').css({'background-color': '#f8d7da', 'border-color': '#f5c6cb'});
                            select.parent().find('.feedback-disponibilidad')
                                .removeClass('bg-light text-secondary')
                                .addClass('bg-danger text-white border-0')
                                .html(`<i class="fas fa-times-circle"></i> Ocupado el: ${dates}`);
                        });
                    });
                }

                // 2. Renderizar el Acordeón con el desglose general de errores
                let htmlAcordeon = '';
                Object.keys(response.errorsGrouped).forEach(groupId => {
                    let g = response.errorsGrouped[groupId];
                    let collapseId = `collapse_group_${groupId}`;
                    
                    let detailsList = '';
                    g.details.forEach(detail => {
                        detailsList += `<li>${detail}</li>`;
                    });

                    htmlAcordeon += `
                        <div class="card mb-2 border-0 shadow-sm" style="border-radius:8px; overflow:hidden;">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between p-3" style="cursor:pointer;" data-toggle="collapse" data-target="#${collapseId}">
                                <div class="d-flex align-items-center" style="gap:.5rem;">
                                    <i class="fas fa-users text-secondary"></i>
                                    <strong class="text-dark">${g.name}</strong>
                                    <small class="text-muted">- ${g.zona} - ${g.turno}</small>
                                </div>
                                <div class="d-flex align-items-center" style="gap:.5rem;">
                                    <span class="badge bg-danger text-white px-2 py-1 rounded text-xs font-weight-bold">Con Conflictos</span>
                                    <i class="fas fa-chevron-down text-muted ml-2"></i>
                                </div>
                            </div>
                            <div id="${collapseId}" class="collapse show">
                                <div class="card-body p-3" style="background-color: #fff1f2; border-top: 1px solid #fbcfe8;">
                                    <div class="text-danger font-weight-black text-sm mb-1"><i class="fas fa-times-circle mr-1"></i> Conflictos detectados en el rango:</div>
                                    <ul class="text-xs text-danger pl-4 mb-2 font-weight-bold" style="list-style-type: disc;">
                                        ${detailsList}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#seccionValidacionGeneral').removeClass('d-none');
                $('#containerInconsistenciasAcordeon').html(htmlAcordeon);
                Swal.fire('Atención', 'Se detectaron inconsistencias operativas. Revise los detalles abajo.', 'error');
            },
            error: function() {
                Swal.fire('Error', 'No se pudo procesar la validación en el servidor.', 'error');
            }
        });
    });

    // 5. Envío del formulario vía AJAX
    $('#frmMassiveAssignment').submit(function(e) {
        e.preventDefault();
        
        let form = $(this);
        let url = form.attr('action');
        let data = form.serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function(response) {
                Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                    $('#modalMassiveAssignment').modal('hide'); 
                    location.reload(); 
                });
            },
            error: function(xhr) {
                let errorMsg = 'Ocurrió un error inesperado.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });
});
</script>