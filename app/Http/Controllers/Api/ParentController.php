<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    /**
     * Profil de l'utilisateur connecté + élève lié.
     *
     * GET /api/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        return response()->json([
            'id' => $user->id,
            'name' => $student?->parent_name,
            'email' => $user->email,
            'role' => $user->role,
            'student' => $student ? [
                'id' => $student->id,
                'full_name' => $student->first_name.' '.$student->last_name,
                'level' => $student->level,
            ] : null,
        ]);
    }

    /**
     * Annonces actives.
     *
     * GET /api/announcements
     */
    public function announcements(Request $request): JsonResponse
    {
        $announcements = Announcement::where('is_active', true)
            ->latest('published_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'type' => $a->type,
                'published_at' => $a->published_at?->toISOString(),
            ]);

        return response()->json($announcements);
    }
}
