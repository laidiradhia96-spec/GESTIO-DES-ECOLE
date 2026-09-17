---
paths:
  - routes/web.php
---

# Routes

## Define AJAX routes before resource routes to prevent parameter capture
Laravel resource routes like `Route::resource('payments', ...)` register `{payment}` parameter routes that match any path segment including named AJAX routes like `payments/subjects-by-student`. Define specific GET routes (AJAX endpoints) BEFORE the resource route to prevent them from being captured by `{payment}`. Example: put `payments/subjects-by-student` before `Route::resource('payments', ...)`.
