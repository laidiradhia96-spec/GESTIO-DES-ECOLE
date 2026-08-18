<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    /**
     * Dashboard de l'élève connecté
     */
    public function index()
    {
        // =====================================================
        // UTILISATEUR CONNECTÉ
        // =====================================================

        $user = Auth::user();

        $student = $user->student;

        // Vérifier que le compte possède bien un profil élève
        if (! $student) {
            abort(
                403,
                'Ce compte ne possède pas de profil élève.'
            );
        }

        // =====================================================
        // INSCRIPTIONS / MATIÈRES
        // =====================================================

        $schoolYear = SchoolYear::current() ?? SchoolYear::forDate(now());

        $enrollments = $student->enrollments()
            ->with([
                'subject',
                'teacher',
            ])
            ->where('status', 'active')
            ->when(
                $schoolYear,
                fn ($query) => $query->where('school_year_id', $schoolYear->id)
            )
            ->latest()
            ->get();

        // =====================================================
        // DATE ACTUELLE
        // =====================================================

        $today = now()->toDateString();

        /*
         * Mois actuel en français.
         *
         * Exemple :
         * 01 -> Janvier
         * 08 -> Août
         * 09 -> Septembre
         * 10 -> Octobre
         */

        $frenchMonths = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];

        $currentPeriod = $frenchMonths[now()->month];

        // =====================================================
        // STATUT DES PAIEMENTS PAR MATIÈRE
        // =====================================================

        foreach ($enrollments as $enrollment) {

            /*
             * Recherche des paiements de cet élève
             * pour cette matière.
             *
             * IMPORTANT :
             *
             * On considère le paiement comme réellement payé
             * uniquement lorsque :
             *
             * remaining_amount <= 0
             *
             * ET
             *
             * amount_paid > 0
             *
             * Donc un paiement partiel reste NON PAYÉ.
             */

            $paymentQuery = Payment::where(
                'student_id',
                $student->id
            )
                ->where(
                    'subject_id',
                    $enrollment->subject_id
                )
                ->where(
                    'remaining_amount',
                    '<=',
                    0
                )
                ->where(
                    'amount_paid',
                    '>',
                    0
                );

            // =================================================
            // ⭐ VIP
            // =================================================

            if ($enrollment->payment_type === 'vip') {

                /*
                 * VIP :
                 *
                 * Le paiement est valable uniquement
                 * pour la journée actuelle.
                 *
                 * Exemple :
                 *
                 * 20/08 -> payé
                 * 21/08 -> nouveau paiement obligatoire
                 */

                $paidToday = (clone $paymentQuery)
                    ->whereDate(
                        'payment_date',
                        $today
                    )
                    ->exists();

                if ($paidToday) {

                    $enrollment->payment_status = 'paid';

                    $enrollment->payment_label =
                        "Payé aujourd'hui";

                } else {

                    $enrollment->payment_status = 'unpaid';

                    $enrollment->payment_label =
                        "Non payé aujourd'hui";
                }
            }

            // =================================================
            // 📅 MENSUEL
            // =================================================

            elseif ($enrollment->payment_type === 'monthly') {

                /*
                 * MENSUEL :
                 *
                 * IMPORTANT :
                 * On ne regarde PAS payment_date.
                 *
                 * On regarde la colonne "period".
                 *
                 * Exemple :
                 *
                 * Paiement :
                 * payment_date = 15/08/2026
                 * period       = Septembre
                 *
                 * Résultat :
                 *
                 * Août       -> NON PAYÉ
                 * Septembre  -> PAYÉ
                 *
                 * Même si le paiement a été effectué en août.
                 */

                $paidThisMonth = (clone $paymentQuery)
                    ->whereRaw(
                        'LOWER(TRIM(period)) = ?',
                        [
                            mb_strtolower($currentPeriod),
                        ]
                    )
                    ->exists();

                if ($paidThisMonth) {

                    $enrollment->payment_status = 'paid';

                    $enrollment->payment_label =
                        'Payé pour ce mois';

                } else {

                    $enrollment->payment_status = 'unpaid';

                    $enrollment->payment_label =
                        'Non payé ce mois';
                }
            }

            // =================================================
            // TYPE DE PAIEMENT INCONNU
            // =================================================

            else {

                $enrollment->payment_status = 'unknown';

                $enrollment->payment_label =
                    'Paiement non défini';
            }
        }

        // =====================================================
        // DERNIÈRES PRÉSENCES
        // =====================================================

        $attendances = Attendance::where(
            'student_id',
            $student->id
        )
            ->when(
                $schoolYear,
                fn ($query) => $query->where('school_year_id', $schoolYear->id)
            )
            ->with([
                'subject',
                'teacher',
            ])
            ->latest('date')
            ->take(5)
            ->get();

        // =====================================================
        // DERNIERS PAIEMENTS
        // =====================================================

        $payments = $student->payments()
            ->when(
                $schoolYear,
                fn ($query) => $query->where('school_year_id', $schoolYear->id)
            )
            ->latest()
            ->take(5)
            ->get();

        // =====================================================
        // STATISTIQUES
        // =====================================================

        $subjectsCount = $enrollments->count();

        // Nombre de présences
        $presentCount = Attendance::where(
            'student_id',
            $student->id
        )
            ->when(
                $schoolYear,
                fn ($query) => $query->where('school_year_id', $schoolYear->id)
            )
            ->where(
                'status',
                'present'
            )
            ->count();

        // Nombre d'absences
        $absentCount = Attendance::where(
            'student_id',
            $student->id
        )
            ->when(
                $schoolYear,
                fn ($query) => $query->where('school_year_id', $schoolYear->id)
            )
            ->where(
                'status',
                'absent'
            )
            ->count();

        // =====================================================
        // ANNONCES
        // =====================================================

        $announcements = Announcement::where(
            'is_active',
            true
        )
            ->latest('published_at')
            ->get();

        // =====================================================
        // MARQUER LES ANNONCES COMME VUES
        // =====================================================

        foreach ($announcements as $announcement) {

            $alreadyViewed = $user
                ->viewedAnnouncements()
                ->where(
                    'announcement_id',
                    $announcement->id
                )
                ->exists();

            if (! $alreadyViewed) {

                $user->viewedAnnouncements()->attach(
                    $announcement->id,
                    [
                        'seen_at' => now(),
                    ]
                );
            }
        }

        // =====================================================
        // ENVOI À LA VUE
        // =====================================================

        return view(
            'students.dashboard',
            compact(
                'student',
                'enrollments',
                'attendances',
                'payments',
                'subjectsCount',
                'presentCount',
                'absentCount',
                'announcements'
            )
        );
    }
}
