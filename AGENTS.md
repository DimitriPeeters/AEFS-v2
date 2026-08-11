# AGENTS.md

# AEFS v2 — Repository Instructions for Codex

This file is the authoritative working contract for AI-assisted development in the AEFS v2 repository.

Codex must read this file before making changes.

If code in the repository conflicts with this document, do not silently invent a compromise. Inspect the surrounding implementation, identify whether the code is legacy or current, and preserve the current AEFS architecture unless the user explicitly requests an architectural change.

---

## 1. Project identity

AEFS v2 is a custom CRM and event-management application for All Events Forever Sure.

The application is being rebuilt as a maintainable custom PHP application with its own framework and application layer.

### Technology

- PHP 8.4
- Composer
- PSR-4 autoloading
- PSR-12 coding style
- SOLID principles
- Constructor Dependency Injection
- PDO / custom database abstraction
- Custom MVC-style architecture
- Custom router
- Custom dependency-injection container
- Custom request/response layer
- Custom view engine
- Custom authentication and authorization
- Custom middleware
- Custom session and flash-message handling

### Explicitly not used

Do not introduce:

- Laravel
- Symfony
- Twig
- Blade
- an external MVC framework
- Eloquent
- Doctrine ORM
- global service locators as a replacement for constructor injection
- a JavaScript framework unless explicitly requested
- Bootstrap as a new dependency

Existing functionality should remain framework-native to AEFS.

---

# 2. Primary development principle

Before changing code, understand the existing implementation.

For every non-trivial task:

1. Inspect the relevant current files.
2. Inspect adjacent architecture and conventions.
3. Inspect database dependencies if persistence is involved.
4. Check routes and middleware if HTTP behavior is involved.
5. Check views and existing frontend conventions if UI behavior is involved.
6. Only then modify code.

Never generate a parallel architecture because the current implementation was not inspected carefully enough.

Examples of prohibited behavior:

- creating a second service for logic already owned by an existing service;
- creating a separate controller for one small action when the existing domain controller already owns that action;
- introducing a new repository style inconsistent with the current repositories;
- reintroducing old table names because they appear in historical code;
- inventing an `eventmanager` role because an old prototype referenced it;
- replacing working custom framework components with third-party equivalents.

---

# 3. Current project architecture

The repository is divided into a framework layer and an application layer.

## Framework/Core

Primary namespace:

```text
AEFS\
```

Core/framework code belongs under:

```text
src/
```

Do not move application-specific business logic into `src/`.

The Core owns generic infrastructure such as:

- container/autowiring;
- routing;
- HTTP request handling;
- HTTP responses;
- session;
- authentication infrastructure;
- middleware infrastructure;
- database infrastructure;
- view engine;
- view helpers;
- validation infrastructure where generic;
- framework exceptions/utilities.

## Application

Primary namespace:

```text
App\
```

Application-specific code belongs under:

```text
app/
```

Current major application directories include:

```text
app/
├── Controllers/
├── Http/
│   └── Requests/
├── Mappers/
├── Middleware/
├── Models/
├── Repositories/
├── Services/
├── Validators/
└── Views/
```

Routes live under:

```text
routes/
```

Database assets/migrations belong under:

```text
database/
```

Public web assets belong under the existing public asset structure.

Do not change these boundaries without explicit approval.

---

# 4. Layer responsibilities

AEFS v2 uses clear layer ownership.

## Controller

Controllers handle HTTP orchestration only.

Controllers may:

- read route parameters;
- read query parameters;
- read submitted form data;
- validate CSRF;
- instantiate/use request DTO-style objects;
- call services;
- choose a view;
- redirect;
- produce JSON for AJAX endpoints;
- set flash messages;
- translate expected failures into HTTP/UI feedback.

Controllers must not contain substantial business rules.

### Current controller conventions

Controllers normally extend:

```php
App\Controllers\BaseController
```

Typical constructor structure:

```php
public function __construct(
    ViewFactory $views,
    Request $request,
    private readonly SomeService $service
) {
    parent::__construct(
        $views,
        $request
    );
}
```

Use the current request object.

Typical route parameter:

```php
$id = (int) $this->request()->route('id', 0);
```

Typical POST input:

```php
$input = $this->request()->request->all();
```

Typical query input:

```php
$search = trim(
    (string) $this->request()->query->get('zoek', '')
);
```

Do not introduce old/global helpers such as:

```php
auth()
abort()
request()
redirect()
```

