---
paths:
  - 'database/migrations/**'
---

# Migrations

## payments.subject_id exists only via 133003 migration (driver-guarded)
Migration 2026_08_14_133003 touches payments.subject_id: on MySQL it only runs a raw ALTER ... MODIFY; on non-MySQL drivers (sqlite tests) it creates the column via Schema when missing, because no other migration defines payments.subject_id. Keep DB::statement calls driver-guarded (DB::connection()->getDriverName() === 'mysql').
