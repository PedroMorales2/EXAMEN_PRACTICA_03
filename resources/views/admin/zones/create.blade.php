{!! Form::open(['route' => 'admin.zone.store', 'method' => 'POST', 'id' => 'formZoneRegister']) !!}
    
    @include('admin.zones.template.form')

    <div class="modal-footer p-0 pt-3 border-top-0 d-flex justify-content-end mt-3" style="gap: 8px;">
        <button type="button" class="btn btn-sm text-white font-weight-bold px-3 py-2 rounded-xl d-flex align-items-center" data-dismiss="modal" style="background-color: #a13825; border-color: #a13825;">
            <i class="fas fa-times mr-1.5"></i> Cancelar
        </button>
        
        <button type="submit" class="btn btn-sm text-white font-weight-bold px-3 py-2 rounded-xl d-flex align-items-center" style="background-color: #071D38; border-color: #071D38;">
            <i class="fas fa-save mr-1.5"></i> Guardar Registro
        </button>
    </div>

{!! Form::close() !!}