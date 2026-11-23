# Smart Kos — Step by Step Implementation Guide

## 1. Purpose

This document defines the implementation order for Smart Kos.

The project must be developed incrementally from the existing TailAdmin Laravel template.

The agent must follow this document together with:

```text
AGENTS.md
ERD.md
```

The implementation order is important because later features depend on earlier database, authentication, authorization, and business logic.

---

# 2. Core Development Principle

Smart Kos must be built:

```text
TailAdmin
    ↓
Foundation
    ↓
Authentication
    ↓
Database
    ↓
Authorization
    ↓
Guest
    ↓
Tenant
    ↓
Staff
    ↓
Admin
    ↓
Payment
    ↓
Complaints
    ↓
Finance
    ↓
Automation
    ↓
Testing
    ↓
Deployment
```

Do not attempt to build every feature at the same time.

---

# 3. Mandatory TailAdmin Rule

Before creating any UI:

```text
1. Search the existing TailAdmin template.
2. Find the closest page/component/example.
3. Copy the TailAdmin structure.
4. Put the copied/adapted version into the Smart Kos extension path.
5. Modify only the content, data, and behavior required by Smart Kos.
```

Example:

```text
TailAdmin Dashboard Example
        ↓
       COPY
        ↓
resources/views/admin/dashboard.blade.php
        ↓
Modify Smart Kos content
```

Another example:

```text
TailAdmin Modal
        ↓
       COPY
        ↓
resources/views/components/smart-kos/modal.blade.php
        ↓
Adapt for Smart Kos
```

Do not build the equivalent UI from scratch when a suitable TailAdmin example already exists.

---

# 4. Initial Project Preparation

## 4.1 Obtain the TailAdmin Template

The project starts from:

```text
TailAdmin/tailadmin-laravel
```

The initial repository/template acquisition is a project-owner/setup activity.

Do not use a forbidden Git/network command to bypass the command policy in `AGENTS.md`.

After the template is available locally, verify that the original structure is still present.

---

## 4.2 Verify Laravel Environment

Check:

```bash
php -v
composer -V
node -v
npm -v
```

The project should follow the versions required by the checked-out TailAdmin repository.

Do not downgrade Laravel to solve implementation problems.

---

# 5. Baseline the TailAdmin Template

Before implementing Smart Kos:

```text
1. Open the project.
2. Run the existing TailAdmin application.
3. Verify the original dashboard.
4. Verify the existing sidebar.
5. Verify the existing header.
6. Verify existing reusable components.
7. Verify dark mode.
8. Verify RTL behavior.
9. Verify existing demo pages.
10. Confirm that TailAdmin works before Smart Kos modifications.
```

Use the approved development commands from `AGENTS.md`.

Recommended:

```bash
composer run dev
```

or the relevant development scripts already defined by the project.

Do not modify TailAdmin during this baseline stage.

---

# 6. Create Project Documentation

Before feature implementation, ensure these files exist at the project root:

```text
AGENTS.md
ERD.md
stepbystep.md
```

Responsibilities:

```text
AGENTS.md
→ agent behavior, protected paths, commands, development rules

ERD.md
→ database architecture and relationships

stepbystep.md
→ implementation sequence
```

These documents must remain consistent.

---

# 7. Phase 1 — Project Foundation (Completed)

## Goal

Prepare Smart Kos without changing the TailAdmin architecture.

### Tasks

```text
1. Confirm Laravel version.
2. Confirm frontend build system.
3. Confirm MySQL connection.
4. Confirm environment variables.
5. Confirm application URL.
6. Confirm session configuration.
7. Confirm storage configuration.
8. Confirm mail configuration placeholders.
9. Confirm queue configuration placeholders.
```

Do not add external dependencies during this phase.

---

# 8. Phase 2 — Database Foundation (Completed)

Implement the database according to `ERD.md`.

Migration order:

```text
1. users
2. admin_google_accounts
3. locations
4. rooms
5. tenant_profiles
6. room_assignments
7. midtrans_va_accounts
8. va_payments
9. manual_payments
10. payment_reminders
11. posts
12. post_targets
13. complaints
14. complaint_actions
15. expense_categories
16. expenses
17. payroll_records
18. cash_advances
19. financial_records
```

Do not create tables that were intentionally excluded from the ERD.

Examples:

```text
roles
permissions
menus
role_permissions
```

are not part of the initial architecture.

---

# 9. Phase 3 — Eloquent Models (Completed)

Create models corresponding to the ERD.

Expected core models:

```text
User
AdminGoogleAccount

Location
Room

TenantProfile
RoomAssignment

MidtransVAAccount
VAPayment
ManualPayment
PaymentReminder

Post
PostTarget

Complaint
ComplaintAction

ExpenseCategory
Expense
PayrollRecord
CashAdvance
FinancialRecord
```

Define relationships carefully.

Examples:

```text
Location
    hasMany Room

Room
    belongsTo Location
    hasMany RoomAssignment

TenantProfile
    belongsTo User
    hasMany RoomAssignment
    hasMany Complaint

RoomAssignment
    belongsTo Room
    belongsTo TenantProfile
```

Do not put unnecessary business logic into models.

---

# 10. Phase 4 — Authentication Foundation (Completed)

## Goal

Create one authentication system for:

```text
admin
staff
penyewa
```

Guest does not authenticate.

Normal login:

```text
/login
```

Credentials:

```text
username
password
```

Use Laravel's session-based web authentication.

Do not create separate login systems for each role.

---

# 11. Phase 5 — Authentication UI (Completed)

Use the existing TailAdmin authentication/fullscreen design.

Mandatory process:

```text
Existing TailAdmin auth page
        ↓
       COPY
        ↓
Smart Kos authentication page
        ↓
Modify wording + form fields
```

Normal login contains only:

```text
Username
Password
Login
```

Do not add Google login to the normal login page.

Do not add a visible:

```text
Forgot Password?
Admin Recovery
Continue with Google
```

link unless explicitly requested later.

---

# 12. Phase 6 — Role Authorization (Completed)

Implement role middleware.

Roles:

```text
admin
staff
penyewa
```

Example:

```php
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        //
    });
```

Equivalent protection is required for:

```text
staff
penyewa
```

Test direct URL access.

Example:

