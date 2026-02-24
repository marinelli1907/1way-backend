# Admin Driver Create/Update – Bug Fix Evidence

## 1) Bug reproduction (trace)

- **Endpoints**:  
  - Create: `POST /admin/driver/store` (form action from `create.blade.php`)  
  - Update: `PUT /admin/driver/update/{id}` (form uses `@method('PUT')` from `edit.blade.php`)
- **Controller**: `Modules\UserManagement\Http\Controllers\Web\New\Admin\Driver\DriverController`  
  - `store(DriverStoreOrUpdateRequest $request)` → `$this->driverService->create($request->validated())`  
  - `update(DriverStoreOrUpdateRequest $request, $id)` → `$this->driverService->update($id, $request->validated() + ['type' => 'web'])`
- **FormRequest**: `Modules\UserManagement\Http\Requests\DriverStoreOrUpdateRequest`  
  - Uses `unique:users,email,{id}` and `unique:users,phone,{id}` so **update** must pass the current driver’s id to avoid “already taken” on unchanged email/phone.

## 2) Root cause

- **DriverStoreOrUpdateRequest** used `$id = $this->id`. The edit form does **not** send an `id` input, and the route parameter `{id}` is **not** available as `$this->id` in the FormRequest. So `$id` was always `null` on update.
- **Effect**: On **Edit Driver**, validation rules became `unique:users,email,` and `unique:users,phone,`, which do **not** ignore the current record. Saving without changing email/phone caused validation to fail (email/phone “already taken”), so the update never persisted.
- **DriverService::update** (else branch): `$existingDocuments` from the web form is an **array**; the code did `json_decode($existingDocuments, true)`, which returns `null` for an array. That could overwrite `other_documents` with `null`.

## 3) Fixes applied

| File | Change |
|------|--------|
| `Modules/UserManagement/Http/Requests/DriverStoreOrUpdateRequest.php` | Use `$id = $this->route('id')` so update unique rules exclude the current driver. |
| `Modules/UserManagement/Service/DriverService.php` | In the else branch for `existing_documents`, use `is_array($existingDocuments) ? $existingDocuments : (array) json_decode($existingDocuments, true)` so web form array is not json_decoded. |

## 4) Verification

- **Create**: Still uses `$request->validated()`; password is hashed in `DriverService::create()`; user, driverDetails, and userAccount are created in a transaction.
- **Update**: With the FormRequest fix, submitting the edit form with unchanged email/phone now passes validation and the driver record is updated; password is only updated when provided (`DriverService::update`).
- **Regression tests**: `tests/Feature/AdminDriverCreateUpdateTest.php` – admin driver create (DB insert + password hashed), admin driver update (DB update), and validation (bad payload returns errors).

## 5) Known driver command

- **Command**: `php artisan drivers:ensure-known [--force] [--test]`
- **Daniel Marinelli (primary)**: phone `14404889279`, password `Jake5419`. Idempotent; in production requires `--force`.
- **Test driver (secondary)**: phone `15555550124`, password `Jake5419`. Created only with `--test`; in production also requires `--force`.
- **Documentation**: See command `--help` and this file.
