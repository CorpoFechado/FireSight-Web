# Graph Report - firesight-web-v2  (2026-11-17)

## Corpus Check
- 462 files · ~689,985 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 36 file(s) not represented in the graph (top: (none) 22, .jmx 5, .example 1)

## Summary
- 2386 nodes · 5385 edges · 187 communities (121 shown, 13 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 25 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `b98cd638`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- User
- PhpWordSpotReportGenerator
- Illuminate\Database\Migrations\Migration
- index.ts
- duty-schedule/index.tsx
- dependencies
- ReportController.php
- package.json
- personnel/index.tsx
- AGENTS.md
- lucide-react
- Illuminate\Http\JsonResponse
- Inertia React Development
- app.tsx
- Announcement
- JmeterTestAccounts
- PasswordValidationRules
- two-factor-setup-modal.tsx
- dialog.tsx
- FireEducationContent
- fire-status.ts
- components.json
- Pest Testing 4
- risk-analytics/index.tsx
- Laravel Fortify Development
- compilerOptions
- app-sidebar.tsx
- devDependencies
- hero-section.tsx
- Carbon\CarbonInterface
- @inertiajs/react
- residents/index.tsx
- welcome.tsx
- Tailwind CSS Development
- Architecture Best Practices
- ResidentAddress
- RealisticIncidentDataSeeder
- scripts
- Illuminate\Database\Seeder
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
- show.tsx
- map/index.tsx
- Migration Best Practices
- Blade & Views Best Practices
- Error Handling Best Practices
- scripts
- require
- Task Scheduling Best Practices
- Testing Best Practices
- cn
- 2026_09_24_000006_update_risk_level_enum_to_critical.php
- WebPushTest.php
- config
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing & Controllers Best Practices
- laravel-best-practices/SKILL.md
- RiskLevel
- optionalDependencies
- use-appearance.tsx
- Conventions & Style
- Validation & Forms Best Practices
- psr-4
- laravel
- logging.php
- collapsible.tsx
- laravel-boost
- console.php
- Illuminate\Support\Facades\DB
- Barangay
- rules/graphify.md
- workflows/graphify.md
- VerifyEmailMail
- Q: Please help me analyze an approach and also work on a prompt after we work on the approach. In the system, there is currently a redirect to a Personnel Dashboard, however, we already removed the personnel for the web app thats why there is not much for the Personnel part of the app. I want to remove everything connected for the personnel within the web app such as redirects and I want a safe approach without breaking the functionality of both admin and mobile users. Just safely remove the personnel in web app.
- Illuminate\Http\Request
- analytics/index.tsx
- Illuminate\Console\Command
- Illuminate\Database\Schema\Blueprint
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- reliability-run.mjs
- Inertia\Response
- toggle-group.tsx
- portal-layout.tsx
- main.py
- IncidentRecord
- 2026_10_18_205341_rename_users_id_to_user_id.php
- PrivacyReporter
- Quick Start (Local Development)
- CommunityReport
- UserFactory
- 2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php
- FireSight Chapter 4 JMeter workflow
- portal-card.tsx
- Illuminate\Http\RedirectResponse
- predict_risk.py
- Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.
- Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them.
- JmeterAccountLifecycleTest.php
- Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.
- Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors.
- dashboard.tsx
- Illuminate\Support\Facades\Schema
- passkey-item.tsx
- create-manual-report-modal.tsx
- rename_dataset.py
- ResidentController
- react
- 2026_11_04_092454_add_verification_to_alarm_level_enums.php
- response-tracking/index.tsx
- Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?
- FireSight Risk Prediction Service
- GenerateVapidKeysCommand.php
- user-info.tsx
- Illuminate\Database\Eloquent\Relations\BelongsTo
- audience-section.tsx
- ISOLATION.md
- Chapter 4 Playwright execution results
- icon.tsx

## God Nodes (most connected - your core abstractions)
1. `User` - 155 edges
2. `cn()` - 131 edges
3. `CommunityReport` - 106 edges
4. `react` - 82 edges
5. `@inertiajs/react` - 74 edges
6. `lucide-react` - 64 edges
7. `Barangay` - 55 edges
8. `IncidentRecord` - 50 edges
9. `Notification` - 28 edges
10. `AlarmLevel` - 26 edges

## Surprising Connections (you probably didn't know these)
- `down()` --calls--> `AlarmLevel`  [EXTRACTED]
  database/migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php → app/Enums/AlarmLevel.php
- `up()` --calls--> `AlarmLevel`  [EXTRACTED]
  database/migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php → app/Enums/AlarmLevel.php
- `createTestBfpPersonnel()` --calls--> `AuthToken`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/AuthToken.php
- `createTestResident()` --calls--> `AuthToken`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/AuthToken.php
- `createResidentAddress()` --references--> `Barangay`  [EXTRACTED]
  tests/Feature/ResidentAccountsTest.php → app/Models/Barangay.php

## Import Cycles
- None detected.

## Communities (187 total, 13 thin omitted)

### Community 0 - "User"
Cohesion: 0.04
Nodes (37): PersonnelController, BfpPersonnelDetails, User, Attribute, BfpAccountSeeder, BfpPersonnelSeeder, Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword (+29 more)

### Community 1 - "PhpWordSpotReportGenerator"
Cohesion: 0.11
Nodes (14): AppServiceProvider, FortifyServiceProvider, PhpWordSpotReportGenerator, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password, PhpOffice\PhpWord\Element\Cell, PhpOffice\PhpWord\Element\Section, PhpOffice\PhpWord\Element\Table (+6 more)

### Community 3 - "index.ts"
Cohesion: 0.12
Nodes (20): AppContent(), Props, AppShell(), Props, AppSidebar(), AppSidebarHeader(), Breadcrumbs(), Breadcrumb() (+12 more)

### Community 4 - "duty-schedule/index.tsx"
Cohesion: 0.12
Nodes (30): DutyOverrideModal(), RemoveOverrideDialog(), UserInfo(), getInitial(), GetInitialsFn, useInitials(), addDays(), computeWeekEnd() (+22 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "ReportController.php"
Cohesion: 0.31
Nodes (3): ReportController, FireVerificationService, CURLFile

### Community 7 - "package.json"
Cohesion: 0.05
Nodes (39): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+31 more)

### Community 8 - "personnel/index.tsx"
Cohesion: 0.09
Nodes (31): @radix-ui/react-dropdown-menu, BarangayOption, ContactFormModal(), DeleteContactDialog(), NavUser(), DeletePersonnelDialog(), DropdownMenu(), DropdownMenuCheckboxItem() (+23 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "lucide-react"
Cohesion: 0.16
Nodes (17): lucide-react, InputError(), Props, PasskeyVerify(), Props, PasswordInput(), Props, TextLink() (+9 more)

### Community 11 - "Illuminate\Http\JsonResponse"
Cohesion: 0.12
Nodes (6): ApiController, DashboardController, IncidentController, NotificationController, ReportStatusHistory, Illuminate\Http\JsonResponse

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "app.tsx"
Cohesion: 0.19
Nodes (8): AuroraBackground(), Toaster(), useFlashToast(), AppLayout(), AuthSimpleLayout(), AuthLayout(), LoginLayout(), FlashToast

### Community 14 - "Announcement"
Cohesion: 0.13
Nodes (4): AnnouncementController, AnnouncementController, MapController, Announcement

### Community 15 - "JmeterTestAccounts"
Cohesion: 0.33
Nodes (3): JmeterTestAccounts, Closure, RuntimeException

### Community 16 - "PasswordValidationRules"
Cohesion: 0.10
Nodes (14): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+6 more)

### Community 17 - "two-factor-setup-modal.tsx"
Cohesion: 0.15
Nodes (14): input-otp, Props, TwoFactorSetupModal(), TwoFactorSetupStep(), InputOTP, InputOTPGroup, InputOTPSeparator, InputOTPSlot (+6 more)

### Community 18 - "dialog.tsx"
Cohesion: 0.11
Nodes (37): sonner, ContactRow, DeleteFireEducationDialog(), CATEGORY_OPTIONS, FireEducationFormModal(), FireEducationRow, FireEducationViewModal(), Barangay (+29 more)

### Community 19 - "FireEducationContent"
Cohesion: 0.32
Nodes (3): EducationController, FireEducationController, FireEducationContent

### Community 20 - "fire-status.ts"
Cohesion: 0.11
Nodes (22): AiVerificationCard(), AiVerificationCardProps, PersonnelStatusBadge(), RoleBadge(), AI_FIRE_LABEL_CFG, AiFireConfig, AiFireLabel, AlarmLevelCfg (+14 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "risk-analytics/index.tsx"
Cohesion: 0.11
Nodes (29): leaflet, react-leaflet, BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), detectBarangayName(), pointInRing(), fireIcon (+21 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "app-sidebar.tsx"
Cohesion: 0.11
Nodes (24): AppHeader(), AppLogo(), footerNavItems, mainNavItems, NavFooter(), NavMain(), SidebarContent(), SidebarFooter() (+16 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (17): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+9 more)

### Community 28 - "hero-section.tsx"
Cohesion: 0.24
Nodes (7): AppCtaSection(), ComingSoonButton(), ComingSoonButtonProps, HeroSection(), LowlandLandscape(), PhoneMockup(), quickActions

### Community 29 - "Carbon\CarbonInterface"
Cohesion: 0.06
Nodes (19): DutyGroup, AnalyticsController, DutyScheduleController, DutyOverride, DateRange, DutyRotation, CarbonInterface, IncidentTypeBreakdown (+11 more)

### Community 30 - "@inertiajs/react"
Cohesion: 0.14
Nodes (14): @inertiajs/react, Heading(), ManagePasskeys(), Props, ManageTwoFactor(), Props, PasskeyRegistration(), Separator() (+6 more)

### Community 31 - "residents/index.tsx"
Cohesion: 0.09
Nodes (21): PersonnelOption, PaginationBar(), AiFireBadge(), ALARM_LEVEL_OPTIONS, BarangayOption, Filters, IncidentRow, inputStyle (+13 more)

### Community 32 - "welcome.tsx"
Cohesion: 0.15
Nodes (14): Feature, features, FeaturesSection(), HowItWorksSection(), ProcessStep, steps, LandingFooter(), scrollToSection() (+6 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "ResidentAddress"
Cohesion: 0.12
Nodes (9): PurgeExpiredDeletedAccounts, ProfileController, EmailVerificationController, ResidentAddress, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\File, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log (+1 more)

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "Illuminate\Database\Seeder"
Cohesion: 0.10
Nodes (11): BarangayBoundarySeeder, BarangayContactSeeder, BarangaySeeder, DatabaseSeeder, FireEducationContentSeeder, HistoricalIncidentSeeder, IncidentReportSeeder, JmeterTestAdminSeeder (+3 more)

### Community 39 - "retrieval.spec.ts"
Cohesion: 0.17
Nodes (12): query(), Detail, incidentDetails(), queryStatus(), verifyExtendedIncidentDetail(), verifyIncidentDetail(), cookies, sample() (+4 more)

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
Cohesion: 0.14
Nodes (15): AlertError(), AppLogoIcon(), Props, TwoFactorRecoveryCodes(), Alert(), AlertDescription(), AlertTitle(), alertVariants (+7 more)

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
Cohesion: 0.26
Nodes (3): ContactController, BarangayContactController, BarangayContact

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

### Community 53 - "show.tsx"
Cohesion: 0.10
Nodes (22): IncidentActions(), ResolveReportModal(), AlarmLevel, ReportStatus, STATUS_CFG, formatDateTime(), generateStatusHistory(), INVALID_SEQUENCE (+14 more)

### Community 54 - "map/index.tsx"
Cohesion: 0.11
Nodes (20): ALARM_LEVEL_OPTIONS, BarangayFeature, BarangayGeoProperties, BarangayOption, BarangayRiskPoint, buildResultSummary(), ColorBy, Filters (+12 more)

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
Cohesion: 0.20
Nodes (10): require, inertiajs/inertia-laravel, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, laravel/wayfinder, minishlink/web-push (+2 more)

### Community 60 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Task Scheduling Best Practices, Use `environments()` to Restrict Tasks, Use `onOneServer()` on Multi-Server Deployments, Use `runInBackground()` for Concurrent Long Tasks, Use Schedule Groups for Shared Configuration, Use `takeUntilTimeout()` for Time-Bounded Processing, Use `withoutOverlapping()` on Variable-Duration Tasks

### Community 61 - "Testing Best Practices"
Cohesion: 0.25
Nodes (7): Call `Event::fake()` After Factory Setup, Testing Best Practices, Use `Exceptions::fake()` to Assert Exception Reporting, Use Factory States and Sequences, Use `LazilyRefreshDatabase` Over `RefreshDatabase`, Use Model Assertions Over Raw Database Assertions, Use `recycle()` to Share Relationship Instances Across Factories

### Community 62 - "cn"
Cohesion: 0.09
Nodes (41): @radix-ui/react-tooltip, mainNavItems, Props, rightNavItems, NavigationMenu(), NavigationMenuContent(), NavigationMenuIndicator(), NavigationMenuItem() (+33 more)

### Community 63 - "2026_09_24_000006_update_risk_level_enum_to_critical.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 64 - "WebPushTest.php"
Cohesion: 0.15
Nodes (11): PushSubscriptionController, PushSubscription, WebPush, WebPushService, GuzzleHttp\Psr7\Request, GuzzleHttp\Psr7\Response, Minishlink\WebPush\MessageSentReport, Minishlink\WebPush\Subscription (+3 more)

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

### Community 71 - "RiskLevel"
Cohesion: 0.18
Nodes (3): self, RiskLevel, RiskAssessment

### Community 72 - "optionalDependencies"
Cohesion: 0.29
Nodes (7): optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 73 - "use-appearance.tsx"
Cohesion: 0.19
Nodes (16): AppearanceToggleTab(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), isDarkMode(), listeners (+8 more)

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

### Community 82 - "Illuminate\Support\Facades\DB"
Cohesion: 0.22
Nodes (5): down(), downSqlite(), up(), upSqlite(), Illuminate\Support\Facades\DB

### Community 83 - "Barangay"
Cohesion: 0.08
Nodes (5): RecheckIncidentBarangays, Barangay, GeoBoundaryService, Geo, Illuminate\Database\Eloquent\Relations\HasMany

### Community 103 - "VerifyEmailMail"
Cohesion: 0.15
Nodes (12): PasswordResetOtpMail, Content, Envelope, Content, Envelope, VerifyEmailMail, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable (+4 more)

### Community 104 - "Q: Please help me analyze an approach and also work on a prompt after we work on the approach. In the system, there is currently a redirect to a Personnel Dashboard, however, we already removed the personnel for the web app thats why there is not much for the Personnel part of the app. I want to remove everything connected for the personnel within the web app such as redirects and I want a safe approach without breaking the functionality of both admin and mobile users. Just safely remove the personnel in web app."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Please help me analyze an approach and also work on a prompt after we work on the approach. In the system, there is currently a redirect to a Personnel Dashboard, however, we already removed the personnel for the web app thats why there is not much for the Personnel part of the app. I want to remove everything connected for the personnel within the web app such as redirects and I want a safe approach without breaking the functionality of both admin and mobile users. Just safely remove the personnel in web app., Source Nodes

### Community 105 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (15): AuthController, EnsureBfpAdmin, EnsureMobileTokenIsValid, HandleAppearance, HandleInertiaRequests, AuthToken, Closure, Illuminate\Foundation\Application (+7 more)

### Community 107 - "analytics/index.tsx"
Cohesion: 0.14
Nodes (12): recharts, AlarmLevelBar, BARANGAY_BAR_COLORS, BarangayIncidentPoint, CURRENT_YEAR, Filters, IncidentTypeSlice, inputStyle (+4 more)

### Community 109 - "Illuminate\Console\Command"
Cohesion: 0.14
Nodes (8): AlternateReportImages, CleanupJmeterTestAccounts, InspectJmeterTestAccounts, PredictFireRisk, VerifyJmeterEnvironment, Command, Illuminate\Console\Command, Symfony\Component\Process\Process

### Community 111 - "2026_09_24_000002_harmonize_community_report_schema.php"
Cohesion: 0.53
Nodes (5): down(), downSqlite(), up(), upMysql(), upSqlite()

### Community 112 - "2026_09_24_000001_harmonize_incident_record_schema.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 113 - "2026_09_24_000005_harmonize_workflow_statuses_and_types.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 115 - "reliability-run.mjs"
Cohesion: 0.20
Nodes (9): collect(), walk(), folder, hashSources(), visit(), initial, runCount, runs (+1 more)

### Community 116 - "Inertia\Response"
Cohesion: 0.11
Nodes (15): Controller, FireMapController, ResponseTrackingController, RiskAnalyticsController, ProfileController, SecurityController, BarangayRiskSnapshot, Illuminate\Auth\Middleware\RequirePassword (+7 more)

### Community 117 - "toggle-group.tsx"
Cohesion: 0.20
Nodes (11): class-variance-authority, @radix-ui/react-slot, @radix-ui/react-toggle, @radix-ui/react-toggle-group, Badge(), badgeVariants, ToggleGroup(), ToggleGroupContext (+3 more)

### Community 118 - "portal-layout.tsx"
Cohesion: 0.21
Nodes (12): getXsrfToken(), isPushApiSupported(), jsonRequest(), mapBrowserPermission(), registerServiceWorker(), urlBase64ToUint8Array(), useWebPush(), UseWebPushReturn (+4 more)

### Community 120 - "main.py"
Cohesion: 0.18
Nodes (11): health_check(), load_model(), predict(), preprocess_image(), FIRESIGHT Fire Verification Service…, Resize + apply MobileNetV3 preprocessing (scale to [-1, 1]), matching…, get, ndarray (+3 more)

### Community 121 - "IncidentRecord"
Cohesion: 0.08
Nodes (6): AlarmLevel, DashboardController, IncidentRecord, createFireMapIncident(), makeResolved(), createSpotReportIncident()

### Community 122 - "2026_10_18_205341_rename_users_id_to_user_id.php"
Cohesion: 0.73
Nodes (5): createForeignKeys(), down(), dropForeignKeys(), foreignKeysReferencingUsers(), up()

### Community 124 - "Quick Start (Local Development)"
Cohesion: 0.29
Nodes (6): 1. Create and Activate Virtual Environment, 2. Install Dependencies, 3. Run the Service, Endpoints, FireSight Fire Verification Service, Quick Start (Local Development)

### Community 126 - "CommunityReport"
Cohesion: 0.07
Nodes (11): IncidentController, NotificationController, CommunityReport, Notification, CommunityReportObserver, ExpoPushService, MobileNotificationService, AdminNotifier (+3 more)

### Community 127 - "UserFactory"
Cohesion: 0.18
Nodes (4): static, UserFactory, Illuminate\Support\Str, Pdo\Mysql

### Community 128 - "2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 129 - "FireSight Chapter 4 JMeter workflow"
Cohesion: 0.29
Nodes (6): Completed dedicated-account preflight, FireSight Chapter 4 JMeter workflow, PERF-001: execute manually when ready, Provisioning and private evidence, Safe cleanup after explicit authorization, Scope and assertions

### Community 130 - "portal-card.tsx"
Cohesion: 0.22
Nodes (8): NotificationItem(), NotificationRow, PushNotificationsCard(), PortalCard(), formatNotificationTime(), getNotificationVisual(), NotificationType, Groups

### Community 131 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.12
Nodes (7): IncidentActionController, SpotReportController, SpotReportGenerator, self, SpotReportData, Illuminate\Http\RedirectResponse, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 132 - "predict_risk.py"
Cohesion: 0.22
Nodes (11): build_features(), fetch_weather_open_meteo(), group_cause(), load_data(), main(), predict(), predict_risk_db.py - FireSight Live Database-Driven Fire Risk Prediction…, Loads demographics reference CSV, live MySQL incidents, and weather data. (+3 more)

### Community 134 - "Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend., Source Nodes

### Community 135 - "Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them., Source Nodes

### Community 138 - "JmeterAccountLifecycleTest.php"
Cohesion: 0.20
Nodes (4): Illuminate\Filesystem\Filesystem, Illuminate\Foundation\Testing\TestCase, TestCase, MemoryJmeterAccounts

### Community 139 - "Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button., Source Nodes

### Community 140 - "Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors., Source Nodes

### Community 141 - "dashboard.tsx"
Cohesion: 0.20
Nodes (8): BarangayRiskPoint, KPICard(), StatusBadge(), RISK_CFG, IncidentTypeSlice, Kpis, MonthlyTrendPoint, RecentIncident

### Community 143 - "passkey-item.tsx"
Cohesion: 0.13
Nodes (15): PasskeyItem(), Props, DialogClose(), DialogDescription(), DialogTrigger(), Auth, BfpRole, Passkey (+7 more)

### Community 144 - "create-manual-report-modal.tsx"
Cohesion: 0.20
Nodes (8): ALARM_LEVELS, CreateManualReportModal(), GeoJsonFeature, INCIDENT_TYPES, LIAN_CENTER, pinIcon, todayDateString(), ALARM_LEVEL_CFG

### Community 145 - "rename_dataset.py"
Cohesion: 0.67
Nodes (3): main(), Batch-renames all images inside a FIRESIGHT-style dataset folder: dataset_root/…, rename_folder()

### Community 149 - "react"
Cohesion: 0.31
Nodes (5): react, AnnouncementFormModal(), AnnouncementRow, DeleteAnnouncementDialog(), PlaceholderPatternProps

### Community 150 - "2026_11_04_092454_add_verification_to_alarm_level_enums.php"
Cohesion: 0.70
Nodes (4): changeAlarmLevelColumn(), changeAlarmLevelColumns(), down(), up()

### Community 151 - "response-tracking/index.tsx"
Cohesion: 0.22
Nodes (8): AlarmLevelBadge(), DispatchedIncident, Incident, incidentIcon(), ResponseTracking(), RouteSummary, Station, stationIcon

### Community 152 - "Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?"
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?, Source Nodes

### Community 153 - "FireSight Risk Prediction Service"
Cohesion: 0.20
Nodes (9): 1. Create a Python Virtual Environment, 2. Install Required Packages, A. Dry Run (Generates CSV output only, no database modification), B. Write to FireSight MySQL Database (`risk_assessment` table), C. Run via Laravel Artisan Command (from FireSight-Web root), FireSight Risk Prediction Service, Running Predictions, Service Contents (+1 more)

### Community 154 - "GenerateVapidKeysCommand.php"
Cohesion: 0.38
Nodes (3): GenerateVapidKeysCommand, Base64Url\Base64Url, Minishlink\WebPush\VAPID

### Community 155 - "user-info.tsx"
Cohesion: 0.53
Nodes (4): @radix-ui/react-avatar, Avatar(), AvatarFallback(), AvatarImage()

### Community 156 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.06
Nodes (13): AforReport, EmergencyContact, IncidentDispatch, IncidentDispatchPersonnel, IncidentDispatchTruck, ReportEvidence, RiskVulnerabilityChecklist, Carbon\Carbon (+5 more)

### Community 161 - "audience-section.tsx"
Cohesion: 0.50
Nodes (3): Audience, audiences, AudienceSection()

## Knowledge Gaps
- **612 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+607 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 942 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **13 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Work-memory lessons

**Preferred sources** — corroborated by past sessions; start here.
- `AnalyticsController` (2× useful, score=1.49446942)
- `ALARM_LEVEL_CFG` (2× useful, score=1.49446942)
- `portal-layout.tsx` (2× useful, score=0.944051581) _(code changed — re-verify)_
- `welcome.tsx` (2× useful, score=0.944051581)
- `Welcome()` (2× useful, score=0.944051581)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `PhpWordSpotReportGenerator`, `Illuminate\Http\RedirectResponse`, `ReportController.php`, `Illuminate\Http\JsonResponse`, `Announcement`, `PasswordValidationRules`, `ResidentController`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Carbon\CarbonInterface`, `ResidentAddress`, `Illuminate\Database\Seeder`, `WebPushTest.php`, `RiskLevel`, `Barangay`, `VerifyEmailMail`, `Illuminate\Http\Request`, `Inertia\Response`, `IncidentRecord`, `CommunityReport`, `UserFactory`?**
  _High betweenness centrality (0.058) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `portal-card.tsx`, `index.ts`, `duty-schedule/index.tsx`, `package.json`, `personnel/index.tsx`, `lucide-react`, `app.tsx`, `passkey-item.tsx`, `create-manual-report-modal.tsx`, `two-factor-setup-modal.tsx`, `dialog.tsx`, `risk-analytics/index.tsx`, `response-tracking/index.tsx`, `app-sidebar.tsx`, `user-info.tsx`, `hero-section.tsx`, `@inertiajs/react`, `residents/index.tsx`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `show.tsx`, `map/index.tsx`, `cn`, `use-appearance.tsx`, `toggle-group.tsx`, `portal-layout.tsx`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `@inertiajs/react` to `portal-card.tsx`, `index.ts`, `duty-schedule/index.tsx`, `package.json`, `personnel/index.tsx`, `lucide-react`, `app.tsx`, `dashboard.tsx`, `create-manual-report-modal.tsx`, `two-factor-setup-modal.tsx`, `dialog.tsx`, `fire-status.ts`, `react`, `response-tracking/index.tsx`, `app-sidebar.tsx`, `hero-section.tsx`, `residents/index.tsx`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `show.tsx`, `map/index.tsx`, `cn`, `use-appearance.tsx`, `analytics/index.tsx`, `portal-layout.tsx`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _612 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.04020570359981299 - nodes in this community are weakly interconnected._
- **Should `PhpWordSpotReportGenerator` be split into smaller, more focused modules?**
  _Cohesion score 0.1066066066066066 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._