```text
Staff → /admin/finance
Tenant → /admin/finance
Tenant → /staff/complaints
```

must be rejected.

---

# 13. Phase 7 — Hardcoded Role Navigation (Completed)

Create:

```text
app/Helpers/SmartKosMenuHelper.php
```

The menu must be defined in application code.

Do not create:

```text
menus table
permissions table
dynamic menu builder
```

Recommended menu structure:

### Guest

```text
Landing Page
Lokasi & Kamar
Login
```

### Penyewa

```text
Dashboard
Pembayaran
Aduan
Akun
```

### Staff

```text
Dashboard
Status Pembayaran Penyewa
Tindakan Aduan
Akun
```

### Admin

```text
Dashboard
Status Penyewa
Tindakan Aduan
Keuangan
Lokasi & Kamar
Pelanggan & Staff
Akun
```

> **Catatan Fitur Keuangan**: Menu sidebar Admin tetap ringkas dengan hanya menampilkan satu menu **Keuangan** (`/admin/finance`). Seluruh fitur finansial (Laporan Keuangan, Pengeluaran, Gaji & Kasbon, Arus Kas, Piutang) dikelompokkan dalam sistem **tab** di dalam halaman `/admin/finance`, bukan dipisah menjadi menu-menu baru di sidebar.

Connect the menu to the existing TailAdmin sidebar with the smallest possible modification.

Do not replace the entire sidebar.

---

# 14. Phase 8 — Guest Landing Page (Completed)

## Goal

Create the public Smart Kos entry point.

Route:

```text
/
```

Use the closest TailAdmin page/layout pattern.

Guest landing page contains:

```text
Smart Kos
Contact staff
Contact owner
Button: View Locations and Rooms
Login
```

Guest has no private information access.

Do not display tenant names.

---

# 15. Phase 9 — Guest Location and Room View (Completed)

Create:

```text
Location list
Room availability
```

Flow:

```text
Landing Page
      ↓
View Locations
      ↓
Select Location
      ↓
Room Popup / Room List
```

Room visualization:

```text
empty    → white/green presentation according to approved UI design
occupied → red presentation
```

Important:

The public page must only expose room availability.

Do not expose:

```text
tenant name
payment information
rent history
financial data
complaints
```

---

# 16. Phase 10 — Location and Room Management (Completed)

Admin functionality:

```text
Create location
Edit location
Delete location
Add rooms
Remove rooms
Edit room
```

Location fields:

```text
name
address
photo
facility description
Google Maps location
number of floors
```

Location photo organization:

```text
location photos (exterior/facility, tagged 'Foto Lokasi')
room photos (sample room views, tagged 'Foto Kamar')
```

Photo gallery ordering:

```text
Photos tagged 'Foto Lokasi' are sorted to appear first (leftmost/initial slides).
Photos tagged 'Foto Kamar' follow afterwards.
```

Room fields:

```text
location
floor
room number
rent amount
```

All new rooms initially have:

```text
no tenant
```

Room assignment is performed separately.

---

# 17. Phase 11 — Sensitive Location/Room Actions (Completed)

These operations require additional password confirmation:

```text
Delete location
Add/remove rooms
Other destructive location operations
```

Flow:

```text
Admin action
    ↓
Password confirmation
    ↓
Authorization
    ↓
Action
```

Frontend confirmation alone is not sufficient.

---

# 18. Phase 12 — Tenant Account Management (Completed)

Admin can:

```text
Create tenant account
Edit tenant account
Reset tenant password
Deactivate tenant account
Reactivate tenant account
```

Tenant account:

```text
role = penyewa
status = active/inactive
```

Tenant-specific profile information belongs in:

```text
tenant_profiles
```

Authentication credentials remain in:

```text
users
```

---

# 19. Phase 13 — Staff Account Management (Completed)

Admin can:

```text
Create staff account
Edit staff account
Reset staff password
Deactivate staff account
Reactivate staff account
```

Staff account:

```text
role = staff
status = active/inactive
```

Do not delete historical financial information when a staff account becomes inactive.

---

# 20. Phase 14 — Account Lifecycle (Completed)

Implement the account lifecycle:

```text
active
   ↓
inactive
   ↓
7+ days according to application policy
   ↓
eligible for deletion
```

Admin can reactivate an inactive account.

Permanent deletion must not destroy historical financial records.

Do not implement automatic hard deletion until the retention policy is explicitly finalized.

---

# 21. Phase 15 — Tenant Room Assignment (Completed)

Admin page:

```text
Status Penyewa
```

Features:

```text
Select location
View unassigned tenants
View assigned tenants
Assign tenant to room
Select monthly or yearly payment cycle
Select complete start and end dates
Change tenant room
See assigned room
```

Flow:

```text
Tenant
   ↓
Select Location
   ↓
Select Room
   ↓
Create Room Assignment
   ↓
Room becomes occupied
```

Prevent:

```text
one room → multiple active tenants
```

unless a future requirement explicitly allows it.

---

# 22. Phase 16 — Room Status Visualization (Completed)

Room layout:

```text
2 floors × 5 rooms
```

can be displayed as:

```text
Floor 2:
[101] [102] [103] [104] [105]

Floor 1:
[001] [002] [003] [004] [005]
```

Each room card shows payment/occupancy state where appropriate.

Staff and Admin have different detail levels.

---

# 23. Phase 17 — Tenant Dashboard (Completed)

Tenant dashboard contains:

```text
role
tenant name
floor
room number
payment deadline
payment status
location-relevant announcements
```

Payment deadline display:

```text
more than 7 days → gray
less than 7 days → yellow
today             → red
```

The dashboard may be filtered:

```text
all locations
specific location
```

Tenant should only receive data belonging to the tenant's own active assignment.

---

# 24. Phase 18 — Tenant Account Page (Completed)

Tenant can change:

```text
personal information
password
```

Use appropriate Laravel password validation and confirmation.

Do not allow tenant to change:

```text
role
account status
assigned room
rent amount
```

unless a future requirement explicitly authorizes it.

---

# 25. Phase 19 — Staff Dashboard (Completed)

Staff dashboard:

```text
role
staff name
announcements
create post
payment deadlines
location filter
```

Staff can see payment status but not the rental nominal amount where the business requirement says it should be hidden.

---

# 26. Phase 20 — Staff Post Creation (Completed)

