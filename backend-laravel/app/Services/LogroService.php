<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\LogroMedalla;
use App\Models\User;
use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\RegistreActivitat;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de gestió de logros i medalles.
 * Conté la lògica de negoci per als logros.
 */
class LogroService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Comprova tots els logros d'un usuari després d'una acció (ex: completar hàbit).
     *
     * A. Crida a les funcions de comprovació per categories.
     * B. Retorna cert si tot ha anat bé.
     *
     * @param int $usuariId
     * @param Habit|null $habit (Opcional) Hàbit que s'acaba de completar
     * @return bool
     */
    public function comprovarLogros(int $usuariId, ?Habit $habit = null): bool
    {
        // ==========================================
        // CATEGORIA: TEMPS
        // ==========================================
        $this->comprovarLogrosTemps($usuariId);

        // ==========================================
        // CATEGORIA: QUANTITAT
        // ==========================================
        $this->comprovarLogrosQuantitat($usuariId);

        // ==========================================
        // CATEGORIA: RATXA
        // ==========================================
        $this->comprovarLogrosRatxa($usuariId);

        // ==========================================
        // CATEGORIA: DIFICULTAT I OCULTS
        // ==========================================
        if ($habit !== null) {
            $this->comprovarLogrosDificultat($usuariId, $habit);
            $this->comprovarLogrosOcults($usuariId, $habit);
        }

        // B. Retornar èxit
        return true;
    }





    /**
     * Comprova els logros relacionats amb el temps.
     */



    private function comprovarLogrosTemps(int $usuariId): void
    {
        // A. Comprovar 'Primer Encuentro' (Inicia sessió per primer cop / Primera acció)
        // Com que l'usuari interacciona amb el sistema, li donem directament.
        // La funció donarLogro ja comprova si el té perquè no se li doni repetit.
        $this->donarLogro($usuariId, 'Primer Encuentro');

        // B. Comprovar 'Fidelidad de Hierro' i 'Aniversario'
        // Extraiem la data més antiga d'un hàbit assignat per saber quan va començar realment la seva progressió.
        $primerHabit = DB::table('usuaris_habits')
            ->where('usuari_id', $usuariId)
            ->orderBy('data_inici', 'asc')
            ->first();

        if ($primerHabit !== null) {
            $dataIniciStr = $primerHabit->data_inici;
            
            // Farem servir Carbon per calcular fàcilment els mesos i els anys passats.
            $dataInici = \Carbon\Carbon::parse($dataIniciStr);
            $avui = \Carbon\Carbon::now();
            
            $mesosPassats = $dataInici->diffInMonths($avui);
            $anysPassats = $dataInici->diffInYears($avui);

            // Fidelidad de Hierro (6 mesos o més)
            if ($mesosPassats >= 6) {
                $this->donarLogro($usuariId, 'Fidelidad de Hierro');
            }

            // Aniversario (1 any o més)
            if ($anysPassats >= 1) {
                $this->donarLogro($usuariId, 'Aniversario');
            }
        }
    }





    /**
     * Comprova els logros relacionats amb la quantitat d'hàbits.
     */


    private function comprovarLogrosQuantitat(int $usuariId): void
    {
        // A. Obtenir dades necessàries: contar quants registres d'activitat té l'usuari finalitzats
        $totalHabitsCompleats = RegistreActivitat::whereHas('habit', function ($query) use ($usuariId) {
            $query->where('usuari_id', $usuariId);
        })->where('acabado', true)->count();

        // B. Comprovar 'Paso a Paso' (1r hàbit)
        if ($totalHabitsCompleats >= 1) {
            // C. Donar logro
            $this->donarLogro($usuariId, 'Paso a Paso');
        }

        // B. Comprovar 'Coleccionista de Éxitos' (100 hàbits)
        if ($totalHabitsCompleats >= 100) {
            // C. Donar logro
            $this->donarLogro($usuariId, 'Coleccionista de Éxitos');
        }

        // B. Comprovar 'Leyenda Activa' (1000 hàbits)
        if ($totalHabitsCompleats >= 1000) {
            // C. Donar logro
            $this->donarLogro($usuariId, 'Leyenda Activa');
        }

        // B. Comprovar 'Productividad Pura' (10 hàbits en un sol dia)
        // Busquem si avui la quantitat de registres arriba a 10.
        $habitsCompletatsAvui = RegistreActivitat::whereHas('habit', function ($query) use ($usuariId) {
            $query->where('usuari_id', $usuariId);
        })->where('acabado', true)
          ->whereDate('data', \Carbon\Carbon::today())
          ->count();

        if ($habitsCompletatsAvui >= 10) {
            $this->donarLogro($usuariId, 'Productividad Pura');
        }
    }





    /**
     * Comprova els logros relacionats amb les ratxes.
     */


    private function comprovarLogrosRatxa(int $usuariId): void
    {
        // A. Recuperem la ratxa actual de l'usuari
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        
        if ($ratxa !== null) {
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;
            $ratxaActual = (int) $ratxa->ratxa_actual;

            // B. Comprovar 'Buen Comienzo' (3 dies)
            if ($ratxaMaxima >= 3) {
                // C. Donar logro
                $this->donarLogro($usuariId, 'Buen Comienzo');
            }

            // B. Comprovar 'Constancia de Acero' (30 dies)
            if ($ratxaMaxima >= 30) {
                // C. Donar logro
                $this->donarLogro($usuariId, 'Constancia de Acero');
            }

            // B. Comprovar 'Inquebrantable' (100 dies)
            if ($ratxaMaxima >= 100) {
                // C. Donar logro
                $this->donarLogro($usuariId, 'Inquebrantable');
            }

            // B. Comprovar 'Fénix' (Recupera una ratxa perduda)
            // Lògica: la ratxa màxima històrica és major o igual a 3, però actualment
            // n'està reconstruint una de nova (actual >= 2 i menor que la màxima).
            if ($ratxaMaxima >= 3 && $ratxaActual >= 2 && $ratxaActual < $ratxaMaxima) {
                // C. Donar logro
                $this->donarLogro($usuariId, 'Fénix');
            }
        }
    }

    /**
     * Comprova els logros relacionats amb la dificultat.
     */


    private function comprovarLogrosDificultat(int $usuariId, Habit $habit): void
    {
        // A. Càlcul agrupat de les quantitats segons dificultat per aquest usuari
        // Així evitem fer múltiples consultes a base de dades.
        $comptatges = DB::table('registre_activitat')
            ->join('habits', 'registre_activitat.habit_id', '=', 'habits.id')
            ->where('habits.usuari_id', $usuariId)
            ->where('registre_activitat.acabado', true)
            ->select('habits.dificultat', DB::raw('count(*) as total'))
            ->groupBy('habits.dificultat')
            ->pluck('total', 'dificultat');

        // B. Comprovar 'Sin Esfuerzo' (50 fàcils)
        if (($comptatges['facil'] ?? 0) >= 50) {
            // C. Donar logro
            $this->donarLogro($usuariId, 'Sin Esfuerzo');
        }

        // B. Comprovar 'Reto Aceptado' (25 mitjans)
        if (($comptatges['media'] ?? 0) >= 25) {
            // C. Donar logro
            $this->donarLogro($usuariId, 'Reto Aceptado');
        }

        // B. Comprovar 'Héroe del Esfuerzo' (10 difícils)
        if (($comptatges['dificil'] ?? 0) >= 10) {
            // C. Donar logro
            $this->donarLogro($usuariId, 'Héroe del Esfuerzo');
        }

        // B. Comprovar 'Maestría Extrema' (Un hàbit difícil 7 dies seguits)
        // Només validem si aquest hàbit que acabem de rebre per paràmetre és difícil.
        if (strtolower($habit->dificultat) === 'dificil') {
            $dataInici = \Carbon\Carbon::today()->subDays(6);
            
            // Traiem els dies únics i diferents recents on s'ha completat aquest hàbit.
            // Fem us de DATE per extreure només el dia sense les hores, evitant falsos postius
            // si un usuari ha completat l'hàbit vàries vegades al llarg del dia.
            $diesDiferentsCompleats = DB::table('registre_activitat')
                ->where('habit_id', $habit->id)
                ->where('acabado', true)
                ->where('data', '>=', $dataInici)
                ->selectRaw('DATE(data) as dia_net')
                ->distinct()
                ->get()
                ->count();

            // Si hi trobem 7 dies únics, significa que ha omplert cadascun dels últims 7 dies.
            if ($diesDiferentsCompleats >= 7) {
                // C. Donar logro
                $this->donarLogro($usuariId, 'Maestría Extrema');
            }
        }
    }





    /**
     * Comprova els logros relacionats amb accions ocultes o especials.
     */


    private function comprovarLogrosOcults(int $usuariId, Habit $habit): void
    {
        $horaAct = \Carbon\Carbon::now()->hour;

        // B. Comprovar 'Ave Nocturna' (Entre 2:00 AM i les 5:00 AM)
        if ($horaAct >= 2 && $horaAct < 5) {
            $this->donarLogro($usuariId, 'Ave Nocturna');
        }

        // B. Comprovar 'Rayo Veloz' (Tots els hàbits abans de les 9:00 AM)
        if ($horaAct < 9) {
            // Busquem quants hàbits hi ha actius a l'instant
            $habitsTotalsAssignats = DB::table('usuaris_habits')->where('usuari_id', $usuariId)->where('actiu', true)->count();
            
            // Recompte dels hàbits resolts avui
            $completsAvui = DB::table('registre_activitat')
                ->join('habits', 'registre_activitat.habit_id', '=', 'habits.id')
                ->where('habits.usuari_id', $usuariId)
                ->where('registre_activitat.acabado', true)
                ->whereDate('registre_activitat.data', \Carbon\Carbon::today())
                ->count();
            
            if ($habitsTotalsAssignats > 0 && $completsAvui >= $habitsTotalsAssignats) {
                $this->donarLogro($usuariId, 'Rayo Veloz');
            }
        }

        // B. Comprovar 'Silencioso' (Després d'un mes inactiu)
        // Revisem la data de l'antepenúltim registre de l'usuari de l'historial (ja que l'últim és l'actual on es processa!).
        $penultimRegistre = DB::table('registre_activitat')
            ->join('habits', 'registre_activitat.habit_id', '=', 'habits.id')
            ->where('habits.usuari_id', $usuariId)
            ->where('registre_activitat.acabado', true)
            ->orderBy('registre_activitat.data', 'desc')
            ->skip(1) 
            ->first();

        if ($penultimRegistre !== null) {
            $dataPenultim = \Carbon\Carbon::parse($penultimRegistre->data);
            $diesAbsencia = $dataPenultim->diffInDays(\Carbon\Carbon::today());
            
            if ($diesAbsencia >= 30) {
                $this->donarLogro($usuariId, 'Silencioso');
            }
        }
    }


    //================================ RUTES / LOGICA PRIVADA ========

    /**
     * Funció auxiliar per atorgar un logro a un usuari.
     * Evita assignar el mateix logro dos cops.
     *
     * @param int $usuariId
     * @param string $nomLogro El nom exacte del logro a base de dades
     */
    public function donarLogro(int $usuariId, string $nomLogro): void
    {
        // A. Buscar el logro pel nom
        $logro = LogroMedalla::where('nom', $nomLogro)->first();

        // B. Si existeix, comprovar si l'usuari ja el té
        if ($logro !== null) {
            $jaElTe = DB::table('usuaris_logros')
                ->where('usuari_id', $usuariId)
                ->where('logro_id', $logro->id)
                ->exists();
                
            // C. Si no el té, l'inserim
            if ($jaElTe === false) {
                DB::table('usuaris_logros')->insert([
                    'usuari_id' => $usuariId,
                    'logro_id' => $logro->id,
                    'data_obtencio' => now()
                ]);
            }
        }
    }

    /**
     * Obté tots els logros i medalles disponibles.
     * Mapeja quins d'ells han estat desbloquejats per l'usuari.
     *
     * A. Recupera l'array d'IDs dels logros que té l'usuari actual.
     * B. Recupera tots els registres de la taula logros_medalles.
     * C. Afegeix el camp 'desbloquejat' a cadascun.
     * D. Retorna la col·lecció de models.
     *
     * @param int $usuariId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function llistarTotsElsLogros(int $usuariId)
    {
        // A. Cerca a la taula 'usuaris_logros' per saber què té l'usuari
        $logrosDesbloquejats = DB::table('usuaris_logros')
            ->where('usuari_id', $usuariId)
            ->pluck('logro_id')
            ->toArray();

        // B. Recuperar tots els logros totals
        $logros = LogroMedalla::all();

        // C. Marcar com a desbloquejats si s'escau
        foreach ($logros as $logro) {
            $esDesbloquejat = in_array($logro->id, $logrosDesbloquejats);
            // Injectem la propietat perquè el recurs pugui llegir-la
            $logro->desbloquejat = $esDesbloquejat;
        }

        // D. Retornar la col·lecció
        return $logros;
    }
}
