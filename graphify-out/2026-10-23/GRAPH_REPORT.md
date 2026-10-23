# Graph Report - firesight-web-v2  (2026-10-23)

## Corpus Check
- 383 files · ~533,969 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 26 file(s) not represented in the graph (top: (none) 20, .example 1, .onnx 1)

## Summary
- 1952 nodes · 4346 edges · 155 communities (107 shown, 11 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 23 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- button.tsx
- incidents/index.tsx
- Illuminate\Database\Migrations\Migration
- index.ts
- response-tracking/index.tsx
- dependencies
- Illuminate\Http\Request
- package.json
- duty-schedule.ts
- AGENTS.md
- Inertia\Response
- Inertia React Development
- map/index.tsx
- BarangayContact
- Laravel\Fortify\Features
- PasswordValidationRules
- use-appearance.tsx
- personnel-form-modal.tsx
- Illuminate\Http\RedirectResponse
- fire-status.ts
- components.json
- Pest Testing 4
- duty-schedule/index.tsx
- Laravel Fortify Development
- compilerOptions
- sidebar.tsx
- devDependencies
- analytics/index.tsx
- Illuminate\Database\Seeder
- utils.ts
- react
- welcome.tsx
- Tailwind CSS Development
- Architecture Best Practices
- Carbon\CarbonInterface
- DutyOverride
- scripts
- IncidentRecord
- CommunityReport
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
- Announcement
- risk-analytics/index.tsx
- Migration Best Practices
- Blade & Views Best Practices
- Error Handling Best Practices
- scripts
- require
- Task Scheduling Best Practices
- Testing Best Practices
- cn
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
- two-factor-setup-modal.tsx
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
- lucide-react
- toggle-group.tsx
- bootstrap/app.php
- show.tsx
- Notification
- 2026_09_24_000002_harmonize_community_report_schema.php
- 2026_09_24_000001_harmonize_incident_record_schema.php
- 2026_09_24_000005_harmonize_workflow_statuses_and_types.php
- Illuminate\Database\Schema\Blueprint
- ProfileValidationRules
- main.py
- Controller
- 2026_10_18_205341_rename_users_id_to_user_id.php
- Quick Start (Local Development)
- Illuminate\Support\Facades\Schema
- Geo
- 2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php
- alert.tsx
- breadcrumbs.tsx
- Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.
- personnel/index.tsx
- Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.
- Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors.
- useIsMobile
- rename_dataset.py
- @inertiajs/react
- UserFactory
- placeholder-pattern.tsx
- profile/index.tsx
- app.tsx
- status-history.ts
- SecurityController
- use-flash-toast.ts

## God Nodes (most connected - your core abstractions)
1. `cn()` - 129 edges
2. `User` - 108 edges
3. `CommunityReport` - 84 edges
4. `react` - 79 edges
5. `@inertiajs/react` - 71 edges
6. `lucide-react` - 60 edges
7. `Barangay` - 48 edges
8. `IncidentRecord` - 40 edges
9. `Notification` - 27 edges
10. `ReportStatusHistory` - 24 edges

## Surprising Connections (you probably didn't know these)
- `makeCommunityReport()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/AdminNotificationTest.php → app/Models/CommunityReport.php
- `makeResolved()` --references_constant--> `CommunityReport`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/CommunityReport.php
- `createTestBfpPersonnel()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/User.php
- `createTestResident()` --calls--> `User`  [EXTRACTED]
  tests/Feature/Api/Phase4ReportsAndIncidentsApiTest.php → app/Models/User.php
- `makeResolved()` --calls--> `User`  [EXTRACTED]
  tests/Feature/FireMapTest.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (155 total, 11 thin omitted)

### Community 0 - "button.tsx"
Cohesion: 0.16
Nodes (16): InputError(), Props, PasskeyVerify(), Props, PasswordInput(), Props, TextLink(), Button() (+8 more)

### Community 1 - "incidents/index.tsx"
Cohesion: 0.16
Nodes (12): PaginationBar(), AlarmLevelBadge(), ALARM_LEVEL_CFG, ALARM_LEVEL_OPTIONS, BarangayOption, Filters, IncidentRow, inputStyle (+4 more)

### Community 3 - "index.ts"
Cohesion: 0.16
Nodes (14): AppContent(), Props, AppShell(), Props, AppSidebar(), AppSidebarHeader(), Breadcrumbs(), SidebarInset() (+6 more)

### Community 4 - "response-tracking/index.tsx"
Cohesion: 0.14
Nodes (16): leaflet, react-leaflet, fireIcon, formatDistance(), IncidentLocationMap(), reporterIcon, LIAN_CENTER, LIAN_DEFAULT_ZOOM (+8 more)

### Community 5 - "dependencies"
Cohesion: 0.05
Nodes (39): dependencies, class-variance-authority, clsx, concurrently, globals, @inertiajs/react, @inertiajs/vite, input-otp (+31 more)

### Community 6 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (12): AnnouncementController, ApiController, AuthController, ContactController, DashboardController, IncidentController, MapController, NotificationController (+4 more)

### Community 7 - "package.json"
Cohesion: 0.05
Nodes (41): private, $schema, type, babel-plugin-react-compiler, clsx, concurrently, eslint, eslint-config-prettier (+33 more)

### Community 8 - "duty-schedule.ts"
Cohesion: 0.14
Nodes (24): DutyOverrideModal(), RemoveOverrideDialog(), addDays(), computeWeekEnd(), DUTY_COLORS, DutyGroup, DutySchedulePageProps, formatChipDate() (+16 more)

### Community 9 - "AGENTS.md"
Cohesion: 0.06
Nodes (30): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+22 more)

