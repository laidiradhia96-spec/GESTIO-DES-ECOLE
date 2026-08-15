# Structure de la base de données

Base : **MySQL** (`ecole`) — 23 tables. App Laravel 12 de gestion d'école (Académie El Tafawok).

Les colonnes sont en français, les énumérations (enums) sont en anglais (`monthly`, `vip`, `present`, ...).

---

## 1. Users & Auth

### users
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| name | varchar(255) | |
| email | varchar(255) | unique |
| role | varchar(255) | `'admin'` \| `'student'` (chaîne simple, pas de package de rôles) |
| email_verified_at | timestamp nullable | |
| password | varchar(255) | |
| remember_token | varchar(100) nullable | |
| created_at / updated_at | timestamp | |

### sessions
| Colonne | Type |
|---|---|
| id | varchar(255) PK |
| user_id | bigint unsigned nullable (FK → users) |
| ip_address | varchar(45) nullable |
| user_agent | text nullable |
| payload | longtext |
| last_activity | int(11) |

### password_reset_tokens
`email` varchar(255) PK · `token` varchar(255) · `created_at` timestamp

### Tables framework (queues & cache)
`jobs` · `failed_jobs` · `job_batches` · `cache` · `cache_locks` — structure Laravel standard, non métier.

---

## 2. Élèves & Enseignants

