<div class="row">
    <div class="col-lg-7">
        <div class="row">
            <div class="col-md-6 form-group mb-3">
                {!! Form::label('dni', 'DNI *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::text('dni', null, ['class' => 'form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2', 'placeholder' => '12345678', 'required', 'maxlength' => '8']) !!}
                <small class="text-muted text-xxs mt-1">8 dígitos únicos</small>
            </div>
            
            <div class="col-md-6 form-group mb-3">
                {!! Form::label('usertype_id', 'Tipo de Personal *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::select('usertype_id', $userTypes, null, ['class' => 'form-control custom-select-xl rounded-xl text-sm font-medium text-dark-blue px-3', 'id' => 'cmbUserType', 'placeholder' => 'Seleccione un tipo', 'required']) !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 form-group mb-3">
                {!! Form::label('name', 'Nombres y Apellidos *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::text('name', null, ['class' => 'form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2', 'placeholder' => 'Ingrese los nombres y apellidos', 'required']) !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                {!! Form::label('birthdate', 'Fecha de Nacimiento *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::date('birthdate', isset($user) && $user->birthdate ? $user->birthdate->format('Y-m-d') : null, ['class' => 'form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2', 'required']) !!}
                <small class="text-muted text-xxs mt-1">Mayor de 18 años</small>
            </div>

            <div class="col-md-6 form-group mb-3">
                {!! Form::label('email', 'Email *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::email('email', null, ['class' => 'form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2', 'placeholder' => 'personal@ejemplo.com', 'required']) !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3" id="wrapper-license" style="display: none;">
                {!! Form::label('license', 'Licencia de Conducir *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::text('license', null, ['class' => 'form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2', 'id' => 'txtLicense', 'placeholder' => 'Nro. de Licencia']) !!}
            </div>

            <div class="col-md-6 form-group mb-3">
                {!! Form::label('status', 'Estado *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::select('status', ['1' => 'Activo', '0' => 'Inactivo'], null, ['class' => 'form-control custom-select-xl rounded-xl text-sm font-medium text-dark-blue px-3', 'required']) !!}
            </div>

            <div class="col-md-6 form-group mb-3">
                {!! Form::label('password', 'Contraseña ' . (isset($user) ? '(Opcional)' : '*'), ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                <input type="password" name="password" class="form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2" placeholder="{{ isset($user) ? 'Solo si desea cambiarla' : 'Mínimo 6 caracteres' }}" {{ isset($user) ? '' : 'required' }} autocomplete="new-password" value="">
                <small class="text-muted text-xxs mt-1">{{ isset($user) ? 'Si se deja vacío, se conservará la contraseña anterior' : 'Mínimo 6 caracteres' }}</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 form-group mb-3">
                {!! Form::label('address', 'Dirección *', ['class' => 'font-weight-bold text-xs uppercase text-secondary tracking-wider']) !!}
                {!! Form::text('address', null, ['class' => 'form-control rounded-xl text-sm font-medium text-dark-blue px-3 py-2', 'placeholder' => 'Av. Principal 123, Distrito, Ciudad', 'required']) !!}
                <small class="text-muted text-xxs mt-1">Dirección completa</small>
            </div>
        </div>
    </div>

    <div class="col-lg-5 text-center d-flex flex-column align-items-center justify-content-center border-left border-light-gray pl-4">
        <label class="font-weight-bold text-xs uppercase text-secondary tracking-wider mb-3">Foto de Perfil</label>
        
        <div class="preview-box-brand d-flex align-items-center justify-content-center border shadow-sm position-relative overflow-hidden bg-white mb-3" style="width: 220px; height: 160px; border-radius: 15px; border: 1px solid #e2e8f0 !important;">
            @if(isset($user) && $user->profile_photo_path)
                <img id="img-preview" src="{{ asset('storage/' . $user->profile_photo_path) }}" class="w-100 h-100" style="object-fit: cover;">
            @else
                <img id="img-preview" src="" class="w-100 h-100 d-none" style="object-fit: cover;">
                <div id="placeholder-brand-icon" class="text-muted">
                    <i class="fas fa-user-circle fa-4x opacity-20"></i>
                </div>
            @endif
        </div>

        <div class="input-group dropzone-trigger-wrapper mb-2 shadow-sm" style="max-width: 220px; border-radius: 10px; overflow: hidden;">
            <button type="button" class="btn btn-white text-sm font-weight-bold text-secondary flex-grow-1 border px-3 py-2 bg-white id-trigger-upload" style="border-color: #cbd5e1 !important; font-size: 0.85rem;">
                Subir Foto
            </button>
            <div class="input-group-append">
                <button type="button" class="btn btn-light border px-3 py-2 id-trigger-upload" style="border-color: #cbd5e1 !important; background-color: #f1f5f9; color: #64748b;">
                    <i class="fas fa-folder-open"></i>
                </button>
            </div>
        </div>
        
        <span class="text-muted text-xxs font-medium mt-1">Formatos: JPG, PNG, WEBP (Max 2MB).</span>
        <input type="file" name="photo" id="filePhotoInput" class="d-none" accept="image/*">
    </div>
</div>

<style>
    .rounded-xl { border-radius: 10px !important; }
    .text-xs { font-size: 0.75rem; }
    .text-xxs { font-size: 0.68rem; display: block; color: #64748b; }
    .text-dark-blue { color: #071D38 !important; }
    .tracking-wider { letter-spacing: 0.06em; }
    .border-light-gray { border-left: 1px solid #e2e8f0 !important; }
    
    .custom-select-xl {
        height: calc(2.25rem + 6px) !important;
        padding-top: 0.375rem !important;
        padding-bottom: 0.375rem !important;
        line-height: 1.5 !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e") no-repeat right 0.75rem center/8px 10px;
        background-color: #fff;
    }

    .form-control:focus {
        border-color: #071D38;
        box-shadow: 0 0 0 3px rgba(7, 29, 56, 0.15);
    }

    .id-trigger-upload:hover {
        background-color: #f8fafc !important;
        border-color: #94a3b8 !important;
    }
</style>

<script>
    $(document).ready(function() {
        function checkLicenseRequirement() {
            var selectedText = $("#cmbUserType option:selected").text().toLowerCase();
            if(selectedText.includes("conductor") || selectedText.includes("chofer")) {
                $("#wrapper-license").fadeIn(250);
                $("#txtLicense").attr("required", "required");
            } else {
                $("#wrapper-license").fadeOut(200);
                $("#txtLicense").removeAttr("required").val("");
            }
        }

        $("#cmbUserType").on("change", function() {
            checkLicenseRequirement();
        });

        checkLicenseRequirement();

        // Cualquiera de los dos botones nuevos gatilla el explorador de archivos
        $(".id-trigger-upload").on("click", function() {
            $("#filePhotoInput").click();
        });

        $("#filePhotoInput").on("change", function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#placeholder-brand-icon").addClass("d-none");
                    $("#img-preview").attr("src", e.target.result).removeClass("d-none");
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>