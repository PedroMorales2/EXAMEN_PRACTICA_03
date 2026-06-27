{!! Form::model($user, ['route' => ['admin.user.update', $user->id], 'method' => 'PUT']) !!}
    
    @include('admin.users.template.form')

    <div class="modal-footer p-0 pt-3 border-top-0 d-flex justify-content-end">
        <button type="button" class="btn btn-sm text-white" data-dismiss="modal" style="background-color: #a13825;">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-sm text-white" style="background-color: #071D38;">
            <i class="fas fa-sync"></i> Actualizar Personal
        </button>
    </div>

{!! Form::close() !!}