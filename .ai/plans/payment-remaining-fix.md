# Plan: Fix Partial Payment + Impayés Calculation

## Problem Summary

The payment system has a **double-counting bug**: when multiple payments exist for the same period, `amount_due` is summed across all payment records, inflating the obligation. The system should track the **real remaining balance** per obligation, not just "paid/not paid".

### Root Causes

1. **`PaymentController::store()`** always sets `amount_due` to the full tariff price, even when previous payments already cover part of the obligation.
2. **`UnpaidDebtService::monthlyDebts()`** (line 152) sums `amount_due` across all payments: `$payments->sum('amount_due')`. Two partial payments for the same month = double obligation.
3. **`PaymentSignalementService::syncMonthly()`** (line 197) has the same double-counting.
4. **`UnpaidDebtService::vipDebts()`** (line 215-219) only checks ONE payment per day via `->first()`, missing additional VIP payments.
5. **No payment allocation**: A single payment can't cover multiple obligations (oldest unpaid first).

---

## Implementation Plan

### Step 1: Add `getObligationAmount()` helper to both services

Both `UnpaidDebtService` and `PaymentSignalementService` need to resolve the tariff price for a student+subject pair. Add a private method:

```php
private function getObligationAmount(int $studentId, int $subjectId): float
{
    $group = Group::where('subject_id', $subjectId)
        ->where('is_active', true)
        ->whereHas('students', fn ($q) => $q->where('students.id', $studentId))
        ->first();

    if (! $group) {
        return 0;
    }

    $tariff = GroupTariff::where('group_id', $group->id)
        ->where('is_active', true)
        ->latest('effective_from')
        ->first();

    return $tariff ? (float) $tariff->student_price : 0;
}
```

**Files:**
- `app/Services/UnpaidDebtService.php` — add method + import `Group`, `GroupTariff`
- `app/Services/PaymentSignalementService.php` — add method + import `Group`, `GroupTariff`

### Step 2: Fix `UnpaidDebtService::monthlyDebts()` (lines 152-154)

**Before:**
```php
$amountDue = (float) $payments->sum('amount_due');
$amountPaid = (float) $payments->sum('amount_paid');
$remaining = max($amountDue - $amountPaid, 0);
```

**After:**
```php
$amountPaid = (float) $payments->sum('amount_paid');
$amountDue = $this->getObligationAmount($studentId, $subjectId);
$remaining = max($amountDue - $amountPaid, 0);
```

Also return `amount_due` and `amount_paid` in the debt object for the view:
```php
$debts->push((object) [
    'student' => $student,
    'subject' => $subject,
    'type' => 'monthly',
    'period' => $month,
    'amount_due' => $amountDue,
    'amount_paid' => $amountPaid,
    'amount_remaining' => $remaining,
    'status' => $stored?->status ?? 'pending',
    'signalement' => $stored,
]);
```

### Step 3: Fix `UnpaidDebtService::vipDebts()` (lines 215-246)

**Before:** Only checks ONE payment per day via `->first()`.

**After:** Get ALL VIP payments for the day, sum `amount_paid`, use tariff as obligation.

```php
$payments = Payment::where('student_id', $studentId)
    ->where('subject_id', $subjectId)
    ->whereIn('payment_type', ['vip', 'vip_monthly', 'vip_per_session'])
    ->get()
    ->filter(fn (Payment $payment) => $this->paymentCoversDay($payment, $date));

$amountPaid = (float) $payments->sum('amount_paid');
$amountDue = $this->getObligationAmount($studentId, $subjectId);
$remaining = max($amountDue - $amountPaid, 0);

if ($amountPaid > 0 && $remaining <= 0) {
    continue; // Fully paid
}
```

Also return `amount_due` and `amount_paid` in the debt object.

### Step 4: Fix `PaymentSignalementService::syncMonthly()` (lines 197-199)

**Before:**
```php
$amountDue = (float) $payments->sum('amount_due');
$amountPaid = (float) $payments->sum('amount_paid');
$remaining = max($amountDue - $amountPaid, 0);
```

**After:**
```php
$amountPaid = (float) $payments->sum('amount_paid');
$amountDue = $this->getObligationAmount($studentId, $subjectId);
$remaining = max($amountDue - $amountPaid, 0);
```

### Step 5: Fix `PaymentSignalementService::syncVip()` (lines 253-259)

**Before:** Gets only one VIP payment per day.

**After:** Get ALL VIP payments for the day, sum amounts.

