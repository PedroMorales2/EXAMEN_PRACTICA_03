<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceDay;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MaintenanceDayController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX — días generados de un horario (DataTable)
    // ──────────────────────────────────────────────────────────
    public function index(Request $request, MaintenanceSchedule $schedule)
    {
        $schedule->load(['maintenance', 'vehicle', 'responsible']);

        if ($request->ajax()) {
            $days = MaintenanceDay::where('maintenance_schedule_id', $schedule->id)
                ->orderBy('date')
                ->select('maintenance_days.*');

            return DataTables::of($days)
                ->addColumn('date_fmt', fn($d) => $d->date->format('d/m/Y'))
                ->addColumn('observation_fmt', fn($d) => $d->observation ?: '-')
                ->addColumn('image_fmt', function ($d) {
                    if (! $d->image_path) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<a href="' . Storage::url($d->image_path) . '" target="_blank">
                                <img src="' . Storage::url($d->image_path) . '"
                                     style="height:40px;border-radius:6px;">
                            </a>';
                })
                ->addColumn('status_fmt', function ($d) {
                    return $d->completed
                        ? '<i class="fas fa-check-circle text-success fa-lg" title="Realizado"></i>'
                        : '<i class="fas fa-times-circle text-danger fa-lg" title="No realizado"></i>';
                })
                ->addColumn('actions', function ($d) {
                    return '<button class="btn btn-sm btn-warning btn-editar"
                                    data-id="' . $d->id . '" title="Editar">
                                <i class="fas fa-pen text-dark"></i>
                            </button>';
                })
                ->rawColumns(['image_fmt', 'status_fmt', 'actions'])
                ->make(true);
        }

        return view('admin.maintenance_days.index', compact('schedule'));
    }

    // ──────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────
    public function edit(MaintenanceDay $day)
    {
        return view('admin.maintenance_days.template.form', compact('day'));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE  (POST por la subida de imagen / multipart)
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, MaintenanceDay $day)
    {
        try {
            $request->validate([
                'observation' => 'nullable|string|max:1000',
                'completed'   => 'required|boolean',
                'image'       => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            ], [
                'image.image' => 'El archivo debe ser una imagen válida.',
                'image.max'   => 'La imagen no debe superar los 4MB.',
            ]);

            $day->observation = $request->input('observation');
            $day->completed   = (bool) $request->input('completed');

            if ($request->hasFile('image')) {
                if ($day->image_path) {
                    Storage::disk('public')->delete($day->image_path);
                }
                $file     = $request->file('image');
                $filename = 'day_' . $day->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $day->image_path = $file->storeAs('maintenance_days', $filename, 'public');
            }

            $day->save();

            return response()->json(['message' => 'Día actualizado correctamente.'], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