Staff can create announcements targeted to:

```text
all locations
specific location
specific room
```

Flow:

```text
Create Post
    ↓
Select target
    ↓
Write content
    ↓
Publish
```

Database:

```text
posts
post_targets
```

Validate the target combination server-side.

---

# 27. Phase 21 — Admin Dashboard (Completed)

Admin dashboard contains:

```text
admin role
admin name
announcements
create post
financial summary
empty rooms
occupied rooms
payment deadlines
```

Financial summary can later include:

```text
net income
total income
total expenses
empty rooms
occupied rooms
```

Use the TailAdmin chart/card components by copying/adapting existing examples.

---

# 28. Phase 22 — Payment Architecture (Completed)

Payment is divided into two independent mechanisms:

```text
                    PAYMENT
                       │
             ┌─────────┴─────────┐
             │                   │
             ▼                   ▼
      MIDTRANS VA         MANUAL TRANSFER
             │                   │
             ▼                   ▼
       va_payments       manual_payments
```

Do not merge them into one operational payment table.

---

# 29. Phase 23 — Midtrans VA Account (Completed)

Create:

```text
midtrans_va_accounts
```

Purpose:

```text
Store the room's Midtrans Virtual Account configuration.
```

Fields include:

```text
bank_code
va_number
external_id
amount
status
expires_at
```

The Midtrans secret/API credentials must be stored in environment/configuration.

Never hardcode them.

---

# 30. Phase 24 — Midtrans VA Payment (Completed)

Create:

```text
va_payments
```

Store:

```text
order_id
transaction_id
va_account_id
room_assignment_id
amount
transaction_status
transaction_time
paid_at
```

The application must be able to distinguish:

```text
pending
paid/settlement
expired
cancelled/denied
refund
```

according to the normalized application status.

Midtrans-specific provider statuses must not be mixed with manual payment statuses.

---

# 31. Phase 25 — Manual Transfer (Completed)

Create:

```text
manual_payments
```

Tenant flow:

```text
Payment
   ↓
Manual Transfer
   ↓
Show bank name
Show bank account number
   ↓
Upload proof
   ↓
Submit
```

Proof upload is mandatory.

Submission is limited to:

```text
once every 10 minutes
```

The server must enforce the rate limit.

---

# 32. Phase 26 — Manual Payment Verification (Completed)

Admin sees:

```text
Pending manual payments
```

Admin actions:

```text
Approve
Reject
```

If rejected:

```text
rejection_reason
```

should be recorded.

Example:

```text
Tenant
   ↓
Manual Payment
   ↓
pending
   ↓
Admin
 ┌───────┴───────┐
 ▼               ▼
Approve         Reject
 ▼               ▼
Approved    Rejection Reason
```

---

# 33. Phase 27 — Payment History (Completed)

Both payment mechanisms must preserve history.

```text
va_payments
manual_payments
```

must not be deleted simply because:

```text
tenant leaves
room becomes empty
tenant account becomes inactive
staff account changes
rent amount changes
```

Historical records must remain reportable.

---

# 34. Phase 28 — Financial Reporting Layer (Completed)

Create:

```text
financial_records
```

This is the reporting layer for:

```text
income
expense
cash flow
profit/loss
monthly reporting
location reporting
```

The Finance report must let Admin select one reporting scope:

```text
All locations (overall)
One location
One room
One person
```

The selected scope filters the financial records and their source records. A
room scope belongs to its location. A person scope must support an individual
tenant or staff member when the source transaction identifies that person;
for example, rent payments are associated with the tenant and payroll is
associated with the staff member. Keep the existing overall view as the
default, and do not duplicate source transaction fields in `financial_records`
just to implement these filters.

Do not duplicate every operational field from payment/expense tables into this table.

Instead maintain a source reference:

```text
reference_type
reference_id
```

---

# 35. Phase 29 — Expense Management (Completed)

Admin can record:

### Operational Expenses

```text
electricity
water
internet
security fee
```

### Maintenance / Incidental Expenses

```text
AC repair
lamp replacement
water pump repair
other unexpected repairs
```

Use:

```text
expense_categories
expenses
```

Each expense should contain:

```text
title
description
amount
expense_date
location
category
receipt
recorded_by
```

### UI Architecture — Unified Tab-Based Finance Hub (`/admin/finance`)

Seluruh fitur modul keuangan dikelompokkan ke dalam menu tunggal **Keuangan** (`/admin/finance`) menggunakan sistem **tab**:
- Tidak membuat menu sidebar baru (seperti "Pengeluaran" atau "Payroll").
- Tab 1: **Laporan Keuangan & Buku Kas** (`?tab=report` / default) — Menampilkan ringkasan income/expense, filter cakupan (All/Location/Room/Person), buku kas (financial ledger), serta histori pembayaran VA & Manual.
- Tab 2: **Pengeluaran** (`?tab=expenses`) — Menampilkan daftar pengeluaran operasional & pemeliharaan, filter kategori & lokasi, modal/tombol catat pengeluaran baru, upload struk, serta aksi edit/hapus.
- Tab 3: **Gaji & Kasbon** (`?tab=payroll` — Phase 30) — Penggajian staff dan pencatatan kasbon.
- Tab 4: **Tunggakan & Piutang** (`tab=arrears` — Phase 33) — Monitoring piutang sewa.

Peralihan antar tab dapat menggunakan query parameter (misal `?tab=expenses`) atau Alpine.js tab switcher agar navigasi tetap instan, bersih, dan mudah di-bookmark.

---

# 36. Phase 30 — Payroll and Cash Advance (Completed)

Implement:

```text
payroll_records
cash_advances
```

Payroll supports:

```text
gross salary
cash advance deduction
net salary
payment date
```

Cash advance supports:

```text
amount
advance date
status
notes
```

Historical records must survive staff deactivation.

### Summary of Implementation:
- **Routes & Controllers**:
  - `Admin\PayrollController`: `index`, `store`, `destroy` routes under `/admin/payroll`.
  - `Admin\CashAdvanceController`: `store`, `updateStatus`, `destroy` routes under `/admin/cash-advances`.
- **Form Requests**:
  - `PayrollRequest`: validates staff_id, gross_amount, cash_advance_deduction (lte:gross_amount), payment_date, notes.
  - `CashAdvanceRequest`: validates staff_id, amount, advance_date, notes.
