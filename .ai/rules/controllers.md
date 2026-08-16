---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Actions Impayés par identité (student+subject+period)
Les routes `payment-signalements.sent` et `.resolved` (PATCH /payment-signalements/sent, /resolved) sont sans paramètre de modèle : elles reçoivent student_id + subject_id + period (+ amount_remaining) et upsertent via resolveSignalementForDebt (recherche y compris alias mois français) pour matérialiser une dette calculée sans signalement stocké, sans créer de doublon. Ne pas revenir au binding `{paymentSignalement}`.
