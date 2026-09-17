# Plan: Recherche + Archive des Présences par Élève

## Audit Summary

### Existing Schema (verified)
- **Attendance table**: `id`, `student_id`, `subject_id`, `teacher_id` (nullable), `group_id` (nullable), `date`, `status` (enum: present/absent/late/justified), `note` (nullable), `school_year_id` (nullable), timestamps
- **Unique constraint**: `(student_id, subject_id, group_id, date)`
- **All relationships exist**: `student()`, `group()`, `teacher()`, `subject()`, `schoolYear()`
- **Student::scopeSearch()** already exists — multi-term AND search across `first_name`, `last_name`, `phone`, `parent_name`
- **Routes** are under `['auth', 'admin']` middleware group (line 100 of `web.php`)

### Design Patterns (verified)
- Navy blue `#0B2A55` for primary, gold `#C89B3C` for accents
- `<x-app-layout>` wrapper, `bg-gray-50` page background
- Cards: `bg-white rounded-3xl border border-gray-100 shadow-sm`
- Table headers: `style="background-color:#0B2A55;"` with `text-white text-xs uppercase`
- Status badges: `rounded-full text-xs font-bold` with color variants
- Filters: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-5`
- Inputs: `rounded-xl border-gray-300 focus:border-[#0B2A55] focus:ring-[#0B2A55]`
- Buttons: `px-5 py-2.5 rounded-xl text-white font-semibold shadow-md` with `style="background-color: #0B2A55;"`

---

## Implementation Plan

### Step 1: Create `AttendanceArchiveController`

**File**: `app/Http/Controllers/AttendanceArchiveController.php`

Single `index()` method:

1. Accept query params: `search`, `student_id`, `school_year_id`, `group_id`, `subject_id`, `teacher_id`, `status`, `date_from`, `date_to`
2. If `student_id` provided, load the student and auto-show their history
3. If `search` provided (and no `student_id`), search students via `Student::scopeSearch()` and return a list for selection
4. If a student is selected (via `student_id` or search result), query their attendances with all filters
5. Eager-load relationships: `student`, `group`, `subject`, `teacher`, `schoolYear`
6. Filter server-side using Eloquent query builder
7. Paginate(25) with `withQueryString()`
8. Pass to view: `$attendances`, `$student`, `$students` (search results), `$schoolYears`, `$groups`, `$subjects`, `$teachers`, `$filters`

Key decisions:
- **AJAX search**: Use a simple server-side search with `?search=Ahmed` that returns a JSON list of matching students. The view will show a dropdown of results. When user selects a student, redirect to `?student_id=123`.
- **Alternative (simpler)**: Use a form with `search` param that shows matching students as clickable links. Each link goes to `?student_id=123`. This avoids AJAX complexity.

**Decision**: Go with the simpler approach — form submission shows matching students as a selection list. No AJAX needed.

### Step 2: Create archive view

**File**: `resources/views/attendances/archive.blade.php`

Layout:
1. **Header**: "Archive des présences" with subtitle "Rechercher et consulter l'historique de présence des élèves"
2. **Search section**: Text input "Nom ou prénom de l'élève..." + search button
3. **Student selection** (if search results exist): List of matching students with name, level. Click to select.
4. **Selected student display**: Show selected student name with clear button
5. **Filters section** (shown when student is selected):
   - Année scolaire (dropdown)
   - Groupe (dropdown, filtered to student's groups if possible)
   - Matière (dropdown)
   - Enseignant (dropdown)
   - Date début / Date fin (date inputs)
   - Statut (Tous / Présent / Absent / Retard / Justifié)
6. **Results table**: Date, Élève, Groupe, Matière, Enseignant, Statut
7. **Pagination**
8. **Empty state**: "Recherchez un élève pour afficher son historique de présence" (initial state) or "Aucune présence trouvée" (no results)

### Step 3: Add route

**File**: `routes/web.php`

Add BEFORE the `{attendance}` wildcard routes (line 284):

```php
Route::get(
    '/attendances/archive',
    [AttendanceArchiveController::class, 'index']
)->name('attendances.archive');
```

### Step 4: Add link from attendance index page

**File**: `resources/views/attendances/index.blade.php`

Add a small link/button in the header area (next to "+ Enregistrer une présence"):

```html
<a href="{{ route('attendances.archive') }}"
   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
          bg-[#C89B3C] text-white font-semibold shadow-md hover:opacity-90 transition">
    📋 Archive
</a>
```

### Step 5: Add link from student show page

**File**: `resources/views/students/show.blade.php`

Add a small link in the actions section (line 233, near the Modifier button):

```html
<a href="{{ route('attendances.archive', ['student_id' => $student->id]) }}"
   class="bg-[#0B2A55] text-white px-5 py-2 rounded-lg hover:bg-[#061A33]">
    📋 Historique des présences
</a>
```

### Step 6: Tests

**File**: `tests/Feature/AttendanceArchiveTest.php`

Tests:
1. Search by name returns matching students
2. Student history shows correct attendance records
3. Filter by status returns only matching records
4. Filter by group returns only that group's records
5. Filter by date range works
6. Filter by school year isolates data
7. Multiple groups per student show separate records
8. `?student_id=X` opens directly with that student's history
9. Pagination preserves filters
10. Empty state shows when no student selected
11. "Aucune présence" shows when no matches

---

## Files to Create

| File | Purpose |
|---|---|
| `app/Http/Controllers/AttendanceArchiveController.php` | Archive controller with search + filter logic |
| `resources/views/attendances/archive.blade.php` | Archive view with search, filters, table, pagination |
| `tests/Feature/AttendanceArchiveTest.php` | Tests for archive functionality |

## Files to Modify

| File | Change |
|---|---|
| `routes/web.php` | Add `GET /attendances/archive` route (before `{attendance}` wildcard) |
| `resources/views/attendances/index.blade.php` | Add "📋 Archive" link in header |
| `resources/views/students/show.blade.php` | Add "Historique des présences" link in actions |

## Files NOT Modified

- Attendance model (all relationships already exist)
- AttendanceController (existing page untouched)
- Database migrations (no schema changes)
- Payment/financial system
- Student model (scopeSearch already exists)

## Route Placement

The new route MUST be placed BEFORE the `Route::get('/attendances/{attendance}', ...)` wildcard route at line 284 of `web.php`. Otherwise, `archive` would be interpreted as an `{attendance}` parameter.

## Verification

1. `composer test` — all tests pass
2. Manual: Visit `/attendances/archive` → see search form
3. Manual: Search "Ahmed" → see list of matching students
4. Manual: Click student → see their attendance history with filters
5. Manual: From student show page → click "Historique des présences" → opens archive pre-filtered
6. Manual: Existing attendance page (`/attendances`) still works unchanged
