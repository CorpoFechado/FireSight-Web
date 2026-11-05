# Graph Report - firesight-web-v2  (2026-11-05)

## Corpus Check
- 415 files · ~654,996 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 27 file(s) not represented in the graph (top: (none) 20, .example 1, .log 1)

## Summary
- 2115 nodes · 4739 edges · 174 communities (105 shown, 27 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 29 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `26a87606`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- two-factor-setup-modal.tsx
- incidents/index.tsx
- Illuminate\Database\Migrations\Migration
- index.ts
- barangay-risk-map.tsx
- dependencies
- Illuminate\Http\Request
- package.json
- duty-schedule/index.tsx
- AGENTS.md
- lucide-react
- vite.config.ts
- Inertia React Development
- map/index.tsx
- ApiController
- Laravel\Fortify\Features
- PasswordValidationRules
- DatabaseSeeder
- react
- FireEducationContent
- fire-status.ts
- components.json
- Pest Testing 4
- personnel/index.tsx
- Laravel Fortify Development
- compilerOptions
- cn
- devDependencies
- analytics/index.tsx
- .index
- sidebar.tsx
- residents/index.tsx
- welcome.tsx
- Tailwind CSS Development
- Architecture Best Practices
- ReportController.php
- RealisticIncidentDataSeeder
- scripts
- DutyOverride
- retrieval.spec.ts
- Queue & Job Best Practices
- composer.json
- require-dev
- Security Best Practices
- two-factor-recovery-codes.tsx
- Advanced Query Patterns
- Database Performance Best Practices
- eslint.config.js
- BarangayContact
- Events & Notifications Best Practices
- Wayfinder Development
- Caching Best Practices
- Eloquent Best Practices
- DutyOverride.php
- utils.ts
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
- FortifyServiceProvider.php
- optionalDependencies
- risk-analytics/index.tsx
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
- User.php
- bootstrap/app.php
- show.tsx
- @inertiajs/react
- Illuminate\Http\RedirectResponse
- Illuminate\Database\Eloquent\Model
- Illuminate\Database\Schema\Blueprint
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- Illuminate\Support\Facades\Schema
- PersonnelController
- Inertia\Response
- ProfileValidationRules
- RiskLevel
- main.py
- IncidentRecord
- 2026_10_18_205341_rename_users_id_to_user_id.php
- dashboard.tsx
- Quick Start (Local Development)
- Carbon\CarbonInterface
- UserFactory
- 2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php
- Illuminate\Support\Carbon
- ReportEvidence
- CommunityReport.php
- Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.
- Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them.
- Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.
- Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors.
- FortifyServiceProvider
- RiskVulnerabilityChecklist
- rename_dataset.py
- TestCase
- placeholder-pattern.tsx
- 2026_11_04_092454_add_verification_to_alarm_level_enums.php
- use-appearance.tsx
- Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?
- ResetUserPassword.php
- IncidentDispatch
- icon.tsx
- ExpoPushService
- .weekStartFor
- ProfileUpdateRequest
- results.md

## God Nodes (most connected - your core abstractions)
1. `User` - 131 edges
2. `cn()` - 131 edges
3. `CommunityReport` - 90 edges
4. `react` - 80 edges
5. `@inertiajs/react` - 72 edges
6. `lucide-react` - 61 edges
7. `Barangay` - 55 edges
8. `IncidentRecord` - 43 edges
9. `AlarmLevel` - 26 edges
10. `Notification` - 25 edges

## Surprising Connections (you probably didn't know these)
- `down()` --calls--> `AlarmLevel`  [EXTRACTED]
  database/migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php → app/Enums/AlarmLevel.php
- `up()` --calls--> `AlarmLevel`  [EXTRACTED]
  database/migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php → app/Enums/AlarmLevel.php
- `makeCommunityReport()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/AdminNotificationTest.php → app/Models/CommunityReport.php
- `createFireMapIncident()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/FireMapBarangayFilterTest.php → app/Models/CommunityReport.php
- `makeResolved()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/CommunityReport.php

## Import Cycles
- None detected.

## Communities (174 total, 27 thin omitted)

### Community 0 - "two-factor-setup-modal.tsx"
Cohesion: 0.12
Nodes (18): input-otp, ManageTwoFactor(), Props, TwoFactorRecoveryCodes(), Props, TwoFactorSetupModal(), TwoFactorSetupStep(), InputOTP (+10 more)

### Community 1 - "incidents/index.tsx"
Cohesion: 0.16
Nodes (12): PaginationBar(), AlarmLevelBadge(), ALARM_LEVEL_CFG, ALARM_LEVEL_OPTIONS, BarangayOption, Filters, IncidentRow, inputStyle (+4 more)

### Community 3 - "index.ts"
Cohesion: 0.09
Nodes (21): AppContent(), Props, AppShell(), Props, AppSidebar(), AppSidebarHeader(), AuroraBackground(), Breadcrumbs() (+13 more)

### Community 4 - "barangay-risk-map.tsx"
Cohesion: 0.14
Nodes (18): leaflet, react-leaflet, BarangayFeature, BarangayGeoProperties, fireIcon, formatDistance(), IncidentLocationMap(), reporterIcon (+10 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (10): DashboardController, IncidentController, NotificationController, ProfileController, IncidentController, CommunityReport, ReportStatusHistory, Carbon\Carbon (+2 more)

### Community 7 - "package.json"
Cohesion: 0.06
Nodes (32): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+24 more)

### Community 8 - "duty-schedule/index.tsx"
Cohesion: 0.18
Nodes (24): DutyOverrideModal(), RemoveOverrideDialog(), addDays(), computeWeekEnd(), DUTY_COLORS, DutyGroup, DutySchedulePageProps, formatChipDate() (+16 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "lucide-react"
Cohesion: 0.12
Nodes (21): lucide-react, InputError(), ManagePasskeys(), Props, PasskeyItem(), PasskeyRegistration(), Props, PasskeyVerify() (+13 more)

### Community 11 - "vite.config.ts"
Cohesion: 0.29
Nodes (6): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite, @vitejs/plugin-react

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "map/index.tsx"
Cohesion: 0.11
Nodes (19): ALARM_LEVEL_OPTIONS, BarangayFeature, BarangayGeoProperties, BarangayOption, BarangayRiskPoint, buildResultSummary(), ColorBy, Filters (+11 more)

### Community 14 - "ApiController"
Cohesion: 0.11
Nodes (5): AnnouncementController, AnnouncementController, ApiController, MapController, Announcement

### Community 15 - "Laravel\Fortify\Features"
Cohesion: 0.18
Nodes (7): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL, Laravel\Fortify\Features

### Community 16 - "PasswordValidationRules"
Cohesion: 0.20
Nodes (7): PasswordValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 18 - "react"
Cohesion: 0.13
Nodes (33): react, DeleteFireEducationDialog(), CATEGORY_OPTIONS, FireEducationFormModal(), FireEducationRow, FireEducationViewModal(), Barangay, ALARM_LEVELS (+25 more)

### Community 19 - "FireEducationContent"
Cohesion: 0.19
Nodes (6): EducationController, FireEducationController, FireEducationContent, FireEducationContentSeeder, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Storage

### Community 20 - "fire-status.ts"
Cohesion: 0.11
Nodes (22): AiVerificationCard(), AiVerificationCardProps, PersonnelStatusBadge(), RoleBadge(), AI_FIRE_LABEL_CFG, AiFireConfig, AiFireLabel, AlarmLevelCfg (+14 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "personnel/index.tsx"
Cohesion: 0.09
Nodes (31): BarangayOption, ContactFormModal(), ContactRow, DeleteContactDialog(), DeletePersonnelDialog(), PersonnelFormModal(), PersonnelRow, DropdownMenu() (+23 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "cn"
Cohesion: 0.08
Nodes (37): class-variance-authority, @radix-ui/react-navigation-menu, @radix-ui/react-slot, @radix-ui/react-toggle, @radix-ui/react-toggle-group, Badge(), badgeVariants, Breadcrumb() (+29 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (17): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+9 more)

### Community 28 - "analytics/index.tsx"
Cohesion: 0.14
Nodes (12): recharts, AlarmLevelBar, BARANGAY_BAR_COLORS, BarangayIncidentPoint, CURRENT_YEAR, Filters, IncidentTypeSlice, inputStyle (+4 more)

### Community 30 - "sidebar.tsx"
Cohesion: 0.10
Nodes (34): footerNavItems, mainNavItems, NavFooter(), NavMain(), NavUser(), SheetDescription(), Sidebar(), SidebarContent() (+26 more)

### Community 31 - "residents/index.tsx"
Cohesion: 0.14
Nodes (13): sonner, Toaster(), useFlashToast(), BarangayOption, Confirmation, formatDateTime(), ResidentIndex(), ResidentProfile (+5 more)

### Community 32 - "welcome.tsx"
Cohesion: 0.06
Nodes (37): AppCtaSection(), Audience, audiences, AudienceSection(), ComingSoonButton(), ComingSoonButtonProps, Feature, features (+29 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "ReportController.php"
Cohesion: 0.12
Nodes (10): RecheckIncidentBarangays, ReportController, FireVerificationService, GeoBoundaryService, MobileNotificationService, Geo, CURLFile, Illuminate\Console\Command (+2 more)

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "DutyOverride"
Cohesion: 0.14
Nodes (6): DutyGroup, DutyScheduleController, DutyOverride, DutyRotation, Carbon, self

### Community 39 - "retrieval.spec.ts"
Cohesion: 0.17
Nodes (8): query(), Detail, incidentDetails(), verifyIncidentDetail(), cookies, Incident, sample(), @playwright/test

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
Cohesion: 0.18
Nodes (13): AlertError(), AppLogoIcon(), Props, Alert(), AlertDescription(), AlertTitle(), alertVariants, Card() (+5 more)

### Community 45 - "Advanced Query Patterns"
Cohesion: 0.20
Nodes (9): Advanced Query Patterns, Create Dynamic Relationships via Subquery FK, Prefer `whereIn` + Subquery Over `whereHas`, Sometimes Two Simple Queries Beat One Complex Query, Use `addSelect()` Subqueries for Single Values from Has-Many, Use Compound Indexes Matching `orderBy` Column Order, Use Conditional Aggregates Instead of Multiple Count Queries, Use Correlated Subqueries for Has-Many Ordering (+1 more)

### Community 46 - "Database Performance Best Practices"
Cohesion: 0.20
Nodes (9): Add Database Indexes, Always Eager Load Relationships, Chunk Large Datasets, Database Performance Best Practices, No Queries in Blade Templates, Prevent Lazy Loading in Development, Select Only Needed Columns, Use `cursor()` for Memory-Efficient Iteration (+1 more)

### Community 47 - "eslint.config.js"
Cohesion: 0.18
Nodes (9): controlStatements, paddingAroundControl, @eslint/js, eslint-plugin-import, eslint-plugin-react, eslint-plugin-react-hooks, globals, @stylistic/eslint-plugin (+1 more)

### Community 48 - "BarangayContact"
Cohesion: 0.19
Nodes (4): ContactController, BarangayContactController, BarangayContact, EmergencyContact

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

### Community 53 - "DutyOverride.php"
Cohesion: 0.19
Nodes (6): DutyOverrideFactory, FireEducationContentFactory, static, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory

### Community 54 - "utils.ts"
Cohesion: 0.19
Nodes (11): AppHeader(), Heading(), Separator(), IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn, WhenCurrentUrlFn (+3 more)

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
Nodes (21): @radix-ui/react-avatar, @radix-ui/react-dialog, @radix-ui/react-tooltip, mainNavItems, Props, rightNavItems, AppLogo(), Avatar() (+13 more)

### Community 63 - "2026_09_24_000006_update_risk_level_enum_to_critical.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

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

### Community 71 - "FortifyServiceProvider.php"
Cohesion: 0.21
Nodes (6): AppServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password, Laravel\Fortify\Fortify

### Community 72 - "optionalDependencies"
Cohesion: 0.29
Nodes (7): optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 73 - "risk-analytics/index.tsx"
Cohesion: 0.18
Nodes (16): BarangayRiskMap(), BARANGAY_GEOJSON_URL, GEOJSON_TO_DB_BARANGAY_NAME, getFeatureBarangayName(), resolveDbBarangayName(), RiskLevel, riskLevelColor(), riskMapPolygonStyle() (+8 more)

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
Cohesion: 0.04
Nodes (23): PurgeExpiredDeletedAccounts, AuthController, AuthToken, Barangay, BfpPersonnelDetails, Notification, ResidentAddress, User (+15 more)

### Community 103 - "PasswordResetOtpMail"
Cohesion: 0.20
Nodes (9): PasswordResetOtpMail, Content, Envelope, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Attachment, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+1 more)

### Community 104 - "User.php"
Cohesion: 0.17
Nodes (10): Attribute, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Casts\Attribute, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Fortify\Contracts\PasskeyUser, Laravel\Fortify\PasskeyAuthenticatable (+2 more)

### Community 105 - "bootstrap/app.php"
Cohesion: 0.12
Nodes (14): EnsureBfpAdmin, EnsureBfpStaff, EnsureMobileTokenIsValid, HandleAppearance, HandleInertiaRequests, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions (+6 more)

### Community 106 - "show.tsx"
Cohesion: 0.10
Nodes (23): IncidentActions(), ResolveReportModal(), AiFireBadge(), AlarmLevel, ReportStatus, STATUS_CFG, formatDateTime(), generateStatusHistory() (+15 more)

### Community 107 - "@inertiajs/react"
Cohesion: 0.16
Nodes (12): @inertiajs/react, AnnouncementFormModal(), AnnouncementRow, DeleteAnnouncementDialog(), NotificationItem(), NotificationRow, PortalCard(), PortalLayout() (+4 more)

### Community 108 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.13
Nodes (7): IncidentActionController, ResidentController, Illuminate\Http\RedirectResponse, Illuminate\Support\Str, Illuminate\Validation\Rule, Illuminate\Validation\ValidationException, Pdo\Mysql

### Community 109 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.18
Nodes (3): IncidentDispatchPersonnel, IncidentDispatchTruck, Illuminate\Database\Eloquent\Model

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
Cohesion: 0.08
Nodes (15): AlarmLevel, Controller, DashboardController, FireMapController, NotificationController, RiskAnalyticsController, ProfileController, SecurityController (+7 more)

### Community 117 - "ProfileValidationRules"
Cohesion: 0.39
Nodes (3): CreateNewUser, ProfileValidationRules, Laravel\Fortify\Contracts\CreatesNewUsers

### Community 120 - "main.py"
Cohesion: 0.18
Nodes (11): health_check(), load_model(), predict(), preprocess_image(), FIRESIGHT Fire Verification Service…, Resize + apply MobileNetV3 preprocessing (scale to [-1, 1]), matching…, get, ndarray (+3 more)

### Community 121 - "IncidentRecord"
Cohesion: 0.09
Nodes (12): IncidentRecord, BarangayBoundarySeeder, BarangayContactSeeder, BarangaySeeder, DummyDataSeeder, IncidentReportSeeder, Illuminate\Database\Seeder, Illuminate\Support\Facades\DB (+4 more)

### Community 122 - "2026_10_18_205341_rename_users_id_to_user_id.php"
Cohesion: 0.73
Nodes (5): createForeignKeys(), down(), dropForeignKeys(), foreignKeysReferencingUsers(), up()

### Community 123 - "dashboard.tsx"
Cohesion: 0.20
Nodes (8): BarangayRiskPoint, KPICard(), StatusBadge(), RISK_CFG, IncidentTypeSlice, Kpis, MonthlyTrendPoint, RecentIncident

### Community 124 - "Quick Start (Local Development)"
Cohesion: 0.29
Nodes (6): 1. Create and Activate Virtual Environment, 2. Install Dependencies, 3. Run the Service, Endpoints, FireSight Fire Verification Service, Quick Start (Local Development)

### Community 128 - "2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

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

### Community 150 - "2026_11_04_092454_add_verification_to_alarm_level_enums.php"
Cohesion: 0.70
Nodes (4): changeAlarmLevelColumn(), changeAlarmLevelColumns(), down(), up()

### Community 151 - "use-appearance.tsx"
Cohesion: 0.22
Nodes (16): AppearanceToggleTab(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), isDarkMode(), listeners (+8 more)

### Community 152 - "Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?"
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?, Source Nodes

### Community 154 - "ResetUserPassword.php"
Cohesion: 0.50
Nodes (3): ResetUserPassword, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 171 - ".weekStartFor"
Cohesion: 0.40
Nodes (3): CarbonInterface, Carbon\CarbonImmutable, CarbonImmutable

## Knowledge Gaps
- **579 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+574 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 846 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **27 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Work-memory lessons

**Preferred sources** — corroborated by past sessions; start here.
- `portal-layout.tsx` (2× useful, score=1.261799436)
- `welcome.tsx` (2× useful, score=1.261799436)
- `Welcome()` (2× useful, score=1.261799436)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `ReportController.php`, `Illuminate\Http\Request`, `DutyOverride`, `User.php`, `bootstrap/app.php`, `Illuminate\Http\RedirectResponse`, `ApiController`, `Laravel\Fortify\Features`, `BarangayContact`, `PersonnelController`, `Inertia\Response`, `ProfileValidationRules`, `DutyOverride.php`, `FireEducationContent`, `IncidentRecord`, `ResetUserPassword.php`, `Carbon\CarbonInterface`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `@inertiajs/react` to `two-factor-setup-modal.tsx`, `incidents/index.tsx`, `index.ts`, `package.json`, `duty-schedule/index.tsx`, `lucide-react`, `map/index.tsx`, `react`, `fire-status.ts`, `personnel/index.tsx`, `cn`, `analytics/index.tsx`, `sidebar.tsx`, `residents/index.tsx`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `utils.ts`, `app-header.tsx`, `show.tsx`, `dashboard.tsx`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `two-factor-setup-modal.tsx`, `incidents/index.tsx`, `index.ts`, `barangay-risk-map.tsx`, `package.json`, `duty-schedule/index.tsx`, `lucide-react`, `map/index.tsx`, `placeholder-pattern.tsx`, `personnel/index.tsx`, `use-appearance.tsx`, `cn`, `sidebar.tsx`, `residents/index.tsx`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `utils.ts`, `app-header.tsx`, `risk-analytics/index.tsx`, `show.tsx`, `@inertiajs/react`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _579 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `two-factor-setup-modal.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.1225071225071225 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._
- **Should `index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.08974358974358974 - nodes in this community are weakly interconnected._