- **Financial Synchronization**:
  - Integrated with `FinancialRecordSyncService` and `FinancialRecord`.
  - Payroll net salary and cash advance disbursements are recorded as `expense`.
  - Cash advance deduction in payroll automatically settles outstanding staff cash advances chronologically.
  - Deleting payroll or cash advance cleans up corresponding `financial_records`.
- **Modular Views**:
  - Integrated into single Admin finance hub Tab: `Gaji & Kasbon` (`/admin/finance?tab=payroll` -> `/admin/payroll`).
  - `payroll-hub.blade.php`: Orchestrator view with Alpine modal triggers.
  - `payroll-metrics.blade.php`: Summary cards for net salary, gross salary, cash advance deductions, and outstanding advances.
  - `payroll-filters.blade.php`: Filters for staff and period (month/year/all).
  - `payroll-table.blade.php`: History of payroll with delete modal.
  - `cash-advance-table.blade.php`: List of cash advances with status switcher and delete modal.
  - `payroll-modal.blade.php`: Modal to record staff salary with real-time net salary calculator.
  - `cash-advance-modal.blade.php`: Modal to record cash advance.
- **Testing**:
  - `PayrollManagementTest`: 13 feature tests covering access control, CRUD, auto-settlement, financial records sync, filter scoping, and lifecycle deletion protection for staff with payroll records. All passed.

---

# 37. Phase 31 — Cash Flow (Completed)

Build monthly visualization:

```text
Income vs Expense
```

Example:

```text
Monthly Cash Flow

Income
████████████████████

Expense
██████████
```

Use the existing TailAdmin chart example as the starting UI.

The chart must use actual database data.

### Summary of Implementation:
- **Routes & Controller**:
  - `Admin\CashFlowController`: `index` under `/admin/cash-flow` (named `admin.cash-flow.index`).
  - Redirect from `/admin/finance?tab=cash-flow` to `/admin/cash-flow`.
- **Navigation & Tabs**:
  - Keuangan tabs updated with Tab 4: **Arus Kas** (`cash-flow`).
  - Admin sidebar highlights `Keuangan` menu when navigating to `/admin/cash-flow`.
- **Backend Data Aggregation**:
  - Auto-sync via `FinancialRecordSyncService::syncIfNeeded()`.
  - Database-agnostic monthly aggregation (12 months) computing total income, total expense, net cash flow, and monthly status (surplus, deficit, balanced).
  - Scope filtering by budget year (`year`), kos location (`location_id`), and highlighted month (`month`).
- **Modular Views**:
  - `cash-flow-hub.blade.php`: Orchestrator layout.
  - `cash-flow-metrics.blade.php`: KPI summary cards (Total Arus Masuk, Total Arus Keluar, Arus Kas Bersih, Rata-rata Arus Bulanan).
  - `cash-flow-filters.blade.php`: Filter bar by year, location, and highlight month.
  - `cash-flow-chart.blade.php`:
    - Responsive horizontal comparison bars for active month showing direct scale of Income vs Expense.
    - 12-month side-by-side column bar chart using ApexCharts with responsive tooltip formatting and dark mode listener.
  - `cash-flow-table.blade.php`: Complete 12-month tabular ledger with surplus/deficit badges and yearly summary row.
- **Testing**:
  - `tests/Feature/Admin/CashFlowTest.php`: 8 feature tests covering guest/tenant/staff protection, metric aggregation, surplus/deficit calculation, year filtering, location filtering, month highlighting, and tab redirection. All passed.

---

# 38. Phase 32 — Profit and Loss (Completed)

Admin can view:

```text
Total Income
- Total Expenses
----------------
Net Profit/Loss
```

Example:

```text
Income       Rp 30.000.000
Expenses     Rp 10.000.000
---------------------------
Net Income   Rp 20.000.000
```

Do not hardcode financial calculations in Blade.

Calculate from the application/data layer.

### Summary of Implementation:
- **Routes & Controller**:
  - `Admin\ProfitLossController`: `index` under `/admin/profit-loss` (named `admin.profit-loss.index`).
  - Redirect from `/admin/finance?tab=profit-loss` to `/admin/profit-loss`.
- **Navigation & Tabs**:
  - Integrated into Keuangan tabs with Tab 5: **Laba Rugi** (`profit-loss`).
  - Single sidebar menu `Keuangan` highlights properly when on `/admin/profit-loss*`.
- **Application Layer Calculations**:
  - All calculations (total income, total expenses, net profit/loss, status surplus/deficit/break-even, profit margin %, and expense ratio %) are strictly computed in `ProfitLossController`.
  - Detailed breakdown of revenue (Midtrans VA, Manual Transfer, other income) and expenses (categorized operational expenses, staff payroll, staff cash advances).
  - Scope filtering by month (`month`), year (`year`), all-time (`all`), and location (`location_id`).
- **Modular Views**:
  - `profit-loss-hub.blade.php`: Orchestrator layout extending `layouts.app`.
  - `profit-loss-metrics.blade.php`: KPI summary cards (Total Pendapatan, Total Beban, Laba/Rugi Bersih, Margin Laba Bersih).
  - `profit-loss-filters.blade.php`: Filter bar for period and kos location.
  - `profit-loss-statement.blade.php`: Formal Income Statement structure with line items and mathematical calculation box.
  - `profit-loss-breakdown.blade.php`: Proportion analysis of income sources and expense categories.
- **Testing**:
  - `tests/Feature/Admin/ProfitLossTest.php`: 9 feature tests covering guest/tenant/staff protection, accurate profit and loss calculation at application layer, month/year/location filtering, and tab redirection. All passed.

---

# 39. Phase 33 — Tenant Receivables / Arrears (Completed)

Create an admin view for:

```text
Tenant
Location
Room
Payment due date
Outstanding amount
Days overdue
Total arrears
```

Sort automatically:

```text
most overdue
        ↓
least overdue
```

Allow filtering by:

```text
location
```

### Summary of Implementation:
- **Routes & Controller**:
  - `Admin\ArrearsController`: `index` under `/admin/arrears` (named `admin.arrears.index`).
  - Redirect from `/admin/finance?tab=arrears` to `/admin/arrears`.
