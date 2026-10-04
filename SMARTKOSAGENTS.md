# Smart Kos — AGENTS.md

> Project-specific instructions for coding agents working on Smart Kos.
>
> **Primary rule:** preserve the upstream TailAdmin Laravel template and extend it minimally.
>
> **Security rule:** commands are **default-deny**. A command that is not explicitly allowlisted below must not be executed.

---

## 1. Project Identity

Smart Kos is a private/internal web application for one kos owner who manages multiple kos locations.

The application is not a public marketplace.

Primary goals:

- manage multiple kos locations and rooms;
- monitor room occupancy;
- assign tenants to rooms;
- monitor payment deadlines and payment status;
- receive and handle tenant complaints;
- manage operational and incidental expenses;
- manage tenant and staff accounts;
- maintain financial history;
- simplify maintenance and day-to-day kos management.

---

## 2. Upstream UI Foundation

This project starts from the official:

```text
TailAdmin/tailadmin-laravel
```

## Smart Kos Technology Stack

Smart Kos Tech stack:

```text
Backend
- Laravel 12
- PHP 8.3+

Frontend
- Blade
- Tailwind CSS v4
- Alpine.js
- Vite 7
- TailAdmin Laravel as the UI template

Database
- MySQL

Authentication
- Laravel session-based authentication
- Username + password for main login
- Google OAuth exclusively for Admin Recovery

Payment
- Virtual Account via payment provider
- Manual bank transfer with payment proof upload

File Storage
- Laravel Filesystem for payment proofs, complaint images,
  and maintenance/repair images

Background Processing
- Laravel Task Scheduling
- Laravel Queues

Testing
- Laravel Feature Tests / PHPUnit

Configuration
- `.env` for secrets and environment configuration
```

### Tech Stack Rules

1. Use Laravel 12 according to the project version.
2. Do not downgrade Laravel solely to resolve implementation issues.
3. Use Blade as the primary templating engine.
4. Use Tailwind CSS v4 as already used by TailAdmin.
5. Use Alpine.js for lightweight frontend interactions such as modals, dropdowns,
   filters, toggles, and confirmations.
6. Use Vite via the scripts already available in `package.json`.
7. Use MySQL as the primary database.
8. Use Laravel Filesystem for file uploads.
9. Use Laravel Queue and Scheduler for asynchronous/scheduled processes.
10. Use dependencies already available in TailAdmin before adding new dependencies.
11. Do not replace the frontend stack with Bootstrap, React, Vue, Svelte,
    or any other UI framework without explicit approval.
12. All credentials and secrets must come from environment/configuration,
    not hardcoded in the source code.

The upstream project has a modular Blade structure and existing TailAdmin layouts/components that must be reused.

### Core principle

```text
TAILADMIN = UI FOUNDATION
SMART KOS = APPLICATION FEATURES
```

Do not replace TailAdmin with another UI framework or rebuild the application shell from zero.

## 2.1 Mandatory TailAdmin Copy-First Rule

**Do not build the Smart Kos UI from scratch if TailAdmin already provides an example
or component that can be used as a foundation.**

Whenever you need to create a:

```text
page
dashboard
card
table
form
modal
dropdown
alert
badge
chart
sidebar item
header section
other UI component
```

The agent MUST follow this workflow:

```text
Find a TailAdmin example
        ↓
COPY
        ↓
Place it into the Smart Kos extension path
        ↓
Rename as needed
        ↓
Modify data / text / behavior
        ↓
Maintain the TailAdmin pattern
```

### Practical Rules

Example:

```text
resources/views/pages/dashboard/*
        ↓ COPY
resources/views/admin/dashboard.blade.php
```

or:

```text
resources/views/components/ui/*
        ↓ COPY / ADAPT
resources/views/components/smart-kos/*
```

For JavaScript:

```text
resources/js/components/*
        ↓ COPY / ADAPT
resources/js/components/smart-kos/*
```

For layouts:

```text
resources/views/layouts/*
        ↓ REUSE
Smart Kos page
```

Do not create a replacement layout if the existing TailAdmin layout can be used.

### Prohibited Actions

```text
TailAdmin component
        ↓
ignored
        ↓
build a new UI component from scratch
```

atau:

```text
TailAdmin dashboard
        ↓
deleted
        ↓
build your own framework dashboard
```

### Objective

Smart Kos must look like:

```text
TailAdmin
   +
Smart Kos functionality
```

not:

```text
TailAdmin
   →
abandoned
   →
a new custom template
```

### Exceptions

Building UI from scratch is only permitted if:

```text
1. No matching TailAdmin component/example exists; AND
2. The UI is genuinely required by Smart Kos; AND
3. The new UI still adheres to TailAdmin's visual structure, responsive behavior,
   dark mode, RTL, spacing, and interaction patterns.
```

