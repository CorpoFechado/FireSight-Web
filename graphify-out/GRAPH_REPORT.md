# Graph Report - firesight-web-v2  (2026-09-20)

## Corpus Check
- 270 files · ~103,090 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 26 file(s) not represented in the graph (top: (none) 19, .geojson 2, .example 1)

## Summary
- 1484 nodes · 3016 edges · 111 communities (82 shown, 12 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 5 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `37d959d2`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- utils.ts
- Announcement
- Illuminate\Database\Migrations\Migration
- index.ts
- map/index.tsx
- dependencies
- duty-shift-form-modal.tsx
- package.json
- personnel/index.tsx
- AGENTS.md
- sidebar.tsx
- BfpPersonnelDetails
- Inertia React Development
- Inertia\Response
- cn
- Notification
- Illuminate\Http\Request
- use-appearance.tsx
- FortifyServiceProvider.php
- incidents/index.tsx
- status-badge.tsx
- components.json
- Pest Testing 4
- Laravel\Fortify\Features
- Laravel Fortify Development
- compilerOptions
- Illuminate\Database\Seeder
- devDependencies
- analytics/index.tsx
- Barangay
- UserFactory
- PersonnelController
- auth.ts
- Tailwind CSS Development
- Architecture Best Practices
- react
- CommunityReport
- scripts
- response-tracking/index.tsx
- lucide-react
- Queue & Job Best Practices
- composer.json
- require-dev
- Security Best Practices
- two-factor-recovery-codes.tsx
- Advanced Query Patterns
- Database Performance Best Practices
- eslint.config.js
- notifications/index.tsx
- Events & Notifications Best Practices
- Wayfinder Development
- Caching Best Practices
- Eloquent Best Practices
- toggle-group.tsx
- duty-schedule/index.tsx
- Migration Best Practices
- Blade & Views Best Practices
- Error Handling Best Practices
- scripts
- require
- Task Scheduling Best Practices
- Testing Best Practices
- app-header.tsx
- portal-layout.tsx
- IncidentRecord
- config
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing & Controllers Best Practices
- laravel-best-practices/SKILL.md
- TestCase
- optionalDependencies
- vite.config.ts
- Conventions & Style
- Validation & Forms Best Practices
- psr-4
- laravel
- logging.php
- collapsible.tsx
- laravel-boost
- console.php
- layout.tsx
- User
- rules/graphify.md
- workflows/graphify.md
- user-info.tsx
- AnalyticsController
- DutyScheduleFactory.php
- dashboard.tsx
- @inertiajs/react
- PersonnelController.php
- Illuminate\Database\Eloquent\Relations\HasOne
- .name

## God Nodes (most connected - your core abstractions)
1. `cn()` - 127 edges
2. `react` - 69 edges
3. `@inertiajs/react` - 61 edges
4. `User` - 57 edges
5. `lucide-react` - 45 edges
6. `CommunityReport` - 28 edges
7. `Barangay` - 22 edges
8. `Button()` - 21 edges
9. `IncidentRecord` - 19 edges
10. `Label()` - 16 edges

## Surprising Connections (you probably didn't know these)
- `CardFooter()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/card.tsx → resources/js/lib/utils.ts
- `DropdownMenuCheckboxItem()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/dropdown-menu.tsx → resources/js/lib/utils.ts
- `DropdownMenuShortcut()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/dropdown-menu.tsx → resources/js/lib/utils.ts
- `DropdownMenuSubTrigger()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/dropdown-menu.tsx → resources/js/lib/utils.ts
- `DropdownMenuSubContent()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/dropdown-menu.tsx → resources/js/lib/utils.ts

## Import Cycles
- None detected.

## Communities (111 total, 12 thin omitted)

### Community 0 - "utils.ts"
Cohesion: 0.16
Nodes (16): InputError(), Props, PasskeyVerify(), Props, PasswordInput(), Props, TextLink(), Button() (+8 more)

### Community 1 - "Announcement"
Cohesion: 0.23
Nodes (3): AnnouncementController, Announcement, Illuminate\Validation\Rule

### Community 2 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.05
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 3 - "index.ts"
Cohesion: 0.16
Nodes (14): AppContent(), Props, AppShell(), Props, AppSidebarHeader(), Breadcrumbs(), SidebarInset(), SidebarProvider() (+6 more)

### Community 4 - "map/index.tsx"
Cohesion: 0.12
Nodes (27): react-leaflet, BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), BARANGAY_GEOJSON_URL, GEOJSON_TO_DB_BARANGAY_NAME, resolveDbBarangayName(), riskLevelColor() (+19 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "duty-shift-form-modal.tsx"
Cohesion: 0.18
Nodes (24): sonner, AnnouncementFormModal(), AnnouncementRow, TYPE_OPTIONS, DeleteAnnouncementDialog(), NOTE: no fallback to personnel[0] — an empty string forces the, ShiftRow, INCIDENT_TYPES (+16 more)

### Community 7 - "package.json"
Cohesion: 0.06
Nodes (32): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+24 more)

### Community 8 - "personnel/index.tsx"
Cohesion: 0.10
Nodes (24): @radix-ui/react-dropdown-menu, DeletePersonnelDialog(), PersonnelFormModal(), DropdownMenu(), DropdownMenuCheckboxItem(), DropdownMenuContent(), DropdownMenuGroup(), DropdownMenuItem() (+16 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "sidebar.tsx"
Cohesion: 0.09
Nodes (37): AppLogo(), AppSidebar(), footerNavItems, mainNavItems, NavFooter(), NavMain(), NavUser(), Sidebar() (+29 more)

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "Inertia\Response"
Cohesion: 0.14
Nodes (11): Controller, FireMapController, ProfileController, SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Date (+3 more)

### Community 14 - "cn"
Cohesion: 0.12
Nodes (26): @radix-ui/react-navigation-menu, @radix-ui/react-slot, Badge(), badgeVariants, Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink() (+18 more)

### Community 15 - "Notification"
Cohesion: 0.18
Nodes (4): NotificationController, Notification, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Support\Carbon

### Community 16 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (16): DutyScheduleController, EnsureBfpAdmin, EnsureBfpStaff, HandleAppearance, HandleInertiaRequests, DutySchedule, Closure, Illuminate\Database\QueryException (+8 more)

### Community 17 - "use-appearance.tsx"
Cohesion: 0.13
Nodes (21): AppearanceToggleTab(), Toaster(), TooltipProvider(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme() (+13 more)

### Community 18 - "FortifyServiceProvider.php"
Cohesion: 0.07
Nodes (21): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+13 more)

### Community 19 - "incidents/index.tsx"
Cohesion: 0.19
Nodes (10): PaginationBar(), BarangayOption, Filters, IncidentRow, inputStyle, SEVERITY_OPTIONS, STATUS_FILTERS, TYPE_OPTIONS (+2 more)

### Community 20 - "status-badge.tsx"
Cohesion: 0.14
Nodes (19): AnnouncementBadge(), PersonnelStatusBadge(), RoleBadge(), ANNOUNCEMENT_CFG, AnnouncementType, BadgeCfg, PERSONNEL_STATUS_CFG, PersonnelRole (+11 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "Laravel\Fortify\Features"
Cohesion: 0.11
Nodes (10): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Hash, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\URL (+2 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "Illuminate\Database\Seeder"
Cohesion: 0.16
Nodes (9): BarangayBoundarySeeder, BarangaySeeder, DatabaseSeeder, DummyDataSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\File (+1 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (16): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+8 more)

### Community 28 - "analytics/index.tsx"
Cohesion: 0.17
Nodes (10): CURRENT_YEAR, Filters, IncidentTypeSlice, inputStyle, MonthlyTrendPoint, Period, PERIOD_OPTIONS, ResponseTimePoint (+2 more)

### Community 29 - "Barangay"
Cohesion: 0.15
Nodes (7): RecheckIncidentBarangays, IncidentController, RiskAnalyticsController, Barangay, BarangayRiskSnapshot, Geo, Illuminate\Console\Command

### Community 32 - "auth.ts"
Cohesion: 0.17
Nodes (13): navItemsForRole(), PORTAL_NAV, PortalNavItem, PortalSidebar(), Auth, BfpRole, PersonnelStatus, TwoFactorSecretKey (+5 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "react"
Cohesion: 0.11
Nodes (20): input-otp, react, ManageTwoFactor(), Props, TwoFactorRecoveryCodes(), Props, TwoFactorSetupModal(), TwoFactorSetupStep() (+12 more)

### Community 36 - "CommunityReport"
Cohesion: 0.32
Nodes (4): IncidentActionController, CommunityReport, Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Http\RedirectResponse

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "response-tracking/index.tsx"
Cohesion: 0.24
Nodes (8): leaflet, PortalCard(), Incident, incidentIcon(), ResponseTracking(), RouteSummary, Station, stationIcon

### Community 39 - "lucide-react"
Cohesion: 0.12
Nodes (13): lucide-react, Heading(), ManagePasskeys(), Props, PasskeyItem(), Props, PasskeyRegistration(), DialogClose() (+5 more)

### Community 40 - "Queue & Job Best Practices"
Cohesion: 0.18
Nodes (10): Always Implement `failed()`, Batch Related Jobs, Implement `ShouldBeUnique`, Queue & Job Best Practices, Rate Limit External API Calls in Jobs, `retryUntil()` Needs `$tries = 0`, Set `retry_after` Greater Than `timeout`, Use Exponential Backoff (+2 more)

### Community 41 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 42 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 43 - "Security Best Practices"
Cohesion: 0.17
Nodes (11): Audit Dependencies, Authorize Every Action, CSRF Protection, Encrypt Sensitive Database Fields, Escape Output to Prevent XSS, Keep Secrets Out of Code, Mass Assignment Protection, Prevent SQL Injection (+3 more)

### Community 44 - "two-factor-recovery-codes.tsx"
Cohesion: 0.15
Nodes (14): AlertError(), AppLogoIcon(), Props, Alert(), AlertDescription(), AlertTitle(), alertVariants, Card() (+6 more)

### Community 45 - "Advanced Query Patterns"
Cohesion: 0.20
Nodes (9): Advanced Query Patterns, Create Dynamic Relationships via Subquery FK, Prefer `whereIn` + Subquery Over `whereHas`, Sometimes Two Simple Queries Beat One Complex Query, Use `addSelect()` Subqueries for Single Values from Has-Many, Use Compound Indexes Matching `orderBy` Column Order, Use Conditional Aggregates Instead of Multiple Count Queries, Use Correlated Subqueries for Has-Many Ordering (+1 more)

### Community 46 - "Database Performance Best Practices"
Cohesion: 0.20
Nodes (9): Add Database Indexes, Always Eager Load Relationships, Chunk Large Datasets, Database Performance Best Practices, No Queries in Blade Templates, Prevent Lazy Loading in Development, Select Only Needed Columns, Use `cursor()` for Memory-Efficient Iteration (+1 more)

### Community 47 - "eslint.config.js"
Cohesion: 0.18
Nodes (9): controlStatements, paddingAroundControl, @eslint/js, eslint-plugin-import, eslint-plugin-react, eslint-plugin-react-hooks, globals, @stylistic/eslint-plugin (+1 more)

### Community 48 - "notifications/index.tsx"
Cohesion: 0.31
Nodes (6): NotificationItem(), NotificationRow, formatNotificationTime(), getNotificationVisual(), NotificationType, Groups

### Community 49 - "Events & Notifications Best Practices"
Cohesion: 0.20
Nodes (9): Always Queue Notifications, Events & Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Run `event:cache` in Production Deploy, Use `afterCommit()` on Notifications in Transactions, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 50 - "Wayfinder Development"
Cohesion: 0.20
Nodes (9): Common Methods, Common Pitfalls, Documentation, Generate Routes, Import Patterns, Quick Reference, Verification, Wayfinder Development (+1 more)

### Community 51 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::memo()` to Avoid Redundant Hits Within a Request, Use `Cache::remember()` Instead of Manual Get/Put, Use Cache Tags to Invalidate Related Groups, Use `once()` for Per-Request Memoization

### Community 52 - "Eloquent Best Practices"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Avoid Hardcoded Table Names in Queries, Cast Date Columns Properly, Define Attribute Casts, Eloquent Best Practices, Use Correct Relationship Types, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 53 - "toggle-group.tsx"
Cohesion: 0.29
Nodes (8): class-variance-authority, @radix-ui/react-toggle, @radix-ui/react-toggle-group, ToggleGroup(), ToggleGroupContext, ToggleGroupItem(), Toggle(), toggleVariants

### Community 54 - "duty-schedule/index.tsx"
Cohesion: 0.14
Nodes (23): DeleteShiftDialog(), DutyShiftFormModal(), PersonnelOption, addDays(), capitalize(), DAY_LABELS, dayHeader(), formatTime() (+15 more)

### Community 55 - "Migration Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes in the Migration, Generate Migrations with Artisan, Keep Migrations Focused, Migration Best Practices, Mirror Defaults in Model `$attributes`, Never Modify Deployed Migrations, Use `constrained()` for Foreign Keys, Write Reversible `down()` Methods by Default

### Community 56 - "Blade & Views Best Practices"
Cohesion: 0.25
Nodes (7): Blade & Views Best Practices, Prefer Blade Components Over `@include`, Use `$attributes->merge()` in Component Templates, Use `@aware` for Deeply Nested Component Props, Use Blade Fragments for Partial Re-Renders (htmx/Turbo), Use `@pushOnce` for Per-Component Scripts, Use View Composers for Shared View Data

### Community 57 - "Error Handling Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Enable `dontReportDuplicates()`, Error Handling Best Practices, Exception Reporting and Rendering, Force JSON Error Rendering for API Routes, Throttle High-Volume Exceptions, Use `ShouldntReport` for Exceptions That Should Never Log

### Community 58 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, build, build:ssr, dev, format, format:check, lint, lint:check (+1 more)

### Community 59 - "require"
Cohesion: 0.25
Nodes (8): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, php

### Community 60 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Task Scheduling Best Practices, Use `environments()` to Restrict Tasks, Use `onOneServer()` on Multi-Server Deployments, Use `runInBackground()` for Concurrent Long Tasks, Use Schedule Groups for Shared Configuration, Use `takeUntilTimeout()` for Time-Bounded Processing, Use `withoutOverlapping()` on Variable-Duration Tasks

### Community 61 - "Testing Best Practices"
Cohesion: 0.25
Nodes (7): Call `Event::fake()` After Factory Setup, Testing Best Practices, Use `Exceptions::fake()` to Assert Exception Reporting, Use Factory States and Sequences, Use `LazilyRefreshDatabase` Over `RefreshDatabase`, Use Model Assertions Over Raw Database Assertions, Use `recycle()` to Share Relationship Instances Across Factories

### Community 62 - "app-header.tsx"
Cohesion: 0.14
Nodes (15): @radix-ui/react-dialog, mainNavItems, Props, rightNavItems, Sheet(), SheetContent(), SheetDescription(), SheetFooter() (+7 more)

### Community 63 - "portal-layout.tsx"
Cohesion: 0.16
Nodes (8): AuroraBackground(), SinglePointMap(), SeverityBadge(), StatusBadge(), PortalLayout(), Barangay, LinkedReport, ReportDetail

### Community 64 - "IncidentRecord"
Cohesion: 0.18
Nodes (5): DashboardController, IncidentRecord, RiskAssessment, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 65 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 66 - "Collection Best Practices"
Cohesion: 0.29
Nodes (6): Choose `cursor()` vs. `lazy()` Correctly, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 67 - "HTTP Client Best Practices"
Cohesion: 0.29
Nodes (6): Always Set Explicit Timeouts, Fake HTTP Calls in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Use Request Pooling for Concurrent Requests, Use Retry with Backoff for External APIs

### Community 68 - "Mail Best Practices"
Cohesion: 0.29
Nodes (6): Implement `ShouldQueue` on the Mailable Class, Mail Best Practices, Separate Content Tests from Sending Tests, Use `afterCommit()` on Mailables Inside Transactions, Use `assertQueued()` Not `assertSent()` for Queued Mailables, Use Markdown Mailables for Transactional Emails

### Community 69 - "Routing & Controllers Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Thin, Routing & Controllers Best Practices, Type-Hint Form Requests, Use Implicit Route Model Binding, Use Resource Controllers, Use Scoped Bindings for Nested Resources

### Community 70 - "laravel-best-practices/SKILL.md"
Cohesion: 0.17
Nodes (10): Configuration Best Practices, `env()` Only in Config Files, Use `App::environment()` for Environment Checks, Use Constants and Language Files, Use Encrypted Env or External Secrets, Consistency First, Decision Rules, How to Apply (+2 more)

### Community 72 - "optionalDependencies"
Cohesion: 0.29
Nodes (7): optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 73 - "vite.config.ts"
Cohesion: 0.29
Nodes (6): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite, @vitejs/plugin-react

### Community 74 - "Conventions & Style"
Cohesion: 0.29
Nodes (6): Conventions & Style, Follow Laravel Naming Conventions, No Inline JS/CSS in Blade, No Unnecessary Comments, Prefer Shorter Readable Syntax, Use Laravel String & Array Helpers

### Community 75 - "Validation & Forms Best Practices"
Cohesion: 0.29
Nodes (6): Always Use `validated()`, Array vs. String Notation for Rules, Use Form Request Classes, Use `Rule::when()` for Conditional Validation, Use the `after()` Method for Custom Validation, Validation & Forms Best Practices

### Community 76 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 77 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 78 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 82 - "layout.tsx"
Cohesion: 0.24
Nodes (10): AppHeader(), Separator(), IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn, WhenCurrentUrlFn, SettingsLayout() (+2 more)

### Community 83 - "User"
Cohesion: 0.11
Nodes (10): User, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Fortify\Contracts\PasskeyUser (+2 more)

### Community 103 - "user-info.tsx"
Cohesion: 0.23
Nodes (10): @radix-ui/react-avatar, Avatar(), AvatarFallback(), AvatarImage(), UserInfo(), getInitial(), GetInitialsFn, useInitials() (+2 more)

### Community 104 - "AnalyticsController"
Cohesion: 0.33
Nodes (3): AnalyticsController, Carbon\CarbonInterface, Illuminate\Database\Eloquent\Builder

### Community 106 - "dashboard.tsx"
Cohesion: 0.25
Nodes (6): recharts, BarangayRiskPoint, KPICard(), Kpis, MonthlyTrendPoint, RecentIncident

### Community 107 - "@inertiajs/react"
Cohesion: 0.22
Nodes (5): @inertiajs/react, CompleteReportModal(), Barangay, IncidentActions(), VerifyReportModal()

### Community 108 - "PersonnelController.php"
Cohesion: 0.29
Nodes (3): Illuminate\Support\Str, Illuminate\Validation\ValidationException, Pdo\Mysql

## Knowledge Gaps
- **510 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+505 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 674 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **12 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `@inertiajs/react` connect `@inertiajs/react` to `utils.ts`, `index.ts`, `map/index.tsx`, `duty-shift-form-modal.tsx`, `package.json`, `personnel/index.tsx`, `sidebar.tsx`, `cn`, `use-appearance.tsx`, `incidents/index.tsx`, `status-badge.tsx`, `analytics/index.tsx`, `auth.ts`, `react`, `lucide-react`, `two-factor-recovery-codes.tsx`, `notifications/index.tsx`, `duty-schedule/index.tsx`, `app-header.tsx`, `portal-layout.tsx`, `layout.tsx`, `dashboard.tsx`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `utils.ts`, `index.ts`, `map/index.tsx`, `duty-shift-form-modal.tsx`, `package.json`, `personnel/index.tsx`, `sidebar.tsx`, `cn`, `use-appearance.tsx`, `incidents/index.tsx`, `response-tracking/index.tsx`, `lucide-react`, `two-factor-recovery-codes.tsx`, `toggle-group.tsx`, `duty-schedule/index.tsx`, `app-header.tsx`, `portal-layout.tsx`, `layout.tsx`, `user-info.tsx`, `@inertiajs/react`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `lucide-react` connect `lucide-react` to `utils.ts`, `map/index.tsx`, `duty-shift-form-modal.tsx`, `package.json`, `personnel/index.tsx`, `sidebar.tsx`, `cn`, `use-appearance.tsx`, `incidents/index.tsx`, `status-badge.tsx`, `analytics/index.tsx`, `auth.ts`, `react`, `response-tracking/index.tsx`, `two-factor-recovery-codes.tsx`, `notifications/index.tsx`, `duty-schedule/index.tsx`, `app-header.tsx`, `portal-layout.tsx`, `dashboard.tsx`, `@inertiajs/react`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _510 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.05254237288135593 - nodes in this community are weakly interconnected._
- **Should `map/index.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.12310606060606061 - nodes in this community are weakly interconnected._
- **Should `dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._