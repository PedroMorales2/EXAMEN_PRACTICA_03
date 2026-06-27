<form action="{{ route('admin.schedule.store') }}" method="POST">
    @csrf
    @include('admin.schedules.template.form')
    <div class="text-right mt-3">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
</form>