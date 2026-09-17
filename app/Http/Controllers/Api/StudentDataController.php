<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\EnrollmentResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentSignalementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentDataController extends Controller
{
    /**
     * Informations de l'élève lié au compte connecté.
     *
     * GET /api/student
     */
    public function show(Request $request): JsonResponse
    {
        $student = $request->attributes->get('studentModel');

        $student->load([
            'enrollments.subject',
            'enrollments.teacher',
        ]);

        return response()->json([
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->first_name.' '.$student->last_name,
            'date_of_birth' => $student->date_of_birth?->toDateString(),
            'level' => $student->level,
            'phone' => $student->phone,
            'address' => $student->address,
            'enrollments_count' => $student->enrollments->count(),
        ]);
    }

    /**
     * Inscriptions de l'élève.
     *
     * GET /api/student/enrollments
     */
    public function enrollments(Request $request): AnonymousResourceCollection
    {
        $student = $request->attributes->get('studentModel');

        $enrollments = $student->enrollments()
            ->with([
                'subject',
                'teacher',
                'schoolYear',
            ])
            ->latest()
            ->get();

        return EnrollmentResource::collection($enrollments);
    }

    /**
     * Paiements de l'élève.
     *
     * GET /api/student/payments
     */
    public function payments(Request $request): AnonymousResourceCollection
    {
        $student = $request->attributes->get('studentModel');

        $payments = $student->payments()
            ->with([
                'subject',
                'schoolYear',
            ])
            ->latest('payment_date')
            ->get();

        return PaymentResource::collection($payments);
    }

    /**
     * Présences de l'élève.
     *
     * GET /api/student/attendances
     */
    public function attendances(Request $request): AnonymousResourceCollection
    {
        $student = $request->attributes->get('studentModel');

        $attendances = $student->attendances()
            ->with([
                'subject',
                'teacher',
                'schoolYear',
            ])
            ->latest('date')
            ->get();

        return AttendanceResource::collection($attendances);
    }

    /**
     * Signalements d'impayés de l'élève.
     *
     * GET /api/student/signalements
     */
    public function signalements(Request $request): AnonymousResourceCollection
    {
        $student = $request->attributes->get('studentModel');

        $signalements = $student->paymentSignalements()
            ->with([
                'subject',
                'schoolYear',
            ])
            ->latest('signalement_date')
            ->get();

        return PaymentSignalementResource::collection($signalements);
    }
}
