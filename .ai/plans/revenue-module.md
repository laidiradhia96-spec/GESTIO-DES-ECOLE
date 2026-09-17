# Plan: Revenue Module + Teacher Payslip

## Audit Summary

### Schema (no changes needed)
- **payments**: `student_id`, `subject_id`, `group_id`, `amount_paid`, `amount_due`, `remaining_amount`, `payment_date`, `payment_type` (monthly|special_monthly|vip_monthly|vip_per_session), `period`, `school_year_id`. **NO status field** — all payments are collected.
- **group_tariffs**: `group_id`, `student_price`, `teacher_share`, `academy_share`, `effective_from`, `effective_to`, `is_active`
- **groups**: `teacher_id`, `subject_id`, `mode` (normal|special|vip), `school_year_id`
- **teachers**: `first_name`, `last_name`, `speciality`, `active`

### Calculation Chain
```
Payment → group_id → Group (teacher_id, subject_id)
                  → GroupTariff (effective on payment_date)
                     → teacher_share, academy_share
```

### Revenue Formula
```
teacher_revenue = amount_paid × (teacher_share / student_price)
academy_revenue = amount_paid × (academy_share / student_price)
```

### Historical Tariff Resolution
For each payment, find the GroupTariff where:
- `group_id = payment.group_id`
- `is_active = true`
- `effective_from <= payment.payment_date`
- `effective_to IS NULL OR effective_to >= payment.payment_date`

### Key Decisions
1. **Revenue source**: `payments.amount_paid` — actual collected amount
2. **Period filtering**: Use `payment_date` (the real business date)
3. **No cancelled payments**: Schema has no status field; all payments count
4. **Teacher link**: Via `groups.teacher_id`
5. **No schema changes**: All data exists in current tables

---

## Implementation Plan

### Step 1: Create `RevenueService`

**File:** `app/Services/RevenueService.php`

Centralized calculation service. Read-only, no DB writes.

```php
class RevenueService
{
    /**
     * Resolve the GroupTariff effective on a given date for a group.
     */
    public function resolveTariff(int $groupId, Carbon $date): ?GroupTariff

    /**
     * Calculate teacher and academy shares from a payment amount + tariff.
     * Returns ['teacher' => float, 'academy' => float]
     */
    public function calculateShares(float $amountPaid, GroupTariff $tariff): array

    /**
     * Get aggregated revenue data for the teacher list page.
     * Filters: month, year, teacher_id, group_id, subject_id
     * Returns Collection of objects with: teacher, payments_count, students_count, total_collected, teacher_share, academy_share
     */
    public function getTeacherRevenues(?int $month = null, ?int $year = null, ?int $teacherId = null, ?int $groupId = null, ?int $subjectId = null, ?int $schoolYearId = null): Collection

    /**
     * Get detailed payment list for a specific teacher's payslip.
     * Returns Collection of payment rows with: payment, student, group, subject, tariff, teacher_share, academy_share
     */
    public function getTeacherPayments(int $teacherId, ?int $month = null, ?int $year = null, ?int $schoolYearId = null): Collection

    /**
     * Get summary stats for the teacher list dashboard.
     */
    public function getRevenueStats(?int $month = null, ?int $year = null, ?int $schoolYearId = null): array
}
```

**Key query optimization:**
- Single query: `Payment::with(['student', 'group.teacher', 'subject', 'group.tariffs'])` filtered by date range
- Filter payments in PHP for tariff resolution (tariffs are small per group)
- Use `groupBy` for aggregations

### Step 2: Create `RevenueController`

**File:** `app/Http/Controllers/RevenueController.php`

Two pages: teacher list + individual fiche.

```php
class RevenueController extends Controller
{
    /**
     * GET /revenus — Teacher revenue list with dashboard
     */
    public function index(Request $request)

    /**
     * GET /revenus/enseignants/{teacher}/fiche — Individual teacher payslip
     */
    public function fiche(Teacher $teacher, Request $request)

    /**
     * GET /revenus/enseignants/{teacher}/print — Printable payslip
     */
    public function print(Teacher $teacher, Request $request)
}
```

### Step 3: Create Revenue Routes

**File:** `routes/web.php` (modify — add in admin group)

