<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Muestra el listado de personal con DataTables (Ajax)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('userType')->select('users.*');

            return DataTables::of($users)
                ->addColumn('photo', function ($user) {
                    $url = $user->profile_photo_path 
                        ? asset('storage/' . $user->profile_photo_path) 
                        : asset('vendor/adminlte/dist/img/avatar5.png');
                    return '<img src="' . $url . '" class="img-circle shadow-sm" width="35" height="35" style="object-fit: cover;">';
                })
                ->addColumn('type_name', function ($user) {
                    return $user->userType ? $user->userType->name : '<span class="text-muted">Sin tipo</span>';
                })
                ->addColumn('status', function ($user) {
                    // Renderizado del badge dinámico según el estado real de la BD
                    if ($user->status == 1) {
                        return '<span class="badge badge-success px-2 py-1 rounded-pill"><i class="fas fa-check-circle mr-1"></i> Activo</span>';
                    }
                    return '<span class="badge badge-danger px-2 py-1 rounded-pill"><i class="fas fa-times-circle mr-1"></i> Inactivo</span>';
                })
                ->addColumn('created_at_formatted', function ($user) {
                    return $user->created_at->format('d/m/Y g:i A');
                })
                ->addColumn('actions', function ($user) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $user->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    $btnDelete = '<form action="' . route('admin.user.destroy', $user->id) . '" method="POST" class="frmEliminar" style="display:inline;">'
                        . method_field('DELETE') . csrf_field() .
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt"></i></button></form>';
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['photo', 'type_name', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $userTypes = UserType::pluck('name', 'id');
        return view('admin.users.create', compact('userTypes'));
    }

    /**
     * Guarda el registro
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'        => 'required|max:255',
                'dni'         => 'required|string|size:8|unique:users,dni',
                'email'       => 'required|email|max:255|unique:users,email',
                'password'    => 'required|min:6',
                'usertype_id' => 'required|exists:user_types,id',
                'status'      => 'required|in:0,1',
                'photo'       => 'nullable|image|max:2048'
            ]);

            $path = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('profile-photos', 'public');
            }

            User::create([
                'name'               => $request->name,
                'dni'                => $request->dni,
                'email'              => $request->email,
                'password'           => Hash::make($request->password),
                'birthdate'          => $request->birthdate,
                'license'            => $request->license,
                'address'            => $request->address,
                'usertype_id'        => $request->usertype_id,
                'profile_photo_path' => $path,
                'status'             => $request->status
            ]);

            return response()->json(['message' => 'Personal registrado correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al guardar: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $userTypes = UserType::pluck('name', 'id');
        return view('admin.users.edit', compact('user', 'userTypes'));
    }

    /**
     * Actualiza el registro
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name'        => 'required|max:255',
                'dni'         => 'required|string|size:8|unique:users,dni,' . $id,
                'email'       => 'required|email|max:255|unique:users,email,' . $id,
                'usertype_id' => 'required|exists:user_types,id',
                'status'      => 'required|in:0,1',
                'photo'       => 'nullable|image|max:2048'
            ]);

            $data = [
                'name'        => $request->name,
                'dni'         => $request->dni,
                'email'       => $request->email,
                'birthdate'   => $request->birthdate,
                'license'     => $request->license,
                'address'     => $request->address,
                'usertype_id' => $request->usertype_id,
                'status'      => $request->status,
            ];

            if ($request->hasFile('photo')) {
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }
                $data['profile_photo_path'] = $request->file('photo')->store('profile-photos', 'public');
            }

            // Si el campo contraseña viene lleno, se encripta y se añade al array de actualización
            if ($request->filled('password')) {
                $request->validate(['password' => 'min:6']);
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return response()->json(['message' => 'Datos de personal actualizados correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Elimina el registro
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->delete();
            return response()->json(['message' => 'Personal eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar: ' . $th->getMessage()], 500);
        }
    }
}