- **Navigation & Tabs**:
  - Keuangan tabs updated with Tab 6: **Tunggakan & Piutang** (`arrears`).
  - Single sidebar menu `Keuangan` highlights properly when on `/admin/arrears*`.
- **Backend Data Aggregation & Automated Sorting**:
  - Automatically identifies overdue tenants where rent is unpaid for current period and no payment is pending verification (`getDaysUntilDue() < 0`).
  - Automatic sorting strictly from **most overdue** ($\downarrow$) **least overdue** (`sortByDesc('days_overdue')`).
  - Location filtering (`location_id`) and search query for tenant name/phone and room number.
  - Computes total arrears amount, total overdue tenant count, maximum days overdue, and average overdue days at the application layer.
- **Modular Views**:
  - `arrears-hub.blade.php`: Orchestrator layout extending `layouts.app`.
  - `arrears-metrics.blade.php`: KPI summary cards (Total Nominal Piutang, Penyewa Menunggak, Keterlambatan Terlama, Rata-rata Terlambat).
  - `arrears-filters.blade.php`: Filter bar for kos location and search box.
  - `arrears-table.blade.php`: Detailed tabular ledger showing Tenant, Location, Room, Payment Due Date, Days Overdue with urgency badge, Outstanding Amount, Total Arrears, and pre-filled WhatsApp billing reminder trigger.
- **Testing**:
  - `tests/Feature/Admin/TenantArrearsTest.php`: 8 feature tests covering guest/tenant/staff protection, identification of overdue tenants, accurate calculation of outstanding and total arrears, automatic sorting from most overdue to least overdue, location filtering, exclusion of paid and pending verification tenants, and tab redirection. All passed.

---

# 40. Phase 34 — Financial Recap (Completed)

Admin can switch between:

```text
Tenant recap
Room recap
```

The system should be able to calculate:

```text
total payments
total outstanding
total expenses
net amount
```

Use database queries/services rather than hardcoded UI values.

## Summary of Implementation

### Files Created
- `app/Http/Controllers/Admin/FinancialRecapController.php` — Controller with `index()` supporting `view` (tenant|room), `location_id`, and `search` query params. All KPI metrics computed in the application layer from `FinancialRecord` and `RoomAssignment` models.
- `resources/views/admin/financial-recap/financial-recap-hub.blade.php` — Main view orchestrator with view-mode toggle (Per Penyewa / Per Kamar).
- `resources/views/admin/financial-recap/financial-recap-metrics.blade.php` — 4 KPI cards: Total Pembayaran Diterima, Total Piutang Tertunggak, Total Beban Operasional, Net Amount.
- `resources/views/admin/financial-recap/financial-recap-filters.blade.php` — Filter bar with location dropdown and search input (preserves current view mode).
- `resources/views/admin/financial-recap/financial-recap-tenant-table.blade.php` — Table showing per-tenant financial recap (payments, outstanding, net amount).
- `resources/views/admin/financial-recap/financial-recap-room-table.blade.php` — Table showing per-room financial recap with proportional expense allocation.
- `tests/Feature/Admin/FinancialRecapTest.php` — 16 feature tests covering access control, view modes, metric calculations, filtering, and finance tab redirect.

### Files Modified
- `routes/web.php` — Added `GET /admin/financial-recap` route with name `admin.financial-recap.index`.
- `app/Http/Controllers/Admin/FinanceController.php` — Added `?tab=recap` redirect to financial recap page.
- `resources/views/components/smart-kos/finance-tabs.blade.php` — Added Tab 7 (Rekapitulasi) and removed stale disabled "Tunggakan" placeholder.
- `resources/views/layouts/sidebar.blade.php` — Highlighted Keuangan sidebar item for `/admin/financial-recap*` routes.

### Test Results
- Phase 34 specific: **16 tests, 61 assertions — all passing**
- Full suite: **272 tests, 1172 assertions — all passing**

---

# 41. Phase 35 — Complaint Submission (Completed)

Tenant complaint page:

```text
Title
Description
Optional image
Submit
```

Submission limit:

```text
one submission every 10 minutes
```

Server-side validation is mandatory.

## Summary of Implementation

### Files Created
- `app/Http/Requests/Tenant/ComplaintRequest.php` — FormRequest validating `title` (required, max:255), `description` (required), and `image` (nullable, image, mimes:jpeg,png,jpg,webp, max:4096).
- `app/Http/Controllers/Tenant/ComplaintController.php` — Controller providing `index` (displaying active room, submission form, status metrics, and complaint history) and `store` (enforcing strict 10-minute rate limit on server side and storing optional images).
- `resources/views/penyewa/complaints/complaint-submission.blade.php` — Main orchestrator view with room banner, alerts, and 2-column responsive layout.
- `resources/views/penyewa/complaints/complaint-metrics.blade.php` — Metric cards (Total, Menunggu, Sedang Dikerjakan, Selesai).
- `resources/views/penyewa/complaints/complaint-form.blade.php` — Form with rate limit cooldown banner, file upload, and validation feedback.
- `resources/views/penyewa/complaints/complaint-history.blade.php` — History cards with status badges, admin priority highlights, image previews, and staff action logs.
- `tests/Feature/Tenant/TenantComplaintTest.php` — 15 comprehensive feature tests covering authorization, view, submission, rate limiting, and staff action visibility.

### Files Modified
- `routes/web.php` — Connected `penyewa.complaints.index` and `penyewa.complaints.store` to `Tenant\ComplaintController`.
- `resources/views/penyewa/complaints/index.blade.php` — Updated to include `complaint-submission.blade.php`.

### Integration with Financial Module
- Facilities and rooms incur maintenance expenses upon repair. In Phase 36 (Complaint Handling), maintenance costs resulting from resolving complaints are recorded into the Unified Finance Hub (`expenses` table under Maintenance/Perbaikan category and `financial_records` as expense).
- Because complaints are associated with rooms and locations, repair expenses recorded directly reflect in **Cash Flow** (Phase 31), **Profit & Loss** (Phase 32), and **Room Financial Recap** (Phase 34) under allocated room expenses.

### Test Results
- Phase 35 specific: **15 tests, 58 assertions — all passing**
- Full suite: **287 tests, 1230 assertions — all passing** (zero regressions)

---

# 42. Phase 36 — Complaint Handling (Completed)