unless such a helper already exists in the current Core and the surrounding code actively uses it.

## HTTP Request objects

Application request-normalization classes belong under:

```text
app/Http/Requests/
```

Their purpose is to:

- normalize submitted input;
- convert booleans;
- trim strings;
- combine related form values;
- return a predictable application array.

They are not repositories and must not perform SQL.

## Validator

Domain/input validation belongs under:

```text
app/Validators/
```

Validators should:

- enforce field/domain invariants;
- throw clear exceptions for invalid input;
- not render views;
- not redirect;
- not perform unrelated persistence.

## Service

Business logic belongs in services.

Services may:

- coordinate repositories;
- enforce domain rules;
- run transactions;
- perform authorization-sensitive domain checks;
- create audit logs;
- trigger notification workflows;
- decide allowed state transitions.

A service should represent one coherent domain responsibility.

Do not split one domain into multiple overlapping services without a clear architectural reason.

## Repository

Repositories own database access for an application concept.

Repositories may:

- execute queries;
- insert/update/delete rows;
- provide read models;
- implement locking queries;
- count related rows;
- expose persistence operations required by services.

Repositories must not:

- render HTML;
- redirect;
- own HTTP request logic;
- silently implement business workflow that belongs in a service.

## Mapper

Mappers convert persistence representation to application models and vice versa.

Keep database-column knowledge in mappers/repositories rather than scattering it through controllers and views.

## Model

Models represent domain/read state.

Models may expose presentation-neutral derived behavior, for example:

- status checks;
- capacity calculations;
- date/time calculations;
- domain state helpers;
- display labels where this is already the project convention.

Models must not query the database.

## View

Views render already-prepared data.

Views may contain small display calculations, but must not query repositories or implement domain workflows.

---

# 5. Dependency Injection

Use constructor dependency injection.

The AEFS container/autowiring mechanism is the normal dependency-resolution mechanism.

Preferred:

```php
public function __construct(
    SomeRepository $repository,
    SomeValidator $validator
) {
}
```

Avoid:

```php
$repository = new SomeRepository(...);
```

inside controllers/services.

Avoid static service containers unless the current Core explicitly requires a static facade for that specific concern.

`AEFS\Core\Auth` is an existing project convention and may be used where current code uses it.

---

# 6. HTTP and response conventions

Controller actions return:

```php
AEFS\Core\Http\Response
```

Use the existing BaseController helpers for:

- views;
- redirects;
- success flash messages;
- error flash messages.

Use the existing response JSON capability for AJAX.

Do not emit raw headers or call `exit` from normal controllers.

For AJAX behavior:

- keep server-side authorization;
- keep CSRF validation;
- return structured JSON;
- use meaningful HTTP status codes;
- preserve a normal non-JavaScript POST/redirect fallback when practical.

A UI optimization must never bypass business rules because it uses AJAX.

---

# 7. CSRF

All state-changing browser requests must remain CSRF protected.

Views use the existing helper, for example:

```php
<?= $helpers->csrf->field() ?>
```

Controllers must validate the submitted token using the existing `CsrfHelper`/current project mechanism.

AJAX requests must submit the same CSRF token.

Never disable CSRF merely to simplify fetch/AJAX code.

---

# 8. Authentication and authorization

## Roles

The only currently defined application roles are:

```text
admin
lid
```

Do not invent additional roles unless explicitly requested.

In particular:

```text
eventmanager
```

is not currently a valid AEFS v2 role.

## Role ownership

Roles belong to the linked user account in:

```text
gebruikers
```

Roles do not belong directly to a member record in:

```text
leden
```

## Admin

An administrator may perform administration functionality such as:

- member administration;
- user approval and role management;
- event administration;
- shift administration;
- shift-registration decisions;
- presence administration.

## Member

A normal member:

- must not receive general member-management access;
- must not receive user-management access;
- may access permitted member-facing functionality;
- may view/edit their own profile where implemented;
- may register for eligible events/shifts;
- may manage only their own registration where allowed.

## Middleware

Use existing middleware such as:

```php
AuthMiddleware::class
AdminMiddleware::class
GuestMiddleware::class
```

Keep authorization in routes and domain logic where appropriate.

Never rely only on hiding a button in a view.

---

# 9. Routing conventions

Routes are split by module under:

```text
routes/
```

Examples include:

```text
routes/auth.php
routes/members.php
routes/users.php
routes/events.php
routes/shifts.php
```