```php
// Revenus
Route::get('/revenus', [RevenueController::class, 'index'])->name('revenus.index');
Route::get('/revenus/enseignants/{teacher}/fiche', [RevenueController::class, 'fiche'])->name('revenus.fiche');
Route::get('/revenus/enseignants/{teacher}/print', [RevenueController::class, 'print'])->name('revenus.print');
```

### Step 4: Add Navigation Links

**File:** `resources/views/layouts/navigation.blade.php`

Add after "Années scolaires" in both desktop and mobile sections:
- Desktop: `<x-nav-link>` with 💰 Revenus
- Mobile: `<x-responsive-nav-link>` with 💰 Revenus

### Step 5: Create `revenues/index.blade.php`

**File:** `resources/views/revenues/index.blade.php`

Layout following existing patterns (payments/index.blade.php style).

Sections:
1. **Header**: 💰 icon + "Revenus des enseignants" title
2. **Dashboard cards** (4 cards):
   - Total encaissé (gold accent)
   - Part enseignants (blue accent)
   - Part académie (green accent)
   - Nombre de paiements
3. **Filters**: Mois, Année, Enseignant, Groupe, Matière (form with GET params)
4. **Teachers table**: Enseignant | Paiements | Élèves | Total encaissé | Part prof | Part académie | Actions (Voir)

### Step 6: Create `revenues/teacher-fiche.blade.php`

**File:** `resources/views/revenues/teacher-fiche.blade.php`

Individual teacher payslip page.

Sections:
1. **Header**: Fiche de paie enseignant + teacher info
2. **Summary cards** (5 cards):
   - Total encaissé
   - Part enseignant
   - Part académie
   - Nombre de paiements
   - Nombre d'élèves payeurs
3. **Print button**: 🖨️ Imprimer (links to print route)
4. **Payment details table**: Date | Élève | Groupe | Matière | Type | Montant payé | Part prof | Part académie
5. **Totals row** at bottom

### Step 7: Create `revenues/teacher-print.blade.php`

**File:** `resources/views/revenues/teacher-print.blade.php`

Standalone print view (no layout, like payments/print.blade.php).

Same structure as fiche but with:
- White background, A4 format
- Academy name + logo reference
- Clean professional design
- Print-friendly CSS (`@media print`)
- Date d'impression at bottom

### Step 8: Create Tests

**File:** `tests/Feature/RevenueTest.php`

12 tests covering all scenarios:
1. Full payment → correct shares
2. Partial payment → proportional shares
3. Multiple partial payments → aggregated correctly
4. VIP payments → independent per session
5. Multiple groups → each payment to correct teacher
6. Multiple teachers → no cross-contamination
7. Multiple subjects → filter works
8. Month filter → payments from other months excluded
9. Historical tariff → old payment uses old tariff
10. No payments → revenue = 0
11. Dashboard stats → total = teacher + academy
12. Print page → renders correctly

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Services/RevenueService.php` | Centralized revenue calculation |
| `app/Http/Controllers/RevenueController.php` | Controller for revenue pages |
| `resources/views/revenues/index.blade.php` | Teacher revenue list page |
| `resources/views/revenues/teacher-fiche.blade.php` | Individual teacher payslip |
| `resources/views/revenues/teacher-print.blade.php` | Printable payslip (A4) |
| `tests/Feature/RevenueTest.php` | Feature tests |

## Files to Modify

| File | Change |
|------|--------|
| `routes/web.php` | Add 3 revenue routes in admin group |
| `resources/views/layouts/navigation.blade.php` | Add nav links (desktop + mobile) |

## Files NOT Modified

- All models (no schema changes)
- Migrations (none needed)
- PaymentController (workflow unchanged)
- UnpaidDebtService (read-only, unaffected)
- PaymentSignalementService (write service, unaffected)
- Existing views (no modifications)

## Verification

1. `php artisan test --compact --filter=Revenue` — all 12 tests pass
2. Manual: Navigate to /revenus → see dashboard with stats
3. Manual: Click "Voir" → see teacher fiche with payment details
4. Manual: Click "Imprimer" → see clean A4 print layout
5. `vendor/bin/pint --dirty --format agent` — code formatted
