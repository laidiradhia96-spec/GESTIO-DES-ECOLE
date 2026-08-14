<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;

class StudentDashboardController extends Controller
{
    /**
     * Dashboard de l'élève connecté
     */
    public function index()
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Récupérer le profil élève lié au compte
        $student = $user->student;

        // Vérifier que le compte appartient bien à un élève
        if (!$student) {
            abort(403, 'Ce compte ne possède pas de profil élève.');
        }

        // Charger les inscriptions avec matière et enseignant
        $enrollments = $student->enrollments()
            ->with([
                'subject',
                'teacher',
            ])
            ->where('status', 'active')
            ->latest()
            ->get();

        // Dernières présences
        $attendances = Attendance::where(
                'student_id',
                $student->id
            )
            ->with([
                'subject',
                'teacher',
            ])
            ->latest('date')
            ->take(5)
            ->get();

        // Derniers paiements
        $payments = $student->payments()
            ->latest()
            ->take(5)
            ->get();

        // Statistiques
        $subjectsCount = $enrollments->count();

        $presentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        $absentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->count();
$announcements = Announcement::where('is_active', true)
    ->latest('published_at')
    ->get();

foreach ($announcements as $announcement) {

    $alreadyViewed = $user->viewedAnnouncements()
        ->where('announcement_id', $announcement->id)
        ->exists();

    if (!$alreadyViewed) {

        $user->viewedAnnouncements()->attach(
            $announcement->id,
            [
                'seen_at' => now(),
            ]
        );
    }
}

        return view('students.dashboard', compact(
    'student',
    'enrollments',
    'attendances',
    'payments',
    'subjectsCount',
    'presentCount',
    'absentCount',
    'announcements'
));
    }
}