Staff/Admin see complaint cards:

```text
Complaint
↓
Click
↓
Popup
↓
Action description
Optional image
Status
```

Status:

```text
belum
dalam_perbaikan
selesai
```

Changing to:

```text
selesai
```

requires confirmation.

## Summary of Implementation

### Files Created
- `app/Http/Requests/ComplaintActionRequest.php` — FormRequest validating `action_description` (required), `status` (required, in:belum,dalam_perbaikan,selesai), `image` (nullable, max:4096), `cost` (nullable, min:0), and strict conditional validation requiring `confirm_selesai` (accepted) when status is `selesai`.
- `app/Http/Controllers/Staff/ComplaintController.php` — Controller for staff handling complaints: listing complaints with status & location filters, search, metrics, and recording complaint actions with optional maintenance expense sync.
- `app/Http/Controllers/Admin/ComplaintController.php` — Controller for admin handling complaints: listing complaints with filters, metrics, recording actions, and toggling highlight priority.
- `resources/views/staff/complaints/complaint-handling.blade.php` — Staff main orchestrator view.
- `resources/views/staff/complaints/complaint-metrics.blade.php` — Metrics summary for staff.
- `resources/views/staff/complaints/complaint-filters.blade.php` — Filter bar for staff (status, location, search).
- `resources/views/staff/complaints/complaint-cards.blade.php` — Responsive cards grid showing complaint information and detail/action trigger.
- `resources/views/staff/complaints/complaint-action-modal.blade.php` — Alpine.js popup modal showing full complaint details, action history timeline, action form with optional repair expense, and mandatory confirmation when status is `selesai`.
- `resources/views/admin/complaints/complaint-management.blade.php` — Admin main orchestrator view.
- `resources/views/admin/complaints/complaint-metrics.blade.php` — Metrics summary for admin.
- `resources/views/admin/complaints/complaint-filters.blade.php` — Filter bar for admin.
- `resources/views/admin/complaints/complaint-cards.blade.php` — Cards grid with Admin Highlight toggle button.
- `resources/views/admin/complaints/complaint-action-modal.blade.php` — Action popup modal for admin.
- `tests/Feature/Staff/StaffComplaintHandlingTest.php` — 9 feature tests for staff complaint handling, filtering, confirmation on completion, and financial expense sync.
- `tests/Feature/Admin/AdminComplaintHandlingTest.php` — 6 feature tests for admin complaint management, status updates, completion confirmation, and highlight toggling.

### Files Modified
- `routes/web.php` — Connected `/admin/complaints`, `/admin/complaints/{complaint}/actions`, `/admin/complaints/{complaint}/highlight`, and `/staff/complaints`, `/staff/complaints/{complaint}/actions`.
- `resources/views/staff/complaints/index.blade.php` — Included `complaint-handling`.
- `resources/views/admin/complaints/complaint-management.blade.php` — Replaced dummy page with real modular feature views.

### Integration with Financial Module
- When recording a complaint action, staff or admin can optionally specify `cost` (biaya perbaikan / pembelian material).
- If filled, the system automatically creates an `Expense` categorized under `Perbaikan & Pemeliharaan Fasilitas` (`type: maintenance`) assigned to the complaint's room/location, and creates a corresponding `FinancialRecord` (`type: expense`).
- This ensures maintenance expenses incurred during repair immediately reflect in the Unified Finance Hub (`/admin/finance`), Cash Flow (Phase 31), Profit & Loss (Phase 32), and Room Financial Recap (Phase 34).

### Test Results
- Phase 36 specific: **15 tests (9 staff + 6 admin), 60 assertions — all passing**
- Full suite: **302 tests, 1290 assertions — all passing** (zero regressions)

---

# 43. Phase 37 — Complaint Highlight (Completed)

Admin can highlight complaints for staff.

Example:

```text
highlighted = true
```

The staff interface should make highlighted complaints visually noticeable.

Use existing TailAdmin badges, alerts, cards, or emphasis styles.

Do not create a completely new visual framework.

## Summary of Implementation

### Files Created
- `tests/Feature/Admin/ComplaintHighlightTest.php` — 9 comprehensive feature tests verifying admin highlight toggle, authorization restrictions (guest, staff, and tenant cannot toggle), visual noticeability in staff interface, automatic sorting to top of list, and dedicated filtering by highlighted complaints.

### Files Enhanced & Modified
- `app/Http/Controllers/Staff/ComplaintController.php` — Added `highlighted` filter support (`?highlighted=1`) and ordering by `highlighted` first (`->orderByDesc('highlighted')`).
- `app/Http/Controllers/Admin/ComplaintController.php` — Provided `toggleHighlight(Complaint $complaint)` endpoint for Admin and added `highlighted` filter support.
- `resources/views/staff/complaints/complaint-handling.blade.php` — Added prominent TailAdmin Attention Banner surfacing highlighted complaints from Admin with one-click direct filter.
- `resources/views/staff/complaints/complaint-cards.blade.php` — Enhanced highlighted cards with top accent gradient ribbon, prominent ring border, and bold "Prioritas Admin" badge.
- `resources/views/staff/complaints/complaint-filters.blade.php` — Added dedicated quick filter button for "Prioritas Admin" with live counter.
- `resources/views/admin/complaints/complaint-filters.blade.php` — Added dedicated quick filter button for "Prioritas Admin" with live counter.
- `resources/views/admin/complaints/complaint-cards.blade.php` — Included one-click highlight toggle button on each card for Admin.

### Test Results
- Phase 37 specific: **9 tests, 29 assertions — all passing**
- Full Complaint test suite: **39 tests, 147 assertions — all passing**
- Full application suite: **311 tests, 1319 assertions — all passing** (zero regressions)

---

# 44. Phase 38 — Admin Account

Admin can:

```text
change name
change username
change email
change password
```

Normal authentication remains:

```text
username + password
```

---

# 45. Phase 39 — Separate Admin Recovery

Admin Recovery is deliberately separate from normal login.

Route:

```text
/admin-recovery
```

Normal login:

```text
/login
```

must not contain a recovery link.

Recovery flow:

```text
/admin-recovery
        ↓
Continue with Google
        ↓
Google OAuth
        ↓
Verify Google identity
        ↓
Find matching admin account
        ↓
role = admin
        ↓
status = active
        ↓
allow password recovery
```