If a matching TailAdmin component is available, copy/adapt is mandatory.

---

# 3. Current Upstream Repository Structure

Keep the existing root structure.

```text
tailadmin-laravel/
│
├── app/
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Providers/
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   ├── build/              # generated - do not edit manually
│   ├── images/
│   └── index.php
│
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── components/
│   └── views/
│       ├── components/
│       ├── layouts/
│       └── pages/
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
│
├── storage/
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── .env.example
├── AGENTS.md
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── vite.config.js
├── docker-compose.yml
├── nixpacks.toml
├── README.md
└── LICENSE
```

The root structure above follows the current upstream TailAdmin repository.

---

# 4. Smart Kos Extension Structure

Add Smart Kos code without reorganizing the TailAdmin foundation.

Recommended application structure:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Staff/
│   │   ├── Penyewa/
│   │   ├── Guest/
│   │   └── Auth/
│   │
│   ├── Middleware/
│   │
│   └── Requests/
│       ├── Admin/
│       ├── Staff/
│       └── Penyewa/
│
├── Models/
├── Policies/
├── Services/
├── Jobs/
├── Notifications/
└── Helpers/
    └── SmartKosMenuHelper.php
```

Views:

```text
resources/views/
│
├── components/
│   └── smart-kos/
│
├── pages/
│   ├── admin/
│   ├── staff/
│   ├── penyewa/
│   ├── guest/
│   └── auth/
│
└── layouts/
    └── [existing TailAdmin layouts remain here]
```

Client-side modules:

```text
resources/js/
├── app.js
├── bootstrap.js
└── components/
    └── smart-kos/