The central route loader is:

```text
routes/web.php
```

Before adding a new route:

1. inspect the module route file;
2. inspect route naming conventions;
3. inspect middleware conventions;
4. ensure route order cannot conflict with parameter routes.

Use route names consistently with the existing module.

State-changing operations must use POST or another appropriate mutation method supported by the router.

Do not use GET for:

- deleting;
- approving;
- cancelling;
- presence toggling;
- role changes;
- other mutations.

---

# 10. View engine and frontend conventions

AEFS v2 uses its own view engine.

Application views belong under:

```text
app/Views/
```

Do not create active duplicate views under unrelated legacy paths.

Views typically use the current helpers and layout system.

Typical layout extension:

```php
$this->extend(
    'layouts.app',
    [
        'title' => $title,
    ]
);
```

Typical sections:

```php
<?php $this->startSection('content'); ?>
...
<?php $this->endSection(); ?>
```

The existing system also supports view sections for page-local styles/scripts.

Use existing reusable components and helpers where they already solve the problem.

Examples:

- page headers;
- cards;
- empty states;
- form helpers;
- URL helper;
- asset helper;
- CSRF helper;
- validation/error renderer.

## Styling

The current interface is responsive and uses the AEFS visual language.

Requirements:

- desktop-first usage must remain comfortable;
- mobile/tablet layouts must remain usable;
- use existing CSS variables;
- preserve AEFS branding;
- avoid hardcoding a second design system;
- avoid new Bootstrap dependencies;
- avoid making pages look like isolated mini-applications.

Page-specific CSS may be used in the established view section when appropriate.

## JavaScript

Use vanilla JavaScript unless explicitly instructed otherwise.

Prefer progressive enhancement:

- server-side flow remains valid;
- JavaScript improves efficiency;
- failure gives visible feedback.

For repeated inline actions such as presence toggling, avoid a full-page reload when an AJAX update is safe and already authorized.

Do not create a separate frontend framework for a small interaction.

---

# 11. Error handling and user feedback

Expected validation/domain failures should produce clear user-facing messages.

Use existing session flash mechanisms.

Typical pattern:

```php
Session::flash(
    '_errors',
    [
        'form' => [
            $throwable->getMessage(),
        ],
    ]
);
```

Do not expose:

- stack traces;
- SQL queries;
- credentials;
- secrets;
- internal filesystem details

to normal end users.

Development exceptions may still be handled by the existing framework exception layer.

---

# 12. Audit logging

AEFS v2 has an existing:

```text
App\Services\AuditLogService
```

Use that implementation rather than introducing another `AuditService`.

Important administration/domain mutations should be auditable where the current architecture expects it.

Examples:

- member changes;
- user changes;
- event changes;
- shift changes;
- shift-registration state changes;
- presence changes where currently audited.

Never silently delete historical state that is needed for audit/history.

---

# 13. Database safety contract

This section is critical.

## Absolutely protected data

Existing data in these tables must never be lost:

```text
leden
gebruikers
```

Schema evolution is possible when explicitly needed, but existing member/user rows and their meaningful data must be preserved.

Before any risky database migration affecting these tables:

1. inspect existing schema;
2. inspect existing data relationships;
3. design migration;
4. explicitly preserve data;
5. avoid destructive assumptions.

## General migration principle

Database migrations must favor:

- deterministic transformations;
- data preservation;
- foreign-key integrity;
- unique constraints that reflect domain rules;
- reversible/inspectable transitions for major migrations.

Never blindly `DROP TABLE` on a table containing meaningful production data.

When replacing a legacy table, keeping a temporary `_legacy` copy during verification is acceptable.

Legacy backups must not be used by active application code after migration.

---

# 14. Current core application tables and terminology

Current relevant domain naming includes:

```text
leden
gebruikers
evenementen
event_inschrijvingen
shifts
shift_inschrijvingen
shift_types
```

Do not casually rename Dutch database concepts to English tables without an explicit migration decision.

Application class names may remain English where that is the established architecture.

---

# 15. Legacy shift structures

The new shift implementation must not regress to the old schema.

Do not reintroduce active use of:

```text
event_shifts
shift_toewijzingen
```

Older/legacy copies may exist for migration verification, for example:

```text
event_shifts_legacy
shift_inschrijvingen_legacy
```

Treat these as historical backup data only.

Active shift code must use the current definitive shift tables.

---

# 16. Event module contract

