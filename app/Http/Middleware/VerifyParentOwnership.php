<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyParentOwnership
{
    /**
     * Résout le profil élève lié au compte authentifié via students.user_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        if (! $student) {
            abort(404, 'Aucun profil élève lié à ce compte.');
        }

        $request->attributes->set('studentModel', $student);

        return $next($request);
    }
}
