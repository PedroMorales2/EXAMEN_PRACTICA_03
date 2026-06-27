<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\UserType;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\Schedule;
use App\Models\Contract;
use App\Models\PersonalGroup;

/**
 * Datos de prueba de esfuerzo:
 *  - Corrige el tipo "Ayudantes" -> "Ayudante" (lo que esperan los buscadores).
 *  - Crea zonas reales de José Leonardo Ortiz (Chiclayo).
 *  - Crea vehículos, conductores y ayudantes (todos con contrato activo).
 *  - Crea grupos de personal (conductor + ayudantes vía pivote).
 *
 * Ejecutar:  php artisan db:seed --class=StressTestSeeder
 */
class StressTestSeeder extends Seeder
{
    // ── Escala de la prueba ────────────────────────────────────
    const N_CONDUCTORES   = 50;
    const N_AYUDANTES     = 120;
    const N_VEHICLES      = 20;
    const N_GROUPS        = 35;
    const AYU_POR_GRUPO   = 2;     // ayudantes por grupo (deja varios libres para buscar)

    const DAYS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    public function run(): void
    {
        $faker = \Faker\Factory::create('es_PE');

        // ── 1. Tipos de usuario ────────────────────────────────
        // El buscador filtra por name IN ('Ayudante','ayudante') / ('Conductor','conductor').
        UserType::where('name', 'Ayudantes')->update(['name' => 'Ayudante']);

        $ayudanteType  = UserType::firstOrCreate(['name' => 'Ayudante']);
        $conductorType = UserType::firstOrCreate(['name' => 'Conductor']);

        $this->command->info("Tipo Ayudante  = id {$ayudanteType->id}");
        $this->command->info("Tipo Conductor = id {$conductorType->id}");

        // ── 2. Zonas reales de José Leonardo Ortiz (district_id = 2) ──
        $jloSectores = [
            'Urb. Latina', 'P.J. Urrunaga', 'Urb. San Carlos', 'P.J. San Lorenzo',
            'Villa Hermosa', 'P.J. Santa Rosa', 'Urb. El Bosque', 'P.J. Túpac Amaru',
            'P.J. Mariano Melgar', 'Sector Garcés', 'P.J. 9 de Octubre', 'Urb. Las Brisas',
            'P.J. Carlos Stein', 'Sector Chosica del Norte', 'P.J. Villa El Sol',
        ];
        $createdZones = 0;
        foreach ($jloSectores as $nombre) {
            $z = Zone::firstOrCreate(
                ['name' => $nombre],
                [
                    'area'          => $faker->randomFloat(2, 50000, 600000),
                    'description'   => 'Sector de José Leonardo Ortiz - prueba',
                    'district_id'   => 2, // Jose Leonardo Ortiz
                    'average_waste' => $faker->randomFloat(2, 50, 1500),
                    'status'        => 'ACTIVO',
                ]
            );
            if ($z->wasRecentlyCreated) $createdZones++;
        }
        $this->command->info("Zonas JLO nuevas: {$createdZones}");

        // ── 3. Vehículos ───────────────────────────────────────
        $vehicleIds = [];
        for ($i = 1; $i <= self::N_VEHICLES; $i++) {
            $v = Vehicle::create([
                'name'              => 'Camión Recolector ' . $i,
                'code'              => 'VEH-S' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'plate'             => strtoupper($faker->bothify('??#-###')),
                'year'              => (string) $faker->numberBetween(2012, 2024),
                'occupant_capacity' => $faker->numberBetween(4, 8),
                'load_capacity'     => $faker->numberBetween(8, 20),
                'description'       => 'Vehículo de prueba de esfuerzo',
                'status'            => 'activo',
            ]);
            $vehicleIds[] = $v->id;
        }
        $this->command->info('Vehículos creados: ' . count($vehicleIds));

        // ── 4. DNIs existentes para evitar colisiones ──────────
        $usedDni = User::whereNotNull('dni')->pluck('dni')->flip();
        $dniSeq  = 90000000;
        $nextDni = function () use (&$dniSeq, $usedDni) {
            do {
                $dni = (string) $dniSeq++;
            } while ($usedDni->has($dni));
            return $dni;
        };

        // ── 5. Helper para crear usuario + contrato activo ─────
        $crearPersona = function (string $rolPrefix, int $n, int $typeId, bool $conLicencia) use ($faker, $nextDni) {
            $user = User::create([
                'name'        => $faker->firstName . ' ' . $faker->lastName . ' ' . $faker->lastName,
                'dni'         => $nextDni(),
                'birthdate'   => $faker->dateTimeBetween('-55 years', '-20 years')->format('Y-m-d'),
                'license'     => $conLicencia ? strtoupper($faker->bothify('Q##??####')) : null,
                'address'     => $faker->streetAddress . ', J.L.O.',
                'email'       => $rolPrefix . $n . '.stress@rsujlo.test',
                'password'    => Hash::make('password'),
                'status'      => 1,
                'usertype_id' => $typeId,
            ]);

            Contract::create([
                'user_id'      => $user->id,
                'type'         => $faker->randomElement(['Permanente', 'Nombrado', 'Temporal']),
                'start_date'   => $faker->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
                'end_date'     => null,
                'salary'       => $faker->randomFloat(2, 1100, 2800),
                'trial_period' => 0,
                'active'       => true,
            ]);

            return $user->id;
        };

        // ── 6. Conductores y ayudantes ─────────────────────────
        $conductorIds = [];
        for ($i = 1; $i <= self::N_CONDUCTORES; $i++) {
            $conductorIds[] = $crearPersona('conductor', $i, $conductorType->id, true);
        }
        $ayudanteIds = [];
        for ($i = 1; $i <= self::N_AYUDANTES; $i++) {
            $ayudanteIds[] = $crearPersona('ayudante', $i, $ayudanteType->id, false);
        }
        $this->command->info('Conductores: ' . count($conductorIds) . ' | Ayudantes: ' . count($ayudanteIds));

        // ── 7. Grupos de personal ──────────────────────────────
        $zoneIds     = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])->pluck('id')->all();
        $scheduleIds = Schedule::pluck('id')->all();

        // Cada usuario se asigna a un solo grupo => sin conflictos de días.
        $condPool = $conductorIds;
        $ayuPool  = $ayudanteIds;
        $gruposCreados = 0;

        for ($i = 1; $i <= self::N_GROUPS; $i++) {
            if (empty($condPool) || count($ayuPool) < self::AYU_POR_GRUPO) break;

            $vehId = $faker->randomElement($vehicleIds);
            $cap   = Vehicle::find($vehId)->occupant_capacity;
            $maxAyu = min(self::AYU_POR_GRUPO, $cap - 1);
            if ($maxAyu < 1) continue;

            $conductorId = array_pop($condPool);
            $ayudantes   = [];
            for ($k = 0; $k < $maxAyu; $k++) {
                $ayudantes[] = array_pop($ayuPool);
            }

            // Subconjunto aleatorio de días (3 a 5)
            $dias = $faker->randomElements(self::DAYS, $faker->numberBetween(3, 5));

            DB::transaction(function () use ($i, $faker, $zoneIds, $scheduleIds, $vehId, $dias, $conductorId, $ayudantes) {
                $group = PersonalGroup::create([
                    'name'        => 'Grupo JLO ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'zone_id'     => $faker->randomElement($zoneIds),
                    'schedule_id' => $faker->randomElement($scheduleIds),
                    'vehicle_id'  => $vehId,
                    'days'        => array_values($dias),
                    'status'      => 'Activo',
                ]);

                $group->members()->attach($conductorId, ['role' => 'conductor', 'order' => 0]);
                foreach ($ayudantes as $idx => $aid) {
                    $group->members()->attach($aid, ['role' => 'ayudante', 'order' => $idx + 1]);
                }
            });

            $gruposCreados++;
        }

        $this->command->info("Grupos de personal creados: {$gruposCreados}");
        $this->command->info('Ayudantes libres (para buscar): ' . count($ayuPool));
        $this->command->info('Conductores libres (para buscar): ' . count($condPool));
    }
}