Event management is considered an established module.

Do not redesign it as part of an unrelated shift task.

Relevant concepts include:

```text
evenementen
event_inschrijvingen
```

Events have lifecycle/status behavior already implemented.

Shift logic must integrate with event logic rather than duplicate it.

A shift belongs to an event.

Before changing an event in a way that affects shifts:

- inspect related shifts;
- preserve referential integrity;
- respect existing event deletion/restriction logic.

Do not silently cascade-delete shift history.

---

# 17. Shift module — definitive domain contract

Shift management is the current active development area.

## Definitive tables

Use:

```text
shift_types
shifts
shift_inschrijvingen
```

## Shift type

A shift has a shift type/function.

The default function is:

```text
Steward
```

Do not create a second competing `shift_types` concept.

Types may contain current metadata such as:

- name;
- color;
- icon;
- description;
- active state.

Use the actual current schema as source of truth.

## Shift timing

A shift has a concrete start datetime and end datetime.

Night shifts are valid.

Example:

```text
18:30 → 01:00 next day
```

Never compare only `HH:mm` strings and reject valid overnight shifts.

The persisted current representation uses full datetime semantics.

## Capacity

The customer/organizer provides the required number of volunteers for each shift.

Capacity is therefore part of the shift.

Capacity applies to:

```text
bevestigd
```

registrations.

`wachtend` and `reserve` do not consume confirmed capacity.

Never approve beyond capacity.

Approval must remain safe against concurrent changes.

## Registration statuses

The definitive shift-registration statuses are:

```text
wachtend
bevestigd
reserve
geweigerd
geannuleerd
```

Do not invent synonyms or a second status system.

## Self-registration

Members register themselves for shifts.

A new self-registration starts as:

```text
wachtend
```

It must not automatically become confirmed merely because capacity is available.

An administrator decides whether the member becomes:

```text
bevestigd
reserve
geweigerd
```

## Event registration prerequisite

Where current service logic requires it, a member must have a valid event registration before selecting a shift for that event.

Do not remove that rule accidentally when changing UI flow.

## Duplicate registration

There must be at most one logical member/shift registration row under the current unique-key strategy.

A previously cancelled registration may be reactivated/reused through the established repository/service workflow rather than inserting an invalid duplicate.

Do not break re-registration after cancellation.

## Cancellation by member

A member may cancel their own active shift registration until:

```text
14 days before the EVENT start date
```

The rule is based on the event start, not the shift start.

Within the final 14 days:

- a member may not self-cancel;
- an administrator must perform the cancellation.

Do not weaken this restriction in frontend code.

## Cancellation by administrator

An administrator may cancel an active member registration.

The cancellation must preserve historical information and status.

Do not hard-delete the registration.

## Cancelling a complete shift

Cancelling a shift must also transition its active registrations appropriately.

Do not leave:

```text
wachtend
bevestigd
reserve
```

registrations active on a cancelled shift.

Historical rows must remain available.

## Shift deletion

A shift with registration history should not be destructively deleted.

Prefer cancellation.

Only allow hard deletion in the narrow cases already allowed by the current service/repository rules.

## Presence

Presence is meaningful for confirmed registrations.

Current desired UX:

- administrator can mark confirmed volunteers present/not present;
- presence updates should not require a full page refresh;
- the current page/scroll position should remain stable;
- the server remains authoritative;
- AJAX must retain CSRF and admin authorization;
- non-JavaScript fallback should remain possible where practical.

Do not create a second controller solely for presence if `ShiftController` already owns this responsibility.

---

# 18. Shift architecture

Current shift implementation follows the normal AEFS layers.

Relevant types include or are expected under:

```text
app/Models/Shift.php
app/Models/ShiftType.php
app/Models/ShiftRegistration.php

app/Mappers/ShiftMapper.php
app/Mappers/ShiftTypeMapper.php
app/Mappers/ShiftRegistrationMapper.php

app/Repositories/ShiftRepository.php
app/Repositories/ShiftTypeRepository.php
app/Repositories/ShiftRegistrationRepository.php

app/Services/ShiftService.php

app/Validators/ShiftValidator.php
app/Validators/ShiftRegistrationValidator.php

app/Http/Requests/ShiftRequest.php
app/Http/Requests/ShiftRegistrationRequest.php

app/Controllers/ShiftController.php

routes/shifts.php

app/Views/shifts/
```

Before modifying shift functionality, inspect all directly affected files rather than assuming their API.