### Community 10 - "Inertia\Response"
Cohesion: 0.19
Nodes (6): DashboardController, FireMapController, RiskAnalyticsController, BarangayRiskSnapshot, Inertia\Inertia, Inertia\Response

### Community 12 - "Inertia React Development"
Cohesion: 0.07
Nodes (27): Basic Link Component, Basic Usage, Client-Side Navigation, Common Pitfalls, Deferred Props, Documentation, Form Component (Recommended), Form Component Reset Props (+19 more)

### Community 13 - "map/index.tsx"
Cohesion: 0.14
Nodes (15): ALARM_LEVEL_OPTIONS, BarangayFeature, BarangayGeoProperties, BarangayRiskPoint, buildResultSummary(), ColorBy, Filters, FireIncidentsMap() (+7 more)

### Community 15 - "Laravel\Fortify\Features"
Cohesion: 0.09
Nodes (11): Illuminate\Auth\Events\Verified, Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Event, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\RateLimiter, Illuminate\Support\Facades\URL (+3 more)

### Community 16 - "PasswordValidationRules"
Cohesion: 0.14
Nodes (10): ResetUserPassword, PasswordValidationRules, PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Illuminate\Support\Facades\Validator (+2 more)

### Community 17 - "use-appearance.tsx"
Cohesion: 0.19
Nodes (16): AppearanceToggleTab(), Appearance, applyTheme(), getStoredAppearance(), handleSystemThemeChange(), initializeTheme(), isDarkMode(), listeners (+8 more)

### Community 18 - "personnel-form-modal.tsx"
Cohesion: 0.11
Nodes (25): ContactRow, DeleteFireEducationDialog(), CATEGORY_OPTIONS, FireEducationFormModal(), FireEducationRow, FireEducationViewModal(), Barangay, ALARM_LEVELS (+17 more)

### Community 19 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.18
Nodes (8): EducationController, FireEducationController, FireEducationContent, FireEducationContentSeeder, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Http\RedirectResponse, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Storage

### Community 20 - "fire-status.ts"
Cohesion: 0.12
Nodes (19): KPICard(), StatusBadge(), AiFireConfig, AiFireLabel, ALARM_LEVEL_MARKER_COLORS, BadgeCfg, EDUCATION_CATEGORY_CFG, FireEducationCategory (+11 more)

