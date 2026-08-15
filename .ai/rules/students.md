---
paths:
  - 'resources/views/students/**'
---

# Students

## Student create form requires a teacher select per enrollment
students.store validation requires enrollments.*.teacher_id (required + exists). The create form's enrollment rows must include an Enseignant select fed by the AJAX route students.subjects.teachers (GET /subjects/{subject}/teachers). Subjects with no active teacher are rendered disabled with "(aucun enseignant)". When adding enrollments UI, keep this contract or the submission always fails.