Do not recreate removed legacy classes such as overlapping shift-registration models/services.

---

# 19. Transaction and concurrency rules

Use database transactions for workflows that modify multiple related rows or depend on a state check followed by a write.

Examples:

- shift approval with capacity checking;
- cancelling a shift plus active registrations;
- registration state transitions;
- multi-row account/registration workflows.

Where capacity/state can race, use the existing locking approach such as row locking through repository methods before decision writes.

Never implement:

```text
read capacity
then later update
```

without transactional protection when concurrent administrators/users could produce an invalid state.

---

# 20. Members module contract

The members module is considered established.

Important rules:

- ordinary members do not receive `/members` administration access;
- administrators manage member records;
- a normal member only accesses their own profile through the member-facing flow;
- sensitive member data must remain protected;
- existing audit behavior must remain intact.

Do not redesign the module as part of another feature.

Existing sensitive-data handling such as encryption must not be removed.

---

# 21. Users module contract

The users module is considered established.

A user account is linked to a member.

Roles are managed on the user account.

Current roles:

```text
admin
lid
```

Public registration creates a member/user workflow which requires administrator approval before normal account access.

Do not let an unapproved registration bypass the approval state.

Do not duplicate member identity fields unnecessarily into new unrelated tables.

---

# 22. Public registration and approval flow

Current intended workflow:

```text
public registration
→ member record
→ linked user account
→ pending administrator approval
→ approved account
→ login/access according to role
```

Passwords must never be flashed back to session old-input data.

Never log plaintext passwords.

Never expose password hashes.

---

# 23. Dashboard integration

The dashboard is an established module.

When changing table names or domain semantics, inspect:

```text
DashboardRepository
```

and dashboard views/counts.

Do not assume a migration is complete merely because the domain page works; dashboard queries may still reference old schema.

---

# 24. Repository/schema refactors

When changing a table or column used by an established module:

1. search the whole repository for the old identifier;
2. inspect all repository SQL;
3. inspect dashboard/report counters;
4. inspect migrations/seeders;
5. inspect views if field names are surfaced;
6. update all active references;
7. keep legacy references only in deliberate migration/history files.

A schema migration and application update form one coherent change.

---

# 25. Naming conventions

Follow existing names.

PHP:

- classes: PascalCase;
- methods/properties: camelCase;
- constants: UPPER_SNAKE_CASE;
- namespace follows PSR-4;
- files contain one primary class matching the filename.

Database naming is primarily Dutch snake_case.

Do not rename established database terminology merely for stylistic preference.

Use domain names already present in the current module.

---

# 26. PHP standards

All new/modified PHP code must:

```php
<?php

declare(strict_types=1);
```

where appropriate for application/framework classes.

Requirements:

- PHP 8.4 compatible;
- PSR-12 formatting;
- typed parameters;
- typed return values;
- typed properties;
- `readonly` where appropriate;
- no dead imports;
- no undefined methods;
- no placeholder methods;
- no commented-out alternate implementations;
- no debug `var_dump`, `print_r`, `die`, or `exit`.

Prefer:

- early validation;
- clear domain exceptions;
- small cohesive methods;
- named arguments where they improve clarity;
- enums/constants only when consistent with the current codebase.

Do not over-engineer small modules.

---

# 27. SQL standards

SQL must be explicit and readable.

Use parameter binding.

Never concatenate untrusted values into SQL.

Prefer:

- clear aliases;
- explicit selected columns when practical;
- indexes for actual lookup paths;
- foreign keys reflecting domain relationships;
- unique constraints reflecting invariants.

Be careful with MySQL/MariaDB differences and the actual AEFS deployment environment.

When writing migrations, make assumptions explicit.

---

# 28. Security rules

Never place secrets in source code.

Never commit:

- production passwords;
- SMTP passwords;
- API keys;
- private tokens;
- database credentials if they are meant to stay local;
- `.env` secrets.

Never reproduce a secret found in repository history in output.

Treat user/member data as sensitive.

Use:

- existing authorization;
- CSRF protection;
- output escaping;
- password hashing;
- current encryption utilities for sensitive fields.

Views must escape user-controlled output using the existing view escaping mechanism unless intentionally rendering trusted markup.

---

# 29. Data integrity over convenience

When requirements conflict, prioritize:

1. protected user/member data;
2. referential integrity;
3. audit/history;
4. authorization/security;
5. domain correctness;
6. user experience;
7. implementation convenience.