### Community 21 - "components.json"
Cohesion: 0.11
Nodes (17): aliases, components, hooks, lib, ui, utils, iconLibrary, rsc (+9 more)

### Community 22 - "Pest Testing 4"
Cohesion: 0.11
Nodes (17): Architecture Testing, Assertions, Basic Test Structure, Basic Usage, Browser Test Example, Common Pitfalls, Creating Tests, Datasets (+9 more)

### Community 23 - "duty-schedule/index.tsx"
Cohesion: 0.16
Nodes (19): DropdownMenu(), DropdownMenuCheckboxItem(), DropdownMenuContent(), DropdownMenuGroup(), DropdownMenuItem(), DropdownMenuLabel(), DropdownMenuSeparator(), DropdownMenuShortcut() (+11 more)

### Community 24 - "Laravel Fortify Development"
Cohesion: 0.12
Nodes (16): Available Features, Best Practices, Custom Authentication Logic, Documentation, Email Verification Setup, Key Endpoints, Laravel Fortify Development, Passkeys Setup (+8 more)

### Community 25 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowJs, baseUrl, esModuleInterop, forceConsistentCasingInFileNames, isolatedModules, jsx, module (+8 more)

### Community 26 - "sidebar.tsx"
Cohesion: 0.09
Nodes (28): AppLogo(), footerNavItems, mainNavItems, NavUser(), SheetDescription(), Sidebar(), SidebarContent(), SidebarContext (+20 more)

### Community 27 - "devDependencies"
Cohesion: 0.12
Nodes (16): devDependencies, babel-plugin-react-compiler, eslint, eslint-config-prettier, eslint-import-resolver-typescript, @eslint/js, eslint-plugin-import, eslint-plugin-react (+8 more)

### Community 28 - "analytics/index.tsx"
Cohesion: 0.14
Nodes (12): recharts, AlarmLevelBar, BARANGAY_BAR_COLORS, BarangayIncidentPoint, CURRENT_YEAR, Filters, IncidentTypeSlice, inputStyle (+4 more)

### Community 29 - "Illuminate\Database\Seeder"
Cohesion: 0.13
Nodes (9): AdminNotificationSeeder, BarangayBoundarySeeder, BarangayContactSeeder, BarangaySeeder, DatabaseSeeder, DummyDataSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder (+1 more)

### Community 30 - "utils.ts"
Cohesion: 0.16
Nodes (17): AppHeader(), NavFooter(), NavMain(), Separator(), SidebarGroup(), SidebarGroupContent(), SidebarMenu(), SidebarMenuItem() (+9 more)

### Community 31 - "react"
Cohesion: 0.24
Nodes (15): react, sonner, AnnouncementFormModal(), AnnouncementRow, DeleteAnnouncementDialog(), Props, Dialog(), DialogClose() (+7 more)

### Community 32 - "welcome.tsx"
Cohesion: 0.09
Nodes (23): AppCtaSection(), Audience, audiences, AudienceSection(), ComingSoonButton(), ComingSoonButtonProps, Feature, features (+15 more)

### Community 33 - "Tailwind CSS Development"
Cohesion: 0.14
Nodes (13): Basic Usage, Common Patterns, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Flexbox Layout, Grid Layout (+5 more)

### Community 34 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Code to Interfaces, Convention Over Configuration, Default Sort by Descending, Single-Purpose Action Classes, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution, Use `Context` for Request-Scoped Data (+3 more)

### Community 35 - "Carbon\CarbonInterface"
Cohesion: 0.12
Nodes (9): CommunityReportObserver, AdminNotifier, DutyRotation, CarbonInterface, Carbon\CarbonImmutable, Carbon\CarbonInterface, CarbonImmutable, Illuminate\Support\Collection (+1 more)

### Community 36 - "DutyOverride"
Cohesion: 0.13
Nodes (8): DutyGroup, DutyScheduleController, DutyOverride, Carbon, DutyOverrideFactory, Illuminate\Database\Eloquent\Builder, Illuminate\Validation\Rule, Illuminate\Validation\ValidationException

