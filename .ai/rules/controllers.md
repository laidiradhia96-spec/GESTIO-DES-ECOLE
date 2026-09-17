---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Actions Impayés par identité (student+subject+period)
Les routes `payment-signalements.sent` et `.resolved` (PATCH /payment-signalements/sent, /resolved) sont sans paramètre de modèle : elles reçoivent student_id + subject_id + period (+ amount_remaining) et upsertent via resolveSignalementForDebt (recherche y compris alias mois français) pour matérialiser une dette calculée sans signalement stocké, sans créer de doublon. Ne pas revenir au binding `{paymentSignalement}`.

## Use whereDate() not where() for date comparisons in SQLite-compatible queries
SQLite stores dates as `Y-m-d H:i:s` even for DATE columns with Eloquent date casts. Using `where('date_col', '<=', '2026-09-13')` fails because string comparison sees `2026-09-13 00:00:00 > 2026-09-13`. Always use `whereDate()` for date-only comparisons: `whereDate('effective_from', '<=', now()->toDateString())`. This affects all date comparisons in queries that run against SQLite test databases.

## Use where() on pivot table directly instead of wherePivot inside whereHas for SQLite
wherePivot() doesn't work reliably inside whereHas() with SQLite test databases. Use the pivot table name directly: `$sq->where('student_group.is_active', 1)` instead of `$sq->wherePivot('is_active', true)`. This only affects whereHas() closures; wherePivot() on direct relationship queries works fine.
