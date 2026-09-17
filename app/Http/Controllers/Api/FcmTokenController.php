<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Enregistrer ou mettre à jour un FCM token.
     *
     * POST /api/fcm-token
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        $userId = $request->user()->id;

        FcmToken::updateOrCreate(
            [
                'user_id' => $userId,
                'token' => $validated['token'],
            ],
            [
                'platform' => $validated['platform'] ?? 'android',
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'FCM token enregistré.',
        ]);
    }

    /**
     * Supprimer un FCM token (logout).
     *
     * DELETE /api/fcm-token
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:500',
        ]);

        FcmToken::where('user_id', $request->user()->id)
            ->where('token', $validated['token'])
            ->delete();

        return response()->json([
            'message' => 'FCM token supprimé.',
        ]);
    }
}
