<div class="form-group mb-3">
    {!! Form::label('name', 'Nombre del Tipo de Personal *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
    {!! Form::text('name', null, [
        'class' => 'form-control rounded-xl',
        'placeholder' => 'Nombre del tipo de empleado',
        'required',
        'maxlength' => '100'
    ]) !!}
</div>

<div class="form-group mb-2">
    {!! Form::label('description', 'Descripción', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control rounded-xl',
        'placeholder' => 'Agregue una descripción (opcional)',
        'rows' => '3'
    ]) !!}
</div>

<style>
    .rounded-xl { 
        border-radius: 10px !important; 
    }
    .text-xs { 
        font-size: 0.75rem; 
    }
    .tracking-wider { 
        letter-spacing: 0.06em; 
    }
    
    /* Pequeño ajuste para que el input combine con el diseño limpio del modal */
    .form-control:focus {
        border-color: #071D38;
        box-shadow: 0 0 0 3px rgba(7, 29, 56, 0.15);
    }
</style>

<script>
    $(document).ready(function() {
        // Validación en tiempo de ejecución o conversiones cosméticas si las requieres en el futuro
    });
</script>