A Google account that does not match a registered admin must be rejected.

Google OAuth secrets remain in environment configuration.

---

# 46. Phase 40 — Admin Password Confirmation

Sensitive admin actions must require current password confirmation.

Examples:

```text
Delete location
Remove rooms
Bulk rent update
Other high-impact destructive actions
```

Flow:

```text
Action
 ↓
Password Confirmation
 ↓
Authorization
 ↓
Execute
```

Use Laravel server-side verification.

---

# 47. Phase 41 — Payment Reminders

Implement:

```text
payment_reminders
```

Reminder schedule:

```text
H-3
Hari-H
```

Channels:

```text
Email
WhatsApp
```

Use:

```text
Laravel Scheduler
+
Laravel Queue
```

Do not execute reminder delivery directly during a normal browser request.

---

# 48. Phase 42 — Security Review

Review:

```text
authentication
authorization
session handling
CSRF protection
password hashing
file validation
upload security
rate limiting
secret handling
Google OAuth validation
payment verification
direct URL protection
```

Test:

```text
Guest → private route
Tenant → staff route
Tenant → admin route
Staff → admin route
Inactive user → login
Unknown Google account → admin recovery
```

All must behave correctly.

---

# 49. Phase 43 — UI Consistency Review

Review all Smart Kos pages.

Verify:

```text
TailAdmin layout
TailAdmin sidebar
TailAdmin components
Tailwind v4
dark mode
RTL
responsive layout
mobile navigation
tables
cards
modals
forms
alerts
badges
charts
```

Verify that new UI was created using the TailAdmin copy-first principle.

---

# 50. Phase 44 — Responsive Testing

Test at least:

```text
Desktop
Tablet
Mobile
```

Priority pages:

```text
Guest landing page
Location / Room
Tenant dashboard
Tenant payment
Staff payment status
Complaint handling
Admin dashboard
Admin room status
Finance
```

Room grids must remain usable on smaller screens.

---

# 51. Phase 45 — Functional Testing

Create Feature Tests for:

## Authentication

```text
login success
login failure
inactive account blocked
logout
```

## Authorization

```text
admin route protection
staff route protection
tenant route protection
cross-role URL protection
```

## Tenant

```text
assignment
payment
complaint
account update
```

## Staff

```text
payment status
post creation
complaint action
```

## Admin

```text
location management
room management
tenant assignment
payment verification
finance
staff management
```

---

# 52. Phase 46 — Payment Testing

Test independently:

### Midtrans VA

```text
VA created
VA linked to room
transaction received
transaction status updated
payment recorded
financial record generated
```

### Manual Transfer

```text
proof required
invalid proof rejected
rate limit enforced
pending payment visible
approve works
reject works
rejection reason stored
financial record created only when appropriate
```

Do not assume successful manual upload equals successful payment.

---

# 53. Phase 47 — Financial Integrity Testing

Verify:

```text
historical payment remains after tenant deactivation
historical payment remains after room becomes empty
historical expense remains
historical payroll remains
historical cash advance remains
changing current room rent does not rewrite historical payments
```

This phase is mandatory before production.

---

# 54. Phase 48 — Database Integrity Review

Verify:

```text
foreign keys
unique constraints
indexes
nullable historical references
cascade behavior
set-null behavior
assignment constraints
```

Important invariant:

```text
One room must not have multiple active assignments
```

Important historical invariant:

```text
Historical financial records must remain understandable
```

---

# 55. Phase 49 — Code Review

Before declaring the system ready:

```text
1. Review all modified files.
2. Review all new files.
3. Check protected TailAdmin paths.
4. Check unnecessary refactoring.
5. Check duplicate components.
6. Check duplicated logic.
7. Check authorization.
8. Check validation.
9. Check secrets.
10. Check generated files.
```

Use only approved inspection commands from `AGENTS.md`.

Recommended:

```bash
git status
git diff
git diff --check
```

---

# 56. Phase 50 — Final TailAdmin Protection Check

The agent must confirm:

```text
[ ] TailAdmin layouts were preserved
[ ] TailAdmin generic components were preserved
[ ] TailAdmin sidebar was not rebuilt
[ ] TailAdmin header was not rebuilt
[ ] TailAdmin demo pages were not unnecessarily deleted
[ ] Tailwind v4 was preserved
[ ] No tailwind.config.js was introduced
[ ] Existing TailAdmin JavaScript architecture was preserved
[ ] Generated public/build files were not manually edited
```

---

# 57. Phase 51 — Final Smart Kos Feature Checklist

## Guest

```text
[ ] Landing page
[ ] Contact staff
[ ] Contact owner
[ ] Location list
[ ] Room availability
[ ] Room popup/list
[ ] Login
```

## Tenant

```text
[ ] Dashboard
[ ] Room information
[ ] Payment deadline
[ ] Payment status
[ ] Announcements
[ ] Midtrans VA
[ ] Manual transfer
[ ] Proof upload
[ ] Payment submission rate limit
[ ] Complaint
[ ] Complaint rate limit
[ ] Account management
[ ] Password change
```

## Staff

```text
[ ] Dashboard
[ ] Staff profile
[ ] Announcements
[ ] Create post
[ ] Location filtering
[ ] Tenant payment status
[ ] Room payment grid
[ ] Complaint handling
[ ] Complaint action image
[ ] Complaint status
[ ] Staff account management
```

## Admin

```text
[ ] Dashboard
[ ] Financial statistics
[ ] Payment deadline monitoring
[ ] Tenant status
[ ] Tenant assignment
[ ] Bulk room rent update
[ ] Midtrans VA configuration
[ ] Complaint handling
[ ] Complaint highlighting
[ ] Financial verification
[ ] Operational expenses
[ ] Maintenance expenses
[ ] Payroll
[ ] Cash advance
[ ] Cash flow
[ ] Profit/loss
[ ] Receivables
[ ] Financial recap
[ ] Payment reminders
[ ] Location management
[ ] Room management
[ ] Tenant management
[ ] Staff management
[ ] Account management
[ ] Admin recovery
```

---

# 58. Phase Dependency Map

The implementation dependencies are:

