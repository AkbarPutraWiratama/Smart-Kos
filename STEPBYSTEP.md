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

# 7. Phase 1 — Project Foundation

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

# 8. Phase 2 — Database Foundation

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

# 9. Phase 3 — Eloquent Models

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

# 10. Phase 4 — Authentication Foundation

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

# 11. Phase 5 — Authentication UI

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

# 12. Phase 6 — Role Authorization

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

# 13. Phase 7 — Hardcoded Role Navigation

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

Connect the menu to the existing TailAdmin sidebar with the smallest possible modification.

Do not replace the entire sidebar.

---

# 14. Phase 8 — Guest Landing Page

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

# 15. Phase 9 — Guest Location and Room View

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

# 16. Phase 10 — Location and Room Management

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

# 17. Phase 11 — Sensitive Location/Room Actions

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

# 18. Phase 12 — Tenant Account Management

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

# 19. Phase 13 — Staff Account Management

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

# 20. Phase 14 — Account Lifecycle

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

# 21. Phase 15 — Tenant Room Assignment

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

# 22. Phase 16 — Room Status Visualization

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

# 23. Phase 17 — Tenant Dashboard

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

# 24. Phase 18 — Tenant Account Page

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

# 25. Phase 19 — Staff Dashboard

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

# 26. Phase 20 — Staff Post Creation

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

# 27. Phase 21 — Admin Dashboard

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

# 28. Phase 22 — Payment Architecture

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

# 29. Phase 23 — Midtrans VA Account

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

# 30. Phase 24 — Midtrans VA Payment

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

# 31. Phase 25 — Manual Transfer

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

# 32. Phase 26 — Manual Payment Verification

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

# 33. Phase 27 — Payment History

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

# 34. Phase 28 — Financial Reporting Layer

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

Do not duplicate every operational field from payment/expense tables into this table.

Instead maintain a source reference:

```text
reference_type
reference_id
```

---

# 35. Phase 29 — Expense Management

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

---

# 36. Phase 30 — Payroll and Cash Advance

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

---

# 37. Phase 31 — Cash Flow

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

---

# 38. Phase 32 — Profit and Loss

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

---

# 39. Phase 33 — Tenant Receivables / Arrears

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

---

# 40. Phase 34 — Financial Recap

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

---

# 41. Phase 35 — Payment Reminders

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

# 42. Phase 36 — Complaint Submission

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

---

# 43. Phase 37 — Complaint Handling

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

---

# 44. Phase 38 — Complaint Highlight

Admin can highlight complaints for staff.

Example:

```text
highlighted = true
```

The staff interface should make highlighted complaints visually noticeable.

Use existing TailAdmin badges, alerts, cards, or emphasis styles.

Do not create a completely new visual framework.

---

# 45. Phase 39 — Admin Account

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

# 46. Phase 40 — Separate Admin Recovery

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

# 47. Phase 41 — Admin Password Confirmation

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

Never:

```text
rewrite TailAdmin globally
delete unrelated files
introduce a new UI framework
add a package without approval
use an unallowlisted command
use destructive database commands
modify Git history
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
