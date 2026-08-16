---
paths:
  - 'app/Services/**'
---

# Services

## Couverture de paiement strictement par période
Un paiement ne couvre une dette mensuelle/VIP que si sa `period` correspond EXACTEMENT à celle de la dette (Y-m / mois français avec même année pour mensuel ; Y-m-d pour VIP). Le fallback `payment_date` n'est utilisé que si `period` est vide — jamais pour un paiement avec une période déclarée. Un paiement d'octobre ne paie jamais une dette d'août. Un signalement stocké `resolved` pour (student, subject, period) exclut la dette de la liste active (hasResolvedSignalement).