```text
TailAdmin Foundation
        │
        ▼
Database Foundation
        │
        ▼
Models
        │
        ▼
Authentication
        │
        ▼
Authorization
        │
        ├───────────────────┐
        ▼                   ▼
     Guest             Account Management
        │                   │
        └─────────┬─────────┘
                  ▼
          Location & Rooms
                  │
                  ▼
          Tenant Assignment
                  │
          ┌───────┴────────┐
          ▼                ▼
       Tenant            Staff
       Features          Features
          │                │
          └───────┬────────┘
                  ▼
               Admin
                  │
       ┌──────────┼───────────┐
       ▼          ▼           ▼
    Payments   Complaints   Finance
       │          │           │
       └──────────┼───────────┘
                  ▼
          Reminders / Queue
                  │
                  ▼
              Testing
                  │
                  ▼
             Production
```

---

# 59. Recommended Implementation Order

For actual coding sessions, follow this exact order:

```text
PHASE 1
Project Foundation

PHASE 2
Database Foundation

PHASE 3
Eloquent Models

PHASE 4
Authentication

PHASE 5
Role Authorization

PHASE 6
Hardcoded Navigation

PHASE 7
Guest

PHASE 8
Location & Room Management

PHASE 9
Tenant Accounts

PHASE 10
Staff Accounts

PHASE 11
Tenant Assignment

PHASE 12
Tenant Dashboard

PHASE 13
Staff Dashboard

PHASE 14
Admin Dashboard

PHASE 15
Midtrans VA

PHASE 16
Manual Transfer

PHASE 17
Payment Verification

PHASE 18
Payment History / Financial Records

PHASE 19
Complaints

PHASE 20
Expenses

PHASE 21
Payroll / Cash Advance

PHASE 22
Cash Flow / Profit & Loss

PHASE 23
Receivables / Financial Recap

PHASE 24
Payment Reminders

PHASE 25
Admin Recovery

PHASE 26
Security Review

PHASE 27
Functional Testing

PHASE 28
UI / Responsive Testing

PHASE 29
Final Database Integrity Review

PHASE 30
Production Preparation
```

---

# 60. Definition of Done for Each Phase

A phase is not complete merely because the page exists.

A phase is complete when:

```text
Database
    ↓
Model
    ↓
Validation
    ↓
Authorization
    ↓
Controller/Service
    ↓
Route
    ↓
TailAdmin-based UI
    ↓
Testing
    ↓
Security review
```

are complete for the relevant feature.

---

# 61. Development Rules During Every Phase

For every change:

```text
Read first
    ↓
Understand current implementation
    ↓
Find existing TailAdmin example
    ↓
Copy/adapt
    ↓
Implement smallest change
    ↓
Test
    ↓
Inspect diff
```

UI implementation must also follow the modular Blade rule:

```text
Read feature requirements
    ↓
Identify independent page responsibilities
    ↓
Create feature-named Blade files
    ↓
Do not use index.blade.php for primary Smart Kos feature views
    ↓
Compose the feature from focused sections and components
    ↓
Test the route and rendered Blade
```

Do not create or extend a monolithic Blade feature page.

Every primary Smart Kos feature view must have a purpose-specific filename:

```text
tenant-status.blade.php
manual-verification.blade.php
location-management.blade.php
announcement-history.blade.php
staff-management.blade.php
```

Large tables, modals, confirmation dialogs, filters, tabs, and visualization panels must be separated into focused Blade files or components.

Never:

```text
rewrite TailAdmin globally
delete unrelated files
introduce a new UI framework
add a package without approval
use an unallowlisted command
use destructive database commands
stage files or modify Git history (git add / git commit is manual by user only)
```

Refer to `AGENTS.md` for the complete command and path policy.

---

# 62. Stop Conditions

The agent must stop when:

```text
a required command is not allowlisted
a new dependency is required
a destructive database command is needed
a TailAdmin protected path requires major restructuring
a secret is required but unavailable through environment configuration
a feature conflicts with ERD.md
a requirement conflicts with AGENTS.md
```

Do not create a workaround that violates the project rules.

---

# 63. Final Project Structure

After major implementation is complete, the project should remain structurally similar to:

```text
Smart Kos/
│
├── app/
│   ├── Helpers/
│   │   ├── MenuHelper.php
│   │   └── SmartKosMenuHelper.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Staff/
│   │   │   ├── Penyewa/
│   │   │   ├── Guest/
│   │   │   └── Auth/
│   │   │
│   │   ├── Middleware/
│   │   └── Requests/
│   │
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   ├── Jobs/
│   └── Notifications/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── components/
│   │       └── smart-kos/
│   │
│   └── views/
│       ├── components/
│       │   └── smart-kos/
│       │
│       ├── layouts/
│       │   └── TailAdmin layouts
│       │
│       └── pages/
│           ├── admin/
│           ├── staff/
│           ├── penyewa/
│           ├── guest/
│           └── auth/
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── AGENTS.md
├── ERD.md
└── stepbystep.md
```

This structure is an extension of TailAdmin, not a replacement for it.

---

# 64. Final Architecture Principle

The implementation must preserve this architecture:

```text
                   SMART KOS
                       │
            ┌──────────┴──────────┐
            │                     │
         TailAdmin            Laravel
         UI Layer           Application Layer
            │                     │
            ▼                     ▼
       Blade/Tailwind        Controllers
       Alpine.js             Services
       Existing UI           Models
                             Policies
                             Jobs
                             Notifications
                                  │
                                  ▼
                                MySQL
```

Payment architecture:

```text
                 PAYMENT
                    │
          ┌─────────┴──────────┐
          │                    │
          ▼                    ▼
   Midtrans VA          Manual Transfer
          │                    │
          ▼                    ▼
    va_payments         manual_payments
          │                    │
          └──────────┬─────────┘
                     ▼
             financial_records
```

Authentication architecture:

```text
/login
   │
   ▼
Username + Password
   │
   ├── admin
   ├── staff
   └── penyewa


/admin-recovery
   │
   ▼
Google OAuth
   │
   ▼
Registered Active Admin
   │
   ▼
Password Recovery
```

UI architecture:

```text
Existing TailAdmin
        │
        ▼
      COPY
        │
        ▼
Smart Kos Extension
        │
        ▼
Smart Kos Data + Logic
```

This architecture must remain the basis of Smart Kos throughout implementation.




