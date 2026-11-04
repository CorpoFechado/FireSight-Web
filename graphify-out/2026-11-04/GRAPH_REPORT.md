# Graph Report - firesight-web-v2  (2026-11-04)

## Corpus Check
- 404 files · ~648,410 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 26 file(s) not represented in the graph (top: (none) 20, .example 1, .onnx 1)

## Summary
- 2078 nodes · 4660 edges · 166 communities (107 shown, 17 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 29 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- utils.ts
- incidents/index.tsx
- Illuminate\Database\Migrations\Migration
- index.ts
- response-tracking/index.tsx
- dependencies
- Illuminate\Http\Request
- package.json
- duty-schedule.ts
- AGENTS.md
- Notification
- AuthToken
- Inertia React Development
- map/index.tsx
- BarangayContact
- Laravel\Fortify\Features
- Illuminate\Foundation\Http\FormRequest
- CommunityReport
- personnel-form-modal.tsx
- FireEducationContent
- fire-status.ts
- components.json
- Pest Testing 4
- lucide-react
- Laravel Fortify Development
- compilerOptions
- toggle-group.tsx
- devDependencies
- portal-layout.tsx
- Illuminate\Database\Seeder
- sidebar.tsx
- react
- welcome.tsx
- Tailwind CSS Development
- Architecture Best Practices
- RealisticIncidentDataSeeder
- residents/index.tsx
- scripts
- DutyOverride
- AdminNotifier
- Queue & Job Best Practices
- composer.json
- require-dev
- Security Best Practices
- two-factor-recovery-codes.tsx
- Advanced Query Patterns
- Database Performance Best Practices
- eslint.config.js
- Carbon\CarbonInterface
- Events & Notifications Best Practices
- Wayfinder Development
- Caching Best Practices
- Eloquent Best Practices
- Announcement
- risk-analytics/index.tsx
- Migration Best Practices
- Blade & Views Best Practices
- Error Handling Best Practices
- scripts
- require
- Task Scheduling Best Practices
- Testing Best Practices
- app-header.tsx
- 2026_09_24_000006_update_risk_level_enum_to_critical.php
- Illuminate\Database\Eloquent\Relations\BelongsTo
- config
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing & Controllers Best Practices
- laravel-best-practices/SKILL.md
- FortifyServiceProvider
- optionalDependencies
- AlarmLevel
- Conventions & Style
- Validation & Forms Best Practices
- psr-4
- laravel
- logging.php
- collapsible.tsx
- laravel-boost
- console.php
- 2026_10_08_000001_replace_severity_level_with_alarm_level_in_incident_record.php
- User
- rules/graphify.md
- workflows/graphify.md
- PasswordResetOtpMail
- layout.tsx
- bootstrap/app.php
- show.tsx
- MobileNotificationService
- Illuminate\Database\Eloquent\Model
- Illuminate\Database\Schema\Blueprint
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- Illuminate\Support\Facades\Schema
- Inertia\Response
- ProfileValidationRules
- FortifyServiceProvider.php
- main.py
- Illuminate\Http\RedirectResponse
- 2026_10_18_205341_rename_users_id_to_user_id.php
- dashboard.tsx
- Quick Start (Local Development)
- RiskLevel
- alert.tsx
- 2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php
- cn
- DashboardController
- useIsMobile
- Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.
- Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them.
- Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.
- Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors.
- AforReport
- RiskVulnerabilityChecklist
- rename_dataset.py
- TestCase
- placeholder-pattern.tsx
- app.tsx
- PasswordValidationRules
- IncidentDispatch
- icon.tsx

## God Nodes (most connected - your core abstractions)
1. `cn()` - 131 edges
2. `User` - 129 edges
3. `CommunityReport` - 87 edges
4. `react` - 80 edges
5. `@inertiajs/react` - 72 edges
6. `lucide-react` - 61 edges
7. `Barangay` - 52 edges
8. `IncidentRecord` - 40 edges
9. `Notification` - 25 edges
10. `ReportStatusHistory` - 23 edges

## Surprising Connections (you probably didn't know these)
- `createResidentAddress()` --references--> `Barangay`  [EXTRACTED]
  tests/Feature/ResidentAccountsTest.php → app/Models/Barangay.php
- `createResidentAddress()` --calls--> `ResidentAddress`  [EXTRACTED]
  tests/Feature/ResidentAccountsTest.php → app/Models/ResidentAddress.php
- `makeCommunityReport()` --calls--> `User`  [EXTRACTED]
  tests/Feature/AdminNotificationTest.php → app/Models/User.php
- `createTestBfpPersonnel()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/User.php
- `createTestResident()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (166 total, 17 thin omitted)

### Community 0 - "utils.ts"
Cohesion: 0.05
Nodes (55): input-otp, AppearanceToggleTab(), Heading(), InputError(), ManagePasskeys(), Props, ManageTwoFactor(), Props (+47 more)

### Community 1 - "incidents/index.tsx"
Cohesion: 0.16
Nodes (12): PaginationBar(), AiFireBadge(), ALARM_LEVEL_CFG, ALARM_LEVEL_OPTIONS, BarangayOption, Filters, IncidentRow, inputStyle (+4 more)

### Community 3 - "index.ts"
Cohesion: 0.15
Nodes (15): AppContent(), Props, AppShell(), Props, AppSidebarHeader(), Breadcrumbs(), SidebarInset(), SidebarTrigger() (+7 more)

### Community 4 - "response-tracking/index.tsx"
Cohesion: 0.13
Nodes (18): leaflet, react-leaflet, fireIcon, formatDistance(), IncidentLocationMap(), reporterIcon, AlarmLevelBadge(), AlarmLevel (+10 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (12): ApiController, DashboardController, IncidentController, MapController, NotificationController, ProfileController, ReportController, ReportStatusHistory (+4 more)

### Community 7 - "package.json"
Cohesion: 0.06
Nodes (38): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+30 more)

### Community 8 - "duty-schedule.ts"
Cohesion: 0.14
Nodes (24): DutyOverrideModal(), RemoveOverrideDialog(), addDays(), computeWeekEnd(), DUTY_COLORS, DutyGroup, DutySchedulePageProps, formatChipDate() (+16 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 11 - "AuthToken"
Cohesion: 0.17
Nodes (8): PurgeExpiredDeletedAccounts, AuthController, AuthToken, ResidentAddress, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Support\Facades\Mail, createTestBfpPersonnel(), createTestResident()

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "map/index.tsx"
Cohesion: 0.12
Nodes (16): ALARM_LEVEL_OPTIONS, BarangayFeature, BarangayGeoProperties, BarangayRiskPoint, buildResultSummary(), ColorBy, Filters, FireIncidentsMap() (+8 more)

### Community 14 - "BarangayContact"
Cohesion: 0.18
Nodes (4): ContactController, BarangayContactController, BarangayContact, EmergencyContact

### Community 15 - "Laravel\Fortify\Features"
Cohesion: 0.18
Nodes (7): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Laravel\Fortify\Features

### Community 16 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.24
Nodes (6): PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 17 - "CommunityReport"
Cohesion: 0.07
Nodes (12): RecheckIncidentBarangays, IncidentController, Barangay, CommunityReport, IncidentRecord, Geo, Illuminate\Console\Command, Illuminate\Database\Eloquent\Relations\HasMany (+4 more)

### Community 18 - "personnel-form-modal.tsx"
Cohesion: 0.13
Nodes (24): DeleteFireEducationDialog(), CATEGORY_OPTIONS, FireEducationFormModal(), FireEducationRow, FireEducationViewModal(), Barangay, ALARM_LEVELS, combineWithBaseDate() (+16 more)

### Community 19 - "FireEducationContent"
Cohesion: 0.09
Nodes (13): EducationController, FireEducationController, FireEducationContent, DutyOverrideFactory, FireEducationContentFactory, static, static, UserFactory (+5 more)

### Community 20 - "fire-status.ts"
Cohesion: 0.14
Nodes (16): AiVerificationCard(), AiVerificationCardProps, AI_FIRE_LABEL_CFG, AiFireConfig, AiFireLabel, AlarmLevelCfg, BadgeCfg, EDUCATION_CATEGORY_CFG (+8 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "lucide-react"
Cohesion: 0.11
Nodes (31): lucide-react, BarangayOption, ContactFormModal(), ContactRow, DeleteContactDialog(), DeletePersonnelDialog(), PersonnelFormModal(), PersonnelRow (+23 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "toggle-group.tsx"
Cohesion: 0.20
Nodes (11): class-variance-authority, @radix-ui/react-slot, @radix-ui/react-toggle, @radix-ui/react-toggle-group, Badge(), badgeVariants, ToggleGroup(), ToggleGroupContext (+3 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (16): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+8 more)

### Community 28 - "portal-layout.tsx"
Cohesion: 0.07
Nodes (29): recharts, AnnouncementFormModal(), DeleteAnnouncementDialog(), NotificationItem(), NotificationRow, PortalCard(), PersonnelStatusBadge(), RoleBadge() (+21 more)

### Community 29 - "Illuminate\Database\Seeder"
Cohesion: 0.14
Nodes (8): AdminNotificationSeeder, BarangayBoundarySeeder, BarangayContactSeeder, BarangaySeeder, DummyDataSeeder, IncidentReportSeeder, Illuminate\Database\Seeder, RuntimeException

### Community 30 - "sidebar.tsx"
Cohesion: 0.11
Nodes (29): AppSidebar(), footerNavItems, mainNavItems, NavFooter(), NavMain(), NavUser(), Sidebar(), SidebarContent() (+21 more)

### Community 31 - "react"
Cohesion: 0.30
Nodes (13): @inertiajs/react, react, sonner, AnnouncementRow, Props, Dialog(), DialogClose(), DialogContent() (+5 more)

### Community 32 - "welcome.tsx"
Cohesion: 0.06
Nodes (37): AppCtaSection(), Audience, audiences, AudienceSection(), ComingSoonButton(), ComingSoonButtonProps, Feature, features (+29 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "RealisticIncidentDataSeeder"
Cohesion: 0.16
Nodes (4): DatabaseSeeder, RealisticIncidentDataSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Support\Collection

### Community 36 - "residents/index.tsx"
Cohesion: 0.18
Nodes (9): BarangayOption, Confirmation, formatDateTime(), ResidentIndex(), ResidentProfile, ResidentReport, ResidentResponse, ResidentRow (+1 more)

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "DutyOverride"
Cohesion: 0.11
Nodes (8): DutyGroup, DutyScheduleController, DutyOverride, DutyRotation, CarbonInterface, Carbon, Carbon\CarbonImmutable, CarbonImmutable

### Community 39 - "AdminNotifier"
Cohesion: 0.18
Nodes (3): CommunityReportObserver, ExpoPushService, AdminNotifier

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

### Community 48 - "Carbon\CarbonInterface"
Cohesion: 0.19
Nodes (6): AnalyticsController, DateRange, IncidentTypeBreakdown, Carbon\CarbonInterface, Illuminate\Database\Eloquent\Builder, Illuminate\Support\Carbon

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

### Community 53 - "Announcement"
Cohesion: 0.21
Nodes (3): AnnouncementController, AnnouncementController, Announcement

### Community 54 - "risk-analytics/index.tsx"
Cohesion: 0.17
Nodes (18): BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), BARANGAY_GEOJSON_URL, GEOJSON_TO_DB_BARANGAY_NAME, getFeatureBarangayName(), resolveDbBarangayName(), RiskLevel (+10 more)

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
Cohesion: 0.10
Nodes (22): @radix-ui/react-avatar, @radix-ui/react-dialog, @radix-ui/react-tooltip, mainNavItems, Props, rightNavItems, AppLogo(), Avatar() (+14 more)

### Community 63 - "2026_09_24_000006_update_risk_level_enum_to_critical.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 64 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.12
Nodes (3): IncidentDispatchPersonnel, ReportEvidence, Illuminate\Database\Eloquent\Relations\BelongsTo

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

### Community 71 - "FortifyServiceProvider"
Cohesion: 0.23
Nodes (3): AppServiceProvider, FortifyServiceProvider, Illuminate\Support\ServiceProvider

### Community 72 - "optionalDependencies"
Cohesion: 0.29
Nodes (7): optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

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

### Community 81 - "console.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Inspiring, Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\Schedule

### Community 82 - "2026_10_08_000001_replace_severity_level_with_alarm_level_in_incident_record.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 83 - "User"
Cohesion: 0.06
Nodes (21): PersonnelController, BfpPersonnelDetails, User, Attribute, BfpAccountSeeder, BfpPersonnelSeeder, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden (+13 more)

### Community 103 - "PasswordResetOtpMail"
Cohesion: 0.20
Nodes (9): PasswordResetOtpMail, Content, Envelope, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Attachment, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+1 more)

### Community 104 - "layout.tsx"
Cohesion: 0.22
Nodes (11): @radix-ui/react-separator, AppHeader(), Separator(), IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn, WhenCurrentUrlFn (+3 more)

### Community 105 - "bootstrap/app.php"
Cohesion: 0.12
Nodes (14): EnsureBfpAdmin, EnsureBfpStaff, EnsureMobileTokenIsValid, HandleAppearance, HandleInertiaRequests, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions (+6 more)

### Community 106 - "show.tsx"
Cohesion: 0.12
Nodes (20): IncidentActions(), ReportStatus, STATUS_CFG, formatDateTime(), generateStatusHistory(), INVALID_SEQUENCE, parseDate(), STANDARD_SEQUENCE (+12 more)

### Community 107 - "MobileNotificationService"
Cohesion: 0.14
Nodes (6): FireVerificationService, GeoBoundaryService, MobileNotificationService, CURLFile, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log

### Community 109 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.22
Nodes (3): IncidentDispatchTruck, RiskAssessment, Illuminate\Database\Eloquent\Model

### Community 111 - "2026_09_24_000002_harmonize_community_report_schema.php"
Cohesion: 0.53
Nodes (5): down(), downSqlite(), up(), upMysql(), upSqlite()

### Community 112 - "2026_09_24_000001_harmonize_incident_record_schema.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 113 - "2026_09_24_000005_harmonize_workflow_statuses_and_types.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 116 - "Inertia\Response"
Cohesion: 0.14
Nodes (12): Controller, FireMapController, RiskAnalyticsController, ProfileController, SecurityController, BarangayRiskSnapshot, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Contracts\Auth\MustVerifyEmail (+4 more)

### Community 118 - "FortifyServiceProvider.php"
Cohesion: 0.29
Nodes (4): Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\RateLimiter, Illuminate\Validation\Rules\Password, Laravel\Fortify\Fortify

### Community 120 - "main.py"
Cohesion: 0.18
Nodes (11): health_check(), load_model(), predict(), preprocess_image(), FIRESIGHT Fire Verification Service…, Resize + apply MobileNetV3 preprocessing (scale to [-1, 1]), matching…, get, ndarray (+3 more)

### Community 121 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.13
Nodes (7): IncidentActionController, ResidentController, Illuminate\Http\RedirectResponse, Illuminate\Support\Str, Illuminate\Validation\Rule, Illuminate\Validation\ValidationException, Pdo\Mysql

### Community 122 - "2026_10_18_205341_rename_users_id_to_user_id.php"
Cohesion: 0.73
Nodes (5): createForeignKeys(), down(), dropForeignKeys(), foreignKeysReferencingUsers(), up()

### Community 123 - "dashboard.tsx"
Cohesion: 0.20
Nodes (8): BarangayRiskPoint, KPICard(), StatusBadge(), RISK_CFG, IncidentTypeSlice, Kpis, MonthlyTrendPoint, RecentIncident

### Community 124 - "Quick Start (Local Development)"
Cohesion: 0.29
Nodes (6): 1. Create and Activate Virtual Environment, 2. Install Dependencies, 3. Run the Service, Endpoints, FireSight Fire Verification Service, Quick Start (Local Development)

### Community 127 - "alert.tsx"
Cohesion: 0.48
Nodes (5): AlertError(), Alert(), AlertDescription(), AlertTitle(), alertVariants

### Community 128 - "2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 129 - "cn"
Cohesion: 0.12
Nodes (25): Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage(), BreadcrumbSeparator(), DialogOverlay() (+17 more)

### Community 131 - "useIsMobile"
Cohesion: 0.53
Nodes (5): SidebarProvider(), getServerSnapshot(), isSmallerThanBreakpoint(), mediaQueryListener(), useIsMobile()

### Community 134 - "Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend., Source Nodes

### Community 135 - "Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them., Source Nodes

### Community 139 - "Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button., Source Nodes

### Community 140 - "Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors., Source Nodes

### Community 145 - "rename_dataset.py"
Cohesion: 0.67
Nodes (3): main(), Batch-renames all images inside a FIRESIGHT-style dataset folder: dataset_root/…, rename_folder()

### Community 151 - "app.tsx"
Cohesion: 0.20
Nodes (7): AuroraBackground(), LowlandLandscape(), Toaster(), useFlashToast(), AuthSimpleLayout(), AuthLayout(), LoginLayout()

### Community 154 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (6): CreateNewUser, ResetUserPassword, PasswordValidationRules, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 156 - "IncidentDispatch"
Cohesion: 0.17
Nodes (3): IncidentDispatch, Illuminate\Database\Eloquent\Attributes\ObservedBy, Illuminate\Database\Eloquent\Relations\BelongsToMany

## Knowledge Gaps
- **570 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+565 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 829 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **17 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Work-memory lessons

**Preferred sources** — corroborated by past sessions; start here.
- `portal-layout.tsx` (2× useful, score=1.265237404)
- `welcome.tsx` (2× useful, score=1.265237404)
- `Welcome()` (2× useful, score=1.265237404)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `DutyOverride`, `Illuminate\Http\Request`, `AdminNotifier`, `bootstrap/app.php`, `Notification`, `AuthToken`, `MobileNotificationService`, `BarangayContact`, `Laravel\Fortify\Features`, `CommunityReport`, `FireEducationContent`, `Announcement`, `FortifyServiceProvider.php`, `Illuminate\Http\RedirectResponse`, `PasswordValidationRules`, `Illuminate\Database\Seeder`?**
  _High betweenness centrality (0.051) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `react` to `welcome.tsx`, `cn`, `utils.ts`, `index.ts`, `incidents/index.tsx`, `residents/index.tsx`, `package.json`, `layout.tsx`, `show.tsx`, `two-factor-recovery-codes.tsx`, `map/index.tsx`, `personnel-form-modal.tsx`, `lucide-react`, `app.tsx`, `app-header.tsx`, `dashboard.tsx`, `portal-layout.tsx`, `sidebar.tsx`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `utils.ts`, `cn`, `incidents/index.tsx`, `index.ts`, `response-tracking/index.tsx`, `useIsMobile`, `package.json`, `map/index.tsx`, `personnel-form-modal.tsx`, `placeholder-pattern.tsx`, `lucide-react`, `app.tsx`, `toggle-group.tsx`, `portal-layout.tsx`, `sidebar.tsx`, `welcome.tsx`, `residents/index.tsx`, `two-factor-recovery-codes.tsx`, `risk-analytics/index.tsx`, `app-header.tsx`, `layout.tsx`, `show.tsx`, `alert.tsx`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _570 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `utils.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.05218365061590145 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._
- **Should `index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.14666666666666667 - nodes in this community are weakly interconnected._