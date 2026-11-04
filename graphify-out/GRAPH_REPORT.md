# Graph Report - firesight-web-v2  (2026-11-03)

## Corpus Check
- 403 files · ~647,379 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 26 file(s) not represented in the graph (top: (none) 20, .example 1, .onnx 1)

## Summary
- 2069 nodes · 4641 edges · 170 communities (105 shown, 23 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 26 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `412aefb8`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- lucide-react
- residents/index.tsx
- Illuminate\Database\Migrations\Migration
- index.ts
- response-tracking/index.tsx
- dependencies
- Illuminate\Http\Request
- package.json
- duty-schedule/index.tsx
- AGENTS.md
- Illuminate\Http\RedirectResponse
- two-factor-setup-modal.tsx
- Inertia React Development
- use-appearance.tsx
- BarangayContact
- FortifyServiceProvider.php
- ProfileValidationRules
- CommunityReport
- personnel-form-modal.tsx
- FireEducationContent
- fire-status.ts
- components.json
- Pest Testing 4
- dropdown-menu.tsx
- Laravel Fortify Development
- compilerOptions
- toggle-group.tsx
- devDependencies
- personnel/index.tsx
- Barangay
- cn
- react
- welcome.tsx
- Tailwind CSS Development
- Architecture Best Practices
- RealisticIncidentDataSeeder
- security.tsx
- scripts
- DutyGroup
- Notification
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
- AnnouncementController
- map/index.tsx
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
- AppServiceProvider.php
- optionalDependencies
- RiskAssessment
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
- utils.ts
- bootstrap/app.php
- show.tsx
- ReportController.php
- Illuminate\Database\Eloquent\Model
- Illuminate\Database\Schema\Blueprint
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- Illuminate\Support\Facades\Schema
- Controller
- DutyOverride
- Geo
- main.py
- ResidentController
- 2026_10_18_205341_rename_users_id_to_user_id.php
- dashboard.tsx
- Quick Start (Local Development)
- notifications/index.tsx
- PersonnelController
- 2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php
- breadcrumbs.tsx
- vite.config.ts
- Phase1ReadOnlyApiTest.php
- Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.
- barangay-contacts/index.tsx
- Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.
- Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors.
- AforReport
- RiskVulnerabilityChecklist
- rename_dataset.py
- TestCase
- placeholder-pattern.tsx
- FortifyServiceProvider
- app.tsx
- Announcement
- ReportEvidence
- PasswordValidationRules
- IncidentDispatch
- Illuminate\Support\Str
- icon.tsx

## God Nodes (most connected - your core abstractions)
1. `User` - 129 edges
2. `cn()` - 129 edges
3. `CommunityReport` - 85 edges
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
- `makeCommunityReport()` --calls--> `User`  [EXTRACTED]
  tests/Feature/AdminNotificationTest.php → app/Models/User.php
- `makeResolved()` --calls--> `User`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/User.php
- `residentRouteRequests()` --references--> `User`  [EXTRACTED]
  tests/Feature/ResidentAccountsTest.php → app/Models/User.php
- `createTestBfpPersonnel()` --calls--> `AuthToken`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/AuthToken.php

## Import Cycles
- None detected.

## Communities (170 total, 23 thin omitted)

### Community 0 - "lucide-react"
Cohesion: 0.15
Nodes (17): lucide-react, @radix-ui/react-checkbox, InputError(), Props, PasskeyVerify(), Props, PasswordInput(), Props (+9 more)

### Community 1 - "residents/index.tsx"
Cohesion: 0.09
Nodes (22): PaginationBar(), AiFireBadge(), ALARM_LEVEL_CFG, AlarmLevel, ALARM_LEVEL_OPTIONS, BarangayOption, Filters, IncidentRow (+14 more)

### Community 3 - "index.ts"
Cohesion: 0.18
Nodes (12): AppContent(), Props, AppShell(), Props, AppSidebar(), AppSidebarHeader(), Breadcrumbs(), AppSidebarLayout() (+4 more)

### Community 4 - "response-tracking/index.tsx"
Cohesion: 0.16
Nodes (11): leaflet, fireIcon, formatDistance(), IncidentLocationMap(), reporterIcon, Incident, incidentIcon(), ResponseTracking() (+3 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (12): AnnouncementController, ApiController, AuthController, DashboardController, IncidentController, MapController, NotificationController, ProfileController (+4 more)

### Community 7 - "package.json"
Cohesion: 0.06
Nodes (32): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+24 more)

### Community 8 - "duty-schedule/index.tsx"
Cohesion: 0.13
Nodes (29): DutyOverrideModal(), RemoveOverrideDialog(), getInitial(), GetInitialsFn, useInitials(), addDays(), computeWeekEnd(), DUTY_COLORS (+21 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.18
Nodes (8): NotificationController, PasswordUpdateRequest, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Http\RedirectResponse, Illuminate\Validation\Rule, Illuminate\Validation\ValidationException, Inertia\Inertia, Inertia\Response

### Community 11 - "two-factor-setup-modal.tsx"
Cohesion: 0.15
Nodes (14): input-otp, Props, TwoFactorSetupModal(), TwoFactorSetupStep(), InputOTP, InputOTPGroup, InputOTPSeparator, InputOTPSlot (+6 more)

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "use-appearance.tsx"
Cohesion: 0.19
Nodes (16): AppearanceToggleTab(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), isDarkMode(), listeners (+8 more)

### Community 15 - "FortifyServiceProvider.php"
Cohesion: 0.13
Nodes (10): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\URL (+2 more)

### Community 16 - "ProfileValidationRules"
Cohesion: 0.17
Nodes (7): ProfileValidationRules, ProfileDeleteRequest, ProfileUpdateRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Laravel\Fortify\InteractsWithTwoFactorState

### Community 17 - "CommunityReport"
Cohesion: 0.11
Nodes (8): IncidentActionController, IncidentController, CommunityReport, IncidentRecord, IncidentReportSeeder, Illuminate\Support\Facades\DB, makeCommunityReport(), makeResolved()

### Community 18 - "personnel-form-modal.tsx"
Cohesion: 0.13
Nodes (24): DeleteFireEducationDialog(), CATEGORY_OPTIONS, FireEducationFormModal(), FireEducationRow, FireEducationViewModal(), Barangay, ALARM_LEVELS, combineWithBaseDate() (+16 more)

### Community 19 - "FireEducationContent"
Cohesion: 0.09
Nodes (12): EducationController, FireEducationController, FireEducationContent, FireEducationContentFactory, static, static, UserFactory, FireEducationContentSeeder (+4 more)

### Community 20 - "fire-status.ts"
Cohesion: 0.11
Nodes (21): AiVerificationCard(), AiVerificationCardProps, PersonnelStatusBadge(), RoleBadge(), AI_FIRE_LABEL_CFG, AiFireConfig, AiFireLabel, ALARM_LEVEL_MARKER_COLORS (+13 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "dropdown-menu.tsx"
Cohesion: 0.11
Nodes (24): @radix-ui/react-dropdown-menu, NavUser(), DropdownMenu(), DropdownMenuCheckboxItem(), DropdownMenuContent(), DropdownMenuGroup(), DropdownMenuItem(), DropdownMenuLabel() (+16 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "toggle-group.tsx"
Cohesion: 0.24
Nodes (9): class-variance-authority, @radix-ui/react-slot, Badge(), badgeVariants, ToggleGroup(), ToggleGroupContext, ToggleGroupItem(), Toggle() (+1 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (16): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+8 more)

### Community 28 - "personnel/index.tsx"
Cohesion: 0.09
Nodes (21): recharts, AnnouncementFormModal(), DeleteAnnouncementDialog(), AuroraBackground(), DeletePersonnelDialog(), PersonnelFormModal(), PortalCard(), PortalLayout() (+13 more)

### Community 29 - "Barangay"
Cohesion: 0.09
Nodes (9): Barangay, BarangayBoundarySeeder, BarangayContactSeeder, BarangaySeeder, DummyDataSeeder, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Database\Seeder, Illuminate\Support\Facades\File (+1 more)

### Community 30 - "cn"
Cohesion: 0.08
Nodes (41): @radix-ui/react-navigation-menu, DialogOverlay(), NavigationMenu(), NavigationMenuContent(), NavigationMenuIndicator(), NavigationMenuItem(), NavigationMenuLink(), NavigationMenuList() (+33 more)

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

### Community 36 - "security.tsx"
Cohesion: 0.23
Nodes (9): Heading(), ManagePasskeys(), Props, ManageTwoFactor(), Props, PasskeyItem(), PasskeyRegistration(), useTwoFactorAuth() (+1 more)

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "DutyGroup"
Cohesion: 0.10
Nodes (8): DutyGroup, RiskLevel, DutyRotation, CarbonInterface, Carbon\CarbonImmutable, CarbonImmutable, DutyOverrideFactory, self

### Community 39 - "Notification"
Cohesion: 0.11
Nodes (6): Notification, CommunityReportObserver, ExpoPushService, AdminNotifier, AdminNotificationSeeder, Illuminate\Support\Facades\Auth

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
Nodes (14): AlertError(), AppLogoIcon(), Props, TwoFactorRecoveryCodes(), Alert(), AlertDescription(), AlertTitle(), alertVariants (+6 more)

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
Cohesion: 0.13
Nodes (8): AlarmLevel, AnalyticsController, DashboardController, DateRange, IncidentTypeBreakdown, Carbon\CarbonInterface, Illuminate\Support\Carbon, Illuminate\Support\Facades\Date

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

### Community 54 - "map/index.tsx"
Cohesion: 0.08
Nodes (40): react-leaflet, BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), BARANGAY_GEOJSON_URL, GEOJSON_TO_DB_BARANGAY_NAME, getFeatureBarangayName(), resolveDbBarangayName() (+32 more)

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
Cohesion: 0.13
Nodes (16): @radix-ui/react-avatar, @radix-ui/react-dialog, mainNavItems, Props, rightNavItems, AppLogo(), Avatar(), AvatarFallback() (+8 more)

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

### Community 71 - "AppServiceProvider.php"
Cohesion: 0.28
Nodes (3): AppServiceProvider, Illuminate\Support\ServiceProvider, Illuminate\Validation\Rules\Password

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
Cohesion: 0.05
Nodes (28): PurgeExpiredDeletedAccounts, EnsureMobileTokenIsValid, AuthToken, BfpPersonnelDetails, ResidentAddress, User, Attribute, BfpAccountSeeder (+20 more)

### Community 103 - "PasswordResetOtpMail"
Cohesion: 0.20
Nodes (9): PasswordResetOtpMail, Content, Envelope, Illuminate\Bus\Queueable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Attachment, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+1 more)

### Community 104 - "utils.ts"
Cohesion: 0.16
Nodes (19): AppHeader(), footerNavItems, mainNavItems, NavFooter(), NavMain(), SidebarGroup(), SidebarMenu(), SidebarMenuButton() (+11 more)

### Community 105 - "bootstrap/app.php"
Cohesion: 0.13
Nodes (12): EnsureBfpAdmin, EnsureBfpStaff, HandleAppearance, HandleInertiaRequests, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+4 more)

### Community 106 - "show.tsx"
Cohesion: 0.12
Nodes (20): IncidentActions(), ResolveReportModal(), AlarmLevelBadge(), ReportStatus, STATUS_CFG, formatDateTime(), generateStatusHistory(), INVALID_SEQUENCE (+12 more)

### Community 107 - "ReportController.php"
Cohesion: 0.16
Nodes (7): ReportController, FireVerificationService, GeoBoundaryService, MobileNotificationService, CURLFile, Illuminate\Support\Facades\Http, Illuminate\Support\Facades\Log

### Community 109 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.20
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

### Community 116 - "Controller"
Cohesion: 0.15
Nodes (8): Controller, FireMapController, RiskAnalyticsController, ProfileController, SecurityController, BarangayRiskSnapshot, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route

### Community 117 - "DutyOverride"
Cohesion: 0.27
Nodes (4): DutyScheduleController, DutyOverride, Carbon, Illuminate\Database\Eloquent\Builder

### Community 118 - "Geo"
Cohesion: 0.24
Nodes (3): RecheckIncidentBarangays, Geo, Illuminate\Console\Command

### Community 120 - "main.py"
Cohesion: 0.18
Nodes (11): health_check(), load_model(), predict(), preprocess_image(), FIRESIGHT Fire Verification Service…, Resize + apply MobileNetV3 preprocessing (scale to [-1, 1]), matching…, get, ndarray (+3 more)

### Community 122 - "2026_10_18_205341_rename_users_id_to_user_id.php"
Cohesion: 0.73
Nodes (5): createForeignKeys(), down(), dropForeignKeys(), foreignKeysReferencingUsers(), up()

### Community 123 - "dashboard.tsx"
Cohesion: 0.22
Nodes (7): BarangayRiskPoint, KPICard(), StatusBadge(), IncidentTypeSlice, Kpis, MonthlyTrendPoint, RecentIncident

### Community 124 - "Quick Start (Local Development)"
Cohesion: 0.29
Nodes (6): 1. Create and Activate Virtual Environment, 2. Install Dependencies, 3. Run the Service, Endpoints, FireSight Fire Verification Service, Quick Start (Local Development)

### Community 126 - "notifications/index.tsx"
Cohesion: 0.31
Nodes (6): NotificationItem(), NotificationRow, formatNotificationTime(), getNotificationVisual(), NotificationType, Groups

### Community 128 - "2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 129 - "breadcrumbs.tsx"
Cohesion: 0.39
Nodes (7): Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage(), BreadcrumbSeparator()

### Community 130 - "vite.config.ts"
Cohesion: 0.29
Nodes (6): @inertiajs/vite, laravel-vite-plugin, @laravel/vite-plugin-wayfinder, @tailwindcss/vite, vite, @vitejs/plugin-react

### Community 134 - "Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend., Source Nodes

### Community 135 - "barangay-contacts/index.tsx"
Cohesion: 0.25
Nodes (6): BarangayOption, ContactFormModal(), ContactRow, DeleteContactDialog(), DropdownMenuRadioGroup(), DropdownMenuRadioItem()

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
Cohesion: 0.14
Nodes (12): @radix-ui/react-tooltip, LowlandLandscape(), Toaster(), Tooltip(), TooltipProvider(), TooltipTrigger(), useFlashToast(), AppLayout() (+4 more)

### Community 154 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (6): CreateNewUser, ResetUserPassword, PasswordValidationRules, Illuminate\Support\Facades\Validator, Laravel\Fortify\Contracts\CreatesNewUsers, Laravel\Fortify\Contracts\ResetsUserPasswords

### Community 156 - "IncidentDispatch"
Cohesion: 0.17
Nodes (3): IncidentDispatch, Illuminate\Database\Eloquent\Attributes\ObservedBy, Illuminate\Database\Eloquent\Relations\BelongsToMany

## Knowledge Gaps
- **564 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+559 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 820 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **23 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Work-memory lessons

**Preferred sources** — corroborated by past sessions; start here.
- `portal-layout.tsx` (2× useful, score=1.998160968) _(code changed — re-verify)_
- `welcome.tsx` (2× useful, score=1.998160968) _(code changed — re-verify)_
- `Welcome()` (2× useful, score=1.998160968) _(code changed — re-verify)_

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Phase1ReadOnlyApiTest.php`, `Illuminate\Http\Request`, `Notification`, `DutyGroup`, `Illuminate\Http\RedirectResponse`, `ReportController.php`, `FortifyServiceProvider.php`, `ProfileValidationRules`, `CommunityReport`, `FireEducationContent`, `DutyOverride`, `Geo`, `Announcement`, `ResidentController`, `PasswordValidationRules`, `Barangay`, `PersonnelController`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `react` to `lucide-react`, `breadcrumbs.tsx`, `residents/index.tsx`, `index.ts`, `package.json`, `barangay-contacts/index.tsx`, `duty-schedule/index.tsx`, `two-factor-setup-modal.tsx`, `use-appearance.tsx`, `personnel-form-modal.tsx`, `fire-status.ts`, `dropdown-menu.tsx`, `app.tsx`, `personnel/index.tsx`, `welcome.tsx`, `security.tsx`, `two-factor-recovery-codes.tsx`, `map/index.tsx`, `app-header.tsx`, `utils.ts`, `show.tsx`, `dashboard.tsx`, `notifications/index.tsx`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `lucide-react`, `breadcrumbs.tsx`, `residents/index.tsx`, `index.ts`, `response-tracking/index.tsx`, `package.json`, `duty-schedule/index.tsx`, `barangay-contacts/index.tsx`, `two-factor-setup-modal.tsx`, `use-appearance.tsx`, `personnel-form-modal.tsx`, `placeholder-pattern.tsx`, `dropdown-menu.tsx`, `app.tsx`, `toggle-group.tsx`, `personnel/index.tsx`, `cn`, `welcome.tsx`, `security.tsx`, `two-factor-recovery-codes.tsx`, `map/index.tsx`, `app-header.tsx`, `utils.ts`, `show.tsx`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _564 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `lucide-react` be split into smaller, more focused modules?**
  _Cohesion score 0.14935988620199148 - nodes in this community are weakly interconnected._
- **Should `residents/index.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.08866995073891626 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Database\Migrations\Migration` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._