Do not delete history simply because a UI would become easier.

---

# 30. Existing code is the local source of truth

This `AGENTS.md` documents stable contracts, but exact method names and signatures must be verified against the current repository before coding.

For example, before using a method such as:

```php
$repository->findBySomething()
```

search the actual repository class.

Never hallucinate repository/service methods.

Before calling a framework API:

- inspect the current Core;
- inspect nearby working code.

---

# 31. No duplicate abstractions

Before creating a new class, search for an existing class with the same responsibility.

Do not create duplicates such as:

```text
AuditService
AuditLogService
```

for the same purpose.

Do not create both:

```text
ShiftRegistration
ShiftInschrijving
```

as competing active models.

Do not create both:

```text
ShiftService
ShiftRegistrationService
```

when the current architecture intentionally centralizes the domain in one service.

A new class is justified only when it has a distinct responsibility.

---

# 32. Working with legacy code

Historical files may exist in the repository or migration backups.

Do not treat old code as current solely because it compiles.

Signals of legacy code may include:

- references to removed tables;
- references to non-existing roles;
- obsolete controller APIs;
- duplicate models/services;
- old view directories;
- outdated global helpers.

When legacy and current code conflict:

1. inspect route loading;
2. inspect autoloading;
3. inspect current module references;
4. inspect database schema;
5. retain current active architecture.

Ask only if the ambiguity cannot be resolved from the repository.

---

# 33. Current module status

At the current stage, treat the following as established/working areas unless the task explicitly targets them:

```text
Core framework
View Engine
Dashboard
Members
Authentication
Authorization and roles
Public registration
Registration approval flow
User management
Event management
```

Shift management and shift registrations are currently being completed/tested.

Future modules may include:

```text
Payments
Mailings
Documents
Reports
```

Do not prematurely implement future modules during a shift task.

---

# 34. Change scope

Make the smallest coherent change that fully solves the requested problem.

Do not bundle unrelated cleanup into a feature fix.

Example:

If the task is:

```text
presence marking causes a full page refresh
```

inspect the existing presence flow and update that flow.

Do not simultaneously:

- replace the router;
- redesign the shift model;
- rewrite the CSS framework;
- add another controller architecture;
- alter member registration.

Refactoring is acceptable only when required to make the requested change correct and maintainable.

---

# 35. Analysis before implementation

For substantial tasks, Codex should first report a concise implementation assessment.

Recommended internal workflow:

```text
1. git status
2. inspect AGENTS.md
3. identify affected module
4. inspect affected files
5. search references
6. inspect relevant schema/migrations
7. determine minimal coherent changes
8. implement
9. lint/test
10. report changed files and result
```

Do not repeatedly explain the plan to the user when implementation can proceed safely.

The user prefers progress through working code over lengthy architectural discussion.

---

# 36. Output expectations for code tasks

When the user asks for code, the normal expectation is:

- complete affected files;
- no snippets;
- no placeholders;
- no pseudo-code;
- copy-paste ready;
- compileable;
- limited explanation.

If Codex edits the repository directly, it should still summarize:

- files changed;
- what changed;
- validation performed.

Do not dump massive unchanged files into chat if Codex has already applied the changes locally unless the user explicitly requests the full code.

When working in ChatGPT-style interactions where files are not directly edited, return complete affected files.

---

# 37. ZIP policy

Do not generate a full project ZIP for small changes.

For a small change, provide/edit only the involved files.

A ZIP is justified only when:

- many new files must be delivered together;
- the user explicitly requests a ZIP;
- packaging itself is part of the task.

Never replace repository-based development with a stream of full project ZIPs.

---

# 38. Git workflow

The repository is Git-managed.

Before modifying code:

```bash
git status
```

Inspect the current branch.

Do not assume the working tree is clean.

Do not overwrite unrelated uncommitted user changes.

## Commits

Do not commit unless the user explicitly asks for a commit.

Do not push unless the user explicitly asks for a push.

Do not reset/discard user changes without explicit permission.

Preferred commit messages follow a concise conventional style, for example:

```text
feat(shifts): add shift registration approval
fix(shifts): update presence without page reload
refactor(events): simplify event repository query
```

## Branches

For significant new work, a focused feature branch is preferred when practical.

Examples:

```text
feature/shift-management
feature/payments
fix/shift-presence
```

Do not create/switch branches unexpectedly if the user asked only for a small local fix.

