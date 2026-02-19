<?php

namespace App\Console\Commands;

use App\Models\Habit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class HabitProcessCommand extends Command
{
    /**
     * El nom i la signatura del comandament.
     *
     * @var string
     */
    protected $signature = 'habits:process';

    /**
     * La descripció del comandament.
     *
     * @var string
     */
    protected $description = 'Processa la cua de Redis per al CRUD d\'hàbits';

    /**
     * Executa el comandament.
     */
    public function handle()
    {
        $queueName = 'habits_queue';
        $feedbackChannel = 'habits_feedback_channel';

        $this->info("Escoltant la cua '$queueName'...");

        while (true) {
            try {
                // BRPOP bloqueja fins que hi ha dades (timeout de 0 per a infinit)
                // Retorna un array [clau, valor]
                $result = Redis::connection()->command('brPop', [$queueName, 0]);

                if (!$result)
                    continue;

                $payload = json_decode($result[1], true);
                if (!$payload) {
                    $this->error("Payload invàlid rebut.");
                    continue;
                }

                $action = $payload['action'] ?? null;
                $userId = $payload['user_id'] ?? null;
                $habitId = $payload['habit_id'] ?? null;
                $habitData = $payload['habit_data'] ?? [];

                $this->info("Processant $action per a l'usuari $userId...");

                $success = false;
                $habitModel = null;

                switch ($action) {
                    case 'CREATE':
                        $habitModel = Habit::create([
                            'usuari_id' => $userId,
                            'titol' => $habitData['titol'] ?? 'Nou hàbit',
                            'dificultat' => $habitData['dificultat'] ?? 'Baixa',
                            'frequencia_tipus' => $habitData['frequencia_tipus'] ?? 'Diari',
                            'dies_setmana' => $habitData['dies_setmana'] ?? '',
                            'objectiu_vegades' => $habitData['objectiu_vegades'] ?? 1,
                        ]);
                        $success = (bool) $habitModel;
                        break;

                    case 'UPDATE':
                        $habitModel = Habit::find($habitId);
                        if ($habitModel && $habitModel->usuari_id == $userId) {
                            $habitModel->update($habitData);
                            $success = true;
                        }
                        break;

                    case 'DELETE':
                        $habitModel = Habit::find($habitId);
                        if ($habitModel && $habitModel->usuari_id == $userId) {
                            $habitModel->delete();
                            $success = true;
                        }
                        break;

                    case 'TOGGLE':
                        // Aquí podries implementar la lògica de completar hàbit si cal
                        $success = true;
                        break;

                    default:
                        $this->warn("Acció desconeguda: $action");
                        break;
                }

                // Enviar feedback
                $feedback = [
                    'action' => $action,
                    'user_id' => $userId,
                    'success' => $success,
                    'habit' => $habitModel
                ];

                Redis::publish($feedbackChannel, json_encode($feedback));
                $this->info("Feedback enviat per a $action.");

            } catch (\Exception $e) {
                $this->error("Error processant la cua: " . $e->getMessage());
                Log::error("Error HabitProcessCommand: " . $e->getMessage());
                // Evitar bucle infinit ràpid en cas d'error de connexió
                sleep(2);
            }
        }
    }
}