### students
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| user_id | bigint unsigned nullable | FK → users (compte de connexion de l'élève) |
| first_name | varchar(255) | |
| last_name | varchar(255) | |
| date_of_birth | date nullable | |
| phone | varchar(255) nullable | |
| address | text nullable | |
| level | varchar(255) | niveau libre, ex : `1AP`, `4AM`, `1AS` |
| parent_name | varchar(255) nullable | |
| parent_phone | varchar(255) nullable | |
| created_at / updated_at | timestamp | |

> ⚠️ `students.level` est une chaîne libre, PAS une FK vers `levels` (migration en cours).

### teachers
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| first_name | varchar(255) | |
| last_name | varchar(255) | |
| speciality | varchar(255) nullable | |
| phone | varchar(255) nullable | |
| email | varchar(255) nullable | |
| address | text nullable | |
| hire_date | date nullable | |
| active | tinyint(1) | 0 = désactivé (ne peut plus être assigné) |
| created_at / updated_at | timestamp | |

### subject_teacher (pivot N—N)
`id` · `subject_id` bigint unsigned (FK → subjects, cascade) · `teacher_id` bigint unsigned (FK → teachers, cascade) · timestamps
— unique : `(subject_id, teacher_id)`

### teacher_level (pivot N—N)
`id` · `teacher_id` bigint unsigned (FK → teachers) · `level_id` bigint unsigned (FK → levels) · timestamps

### levels
| Colonne | Type |
|---|---|
| id | bigint unsigned PK |
| name | varchar(255) |
| code | varchar(255) |
| description | text nullable |
| active | tinyint(1) |
| created_at / updated_at | timestamp |

> ⚠️ Table plus récente : pas encore câblée sur `students.level` / `subjects.level`.

---

## 3. Matières & Inscriptions

### subjects
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| name | varchar(255) | |
| code | varchar(255) | unique |
| description | text nullable | |
| level | varchar(255) | chaîne libre, ex : `1AP` |
| hours_per_week | int(11) | défaut 1 |
| active | tinyint(1) | défaut 1 |
| created_at / updated_at | timestamp | |

### enrollments
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned | FK → students |
| subject_id | bigint unsigned | FK → subjects |
| teacher_id | bigint unsigned | FK → teachers |
| start_date | date | |
| status | varchar(255) | `'active'` |
| payment_type | enum('monthly','vip') | type d'abonnement |
| created_at / updated_at | timestamp | |

### class_sessions (séances / planning)
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned | FK → students |
| subject_id | bigint unsigned | FK → subjects |
| teacher_id | bigint unsigned | FK → teachers |
| day | varchar(255) | |
| start_time / end_time | time | |
| start_date / end_date | date nullable | |
| status | varchar(255) nullable | |
| note | text nullable | |
| created_at / updated_at | timestamp | |

---

## 4. Paiements

### payments
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| receipt_number | varchar(255) | unique (numéro de reçu) |
| student_id | bigint unsigned | FK → students (cascade) |
| subject_id | bigint unsigned nullable | FK → subjects (set null) — voir ⚠️ ci-dessous |
| period | varchar(255) | période, ex : `2026-09` |
| payment_type | enum('monthly','vip') | défaut `monthly` |
| amount_due | decimal(10,2) | |
| amount_paid | decimal(10,2) | défaut 0 |
| remaining_amount | decimal(10,2) | défaut 0 |
| payment_method | varchar(255) nullable | |
| payment_date | date | |
| payment_time | time | |
| note | text nullable | |
| created_at / updated_at | timestamp | |

> ⚠️ `payments.subject_id` n'est créé que par la migration `2026_08_14_133003_...` (driver-guardée : MySQL = MODIFY, sqlite = création via Schema).

### payment_schedules
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned | FK → students |
| period | varchar(255) | |
| amount_due | decimal(10,2) | |
| amount_paid | decimal(10,2) | défaut 0 |
| remaining_amount | decimal(10,2) | défaut 0 |
| status | enum('unpaid','partial','paid') | |
| due_date | date | |
| created_at / updated_at | timestamp | |

### payment_signalements (auto-créés depuis présences/paiements)
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned | FK → students |
| subject_id | bigint unsigned nullable | FK → subjects (set null) |
| payment_id | bigint unsigned nullable | FK → payments |
| period | varchar(255) | |
| amount_remaining | decimal(10,2) | |
| status | enum('pending','sent','resolved') | |
| signalement_date | date | |
| attendance_date | date nullable | date d'absence à l'origine du signalement |
| sent_at | datetime nullable | |
| note | text nullable | |
| created_at / updated_at | timestamp | |

### unpaid_signalements
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned | FK → students |
| payment_id | bigint unsigned nullable | FK → payments |
| period | varchar(255) | |
| amount_due | decimal(10,2) | |
| amount_remaining | decimal(10,2) | |
| status | enum('pending','contacted','paid','cancelled') | |
| reminder_count | int unsigned | défaut 0 |
| last_reminder_at | datetime nullable | |
| note | text nullable | |
| created_at / updated_at | timestamp | |

---

## 5. Présences & Annonces

### attendances
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| student_id | bigint unsigned | FK → students |
| subject_id | bigint unsigned | FK → subjects |
| teacher_id | bigint unsigned | FK → teachers |
| date | date | |
| status | enum('present','absent','late','justified') | |
| note | text nullable | |
| created_at / updated_at | timestamp | |

### announcements
| Colonne | Type | Notes |
|---|---|---|
| id | bigint unsigned PK | |
| title | varchar(255) | |
| content | text | |
| type | enum('info','important','warning') | |
| is_active | tinyint(1) | |
| published_at | timestamp nullable | |
| created_at / updated_at | timestamp | |

### announcement_user (pivot N—N)
`id` · `announcement_id` bigint unsigned (FK → announcements) · `user_id` bigint unsigned (FK → users) · `seen_at` timestamp nullable · timestamps

---

## Relations clés

```
users 1 ── 0..1 students        (students.user_id)
students 1 ── N enrollments N ── 1 subjects
enrollments N ── 1 teachers
subjects N ── N teachers         (subject_teacher, + teacher_id direct sur enrollments)
students 1 ── N payments / attendances / payment_schedules / payment_signalements / unpaid_signalements
students 1 ── N class_sessions
teachers N ── N levels            (teacher_level)
announcements N ── N users        (announcement_user)
```

## Récapitulatif des enums

| Colonne | Valeurs |
|---|---|
| users.role | `admin` \| `student` |
| payments.payment_type / enrollments.payment_type | `monthly` \| `vip` |
| attendances.status | `present` \| `absent` \| `late` \| `justified` |
| payment_schedules.status | `unpaid` \| `partial` \| `paid` |
| payment_signalements.status | `pending` \| `sent` \| `resolved` |
| unpaid_signalements.status | `pending` \| `contacted` \| `paid` \| `cancelled` |
| announcements.type | `info` \| `important` \| `warning` |

## Pièges connus

- `students.level` et `subjects.level` sont des **chaînes libres** ; les tables `levels` + `teacher_level` + modèle `Level` existent mais ne sont pas branchées.
- `payments.subject_id` n'existe que via la migration `2026_08_14_133003` (driver-guardée).
- Les colonnes monétaires sont `decimal(10,2)` ; les dates sont castées `date` ; `payment_time` est une chaîne `time`.