```php
$payments = Payment::where('student_id', $studentId)
    ->where('subject_id', $subjectId)
    ->whereIn('payment_type', ['vip', 'vip_monthly', 'vip_per_session'])
    ->get()
    ->filter(fn (Payment $payment) => $this->paymentCoversDay($payment, $date));

$amountPaid = (float) $payments->sum('amount_paid');
$amountDue = $this->getObligationAmount($studentId, $subjectId);
$remaining = max($amountDue - $amountPaid, 0);
```

Update the signalement's `amount_remaining` to `$remaining` instead of `$payment->remaining_amount`.

### Step 6: Fix `PaymentController::store()` — correct `amount_due` per payment

When creating a payment for a period that already has payments, set `amount_due` to the **remaining obligation** (tariff - already paid), NOT the full tariff.

**Before (lines 368-379):**
```php
$amountDue = (float) $tariff->student_price;
$amountPaid = (float) $validated['amount_paid'];
if ($amountPaid > $amountDue) { ... reject ... }
$remaining = $amountDue - $amountPaid;
```

**After:**
```php
$tariffPrice = (float) $tariff->student_price;
$amountPaid = (float) $validated['amount_paid'];

// Calculate what's already been paid for this student+subject+period
$existingPaid = (float) Payment::where('student_id', $validated['student_id'])
    ->where('subject_id', $validated['subject_id'])
    ->where('period', $validated['period'])
    ->sum('amount_paid');

$remainingObligation = max($tariffPrice - $existingPaid, 0);

if ($amountPaid > $remainingObligation) {
    return back()->withErrors([
        'amount_paid' => 'Le montant payé ne peut pas dépasser le reste à payer pour cette période ('.number_format($remainingObligation, 2, ',', ' ').' DA).',
    ])->withInput();
}

$amountDue = $remainingObligation;
$remaining = $remainingObligation - $amountPaid;
```

### Step 7: Fix `PaymentController::update()` — same logic as store

Apply the same remaining-obligation calculation in `update()` (lines 610-622). Must exclude the current payment from `existingPaid` sum.

### Step 8: Update `payment-signalements/index.blade.php` view

Add "Montant dû" and "Payé" columns to the impayés table. Currently only shows "Montant restant".

Add columns after "Période":
- **Montant dû**: `{{ number_format($signalement->amount_due, 2, ',', ' ') }} DA`
- **Payé**: `{{ number_format($signalement->amount_paid, 2, ',', ' ') }} DA`
- **Reste** (renamed from "Montant restant"): `{{ number_format($signalement->amount_remaining, 2, ',', ' ') }} DA`

### Step 9: Update tests

Update `tests/Feature/PaymentSignalementsIndexTest.php` to test:
- Multiple partial payments for the same month don't double-count
- Remaining is calculated from tariff, not sum of `amount_due`
- After full payment via multiple partials, debt disappears
- VIP multiple payments for same day
- Allocation across months

Update `tests/Feature/PaymentTest.php` to test:
- Second payment for same period sets correct `amount_due`
- Overpayment is rejected
- Payment allocation across periods

### Step 10: Run `vendor/bin/pint --dirty --format agent`

Format modified PHP files.

---

## Files to Modify

| File | Changes |
|---|---|
| `app/Services/UnpaidDebtService.php` | Add `getObligationAmount()`, fix `monthlyDebts()`, fix `vipDebts()`, return `amount_due`/`amount_paid` in debt objects |
| `app/Services/PaymentSignalementService.php` | Add `getObligationAmount()`, fix `syncMonthly()`, fix `syncVip()` |
| `app/Http/Controllers/PaymentController.php` | Fix `store()` remaining obligation calc, fix `update()` |
| `resources/views/payment-signalements/index.blade.php` | Add Montant dû + Payé columns |
| `tests/Feature/PaymentSignalementsIndexTest.php` | Add partial payment + multi-payment tests |
| `tests/Feature/PaymentTest.php` | Add remaining obligation + allocation tests |

## Files NOT Modified

- Migrations (no schema changes)
- Routes (no new routes)
- Models (no changes needed)
- `payments.unpaid` view (uses per-payment records, already correct)
- `payment-signalements/show.blade.php` (already shows linked payment details)

## Verification

1. `composer test` — all existing + new tests pass
2. Manual verification:
   - Create attendance → impayé appears with full amount
   - Partial payment → impayé shows remaining only
   - Second partial payment → remaining decreases
   - Full payment → impayé disappears
   - VIP: multiple sessions, partial payment per session
