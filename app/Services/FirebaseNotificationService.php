<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    private mixed $messaging;

    public function __construct()
    {
        try {
            $firebase = app('firebase');
            $this->messaging = $firebase->getMessaging();
        } catch (\Throwable $e) {
            Log::warning('Firebase not configured: '.$e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Envoyer une notification à un utilisateur (tous ses appareils).
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        string $type,
        array $extraData = []
    ): void {
        $tokens = FcmToken::where('user_id', $user->id)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $data = array_merge(['type' => $type], $extraData);

        if ($this->messaging === null) {
            Log::warning('Firebase messaging not available. Notification not sent.', [
                'user_id' => $user->id,
                'type' => $type,
            ]);

            return;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $tokens);

            // Supprimer les tokens invalides
            if (null !== ($report['error'] ?? null)) {
                // sendMulticast returns a Report object
            }

            // Nettoyer les tokens qui ont échoué
            $this->cleanupInvalidTokens($tokens, $report);

        } catch (\Throwable $e) {
            Log::error('FCM send failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'type' => $type,
            ]);
        }
    }

    /**
     * Envoyer à un seul token spécifique.
     */
    public function sendToToken(string $token, string $title, string $body, string $type, array $extraData = []): void
    {
        if ($this->messaging === null) {
            return;
        }

        try {
            $notification = Notification::create($title, $body);
            $data = array_merge(['type' => $type], $extraData);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message, $token);
        } catch (\Throwable $e) {
            Log::warning('FCM single send failed: '.$e->getMessage());
        }
    }

    /**
     * Supprimer les tokens devenus invalides après envoi.
     */
    private function cleanupInvalidTokens(array $tokens, mixed $report): void
    {
        try {
            if (method_exists($report, 'tokensNotRegistered')) {
                $invalidTokens = $report->tokensNotRegistered();

                if (! empty($invalidTokens)) {
                    FcmToken::whereIn('token', $invalidTokens)->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FCM cleanup failed: '.$e->getMessage());
        }
    }

    /**
     * Notification Présence (present).
     */
    public function notifyPresence(User $parent, string $studentName, string $subjectName, int $studentId, int $attendanceId): void
    {
        $this->sendToUser(
            $parent,
            'Présence enregistrée',
            "{$studentName} est présent en {$subjectName}.",
            'attendance_present',
            ['student_id' => (string) $studentId, 'attendance_id' => (string) $attendanceId]
        );
    }

    /**
     * Notification Absence.
     */
    public function notifyAbsence(User $parent, string $studentName, string $subjectName, int $studentId, int $attendanceId): void
    {
        $this->sendToUser(
            $parent,
            'Absence',
            "{$studentName} a été marqué absent en {$subjectName}.",
            'attendance_absent',
            ['student_id' => (string) $studentId, 'attendance_id' => (string) $attendanceId]
        );
    }

    /**
     * Notification Retard.
     */
    public function notifyRetard(User $parent, string $studentName, string $subjectName, int $studentId, int $attendanceId): void
    {
        $this->sendToUser(
            $parent,
            'Retard',
            "{$studentName} est arrivé en retard en {$subjectName}.",
            'attendance_retard',
            ['student_id' => (string) $studentId, 'attendance_id' => (string) $attendanceId]
        );
    }

    /**
     * Notification Paiement partiel.
     */
    public function notifyPayment(User $parent, string $studentName, float $amountPaid, float $remaining, int $studentId, int $paymentId): void
    {
        $amountFormatted = number_format($amountPaid, 0, ',', ' ');
        $remainingFormatted = number_format($remaining, 0, ',', ' ');

        $this->sendToUser(
            $parent,
            'Paiement reçu',
            "Paiement de {$amountFormatted} DA reçu pour {$studentName}. Reste: {$remainingFormatted} DA.",
            'payment',
            ['student_id' => (string) $studentId, 'payment_id' => (string) $paymentId]
        );
    }

    /**
     * Notification Paiement complet.
     */
    public function notifyPaymentCompleted(User $parent, string $studentName, int $studentId, int $paymentId): void
    {
        $this->sendToUser(
            $parent,
            'Paiement complet',
            "Le paiement de {$studentName} est réglé.",
            'payment_completed',
            ['student_id' => (string) $studentId, 'payment_id' => (string) $paymentId]
        );
    }

    /**
     * Notification Nouvelle inscription.
     */
    public function notifyEnrollment(User $parent, string $studentName, string $subjectName, string $teacherName, int $studentId, int $enrollmentId): void
    {
        $this->sendToUser(
            $parent,
            'Nouvelle inscription',
            "{$studentName} a été inscrit en {$subjectName} avec M. {$teacherName}.",
            'enrollment',
            ['student_id' => (string) $studentId, 'enrollment_id' => (string) $enrollmentId]
        );
    }
}