---

# 39. Required validation after PHP changes

At minimum, lint every changed PHP file:

```bash
php -l path/to/file.php
```

If multiple PHP files changed, lint all of them.

If repository scripts exist for linting/tests, inspect and use them.

Do not claim a file was linted unless the command was actually run.

Do not claim runtime behavior was verified unless it was actually exercised.

---

# 40. Composer/autoload

Do not run `composer dump-autoload` reflexively after every class change.

PSR-4 class additions generally do not require regeneration unless the repository's Composer configuration/classmap setup requires it.

Inspect:

```text
composer.json
```

when autoload behavior is uncertain.

Do not change Composer dependencies for a task that can be solved with the existing stack.

---

# 41. Runtime environment

Primary local development environment is Windows with Laragon.

Code and commands should remain compatible with that environment.

Avoid shell-only assumptions that make normal Windows/Laragon development difficult.

PHP is currently targeted at:

```text
8.4
```

Do not use syntax requiring a newer unsupported PHP version.

---

# 42. Database changes workflow

For a database-changing task:

1. inspect current schema/dump/migrations;
2. determine whether production data exists;
3. identify protected data;
4. write a migration;
5. preserve existing data;
6. update application references;
7. verify counts/relationships;
8. only later remove legacy backups after explicit verification.

For a migration that replaces a populated table, include verification queries where useful.

Do not assume seeders represent production data.

---

# 43. Testing database migrations

For shift migrations or other populated modules, verify:

- row counts before/after;
- orphan rows;
- duplicate keys;
- foreign-key compatibility;
- enum/status mapping;
- date/time conversions;
- special cases such as overnight shifts.

When possible, report the concrete verification result.

---

# 44. AJAX conventions

AJAX is an enhancement, not an authorization boundary.

For an AJAX mutation:

Frontend:

- intercept only the intended form/action;
- submit the existing CSRF token;
- use `fetch`;
- request JSON explicitly;
- disable the clicked control during the request;
- restore state on failure;
- show local success/error feedback;
- avoid losing scroll position.

Backend:

- use the same route authorization;
- validate CSRF;
- call the same service method used by non-AJAX flow;
- return JSON when AJAX is requested;
- preserve redirect behavior otherwise.

Do not create duplicate business logic for AJAX.

---

# 45. Dates and times

Use explicit datetime semantics for domain rules.

Do not rely on lexical `HH:mm` comparisons when a shift may cross midnight.

For shift cancellation rules, use:

```text
event start date - 14 days
```

not:

```text
shift start - 14 days
```

Use immutable date objects where that is already the module convention.

---

# 46. Status transitions

State transitions must be explicit.

For shift registrations, valid business transitions depend on current service rules.

Never allow an arbitrary form value to directly set a protected state.

Examples:

- member self-registration → `wachtend`;
- admin decision → `bevestigd`, `reserve`, or `geweigerd`;
- cancellation → `geannuleerd`.

Do not let an edit form bypass dedicated transition methods.

---

# 47. Deletion policy

Distinguish between:

- true disposable configuration/data;
- historical business records.

Historical business records should normally be status-transitioned, not hard-deleted.

Examples:

- shift registration history must be retained;
- a populated shift should normally be cancelled rather than deleted.

Never add cascade deletes that silently destroy business history without explicit approval.

---

# 48. Notifications and mail

Do not invent a new mail/notification subsystem during unrelated work.

If a task requires notifications:

1. inspect existing mail/logging infrastructure;
2. inspect current provider/config conventions;
3. keep delivery limits and batching in mind;
4. separate domain event/intent from transport where the existing architecture supports it.

For shift cancellation notifications, preserve the previously agreed business requirement that administration must be informed when a member cancels, but implement it only through the actual current notification/mail architecture after inspection.

---

# 49. Performance

Do not optimize prematurely, but avoid obvious N+1 query patterns in listing pages.

For dashboard/list counts, prefer repository-level aggregate queries.

For large member/registration lists, consider:

- indexed foreign keys;
- status indexes;
- query aggregation;
- pagination when needed.

Do not introduce caching unless there is a measured need or explicit request.

---

# 50. Responsive UX requirements

AEFS is used on varying devices.

Any new/modified UI should be usable on:

- desktop;
- laptop;
- tablet;
- phone.

Avoid fixed-width layouts that only work at one viewport.

Tables should use the project's existing responsive approach or an appropriate card/overflow adaptation.

