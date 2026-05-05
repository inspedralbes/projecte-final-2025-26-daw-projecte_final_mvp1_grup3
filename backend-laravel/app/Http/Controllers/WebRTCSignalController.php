<?php

namespace App\Http\Controllers;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de senyalització WebRTC.
 *
 * Nota: Aquest MVP només defineix el controlador per coherència de rutes.
 * La implementació real es farà quan s'integri el xat/vídeo.
 */
class WebRTCSignalController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Rep un missatge de senyalització (SDP/ICE) i el processa.
     */
    public function handleSignal(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'WebRTC signaling no disponible en aquest MVP',
        ], 501);
    }

    /**
     * Retorna (si existeix) la sala WebRTC per un amic concret.
     */
    public function getRoom(int $friendId): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'WebRTC rooms no disponible en aquest MVP',
            'friend_id' => $friendId,
        ], 501);
    }

    /**
     * Uneix l'usuari a una sala WebRTC amb un amic.
     */
    public function joinRoom(int $friendId): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'WebRTC join no disponible en aquest MVP',
            'friend_id' => $friendId,
        ], 501);
    }
}