### Community 37 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 38 - "IncidentRecord"
Cohesion: 0.09
Nodes (9): AlarmLevel, AnalyticsController, IncidentRecord, DateRange, IncidentTypeBreakdown, IncidentReportSeeder, Illuminate\Support\Carbon, Illuminate\Support\Facades\DB (+1 more)

### Community 39 - "CommunityReport"
Cohesion: 0.17
Nodes (6): IncidentActionController, IncidentController, CommunityReport, Illuminate\Database\Eloquent\Attributes\ObservedBy, Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Database\Eloquent\Relations\HasOne

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
Nodes (9): AppLogoIcon(), Props, TwoFactorRecoveryCodes(), Card(), CardContent(), CardDescription(), CardFooter(), CardHeader() (+1 more)

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

### Community 54 - "risk-analytics/index.tsx"
Cohesion: 0.16
Nodes (19): BarangayFeature, BarangayGeoProperties, BarangayRiskMap(), BarangayRiskPoint, BARANGAY_GEOJSON_URL, GEOJSON_TO_DB_BARANGAY_NAME, getFeatureBarangayName(), resolveDbBarangayName() (+11 more)

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

### Community 62 - "cn"
Cohesion: 0.14
Nodes (24): @radix-ui/react-navigation-menu, mainNavItems, Props, rightNavItems, Avatar(), AvatarFallback(), AvatarImage(), NavigationMenu() (+16 more)

### Community 63 - "2026_09_24_000006_update_risk_level_enum_to_critical.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 64 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.06
Nodes (13): RiskLevel, AforReport, AuthToken, EmergencyContact, ReportEvidence, ReportStatusHistory, ResidentAddress, RiskAssessment (+5 more)

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
Cohesion: 0.11
Nodes (9): AppServiceProvider, FortifyServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\Facades\Date, Illuminate\Support\ServiceProvider, Illuminate\Support\Str, Illuminate\Validation\Rules\Password, Laravel\Fortify\Fortify (+1 more)

### Community 72 - "optionalDependencies"
Cohesion: 0.29
Nodes (7): optionalDependencies, lightningcss-linux-x64-gnu, lightningcss-win32-x64-msvc, @rollup/rollup-linux-x64-gnu, @rollup/rollup-win32-x64-msvc, @tailwindcss/oxide-linux-x64-gnu, @tailwindcss/oxide-win32-x64-msvc

### Community 73 - "two-factor-setup-modal.tsx"
Cohesion: 0.15
Nodes (14): input-otp, Props, TwoFactorSetupModal(), TwoFactorSetupStep(), InputOTP, InputOTPGroup, InputOTPSeparator, InputOTPSlot (+6 more)

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

### Community 82 - "2026_10_08_000001_replace_severity_level_with_alarm_level_in_incident_record.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 83 - "User"
Cohesion: 0.05
Nodes (21): PersonnelController, Barangay, BfpPersonnelDetails, User, Attribute, BfpAccountSeeder, BfpPersonnelSeeder, Illuminate\Database\Eloquent\Attributes\Fillable (+13 more)

### Community 103 - "lucide-react"
Cohesion: 0.13
Nodes (16): lucide-react, IconProps, navItemsForRole(), PORTAL_NAV, PortalNavItem, PortalSidebar(), Auth, BfpRole (+8 more)

### Community 104 - "toggle-group.tsx"
Cohesion: 0.20
Nodes (11): class-variance-authority, @radix-ui/react-slot, @radix-ui/react-toggle, @radix-ui/react-toggle-group, Badge(), badgeVariants, ToggleGroup(), ToggleGroupContext (+3 more)

### Community 105 - "bootstrap/app.php"
Cohesion: 0.12
Nodes (14): EnsureBfpAdmin, EnsureBfpStaff, EnsureMobileTokenIsValid, HandleAppearance, HandleInertiaRequests, Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions (+6 more)

### Community 106 - "show.tsx"
Cohesion: 0.14
Nodes (14): AiVerificationCard(), AiVerificationCardProps, EditDetailsModal(), IncidentActions(), ResolveReportModal(), AiFireBadge(), AI_FIRE_LABEL_CFG, AlarmLevel (+6 more)