Actions should remain reachable on small screens.

---

# 51. Accessibility baseline

Maintain basic accessibility:

- labels for form controls;
- semantic buttons for actions;
- links for navigation;
- meaningful headings;
- `aria-live` for asynchronous feedback where appropriate;
- keyboard-accessible controls;
- visible focus behavior from the existing design system.

Do not replace buttons with clickable `<div>` elements.

---

# 52. User language

The application UI is Dutch.

New end-user text should normally be Dutch unless the relevant module deliberately uses another language.

Code identifiers remain consistent with the existing mixture of English class names and Dutch domain/database terminology.

Do not translate established database columns merely for consistency.

---

# 53. Do not fabricate repository state

Never state:

```text
"this file exists"
"this method is available"
"the migration has run"
"tests pass"
"the branch is clean"
```

without verifying it from the current repository/runtime.

If the repository contradicts this document, report the concrete contradiction.

---

# 54. Do not trust historical conversation artifacts over repository state

The repository is the current implementation source of truth.

Historical code fragments, old ZIPs, previous drafts, legacy SQL, and earlier AI suggestions may be obsolete.

When continuing development:

```text
current repository
> current database schema
> AGENTS.md stable contracts
> old code/history
```

for implementation details.

Business rules explicitly captured in this file remain authoritative unless the user changes them.

---

# 55. Definition of done for a normal code change

A change is not done merely because code was generated.

For a normal PHP feature/fix, completion means:

- affected current files were inspected;
- architecture was respected;
- no duplicate abstraction was introduced;
- business rules are preserved;
- authorization remains correct;
- CSRF remains correct for mutations;
- database integrity is preserved;
- changed PHP files pass `php -l`;
- relevant runtime behavior is tested when possible;
- unrelated user changes were not overwritten;
- user receives a concise summary.

---

# 56. Definition of done for shift-management work

For shift work specifically, also verify as relevant:

- shift belongs to a valid event;
- overnight shifts remain valid;
- capacity uses confirmed registrations;
- no overbooking through approval race;
- member self-registration starts waiting;
- existing registration is not duplicated;
- re-registration after cancellation works;
- member cancellation cutoff uses event start minus 14 days;
- admin can still cancel;
- historical rows remain;
- cancelled shifts do not keep active registrations;
- presence only applies where intended;
- AJAX actions do not bypass CSRF/authorization;
- dashboard/event integrations still use current `shifts` schema.

---

# 57. Preferred Codex behavior for this repository

Codex should behave like a senior maintainer of an existing system, not like a greenfield code generator.

Preferred:

- inspect first;
- use current patterns;
- make focused changes;
- run validation;
- explain only what matters.

Avoid:

- speculative architecture;
- unnecessary abstractions;
- verbose repeated planning;
- broad rewrites;
- unrequested dependencies;
- duplicate classes;
- placeholder implementations;
- silent data-destructive migrations.

---

# 58. First action when opening this repository in a new Codex session

When starting a fresh Codex session, perform this orientation before implementing requested changes:

```text
1. Read AGENTS.md.
2. Run git status.
3. Identify current branch.
4. Inspect composer.json.
5. Inspect the relevant module tree.
6. Inspect the current route file for that module.
7. Inspect related models/repositories/services/controllers/views.
8. Search for legacy duplicate references.
9. Only then change code.
```

For a shift task specifically, inspect at least:

```text
app/Models/Shift.php
app/Models/ShiftRegistration.php
app/Models/ShiftType.php
app/Repositories/ShiftRepository.php
app/Repositories/ShiftRegistrationRepository.php
app/Repositories/ShiftTypeRepository.php
app/Services/ShiftService.php
app/Controllers/ShiftController.php
app/Validators/ShiftValidator.php
app/Validators/ShiftRegistrationValidator.php
app/Http/Requests/ShiftRequest.php
app/Http/Requests/ShiftRegistrationRequest.php
routes/shifts.php
app/Views/shifts/
```

Also search for:

```text
event_shifts
shift_toewijzingen
ShiftPresenceController
ShiftRegistrationService
ShiftInschrijving
eventmanager
```

Any active occurrence must be evaluated carefully because these names may indicate obsolete/incorrect architecture.

---

# 59. Final rule

When uncertain, inspect more current code before adding more code.

The objective is not to produce the most code.

The objective is to keep AEFS v2 coherent, secure, data-safe, maintainable, and aligned with its existing custom architecture.
