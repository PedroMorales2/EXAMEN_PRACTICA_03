{!! Form::model($type, ['route' => ['admin.usertype.update', $type->id], 'method' => 'PUT', 'id' => 'formUserTypeUpdate']) !!}
    
    @include('admin.userTypes.template.form')

    <div class="modal-footer p-0 pt-3 border-top-0 d-flex justify-content-end">
        <button type="button" class="btn btn-sm text-white" data-dismiss="modal" style="background-color: #a13825; border-color: #a13825;">
            <i class="fas fa-times"></i> Cancelar
        </button>
        
        <button type="submit" class="btn btn-sm text-white" style="background-color: #071D38; border-color: #071D38;">
            <i class="fas fa-sync"></i> Actualizar
        </button>
    </div>

{!! Form::close() !!}