### Community 107 - "Notification"
Cohesion: 0.11
Nodes (9): ReportController, Notification, FireVerificationService, GeoBoundaryService, MobileNotificationService, CURLFile, Illuminate\Support\Facades\File, Illuminate\Support\Facades\Http (+1 more)

### Community 111 - "2026_09_24_000002_harmonize_community_report_schema.php"
Cohesion: 0.53
Nodes (5): down(), downSqlite(), up(), upMysql(), upSqlite()

### Community 112 - "2026_09_24_000001_harmonize_incident_record_schema.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 113 - "2026_09_24_000005_harmonize_workflow_statuses_and_types.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 116 - "ProfileValidationRules"
Cohesion: 0.39
Nodes (3): CreateNewUser, ProfileValidationRules, Laravel\Fortify\Contracts\CreatesNewUsers

### Community 120 - "main.py"
Cohesion: 0.18
Nodes (11): health_check(), load_model(), predict(), preprocess_image(), FIRESIGHT Fire Verification Service…, Resize + apply MobileNetV3 preprocessing (scale to [-1, 1]), matching…, get, ndarray (+3 more)

### Community 121 - "Controller"
Cohesion: 0.19
Nodes (5): Controller, NotificationController, ProfileController, ProfileUpdateRequest, Illuminate\Contracts\Auth\MustVerifyEmail

### Community 122 - "2026_10_18_205341_rename_users_id_to_user_id.php"
Cohesion: 0.73
Nodes (5): createForeignKeys(), down(), dropForeignKeys(), foreignKeysReferencingUsers(), up()

### Community 124 - "Quick Start (Local Development)"
Cohesion: 0.29
Nodes (6): 1. Create and Activate Virtual Environment, 2. Install Dependencies, 3. Run the Service, Endpoints, FireSight Fire Verification Service, Quick Start (Local Development)

### Community 127 - "Geo"
Cohesion: 0.24
Nodes (3): RecheckIncidentBarangays, Geo, Illuminate\Console\Command

### Community 128 - "2026_10_08_000002_simplify_risk_level_enum_to_bfp_scale.php"
Cohesion: 0.60
Nodes (4): down(), downSqlite(), up(), upSqlite()

### Community 130 - "alert.tsx"
Cohesion: 0.48
Nodes (5): AlertError(), Alert(), AlertDescription(), AlertTitle(), alertVariants

### Community 132 - "breadcrumbs.tsx"
Cohesion: 0.39
Nodes (7): Breadcrumb(), BreadcrumbEllipsis(), BreadcrumbItem(), BreadcrumbLink(), BreadcrumbList(), BreadcrumbPage(), BreadcrumbSeparator()

### Community 134 - "Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend., Source Nodes

### Community 138 - "personnel/index.tsx"
Cohesion: 0.15
Nodes (12): BarangayOption, ContactFormModal(), DeleteContactDialog(), DeletePersonnelDialog(), PersonnelFormModal(), PortalCard(), DropdownMenuRadioGroup(), DropdownMenuRadioItem() (+4 more)

### Community 139 - "Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button., Source Nodes

### Community 140 - "Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors."
Cohesion: 0.40
Nodes (4): Answer, Outcome, Q: Analyze the current landing page, explain why it looks plain or vibe coded, address the poor cellphone visual, and provide a prompt for Antigravity to improve it while matching the system colors., Source Nodes

### Community 144 - "useIsMobile"
Cohesion: 0.70
Nodes (4): getServerSnapshot(), isSmallerThanBreakpoint(), mediaQueryListener(), useIsMobile()

### Community 145 - "rename_dataset.py"
Cohesion: 0.67
Nodes (3): main(), Batch-renames all images inside a FIRESIGHT-style dataset folder: dataset_root/…, rename_folder()

### Community 147 - "@inertiajs/react"
Cohesion: 0.23
Nodes (10): @inertiajs/react, Heading(), ManagePasskeys(), Props, ManageTwoFactor(), Props, PasskeyItem(), PasskeyRegistration() (+2 more)

