# Graph Report - firesight-web-v2  (2026-09-24)

## Corpus Check
- 309 files · ~142,841 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 26 file(s) not represented in the graph (top: (none) 19, .geojson 2, .example 1)

## Summary
- 1643 nodes · 3465 edges · 131 communities (90 shown, 11 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 8 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4d053774`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- utils.ts
- Announcement
- Illuminate\Support\Facades\Schema
- index.ts
- map/index.tsx
- dependencies
- dialog.tsx
- package.json
- lucide-react
- AGENTS.md
- sidebar.tsx
- Inertia React Development
- Illuminate\Http\Request
- cn
- portal-layout.tsx
- bootstrap/app.php
- use-appearance.tsx
- Illuminate\Foundation\Http\FormRequest
- incidents/index.tsx
- fire-status.ts
- components.json
- Pest Testing 4
- FortifyServiceProvider.php
- Laravel Fortify Development
- compilerOptions
- profile/index.tsx
- devDependencies
- dashboard.tsx
- Barangay
- Geo
- FortifyServiceProvider
- auth.ts
- Tailwind CSS Development
- Architecture Best Practices
- react
- DutySchedule
- scripts
- CommunityReport
- delete-user.tsx
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
- 2026_09_24_000006_update_risk_level_enum_to_critical.php
- Illuminate\Database\Eloquent\Model
- config
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing & Controllers Best Practices
- laravel-best-practices/SKILL.md
- DutyScheduleOnDutyTest.php
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
- FireEducationContent
- IncidentRecord
- UserFactory
- status-history.ts
- app.tsx
- Illuminate\Database\Schema\Blueprint
- alert.tsx
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- AppServiceProvider.php
- Illuminate\Database\Migrations\Migration
- @inertiajs/react
- PasswordValidationRules
- ProfileValidationRules
- useIsMobile

## God Nodes (most connected - your core abstractions)
1. `cn()` - 127 edges
2. `react` - 75 edges
3. `@inertiajs/react` - 67 edges
4. `User` - 66 edges
5. `lucide-react` - 49 edges
6. `CommunityReport` - 41 edges
7. `Barangay` - 34 edges
8. `IncidentRecord` - 32 edges
9. `Button()` - 21 edges
10. `DialogContent()` - 18 edges

## Surprising Connections (you probably didn't know these)
- `makeResolved()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/CommunityReport.php
- `makeResolved()` --calls--> `User`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/User.php
- `makeResolved()` --calls--> `IncidentRecord`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/IncidentRecord.php
- `CardFooter()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/card.tsx → resources/js/lib/utils.ts
- `SheetOverlay()` --calls--> `cn()`  [EXTRACTED]
  resources/js/components/ui/sheet.tsx → resources/js/lib/utils.ts

## Import Cycles
- None detected.

## Communities (131 total, 11 thin omitted)

### Community 0 - "utils.ts"
Cohesion: 0.15
Nodes (17): @radix-ui/react-checkbox, InputError(), Props, PasskeyVerify(), Props, PasswordInput(), Props, TextLink() (+9 more)

### Community 3 - "index.ts"
Cohesion: 0.16
Nodes (14): AppContent(), Props, AppShell(), Props, AppSidebar(), AppSidebarHeader(), Breadcrumbs(), SidebarInset() (+6 more)

### Community 4 - "map/index.tsx"
Cohesion: 0.08
Nodes (38): react-leaflet, BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), BARANGAY_GEOJSON_URL, GEOJSON_TO_DB_BARANGAY_NAME, resolveDbBarangayName(), IncidentType (+30 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "dialog.tsx"
Cohesion: 0.14
Nodes (33): sonner, AnnouncementFormModal(), AnnouncementRow, TYPE_OPTIONS, DeleteAnnouncementDialog(), ContactRow, NOTE: no fallback to personnel[0] — an empty string forces the, ShiftRow (+25 more)

### Community 7 - "package.json"
Cohesion: 0.06
Nodes (31): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+23 more)

### Community 8 - "lucide-react"
Cohesion: 0.12
Nodes (23): lucide-react, BarangayOption, ContactFormModal(), DeleteContactDialog(), DeletePersonnelDialog(), PersonnelFormModal(), DropdownMenu(), DropdownMenuContent() (+15 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "sidebar.tsx"
Cohesion: 0.11
Nodes (30): footerNavItems, mainNavItems, NavFooter(), NavMain(), NavUser(), SheetDescription(), Sidebar(), SidebarContent() (+22 more)

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (25): BarangayContactController, Controller, FireMapController, NotificationController, RiskAnalyticsController, ProfileController, SecurityController, HandleInertiaRequests (+17 more)

### Community 14 - "cn"
Cohesion: 0.11
Nodes (28): @radix-ui/react-navigation-menu, Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage(), BreadcrumbSeparator() (+20 more)

### Community 15 - "portal-layout.tsx"
Cohesion: 0.19
Nodes (9): leaflet, PortalLayout(), PortalTopbar(), Incident, incidentIcon(), ResponseTracking(), RouteSummary, Station (+1 more)

### Community 16 - "bootstrap/app.php"
Cohesion: 0.18
Nodes (10): EnsureBfpAdmin, EnsureBfpStaff, HandleAppearance, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets (+2 more)

### Community 17 - "use-appearance.tsx"
Cohesion: 0.19
Nodes (16): AppearanceToggleTab(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), isDarkMode(), listeners (+8 more)

### Community 18 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.20
Nodes (6): PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 19 - "incidents/index.tsx"
Cohesion: 0.19
Nodes (10): PaginationBar(), BarangayOption, Filters, IncidentRow, inputStyle, SEVERITY_OPTIONS, STATUS_FILTERS, TYPE_OPTIONS (+2 more)

### Community 20 - "fire-status.ts"
Cohesion: 0.10
Nodes (26): AcceptReportModal(), Barangay, IncidentActions(), ResolveReportModal(), SinglePointMap(), AnnouncementBadge(), SeverityBadge(), ANNOUNCEMENT_CFG (+18 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "FortifyServiceProvider.php"
Cohesion: 0.13
Nodes (10): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\URL (+2 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "profile/index.tsx"
Cohesion: 0.19
Nodes (10): PersonnelStatusBadge(), RoleBadge(), getInitial(), GetInitialsFn, useInitials(), PersonnelIndex(), disabledFieldStyle, fieldStyle (+2 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (16): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+8 more)

### Community 28 - "dashboard.tsx"
Cohesion: 0.10
Nodes (18): recharts, BarangayRiskPoint, KPICard(), PortalCard(), StatusBadge(), BarangayIncidentPoint, CURRENT_YEAR, Filters (+10 more)

### Community 29 - "Barangay"
Cohesion: 0.08
Nodes (11): Barangay, BarangayBoundarySeeder, BarangayContactSeeder, BarangaySeeder, DatabaseSeeder, DummyDataSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Eloquent\Relations\HasMany (+3 more)

### Community 30 - "Geo"
Cohesion: 0.38
Nodes (3): RecheckIncidentBarangays, Geo, Illuminate\Console\Command

### Community 32 - "auth.ts"
Cohesion: 0.15
Nodes (15): navItemsForRole(), PORTAL_NAV, PortalNavItem, PortalSidebar(), Auth, BfpRole, Passkey, PersonnelStatus (+7 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "react"
Cohesion: 0.14
Nodes (15): input-otp, react, Props, TwoFactorSetupStep(), InputOTP, InputOTPGroup, InputOTPSeparator, InputOTPSlot (+7 more)

### Community 36 - "DutySchedule"
Cohesion: 0.21
Nodes (5): DutyScheduleController, DutySchedule, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\QueryException

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "CommunityReport"
Cohesion: 0.22
Nodes (4): IncidentActionController, IncidentController, CommunityReport, Illuminate\Database\Eloquent\Relations\BelongsToMany

### Community 39 - "delete-user.tsx"
Cohesion: 0.14
Nodes (15): Heading(), ManagePasskeys(), Props, ManageTwoFactor(), Props, PasskeyItem(), Props, PasskeyRegistration() (+7 more)

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
Cohesion: 0.23
Nodes (9): AppLogoIcon(), Props, Card(), CardContent(), CardDescription(), CardFooter(), CardHeader(), CardTitle() (+1 more)

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
Cohesion: 0.20
Nodes (11): class-variance-authority, @radix-ui/react-slot, @radix-ui/react-toggle, @radix-ui/react-toggle-group, Badge(), badgeVariants, ToggleGroup(), ToggleGroupContext (+3 more)

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
Cohesion: 0.11
Nodes (20): @radix-ui/react-avatar, @radix-ui/react-dialog, @radix-ui/react-tooltip, mainNavItems, Props, rightNavItems, AppLogo(), Avatar() (+12 more)

### Community 63 - "2026_09_24_000006_update_risk_level_enum_to_critical.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 64 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (9): AuthToken, EmergencyContact, Notification, ReportEvidence, ReportStatusHistory, ResidentAddress, RiskAssessment, Illuminate\Database\Eloquent\Model (+1 more)

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

### Community 71 - "DutyScheduleOnDutyTest.php"
Cohesion: 0.29
Nodes (3): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, TestCase

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
Cohesion: 0.07
Nodes (17): PersonnelController, BfpPersonnelDetails, User, Attribute, BfpAccountSeeder, IncidentReportSeeder, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden (+9 more)

### Community 103 - "FireEducationContent"
Cohesion: 0.32
Nodes (3): FireEducationController, FireEducationContent, FireEducationContentSeeder

### Community 104 - "IncidentRecord"
Cohesion: 0.11
Nodes (8): AnalyticsController, DashboardController, IncidentRecord, DateRange, Carbon, Carbon\CarbonInterface, Illuminate\Support\Carbon, makeResolved()

### Community 105 - "UserFactory"
Cohesion: 0.11
Nodes (8): DutyScheduleFactory, FireEducationContentFactory, static, static, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, Pdo\Mysql

### Community 106 - "status-history.ts"
Cohesion: 0.28
Nodes (8): formatDateTime(), generateStatusHistory(), INVALID_SEQUENCE, parseDate(), STANDARD_SEQUENCE, STATUS_PERSONNEL, StatusHistoryEntry, STEP_GAP_MINUTES

### Community 107 - "app.tsx"
Cohesion: 0.27
Nodes (5): AuroraBackground(), TooltipProvider(), AppLayout(), AuthSimpleLayout(), AuthLayout()

### Community 110 - "alert.tsx"
Cohesion: 0.48
Nodes (5): AlertError(), Alert(), AlertDescription(), AlertTitle(), alertVariants

### Community 111 - "2026_09_24_000002_harmonize_community_report_schema.php"
Cohesion: 0.53
Nodes (5): down(), downSqlite(), up(), upMysql(), upSqlite()

### Community 112 - "2026_09_24_000001_harmonize_incident_record_schema.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 113 - "2026_09_24_000005_harmonize_workflow_statuses_and_types.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 118 - "AppServiceProvider.php"
Cohesion: 0.24
Nodes (4): AppServiceProvider, Carbon\CarbonImmutable, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

### Community 127 - "@inertiajs/react"
Cohesion: 0.38
Nodes (3): @inertiajs/react, Toaster(), useFlashToast()

### Community 128 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (6): CreateNewUser, ResetUserPassword, PasswordValidationRules, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 132 - "useIsMobile"
Cohesion: 0.53
Nodes (5): SidebarProvider(), getServerSnapshot(), isSmallerThanBreakpoint(), mediaQueryListener(), useIsMobile()

## Knowledge Gaps
- **524 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+519 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 710 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **11 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `react` connect `react` to `utils.ts`, `index.ts`, `map/index.tsx`, `useIsMobile`, `dialog.tsx`, `package.json`, `lucide-react`, `sidebar.tsx`, `cn`, `portal-layout.tsx`, `use-appearance.tsx`, `incidents/index.tsx`, `fire-status.ts`, `profile/index.tsx`, `dashboard.tsx`, `delete-user.tsx`, `two-factor-recovery-codes.tsx`, `toggle-group.tsx`, `duty-schedule/index.tsx`, `app-header.tsx`, `layout.tsx`, `alert.tsx`, `@inertiajs/react`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `@inertiajs/react` to `utils.ts`, `index.ts`, `map/index.tsx`, `dialog.tsx`, `package.json`, `lucide-react`, `sidebar.tsx`, `cn`, `portal-layout.tsx`, `use-appearance.tsx`, `incidents/index.tsx`, `fire-status.ts`, `profile/index.tsx`, `dashboard.tsx`, `auth.ts`, `react`, `delete-user.tsx`, `two-factor-recovery-codes.tsx`, `notifications/index.tsx`, `duty-schedule/index.tsx`, `app-header.tsx`, `layout.tsx`, `app.tsx`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `PasswordValidationRules`, `Announcement`, `Illuminate\Database\Eloquent\Model`, `DutySchedule`, `DutyScheduleOnDutyTest.php`, `IncidentRecord`, `UserFactory`, `FireEducationContent`, `Illuminate\Http\Request`, `Illuminate\Foundation\Http\FormRequest`, `FortifyServiceProvider.php`, `Barangay`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _524 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `map/index.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.0824524312896406 - nodes in this community are weakly interconnected._
- **Should `dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._
- **Should `dialog.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.1376659678546471 - nodes in this community are weakly interconnected._