```

Database:

```text
database/
├── factories/
├── migrations/
└── seeders/
```

Tests:

```text
tests/
├── Feature/
│   ├── Admin/
│   ├── Staff/
│   ├── Penyewa/
│   ├── Auth/
│   └── Guest/
└── Unit/
```

Do not move existing upstream files merely to match this structure.

---

# 5. TailAdmin Protected Paths / Blacklist

The following paths are protected.

Protection means:

- do not delete;
- do not move;
- do not rename;
- do not replace the entire file;
- do not rebuild from scratch;
- do not mass-edit;
- do not remove functionality merely because Smart Kos does not use it.

## 5.1 Existing layouts

```text
resources/views/layouts/**
```

This includes the existing TailAdmin application shell such as:

```text
resources/views/layouts/app.blade.php
resources/views/layouts/fullscreen-layout.blade.php
resources/views/layouts/sidebar.blade.php
resources/views/layouts/app-header.blade.php
resources/views/layouts/backdrop.blade.php
resources/views/layouts/sidebar-widget.blade.php
```

### Restrictions

Do not:

```text
delete layouts/
replace app.blade.php completely
replace sidebar.blade.php completely
create a second competing application layout
duplicate the TailAdmin sidebar implementation
```

A minimal change to `layouts/sidebar.blade.php` is permitted when required to connect the hardcoded Smart Kos role menus.

---

## 5.2 Existing Blade components

```text
resources/views/components/**
```

Existing component folders include categories such as:

```text
resources/views/components/common/**
resources/views/components/ecommerce/**
resources/views/components/form/**
resources/views/components/header/**
resources/views/components/profile/**
resources/views/components/tables/**
resources/views/components/ui/**
resources/views/components/svg/**
```

### Restrictions

Do not delete or rewrite existing generic components.

For Smart Kos-specific components use:

```text
resources/views/components/smart-kos/**
```

Use existing TailAdmin components first.

Only create a new component when an appropriate existing component does not meet the requirement.

---

## 5.3 Existing JavaScript modules

Protected:

```text
resources/js/app.js
resources/js/bootstrap.js
resources/js/components/**
```

Do not rewrite `app.js` to contain all Smart Kos JavaScript.

Use:

```text
resources/js/components/smart-kos/**
```

for Smart Kos-specific client-side functionality.

Modify `resources/js/app.js` only when a new Smart Kos module genuinely needs to be registered/imported.

---

## 5.4 Tailwind source

Protected:

```text
resources/css/app.css
```

Rules:

- do not replace the file;
- do not create a second Tailwind entrypoint for the whole application;
- do not create `tailwind.config.js`;
- preserve existing `@theme` tokens;
- preserve existing TailAdmin utilities;
- preserve existing third-party library overrides.

Smart Kos-specific CSS should normally use Tailwind utilities and existing theme tokens.

Add custom CSS to `app.css` only when necessary.

---

## 5.5 TailAdmin menu helper

Protected existing file:

```text
app/Helpers/MenuHelper.php
```

Do not overwrite it with Smart Kos business logic.

For Smart Kos role-specific menus create:

```text
app/Helpers/SmartKosMenuHelper.php
```

The Smart Kos menu must remain hardcoded.

Do not store menu definitions in the database.

A minimal adapter in the existing TailAdmin sidebar is permitted so the sidebar can use the Smart Kos menu helper.

---

## 5.6 Existing TailAdmin demo pages

Do not delete existing TailAdmin demo page directories simply because Smart Kos does not display them in navigation.

Examples:

```text
resources/views/pages/dashboard/**
resources/views/pages/auth/**
resources/views/pages/form/**
resources/views/pages/tables/**
resources/views/pages/chart/**
resources/views/pages/ui-elements/**
```

They are part of the template and may also be used as UI references.

Smart Kos pages should be added separately:

```text
resources/views/admin/**
resources/views/staff/**
resources/views/penyewa/**
resources/views/guest/**
```

Do not overwrite the existing demo pages to make them become Smart Kos pages.

---

## 5.7 Generated/public assets

Do not manually edit:

```text
public/build/**
public/hot
```

These are generated frontend artifacts.

Use the approved Vite commands instead.

---

## 5.8 Dependency/configuration files

Protected:

```text
composer.json
composer.lock
package.json
package-lock.json
vite.config.js
phpunit.xml
docker-compose.yml
nixpacks.toml
```

Do not change dependencies, scripts, versions, build configuration, Docker configuration, or deployment configuration unless explicitly authorized.

---

## 5.9 Laravel bootstrap/config

Protected by default:

```text
bootstrap/app.php
config/**
```

Small targeted changes are allowed only when required for an actual Smart Kos feature.

Never mass-rewrite the Laravel configuration.

---

# 6. Secret and Sensitive Files

Never expose, print, copy, commit, or place secrets into source code.

Protected:

```text
.env
.env.*
auth.json
storage/*.key
```

Except:

```text
.env.example
```

which may be updated when a new configuration variable needs to be documented.

Never ask an agent to paste secret values into source code.

Do not put:

```text
database passwords
Google OAuth secrets
Midtrans API keys
mail passwords
session secrets
application keys
```

into Blade, JavaScript, controllers, migrations, or documentation.

---

# 7. Allowed New Files / Extension Points

The agent may create new files in these application areas:

```text
app/Http/Controllers/Admin/**
app/Http/Controllers/Staff/**
app/Http/Controllers/Penyewa/**
app/Http/Controllers/Guest/**
app/Http/Controllers/Auth/**

app/Http/Middleware/**
app/Http/Requests/**

app/Models/**
app/Policies/**
app/Services/**
app/Jobs/**
app/Notifications/**

app/Helpers/SmartKosMenuHelper.php

database/migrations/**
database/factories/**
database/seeders/**

resources/views/admin/**
resources/views/staff/**
resources/views/penyewa/**
resources/views/guest/**
resources/views/auth/**

resources/views/components/smart-kos/**

resources/js/components/smart-kos/**

tests/Feature/**
tests/Unit/**
```

Use Laravel conventions.

---

# 8. Root Documentation Files

The following Smart Kos project documentation files may be created at the root:

```text
AGENTS.md
erd.md
step by step.md
```

Do not create multiple competing agent instruction files unless explicitly requested.

Use the existing:

```text
AGENTS.md
```

as the primary agent instruction file.

---

# 9. Command Security Policy

## 9.1 Default-deny rule

Only commands explicitly listed in this file are allowed.

This is intentionally stricter than a blacklist.

If a command is not listed:

```text
DO NOT RUN IT.
```

Do not infer permission from a similar command.

Example:

```text
Allowed:
php artisan migrate

Not allowed:
php artisan migrate:rollback
php artisan migrate:refresh
php artisan migrate:fresh
```

---

# 10. Command Allowlist — Inspection

Allowed:

```bash
php -v
composer -V
node -v
npm -v

git status
git diff
git diff --stat
git diff --check
git log --oneline -10
git branch --show-current

php artisan about
php artisan route:list
php artisan migrate:status
```

These commands are for inspection only.

---

# 11. Command Allowlist — Development

Allowed:

```bash
php artisan serve
npm run dev
composer run dev
```

Do not substitute these with arbitrary server scripts.

---

# 12. Command Allowlist — Frontend Build

Allowed:

```bash
npm run dev
npm run build
npm run build:watch
```

Do not call `npx`, `yarn`, or `pnpm` directly.

The agent may use the predefined npm scripts from `package.json`.

Do not create new npm scripts without explicit authorization.

---

# 13. Command Allowlist — Composer

Allowed:

```bash
composer install
composer dump-autoload
composer run dev
composer run test
```

`composer install` is intended primarily for initial project setup or a dependency state that is already defined by the lock file.

Do not use Composer as a method of introducing new packages.

---

# 14. Command Allowlist — Laravel Artisan

Allowed:

```bash
php artisan key:generate
php artisan optimize
php artisan optimize:clear

php artisan migrate
php artisan migrate:status
php artisan db:seed

php artisan storage:link

php artisan queue:work
php artisan queue:listen
php artisan pail

php artisan route:list

php artisan test
php artisan test --filter=TestName
```

---

# 15. Artisan Generator Allowlist

The following Laravel generators are permitted when creating Smart Kos code:

```bash
php artisan make:model ModelName -m
php artisan make:controller ControllerName
php artisan make:controller Admin/ControllerName
php artisan make:controller Staff/ControllerName
php artisan make:controller Penyewa/ControllerName
php artisan make:controller Guest/ControllerName
php artisan make:controller Auth/ControllerName

php artisan make:request RequestName
php artisan make:request Admin/RequestName
php artisan make:request Staff/RequestName
php artisan make:request Penyewa/RequestName

php artisan make:middleware MiddlewareName
php artisan make:policy PolicyName
php artisan make:job JobName
php artisan make:notification NotificationName
php artisan make:event EventName
php artisan make:listener ListenerName

php artisan make:migration migration_name
php artisan make:seeder SeederName
php artisan make:factory FactoryName
```

Do not use arbitrary `make:*` commands outside this list.

---

# 16. Command Blacklist — Database Destruction

Forbidden:

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan migrate:refresh
php artisan migrate:reset
php artisan migrate:rollback
php artisan db:wipe
```

These commands can destroy development data and must never be used as a shortcut.

If a destructive database operation is genuinely required, stop and request explicit user authorization.

---

# 17. Command Blacklist — Package Changes

Forbidden:

```bash
composer require
composer remove
composer update
composer global

npm install <new-package>
npm uninstall
npm update

yarn
yarn add
yarn remove
yarn upgrade

pnpm
pnpm add
pnpm remove
pnpm update

npx <arbitrary-command>
```

Do not introduce new dependencies unless the user explicitly authorizes them.

---

# 18. Command Blacklist — Arbitrary Code Execution

Forbidden:

```bash
php -r
php -R
php artisan tinker
python
python3
node
node -e
powershell
pwsh
cmd
bash
sh
```

Do not use an interpreter to bypass the command allowlist.

Do not write a temporary script merely to perform an otherwise forbidden filesystem or system operation.

---

# 19. Command Blacklist — Filesystem Destruction/Movement

Do not use shell commands to delete, move, copy, or mass-rewrite project files.

Forbidden:

```text
rm
rm -rf
rmdir
del
erase
Remove-Item
mv
move
cp
copy
robocopy
```

Use the agent's file-editing mechanism for targeted source changes.

Never mass-replace files in TailAdmin directories.

---

# 20. Command Blacklist — Git Mutation

Only read-only Git commands are allowed.

Forbidden:

```bash
git add
git commit
git push
git pull
git fetch
git merge
git rebase
git cherry-pick
git reset
git reset --hard
git restore
git checkout
git switch
git clean
git revert
git tag
```

The agent may inspect Git state but must not change repository history or working-tree state through Git commands.

---

# 21. Command Blacklist — Network/Remote Installation

Forbidden:

```bash
curl
wget
Invoke-WebRequest
Invoke-RestMethod
git clone
```

Do not download arbitrary code, packages, scripts, or binaries into the project.

Use the existing lock files and approved package workflow.

---

# 22. Command Blacklist — Docker/Sail

Docker/Sail commands are not part of the normal Smart Kos agent allowlist.

Forbidden unless explicitly authorized:

```text
docker
docker-compose
vendor/bin/sail
./vendor/bin/sail
```

The upstream template includes Docker/Sail support, but Smart Kos development should not silently switch environments.

---

# 23. Shell Chaining and Allowlist Bypass

Do not bypass the command allowlist with shell operators.

Forbidden:

```text
&&
||
;
|
>
>>
<
$()
`...`
```

Examples of forbidden patterns:

```bash
allowed-command && forbidden-command
allowed-command; forbidden-command
allowed-command | forbidden-command
allowed-command > file
allowed-command $(forbidden-command)
```

Each executed command must independently match the allowlist.

A predefined project script is allowed only when the script itself is explicitly allowlisted.

---

# 24. Package Script Rule

Predefined scripts such as:

```bash
composer run dev
composer run test
npm run dev
npm run build
```

may internally execute their predefined project processes.

The agent may use the script itself because it is explicitly allowlisted.

The agent must not call the internal subprocesses as arbitrary commands to bypass the policy.

---

# 25. Routing Rules

Main public routes:

```text
/
```

Authentication:

```text
/login
/admin-recovery
```

Role areas:

```text
/admin/*
/staff/*
/penyewa/*
```

Admin Recovery is intentionally separated from the normal login page.

Do not add an Admin Recovery link to the main login UI unless explicitly requested.

---

# 26. Authentication Rules

Primary authentication:

```text
username + password
```

Roles:

```text
admin
staff
penyewa
```

Guest:

```text
no authentication
```

Account status:

```text
active
inactive
```

Inactive accounts must not be permitted to authenticate.

Do not create separate authentication systems for every role.

Use one authentication system and role-based authorization.

---

# 27. Admin Recovery Rules

Admin recovery uses a separate route:

```text
/admin-recovery
```

Google OAuth is only for Admin Recovery.

It must not be the normal login method.

Recovery flow:

```text
/admin-recovery
        ↓
Google OAuth
        ↓
Google identity verified
        ↓
match registered admin account
        ↓
role = admin
        ↓
status = active
        ↓
allow recovery
```

A random Google account must not gain admin access.

The Google OAuth client secret must remain in environment configuration.

---

# 28. Role Authorization Rules

Every protected role route must use server-side authorization.

Do not depend only on hiding menu items.

Concept:

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

A user must not access another role area by manually typing its URL.

---

# 29. Hardcoded Role Menus

Menus are intentionally hardcoded.

Do NOT use:

```text
database-driven menus
permissions table for menu rendering
JSON menu configuration stored in database
dynamic menu builder
```

Role menu definitions belong in application code.

Recommended:

```text
app/Helpers/SmartKosMenuHelper.php
```

with separate hardcoded definitions for:

```text
admin
staff
penyewa
```

Guest navigation remains public.

---

# 30. Role Menu Definition

## Guest

```text
Landing Page
Lokasi & Kamar
Login
```

## Penyewa

```text
Dashboard
Pembayaran
Aduan
Akun
```

## Staff

```text
Dashboard
Status Pembayaran Penyewa
Tindakan Aduan
Akun
```

## Admin

```text
Dashboard
Status Penyewa
Tindakan Aduan
Keuangan
Lokasi & Kamar
Pelanggan & Staff
Akun
```

Do not add extra navigation items without a corresponding approved feature.

---

# 31. Blade View Rules

New pages follow the TailAdmin convention:

```text
resources/views/pages/<category>/<page-name>.blade.php
```

Dashboard pages should reuse:

```text
layouts.app
```

Auth/fullscreen/utility pages should use the appropriate existing fullscreen layout when applicable.

Do not create a second global dashboard layout.

---

# 32. Existing TailAdmin Component Reuse

Before creating a Smart Kos component:

```text
1. Search existing TailAdmin components.
2. Determine whether an existing component can be reused.
3. Reuse it when possible.
4. Create Smart Kos-specific component only when necessary.
```

Examples:

```text
Card
Modal
Table
Badge
Alert
Button
Dropdown
Form
Date picker
Charts
```

Do not clone an existing component with only small wording changes.

---

# 33. UI Modification Rules

Do not redesign TailAdmin globally.

All normal Smart Kos UI creation must follow the mandatory
**TailAdmin Copy-First Rule** from Section 2.1.

Preferred implementation:

```text
Existing TailAdmin example/component
        ↓
COPY
        ↓
Smart Kos extension path
        ↓
Modify only Smart Kos content/data/behavior
        ↓
Keep TailAdmin structure
```

Allowed:

```text
Smart Kos content inside TailAdmin layout
Smart Kos cards using TailAdmin classes
Smart Kos tables using TailAdmin components
Smart Kos modals using TailAdmin modal pattern
Smart Kos charts using TailAdmin chart pattern
```

Not allowed:

```text
replace TailAdmin sidebar with another dashboard system
replace TailAdmin header with another framework
replace Tailwind with Bootstrap
introduce another frontend framework
rewrite all components for Smart Kos
```

---

# 34. Tailwind CSS Rules

TailAdmin uses Tailwind CSS v4.

Configuration belongs in:

```text
resources/css/app.css
```

Do NOT create:

```text
tailwind.config.js
```

Use existing theme tokens and utilities whenever possible.

Do not hardcode hex colors in Blade class attributes when a TailAdmin theme token can be used.

Do not write inline:

```html
style="..."
```

when Tailwind utilities can perform the same task.

---

# 35. RTL Rules

Preserve TailAdmin RTL support.

Prefer logical or RTL-aware utilities:

```text
ms-*
me-*
ps-*
pe-*
start-*
end-*
border-s-*
border-e-*
text-start
text-end
ltr:*
rtl:*
```

Do not introduce directional classes such as:

```text
ml-*
mr-*
pl-*
pr-*
left-*
right-*
text-left
text-right
```

without appropriate RTL behavior.

---

# 36. Dark Mode Rules

Preserve the existing TailAdmin dark mode.

New styled elements must provide appropriate dark-mode variants.

Example pattern:

```text
bg-white dark:bg-gray-800
text-gray-800 dark:text-white/90
border-gray-200 dark:border-gray-800
```

Do not disable TailAdmin dark mode for a single feature.

---

# 37. Alpine.js Rules

Use Alpine.js for small interactive UI behavior.

Examples:

```text
modal
dropdown
filter
toggle
confirmation
accordion
small local state
```

Use:

```text
x-data
x-show
x-cloak
@keydown.escape.window
```

Do not introduce a heavy JavaScript framework for simple UI interactions.

For reusable client modules, place them under:

```text
resources/js/components/smart-kos/
```

---

# 38. Modal Rules

For Smart Kos modals:

```text
x-data="{ isOpen: false }"
```

Use:

```text
@keydown.escape.window="isOpen = false"
x-cloak
```

Include an overlay backdrop and appropriate focus behavior.

Do not build a second modal framework.

---

# 39. Chart Rules

Use TailAdmin's existing chart architecture.

For Smart Kos charts, prefer:

```text
ApexCharts
```

and place initialization logic in:

```text
resources/js/components/smart-kos/
```

or Alpine `x-init` for small isolated charts.

Do not place large chart configuration blocks directly into unrelated Blade layouts.

---

# 40. Controller Rules

Controllers are separated by responsibility.

Preferred structure:

```text
app/Http/Controllers/Admin/
├── DashboardController.php
├── TenantController.php
├── LocationController.php
├── ComplaintController.php
├── FinanceController.php
├── PaymentController.php
├── StaffController.php
└── AccountController.php
```

```text
app/Http/Controllers/Staff/
├── DashboardController.php
├── PaymentStatusController.php
├── ComplaintController.php
└── AccountController.php
```

```text
app/Http/Controllers/Penyewa/
├── DashboardController.php
├── PaymentController.php
├── ComplaintController.php
└── AccountController.php
```

Do not create one giant controller for all Smart Kos features.

---

# 41. Business Logic Rules

Do not place business logic in Blade.

Avoid placing complex business logic directly in controllers.

Use:

```text
Models
Policies
Services
Jobs
Notifications
```

when appropriate.

A controller should primarily coordinate:

```text
Request
→ Validation
→ Authorization
→ Service/Model
→ Response
```

---

# 42. Validation Rules

All user input must be validated.

Use Form Requests for non-trivial validation.

Examples:

```text
tenant creation
tenant assignment
room changes
payment uploads
complaint submission
financial records
account changes
password changes
```

Never trust browser-side validation alone.

---

# 43. File Upload Rules

Smart Kos accepts uploads for:

```text
manual payment proof
complaint images
maintenance/action images
```

Rules:

- validate MIME/type;
- validate size;
- generate safe filenames;
- do not trust original filenames;
- do not store executable files in public upload paths;
- use Laravel filesystem APIs;
- do not manually construct unsafe file paths.

Uploaded files must not become executable application code.

---

# 44. Rate Limit Rules

These actions have application-level rate limits:

```text
manual transfer submit → once per 10 minutes
complaint submit        → once per 10 minutes
```

Do not implement the restriction only in JavaScript.

The server must enforce it.

---

# 45. Sensitive Admin Actions

These actions require additional confirmation:

```text
add/remove location
add/remove rooms
bulk room rent changes
other destructive or high-impact admin actions
```

Expected flow:

```text
User clicks action
        ↓
password confirmation
        ↓
authorization check
        ↓
perform action
```

Do not rely on a frontend confirmation alone.

---

# 46. Tenant Account Lifecycle

Tenant accounts can be:

active
inactive

A tenant can be assigned to a room.

When a tenant has an unpaid payment deadline exceeding 7 days,
the tenant account must automatically become inactive.

Automatic deactivation must be handled server-side.

An inactive tenant:

- cannot authenticate;
- cannot access tenant-protected pages;
- remains visible to Admin;
- retains historical payment and financial records;
- may be reactivated by Admin.

An Admin may reactivate an inactive tenant account.

An inactive tenant account becomes eligible for permanent deletion
when the account has remained inactive for more than 7 days.

Permanent deletion is an Admin-only action and must require appropriate
authorization and confirmation.

Deleting the tenant account must NOT delete:

- payment history;
- financial transactions;
- payment verification history;
- historical financial reports.

Historical financial records must remain understandable even after
the tenant account has been permanently deleted.

The two 7-day rules are independent:

1. Payment overdue > 7 days
   → tenant account becomes inactive.

2. Account inactive > 7 days
   → Admin may permanently delete the tenant account.

---

# 47. Staff Account Lifecycle

Staff accounts can be:

```text
active
inactive
```

An inactive staff account remains in historical records.

Permanent deletion is permitted only when application rules allow it.

Do not delete associated financial history.

---

# 48. Database Rules

All schema changes must use Laravel migrations.

Never modify schema manually when the change can be expressed as a migration.

Use:

```text
database/migrations/**
```

for schema changes.

Model relationships belong in Eloquent models.

Do not put SQL-heavy business logic inside Blade views.

---

# 49. Financial Data Rules

Financial records are historical data.

Do not delete financial history merely because:

```text
tenant account is deleted
staff account is deleted
tenant is moved
room is vacated
```

Use nullable foreign keys, archival strategy, snapshots, or appropriate relationships so historical amounts remain understandable.

---

# 50. Payment Rules

Payment status must be derived from server-side data.

The UI status colors are presentation only.

Recommended status display:

```text
room empty       → white
payment > 7 days → gray
payment < 7 days → yellow
payment due today → red
```

Do not use color as the only meaning.

Also provide text/status labels for accessibility.

---

# 51. Manual Transfer Rules

Manual transfer requires:

```text
bank name
bank account number
proof upload
submit
```

Proof upload is mandatory.

The server must enforce:

```text
required
valid file type
valid file size
rate limit = 10 minutes
```

Admin can:

```text
Approve
Reject
```

Payment verification must be recorded as historical data.

---

# 52. Virtual Account Rules

Virtual Account functionality must be isolated from general payment UI.

Do not hardcode payment provider secrets.

Provider API credentials must be stored in environment/configuration.

Changing room rent must not silently update unrelated rooms.

Bulk rent updates must require:

```text
selected rooms
new rent amount
authorization
confirmation
provider synchronization
```

---

# 53. Complaint Rules

Tenant complaint fields:

```text
title
description
optional image
```

Submission rate:

```text
10 minutes
```

Complaint handling supports:

```text
belum
dalam perbaikan
selesai
```

Changing to:

```text
selesai
```

requires confirmation.

Admin may highlight complaints for staff attention.

---

# 54. Reminder Rules

Payment reminders may later use:

```text
Laravel Task Scheduling
Laravel Queues
Email
WhatsApp
```

Target schedules include:

```text
H-3
Hari-H
```

Do not send reminders directly from a normal page request.

Use scheduled jobs/queued notifications when this feature is implemented.

---

# 55. Testing Rules

Every important feature must have tests.

Minimum priorities:

```text
authentication
admin authorization
staff authorization
tenant authorization
inactive account restriction

tenant assignment
room status
payment status
manual payment upload
manual payment verification

complaint submission
complaint handling

financial records
financial history preservation

location management
room management
bulk rent update
admin password confirmation
```

Also test direct URL access between roles.

Example:

```text
staff → /admin/finance
penyewa → /admin/finance
penyewa → /staff/complaints
```

must be rejected.

---

# 56. Development Workflow

For every feature:

```text
1. Read the relevant existing files.
2. Identify upstream TailAdmin files.
3. Identify protected paths.
4. Create only required Smart Kos files.
5. Implement database changes with migrations.
6. Implement model relationships.
7. Implement validation.
8. Implement authorization.
9. Implement controller/service logic.
10. Implement route.
11. Implement Blade page using TailAdmin.
12. Implement Alpine/JS only when required.
13. Run appropriate tests.
14. Run `git diff --check`.
15. Inspect `git diff`.
```

Do not modify unrelated files.

---

# 57. Minimal Change Principle

For every change:

```text
SMALLEST CHANGE THAT SOLVES THE FEATURE
```

Prefer:

```text
add
extend
reuse
adapt
```

over:

```text
replace
rewrite
reorganize
migrate the whole template
```

---

# 58. Do Not Rebuild TailAdmin

Never perform this pattern:

```text
TailAdmin
   ↓
delete existing UI
   ↓
create new dashboard template
```

Use:

```text
TailAdmin
   ↓
existing layout/components
   ↓
Smart Kos pages/components
   ↓
Smart Kos data
```

The purpose of this rule is to keep future TailAdmin maintenance possible and prevent unnecessary template drift.

---

# 59. Existing Demo Features

TailAdmin demo pages may remain in the source tree even if not used by Smart Kos.

Do not spend development time deleting demo files unless there is a concrete requirement.

The Smart Kos navigation can simply stop linking to unused demo routes.

---

# 60. Dependency Policy

Prefer existing dependencies.

The current upstream frontend already contains libraries for functionality such as:

```text
Alpine.js
ApexCharts
Flatpickr
FullCalendar
jsVectorMap
Prism.js
Swiper
```

Reuse an existing dependency before proposing a replacement or additional package.

New dependencies require explicit user approval.

---

# 61. No Global Visual Refactor

Do not change the global:

```text
font system
theme colors
spacing system
breakpoints
dark mode
RTL system
sidebar architecture
header architecture
```

just to satisfy a single Smart Kos feature.

When a feature needs customization, scope the change to the relevant Smart Kos page/component.

---

# 62. Environment Compatibility

Follow the versions defined by the actual checked-out TailAdmin repository.

Do not downgrade Laravel.

Do not change the Node version requirement unless explicitly authorized.

Do not introduce compatibility shims for older TailAdmin/Laravel versions unless there is a documented need.

---

# 63. Error Handling

Do not expose:

```text
database queries
stack traces
API secrets
internal paths
credentials
```

to end users.

Development debugging may use Laravel's normal tooling, but production responses must remain safe.

---

# 64. Logging

Do not log:

```text
passwords
OAuth client secrets
API keys
full payment credentials
sensitive authentication tokens
```

Use Laravel logging for operational information without exposing secrets.

---

# 65. API / External Service Rules

When an external service is added:

```text
provider credentials
timeouts
error handling
validation
retry behavior
logging
```

must be handled server-side.

Do not put provider secrets in frontend JavaScript.

---

# 66. Route File Rules

Primary Smart Kos web routes belong in:

```text
routes/web.php
```

Use:

```text
routes/api.php
```

only when an actual API endpoint is required.

Use:

```text
routes/console.php
```

only for actual Artisan/console scheduling needs.

Do not duplicate the same web route across multiple route files.

---

# 67. Route Naming

Use consistent named routes.

Recommended naming style:

```text
admin.dashboard
admin.tenants.index
admin.tenants.store
admin.locations.index
admin.finance.index

staff.dashboard
staff.payments.index
staff.complaints.index

penyewa.dashboard
penyewa.payments.index
penyewa.complaints.index
```

Do not expose role-inappropriate actions through generic route names.

---

# 68. Account Rules

All authenticated users use the central:

```text
users
```

authentication identity.

Roles:

```text
admin
staff
penyewa
```

Do not create:

```text
admin_users
staff_users
penyewa_users
```

unless a later architecture decision explicitly requires multiple authentication providers.

---

# 69. Guest Rules

Guest users do not authenticate.

Guest can access:

```text
Landing Page
Lokasi
Kamar availability
Contact information
Login
```

Guest must not access private tenant information.

Tenant names are not public guest data.

---

# 70. Tenant Privacy Rules

Tenant personal information must not be exposed on the public guest room listing.

Guest room view should only show room occupancy state.

Private tenant details are visible only according to application authorization.

---

# 71. Smart Kos Folder Naming

Use consistent naming.

Backend PHP:

```text
PascalCase classes
```

Blade folders/files:

```text
kebab-case where appropriate
```

Database tables:

```text
snake_case plural
```

Routes:

```text
lowercase
```

Avoid random abbreviations.

---

# 72. Comments

Comments should explain:

```text
why
```

not merely:

```text
what
```

Do not fill source files with unnecessary comments.

Never remove important upstream comments unless the surrounding code is actually being changed.

---

# 73. Code Formatting

Follow existing Laravel/PHP style.

Use Laravel Pint if explicitly available and requested.

Do not introduce a separate formatter configuration.

Do not modify project formatting rules globally for a single feature.

---

# 74. Diff Review Requirement

After editing a protected or important file, inspect:

```bash
git diff --check
git diff
```

Confirm that:

```text
only intended files changed
no TailAdmin demo files were accidentally rewritten
no secrets were added
no generated assets were edited
no unrelated CSS/JS was reformatted
```

---

# 75. Stop Conditions

The agent must stop and request explicit authorization when:

```text
a new dependency is required
a protected configuration file needs a major rewrite
a destructive database operation is required
a TailAdmin layout must be replaced
an existing TailAdmin component must be deleted
a Git history operation is required
an operating-system command is needed but not allowlisted
a secret must be accessed
a command is not in the allowlist
```

Do not invent a workaround to bypass this rule.

---

# 76. Final Agent Checklist

Before declaring a feature complete:

```text
[ ] Smart Kos uses the documented technology stack
[ ] Existing TailAdmin structure preserved
[ ] New UI copied/adapted from TailAdmin when a suitable example exists
[ ] No protected file was unnecessarily rewritten
[ ] No new dependency added without authorization
[ ] Only allowlisted commands were executed
[ ] No destructive command was executed
[ ] No Git history mutation was executed
[ ] Server-side authorization implemented
[ ] Input validation implemented
[ ] Sensitive data protected
[ ] Existing TailAdmin components reused where possible
[ ] Dark mode preserved
[ ] RTL behavior preserved
[ ] Tests added/updated
[ ] git diff --check passes
[ ] Unrelated files remain unchanged
```

---

# 77. Absolute Rule

When uncertain:

```text
DO NOT MODIFY TAILADMIN STRUCTURE.
DO NOT EXECUTE UNLISTED COMMANDS.
DO NOT DELETE DATA.
DO NOT ADD DEPENDENCIES.
DO NOT BYPASS AUTHORIZATION.
DO NOT EXPOSE SECRETS.
```

Prefer a smaller, isolated Smart Kos change over a broad refactor.