### Community 148 - "UserFactory"
Cohesion: 0.21
Nodes (5): FireEducationContentFactory, static, static, UserFactory, Illuminate\Database\Eloquent\Factories\Factory

### Community 150 - "profile/index.tsx"
Cohesion: 0.18
Nodes (11): PersonnelStatusBadge(), RoleBadge(), getInitial(), GetInitialsFn, useInitials(), PersonnelRole, PersonnelStatus, disabledFieldStyle (+3 more)

### Community 151 - "app.tsx"
Cohesion: 0.22
Nodes (7): AuroraBackground(), TooltipProvider(), AppLayout(), AuthSimpleLayout(), AuthLayout(), LoginLayout(), AuthLayoutProps

### Community 152 - "status-history.ts"
Cohesion: 0.28
Nodes (8): formatDateTime(), generateStatusHistory(), INVALID_SEQUENCE, parseDate(), STANDARD_SEQUENCE, STATUS_PERSONNEL, StatusHistoryEntry, STEP_GAP_MINUTES

### Community 153 - "SecurityController"
Cohesion: 0.40
Nodes (3): SecurityController, Illuminate\Auth\Middleware\RequirePassword, Illuminate\Support\Facades\Route

### Community 154 - "use-flash-toast.ts"
Cohesion: 0.60
Nodes (3): Toaster(), useFlashToast(), FlashToast

## Knowledge Gaps
- **556 isolated node(s):** `php`, `$schema`, `style`, `rsc`, `tsx` (+551 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 787 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **11 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Work-memory lessons

**Preferred sources** — corroborated by past sessions; start here.
- `portal-layout.tsx` (2× useful, score=1.998160968) _(code changed — re-verify)_
- `welcome.tsx` (2× useful, score=1.998160968) _(code changed — re-verify)_
- `Welcome()` (2× useful, score=1.998160968) _(code changed — re-verify)_

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Carbon\CarbonInterface`, `DutyOverride`, `Illuminate\Http\Request`, `CommunityReport`, `IncidentRecord`, `Inertia\Response`, `Notification`, `Laravel\Fortify\Features`, `PasswordValidationRules`, `Illuminate\Http\RedirectResponse`, `ProfileValidationRules`, `Announcement`, `Illuminate\Database\Seeder`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `@inertiajs/react` connect `@inertiajs/react` to `button.tsx`, `incidents/index.tsx`, `index.ts`, `breadcrumbs.tsx`, `package.json`, `personnel/index.tsx`, `map/index.tsx`, `use-appearance.tsx`, `personnel-form-modal.tsx`, `fire-status.ts`, `profile/index.tsx`, `duty-schedule/index.tsx`, `app.tsx`, `use-flash-toast.ts`, `sidebar.tsx`, `analytics/index.tsx`, `utils.ts`, `react`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `notifications/index.tsx`, `cn`, `two-factor-setup-modal.tsx`, `lucide-react`, `show.tsx`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Why does `react` connect `react` to `button.tsx`, `incidents/index.tsx`, `alert.tsx`, `index.ts`, `breadcrumbs.tsx`, `response-tracking/index.tsx`, `package.json`, `personnel/index.tsx`, `map/index.tsx`, `useIsMobile`, `use-appearance.tsx`, `personnel-form-modal.tsx`, `@inertiajs/react`, `placeholder-pattern.tsx`, `profile/index.tsx`, `duty-schedule/index.tsx`, `sidebar.tsx`, `use-flash-toast.ts`, `utils.ts`, `welcome.tsx`, `two-factor-recovery-codes.tsx`, `risk-analytics/index.tsx`, `cn`, `two-factor-setup-modal.tsx`, `toggle-group.tsx`, `show.tsx`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `style` to the rest of the system?**
  _556 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `response-tracking/index.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `dependencies` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Http\Request` be split into smaller, more focused modules?**
  _Cohesion score 0.0847457627118644 - nodes in this community are weakly interconnected._