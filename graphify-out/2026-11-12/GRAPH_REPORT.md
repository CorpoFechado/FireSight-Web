# Graph Report - firesight-web-v2  (2026-11-12)

## Corpus Check
- 440 files · ~680,576 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 34 file(s) not represented in the graph (top: (none) 21, .jmx 5, .example 1)

## Summary
- 2228 nodes · 5000 edges · 178 communities (108 shown, 22 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 32 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `11ba78e6`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Support\Facades\DB
- react
- Illuminate\Database\Migrations\Migration
- index.ts
- nav-user.tsx
- dependencies
- Illuminate\Http\Request
- package.json
- duty-schedule/index.tsx
- AGENTS.md
- two-factor-setup-modal.tsx
- vite.config.ts
- Inertia React Development
- map/index.tsx
- Announcement
- JmeterTestAccounts
- Controller
- RealisticIncidentDataSeeder.php
- edit-details-modal.tsx
- FireEducationContent
- fire-status.ts
- components.json
- Pest Testing 4
- personnel/index.tsx
- Laravel Fortify Development
- compilerOptions
- cn
- devDependencies
- Barangay
- Carbon\CarbonInterface
- sidebar.tsx
- residents/index.tsx
- welcome.tsx
- Tailwind CSS Development
- Architecture Best Practices
- Geo
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
- FortifyServiceProvider
- optionalDependencies
- Illuminate\Http\RedirectResponse
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
- VerifyEmailMail
- portal-layout.tsx
- bootstrap/app.php
- status-history.ts
- lucide-react
- ResidentController
- Illuminate\Database\Eloquent\Model
- Illuminate\Database\Schema\Blueprint
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- reliability-run.mjs
- Inertia\Response
- toggle-group.tsx
- RiskLevel
- breadcrumbs.tsx
- main.py
- IncidentRecord
- 2026_10_18_205341_rename_users_id_to_user_id.php
- PrivacyReporter
- Quick Start (Local Development)
- CommunityReport
- UserFactory
- 2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php
- FireSight Chapter 4 JMeter workflow
- ReportEvidence
- EmailVerificationController.php
- AforReport
- Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.
- Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them.
- global.d.ts
- Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.
- Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors.
- Illuminate\Support\Facades\Schema
- RiskVulnerabilityChecklist
- rename_dataset.py
- placeholder-pattern.tsx
- 2026_11_04_092454_add_verification_to_alarm_level_enums.php
- Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?
- FortifyServiceProvider.php
- IncidentDispatch
- DutyRotation
- ISOLATION.md
- Chapter 4 Playwright execution results

## God Nodes (most connected - your core abstractions)
1. `User` - 145 edges
2. `cn()` - 131 edges
3. `CommunityReport` - 91 edges
4. `react` - 80 edges
5. `@inertiajs/react` - 72 edges
6. `lucide-react` - 62 edges
7. `Barangay` - 52 edges
8. `IncidentRecord` - 43 edges
9. `Notification` - 26 edges
10. `JmeterTestAccounts` - 26 edges

## Surprising Connections (you probably didn't know these)
- `down()` --calls--> `AlarmLevel`  [EXTRACTED]
  database/migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php → app/Enums/AlarmLevel.php
- `up()` --calls--> `AlarmLevel`  [EXTRACTED]
  database/migrations/2026_11_04_092454_add_verification_to_alarm_level_enums.php → app/Enums/AlarmLevel.php
- `createResidentAddress()` --references--> `Barangay`  [EXTRACTED]
  tests/Feature/ResidentAccountsTest.php → app/Models/Barangay.php
- `createFireMapIncident()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/FireMapBarangayFilterTest.php → app/Models/CommunityReport.php
- `makeResolved()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/CommunityReport.php

## Import Cycles
- None detected.

## Communities (178 total, 22 thin omitted)

### Community 0 - "Illuminate\Support\Facades\DB"
Cohesion: 0.07
Nodes (18): DutyGroup, BfpPersonnelDetails, BarangayBoundarySeeder, BfpAccountSeeder, BfpPersonnelSeeder, DummyDataSeeder, JmeterTestAdminSeeder, Illuminate\Database\Seeder (+10 more)

### Community 1 - "react"
Cohesion: 0.26
Nodes (15): @inertiajs/react, react, sonner, AnnouncementFormModal(), AnnouncementRow, DeleteAnnouncementDialog(), Props, Dialog() (+7 more)

### Community 3 - "index.ts"
Cohesion: 0.08
Nodes (25): AppContent(), Props, AppShell(), Props, AppSidebarHeader(), AuroraBackground(), Breadcrumbs(), LowlandLandscape() (+17 more)

### Community 4 - "nav-user.tsx"
Cohesion: 0.14
Nodes (18): @radix-ui/react-avatar, Avatar(), AvatarFallback(), AvatarImage(), DropdownMenu(), DropdownMenuContent(), DropdownMenuGroup(), DropdownMenuTrigger() (+10 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (16): PurgeExpiredDeletedAccounts, AnnouncementController, ApiController, AuthController, DashboardController, MapController, NotificationController, ProfileController (+8 more)

### Community 7 - "package.json"
Cohesion: 0.06
Nodes (35): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+27 more)

### Community 8 - "duty-schedule/index.tsx"
Cohesion: 0.18
Nodes (24): DutyOverrideModal(), RemoveOverrideDialog(), addDays(), computeWeekEnd(), DUTY_COLORS, DutyGroup, DutySchedulePageProps, formatChipDate() (+16 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "two-factor-setup-modal.tsx"
Cohesion: 0.05
Nodes (54): input-otp, AppearanceToggleTab(), Heading(), InputError(), ManagePasskeys(), Props, ManageTwoFactor(), Props (+46 more)

### Community 11 - "vite.config.ts"
Cohesion: 0.29
Nodes (6): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite, @vitejs/plugin-react

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "map/index.tsx"
Cohesion: 0.06
Nodes (50): leaflet, react-leaflet, BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), BarangayRiskPoint, fireIcon, formatDistance() (+42 more)

### Community 15 - "JmeterTestAccounts"
Cohesion: 0.07
Nodes (16): CleanupJmeterTestAccounts, InspectJmeterTestAccounts, VerifyJmeterEnvironment, JmeterTestAccounts, Closure, Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail (+8 more)

### Community 16 - "Controller"
Cohesion: 0.10
Nodes (14): ProfileValidationRules, Controller, ProfileController, SecurityController, PasswordUpdateRequest, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest (+6 more)

### Community 18 - "edit-details-modal.tsx"
Cohesion: 0.13
Nodes (25): DeleteFireEducationDialog(), CATEGORY_OPTIONS, FireEducationFormModal(), FireEducationRow, FireEducationViewModal(), Barangay, ALARM_LEVELS, combineWithBaseDate() (+17 more)

### Community 19 - "FireEducationContent"
Cohesion: 0.19
Nodes (6): EducationController, FireEducationController, FireEducationContent, FireEducationContentSeeder, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Storage

### Community 20 - "fire-status.ts"
Cohesion: 0.06
Nodes (47): AiVerificationCard(), AiVerificationCardProps, IncidentActions(), ResolveReportModal(), AiFireBadge(), AlarmLevelBadge(), StatusBadge(), AI_FIRE_LABEL_CFG (+39 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "personnel/index.tsx"
Cohesion: 0.10
Nodes (21): BarangayOption, ContactFormModal(), ContactRow, DeleteContactDialog(), DeletePersonnelDialog(), PersonnelRow, PersonnelStatusBadge(), RoleBadge() (+13 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "cn"
Cohesion: 0.10
Nodes (26): @radix-ui/react-checkbox, @radix-ui/react-dropdown-menu, Checkbox(), DialogOverlay(), DropdownMenuCheckboxItem(), DropdownMenuItem(), DropdownMenuSeparator(), DropdownMenuShortcut() (+18 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (17): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+9 more)

### Community 28 - "Barangay"
Cohesion: 0.11
Nodes (4): Barangay, BarangayContactSeeder, BarangaySeeder, Illuminate\Database\Eloquent\Relations\HasMany

### Community 29 - "Carbon\CarbonInterface"
Cohesion: 0.23
Nodes (4): AnalyticsController, DateRange, IncidentTypeBreakdown, Carbon\CarbonInterface

### Community 30 - "sidebar.tsx"
Cohesion: 0.11
Nodes (30): AppSidebar(), footerNavItems, mainNavItems, NavFooter(), NavMain(), NavUser(), SheetDescription(), Sidebar() (+22 more)

### Community 31 - "residents/index.tsx"
Cohesion: 0.15
Nodes (12): PaginationBar(), BarangayOption, Confirmation, formatDateTime(), ResidentIndex(), ResidentProfile, ResidentReport, ResidentResponse (+4 more)

### Community 32 - "welcome.tsx"
Cohesion: 0.09
Nodes (23): AppCtaSection(), Audience, audiences, AudienceSection(), ComingSoonButton(), ComingSoonButtonProps, Feature, features (+15 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "Geo"
Cohesion: 0.09
Nodes (11): AlternateReportImages, RecheckIncidentBarangays, ResponseTrackingController, FireVerificationService, GeoBoundaryService, Geo, Command, CURLFile (+3 more)

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "DutyOverride"
Cohesion: 0.29
Nodes (3): DutyScheduleController, DutyOverride, Carbon

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
Cohesion: 0.16
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
Cohesion: 0.25
Nodes (10): AppHeader(), Separator(), IsCurrentOrParentUrlFn, IsCurrentUrlFn, useCurrentUrl(), UseCurrentUrlReturn, WhenCurrentUrlFn, SettingsLayout() (+2 more)

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
Cohesion: 0.15
Nodes (14): mainNavItems, Props, rightNavItems, AppLogo(), Sheet(), SheetContent(), SheetFooter(), SheetHeader() (+6 more)

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

### Community 71 - "FortifyServiceProvider"
Cohesion: 0.23
Nodes (3): AppServiceProvider, FortifyServiceProvider, Illuminate\Support\ServiceProvider

### Community 72 - "optionalDependencies"
Cohesion: 0.29
Nodes (7): optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 73 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.31
Nodes (3): IncidentActionController, NotificationController, Illuminate\Http\RedirectResponse

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
Nodes (19): PersonnelController, User, Attribute, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Casts\Attribute, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Foundation\Auth\User (+11 more)

### Community 103 - "VerifyEmailMail"
Cohesion: 0.15
Nodes (12): PasswordResetOtpMail, Content, Envelope, Content, Envelope, VerifyEmailMail, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable (+4 more)

### Community 104 - "portal-layout.tsx"
Cohesion: 0.23
Nodes (9): navItemsForRole(), PORTAL_NAV, PortalNavItem, PortalSidebar(), BfpRole, Passkey, PersonnelStatus, TwoFactorSecretKey (+1 more)

### Community 105 - "bootstrap/app.php"
Cohesion: 0.13
Nodes (12): EnsureBfpAdmin, EnsureBfpStaff, HandleAppearance, HandleInertiaRequests, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+4 more)

### Community 106 - "status-history.ts"
Cohesion: 0.28
Nodes (8): formatDateTime(), generateStatusHistory(), INVALID_SEQUENCE, parseDate(), STANDARD_SEQUENCE, STATUS_PERSONNEL, StatusHistoryEntry, STEP_GAP_MINUTES

### Community 107 - "lucide-react"
Cohesion: 0.07
Nodes (27): lucide-react, recharts, NotificationItem(), NotificationRow, KPICard(), PortalCard(), IconProps, PortalLayout() (+19 more)

### Community 109 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.16
Nodes (4): ContactController, EmergencyContact, IncidentDispatchTruck, Illuminate\Database\Eloquent\Model

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
Cohesion: 0.17
Nodes (7): DashboardController, FireMapController, RiskAnalyticsController, BarangayRiskSnapshot, Illuminate\Support\Facades\Date, Inertia\Inertia, Inertia\Response

### Community 117 - "toggle-group.tsx"
Cohesion: 0.24
Nodes (9): class-variance-authority, @radix-ui/react-slot, Badge(), badgeVariants, ToggleGroup(), ToggleGroupContext, ToggleGroupItem(), Toggle() (+1 more)

### Community 119 - "breadcrumbs.tsx"
Cohesion: 0.39
Nodes (7): Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage(), BreadcrumbSeparator()

### Community 120 - "main.py"
Cohesion: 0.18
Nodes (11): health_check(), load_model(), predict(), preprocess_image(), FIRESIGHT Fire Verification Service…, Resize + apply MobileNetV3 preprocessing (scale to [-1, 1]), matching…, get, ndarray (+3 more)

### Community 121 - "IncidentRecord"
Cohesion: 0.09
Nodes (7): AlarmLevel, IncidentController, IncidentRecord, ReportStatusHistory, Carbon\Carbon, IncidentReportSeeder, self

### Community 122 - "2026_10_18_205341_rename_users_id_to_user_id.php"
Cohesion: 0.73
Nodes (5): createForeignKeys(), down(), dropForeignKeys(), foreignKeysReferencingUsers(), up()

### Community 124 - "Quick Start (Local Development)"
Cohesion: 0.29
Nodes (6): 1. Create and Activate Virtual Environment, 2. Install Dependencies, 3. Run the Service, Endpoints, FireSight Fire Verification Service, Quick Start (Local Development)

### Community 126 - "CommunityReport"
Cohesion: 0.09
Nodes (10): IncidentController, CommunityReport, Notification, CommunityReportObserver, ExpoPushService, MobileNotificationService, AdminNotifier, AdminNotificationSeeder (+2 more)

### Community 128 - "2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 129 - "FireSight Chapter 4 JMeter workflow"
Cohesion: 0.29
Nodes (6): Completed dedicated-account preflight, FireSight Chapter 4 JMeter workflow, PERF-001: execute manually when ready, Provisioning and private evidence, Safe cleanup after explicit authorization, Scope and assertions

### Community 131 - "EmailVerificationController.php"
Cohesion: 0.47
Nodes (3): EmailVerificationController, Illuminate\Support\Facades\Cache, Illuminate\View\View

### Community 134 - "Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend., Source Nodes

### Community 135 - "Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them., Source Nodes

### Community 138 - "global.d.ts"
Cohesion: 0.40
Nodes (5): Auth, InertiaConfig, @inertiajs/core, InputHTMLAttributes, react

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

### Community 152 - "Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?"
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?, Source Nodes

### Community 154 - "FortifyServiceProvider.php"
Cohesion: 0.11
Nodes (12): CreateNewUser, ResetUserPassword, PasswordValidationRules, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\Validator, Illuminate\Support\Str, Illuminate\Validation\Rules\Password (+4 more)

### Community 156 - "IncidentDispatch"
Cohesion: 0.17
Nodes (3): IncidentDispatch, Illuminate\Database\Eloquent\Attributes\ObservedBy, Illuminate\Database\Eloquent\Relations\BelongsToMany

### Community 171 - "DutyRotation"
Cohesion: 0.23
Nodes (5): DutyRotation, CarbonInterface, Carbon\CarbonImmutable, CarbonImmutable, Illuminate\Support\Collection

## Knowledge Gaps
- **592 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+587 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 885 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **22 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Work-memory lessons

**Preferred sources** — corroborated by past sessions; start here.
- `portal-layout.tsx` (2× useful, score=1.261799436) _(code changed — re-verify)_
- `welcome.tsx` (2× useful, score=1.261799436) _(code changed — re-verify)_
- `Welcome()` (2× useful, score=1.261799436) _(code changed — re-verify)_

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Support\Facades\DB`, `EmailVerificationController.php`, `Geo`, `Illuminate\Http\Request`, `DutyOverride`, `VerifyEmailMail`, `DutyRotation`, `ResidentController`, `Announcement`, `JmeterTestAccounts`, `FireEducationContent`, `DutyOverride.php`, `IncidentRecord`, `FortifyServiceProvider.php`, `Barangay`, `CommunityReport`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `react` to `welcome.tsx`, `index.ts`, `nav-user.tsx`, `package.json`, `duty-schedule/index.tsx`, `portal-layout.tsx`, `two-factor-setup-modal.tsx`, `lucide-react`, `two-factor-recovery-codes.tsx`, `map/index.tsx`, `edit-details-modal.tsx`, `personnel/index.tsx`, `fire-status.ts`, `utils.ts`, `breadcrumbs.tsx`, `app-header.tsx`, `sidebar.tsx`, `residents/index.tsx`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `index.ts`, `nav-user.tsx`, `package.json`, `duty-schedule/index.tsx`, `two-factor-setup-modal.tsx`, `map/index.tsx`, `edit-details-modal.tsx`, `fire-status.ts`, `placeholder-pattern.tsx`, `personnel/index.tsx`, `cn`, `sidebar.tsx`, `residents/index.tsx`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `utils.ts`, `app-header.tsx`, `portal-layout.tsx`, `lucide-react`, `toggle-group.tsx`, `breadcrumbs.tsx`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _592 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Support\Facades\DB` be split into smaller, more focused modules?**
  _Cohesion score 0.0726950354609929 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._
- **Should `index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.0824524312896406 - nodes in this community are weakly interconnected._