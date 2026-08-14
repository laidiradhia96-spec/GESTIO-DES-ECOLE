<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Liste des annonces
     */
    public function index()
    {
        $announcements = Announcement::latest('published_at')
            ->latest()
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('announcements.create');
    }

    /**
     * Enregistrer une annonce
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => 'info',
            'is_active' => true,
            'published_at' => now(),
        ]);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Annonce créée avec succès.');
    }

    /**
     * Afficher une annonce
     * + Enregistrer que l'utilisateur l'a vue
     */
    public function show(Announcement $announcement)
    {
        $user = auth()->user();

        if ($user) {

            $user->viewedAnnouncements()->syncWithoutDetaching([
                $announcement->id => [
                    'seen_at' => now(),
                ],
            ]);
        }

        return view(
            'announcements.show',
            compact('announcement')
        );
    }

    /**
     * Formulaire de modification
     */
    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Modifier une annonce
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:info,important,warning'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $announcement->update($validated);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Annonce modifiée avec succès.');
    }

    /**
     * Supprimer une annonce
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Annonce supprimée avec succès.');
    }
}