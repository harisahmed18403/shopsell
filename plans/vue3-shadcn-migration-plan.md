# Vue 3 + shadcn-vue Migration Plan

## Objective

Migrate the frontend of this Laravel 12 application from Blade + Alpine + DaisyUI to Vue 3 with `shadcn-vue`, while keeping the PHP backend, database schema, Eloquent models, services, controllers, and business logic unchanged wherever possible.

The target state is:

- Laravel remains the backend and source of truth for routing, auth, validation, persistence, and domain logic.
- Vue 3 becomes the primary frontend rendering layer for authenticated and guest UI.
- `shadcn-vue` becomes the main component system.
- Existing backend endpoints and controller logic are reused as much as possible.
- Any backend changes should be limited to presentation transport concerns only, such as returning Inertia responses or view data shaped for Vue pages.

## Current State Summary

The current app is a server-rendered Laravel application with:

- Blade views in `resources/views`
- Alpine bootstrapped in `resources/js/app.js`
- Tailwind CSS plus DaisyUI in `resources/css/app.css`
- Laravel Breeze auth screens rendered with Blade
- Vite-based asset pipeline
- Several controllers that already serve both HTML and JSON in some places

Current frontend-heavy screens and flows:

- Public: welcome
- Auth: login, register, forgot password, reset password, verify email, confirm password
- Dashboard and reports
- Profile management
- Products CRUD and product search
- Inventory listing and create flow
- Transactions index, create, edit, show, invoice download
- Customers area
- Admin users and product structure

Important existing behavior to preserve:

- Server-side auth and middleware rules
- Validation and redirects/flash messaging
- JSON search endpoints, especially product search
- PDF invoice generation
- Inventory and transaction business rules

## Migration Principles

1. Keep backend domain logic stable.
2. Prefer replacing Blade rendering with Vue page rendering, not rewriting controller business rules.
3. Make the migration incremental so the app remains usable between phases.
4. Remove Alpine and DaisyUI only after equivalent Vue/UI replacements are in place.
5. Preserve existing URLs and route names unless there is a strong reason not to.
6. Preserve form submission semantics and validation behavior unless a page benefits materially from SPA-style UX.

## Recommended Technical Approach

Use Laravel + Inertia.js + Vue 3 + `shadcn-vue`.

Why this is the lowest-risk route:

- It lets Laravel continue owning routes, middleware, controllers, auth, validation, redirects, and sessions.
- It avoids building a separate SPA API layer just to replace Blade templates.
- It maps naturally from current `return view(...)` controller responses to `Inertia::render(...)`.
- It supports gradual page-by-page migration while the backend remains intact.
- It works well with form-heavy admin/product/inventory/transaction screens.

Avoid as primary strategy:

- A standalone Vue SPA backed by new API endpoints. That would force more backend transport changes and duplicate auth/navigation concerns.
- A hybrid of Blade shells with many embedded Vue islands for every page. That can be useful as a temporary bridge, but it tends to leave long-term duplication and inconsistent UI patterns.

## Target Architecture

### Backend

- Keep existing controllers, form requests, models, services, middleware, and routes.
- Update controllers progressively from Blade responses to Inertia page responses.
- Keep JSON endpoints where they already make sense, especially search/autocomplete and any async helpers.
- Continue using Laravel session auth and CSRF protection.

### Frontend

- Add Vue 3 and Inertia client bootstrap under `resources/js`
- Create page components under a structure similar to:
  - `resources/js/pages/Auth/*`
  - `resources/js/pages/Dashboard/*`
  - `resources/js/pages/Products/*`
  - `resources/js/pages/Inventory/*`
  - `resources/js/pages/Transactions/*`
  - `resources/js/pages/Customers/*`
  - `resources/js/pages/Admin/*`
  - `resources/js/pages/Profile/*`
- Create shared app shell/layout/components under:
  - `resources/js/layouts/*`
  - `resources/js/components/ui/*`
  - `resources/js/components/app/*`
  - `resources/js/lib/*`

### UI System

- Replace DaisyUI classes/components with `shadcn-vue` primitives plus project-specific wrappers
- Use Tailwind for layout and spacing
- Use `lucide-vue-next` or equivalent Vue-compatible icon package
- Standardize tables, forms, dialogs, alerts, badges, and navigation on `shadcn-vue`

## Key Package Changes

Expected additions:

- `vue`
- `@inertiajs/vue3`
- `@inertiajs/progress`
- `@vitejs/plugin-vue`
- `shadcn-vue`
- `vue-sonner` or equivalent toast package if needed
- `class-variance-authority`
- `clsx`
- `tailwind-merge`
- `lucide-vue-next`

Expected removals or reductions after migration:

- `alpinejs`
- `daisyui`
- Blade component dependency for frontend rendering

Possible backend package addition:

- `inertiajs/inertia-laravel`

## Route and Screen Migration Inventory

### Phase 1 candidate pages

Low to medium complexity pages that should migrate early:

- `/dashboard`
- `/reports`
- `/products`
- `/products/create`
- `/products/{product}`
- `/products/{product}/edit`
- `/inventory`
- `/inventory/create`
- `/admin/users`
- `/admin/users/create`
- `/admin/users/{user}/edit`
- `/profile`

### Phase 2 candidate pages

Higher complexity or more interaction-heavy pages:

- `/transactions`
- `/transactions/create`
- `/transactions/{transaction}`
- `/transactions/{transaction}/edit`
- `/customers`
- customer create/show/edit pages if actively used
- `/admin/structure`

### Auth pages

These can be migrated either early for visual consistency or slightly later to reduce initial setup risk:

- `/login`
- `/register`
- `/forgot-password`
- `/reset-password/{token}`
- `/verify-email`
- `/confirm-password`

Recommendation:

- Migrate auth after the core Inertia/Vue shell is stable but before final UI cleanup. That keeps Breeze-related Blade removal manageable.

## Detailed Work Plan

## Phase 0: Discovery and Decision Lock

Goals:

- Confirm the migration strategy before touching production-facing behavior.
- Identify all pages, shared partials, Blade components, and Alpine behaviors.
- Decide whether the app should become fully Inertia-driven or retain a few Blade-only pages.

Tasks:

- Audit every `return view(...)` usage in controllers.
- Map Blade layouts, partials, and components to Vue replacements.
- Inventory all Alpine logic, especially the transaction form.
- Confirm whether reports use Chart.js directly and how data is serialized today.
- Confirm whether invoice rendering should remain Blade-based for PDF output.

Deliverables:

- Final architecture decision: Inertia + Vue 3
- Page migration matrix with owner/priority/risk
- Acceptance criteria for parity

## Phase 1: Foundation Setup

Goals:

- Introduce Vue 3 and Inertia without breaking existing routes.
- Create a parallel frontend foundation while Blade still works.

Tasks:

- Install Vue 3, Inertia, Vue Vite plugin, `shadcn-vue`, and utility dependencies.
- Update `vite.config.js` for Vue support.
- Replace the current JS bootstrap with an Inertia/Vue app entrypoint.
- Add root app mounting and progress indicator.
- Set up shared page props for auth user, flash messages, route metadata, and validation state.
- Add a typed or at least well-structured frontend utilities layer for:
  - route helpers
  - money/date formatting
  - flash notifications
  - API calls to existing JSON endpoints
- Keep existing CSS working during the transition.

Backend changes in this phase:

- Minimal. Mostly adding Inertia middleware/service provider configuration.

Exit criteria:

- At least one page can render through Vue/Inertia.
- Existing Blade pages still work for unmigrated routes.

## Phase 2: Design System and Shared Layouts

Goals:

- Establish the reusable Vue UI layer before mass page migration.

Tasks:

- Initialize `shadcn-vue`.
- Create the core shared components:
  - App shell
  - Guest shell
  - Sidebar/top navigation
  - Page header
  - Data table wrapper
  - Form field wrappers
  - Alert/flash/toast handling
  - Confirm dialog
  - Empty states
  - Loading/skeleton states
- Replace current Blade components conceptually with Vue equivalents:
  - buttons
  - modal
  - nav links
  - text input
  - labels/errors
  - product search widget
- Define a single visual language and remove DaisyUI-specific assumptions from new screens.

Risk to manage:

- `shadcn-vue` is intentionally low-level. Expect some custom wrappers to match current admin workflows efficiently.

Exit criteria:

- New Vue pages can be built without ad hoc styling each time.

## Phase 3: Migrate Read-Mostly Pages

Goals:

- Move simpler screens first to validate patterns.

Target screens:

- Dashboard
- Reports
- Products index/show
- Inventory index
- Admin users index

Tasks:

- Convert controller `view(...)` responses to `Inertia::render(...)`.
- Serialize only the data each page needs.
- Port charts and tables to Vue.
- Replace Blade loops and conditional markup with Vue templates.
- Preserve route names and middleware unchanged.

Notes:

- Reports should keep backend aggregation logic in PHP. Only rendering moves to Vue.

Exit criteria:

- Read-only or read-mostly pages match current behavior and styling quality.

## Phase 4: Migrate Standard CRUD Forms

Goals:

- Move moderate-complexity forms with minimal backend rewiring.

Target screens:

- Product create/edit
- Inventory create
- Admin user create/edit
- Profile edit
- Customer create/edit/show if present and used

Tasks:

- Use Inertia forms or classic form submissions behind Vue pages.
- Keep Laravel validation in controllers/form requests.
- Surface validation errors and success flashes in Vue.
- Preserve redirects after save.
- Normalize field components and error display.

Decision point:

- Choose whether every form uses Inertia form helpers or a mixed approach. Prefer consistency.

Exit criteria:

- Standard CRUD flows operate end to end with no business-logic regressions.

## Phase 5: Migrate Complex Transaction Flows

Goals:

- Replace the most interactive Blade/Alpine workflows safely.

Target screens:

- Transactions index
- Transactions create
- Transactions edit
- Transactions show

Tasks:

- Port the `transactionForm()` Alpine logic into Vue composition-based state.
- Preserve current async product search behavior against the existing `/products/search` endpoint.
- Preserve dynamic line items, quantities, pricing, totals, and conditional fields by transaction type.
- Ensure server-side validation still decides final correctness.
- Add debouncing, request cancellation, and loading/error states around product search.
- Split the transaction UI into smaller Vue components:
  - transaction header/details
  - customer section
  - line items table
  - product search/autocomplete
  - totals summary
- Keep invoice download as a backend-generated PDF flow.

Important constraint:

- The PDF invoice view may be best left as Blade if it is rendered by DOMPDF. That is acceptable and consistent with the “minimum backend change” requirement.

Exit criteria:

- Transaction create/edit flows fully work in Vue and preserve inventory/financial behavior.

## Phase 6: Auth and Account Flows

Goals:

- Remove remaining Breeze Blade auth pages and align the UX with the new app shell.

Tasks:

- Migrate login/register/password/email verification/confirm password pages to Vue.
- Keep Laravel auth controllers and middleware unchanged.
- Ensure validation, password reset tokens, verification links, and redirects still work.

Exit criteria:

- Guest/auth pages no longer depend on Blade for standard interactive rendering.

## Phase 7: Cleanup and Decommissioning

Goals:

- Remove obsolete frontend patterns once parity is complete.

Tasks:

- Remove Alpine usage.
- Remove DaisyUI dependency and class usage.
- Remove superseded Blade views except those intentionally retained:
  - PDF invoice templates
  - any email templates
  - any exceptional server-rendered fallback pages
- Simplify `resources/css/app.css` to reflect the new design system.
- Remove unused Blade components.
- Document the new frontend conventions for future contributors.

Exit criteria:

- Vue + `shadcn-vue` is the default frontend path.
- Old Blade/DaisyUI/Alpine code is not lingering in active pages.

## Backend Change Budget

To stay within the “do not change backend models or logic” requirement, backend changes should be limited to:

- Swapping view rendering to Inertia rendering
- Shaping controller props for Vue pages
- Adding shared props middleware for auth/flash data
- Minor response branching where needed during transition
- Possibly adding dedicated transformer/resource classes if controller payloads become messy

Backend changes that should be avoided unless absolutely necessary:

- Rewriting domain services
- Changing model relationships purely for frontend convenience
- Introducing a large new REST or GraphQL API layer
- Moving business calculations from PHP to Vue
- Altering validation rules unless the current rules are incorrect

## Data Transport Strategy

Preferred pattern:

- Keep page data server-delivered through Inertia props.
- Keep selective JSON endpoints for async UI behaviors.

Use server props for:

- dashboard metrics
- reports datasets
- products lists/details
- inventory data
- customer and user records for page rendering
- flash messages

Use JSON endpoints for:

- product autocomplete/search
- any future remote selects or incremental search flows

## Testing Strategy

### Backend tests

- Preserve and extend feature tests around routes, validation, redirects, and database effects.
- Add tests ensuring migrated routes still enforce auth/super-admin middleware.
- Add tests for transaction and inventory invariants before UI migration begins.

### Frontend tests

Recommended:

- Component tests for complex Vue components, especially transaction line-item behavior
- End-to-end browser tests for critical workflows:
  - login
  - create product
  - add inventory item
  - create transaction
  - edit profile
  - admin user management

### Regression focus

Highest-risk regressions:

- transaction totals or item persistence
- inventory updates on buy/sell/repair flows
- validation and error display differences
- auth redirects and flash messages
- PDF invoice behavior

## Rollout Strategy

Recommended rollout:

1. Build the Vue/Inertia foundation on a feature branch.
2. Migrate pages incrementally in small reviewable PRs or commits.
3. Keep the application deployable after each phase.
4. Validate each migrated area with focused manual and automated testing.
5. Remove old frontend dependencies only after equivalent replacement is complete.

If branch lifespan is long:

- Rebase or merge from `main` frequently because page migrations will touch shared layout and asset files.

## Risks and Mitigations

### Risk: Transaction screen complexity

Mitigation:

- Migrate it after the design system and standard form patterns are stable.
- Break it into smaller components.
- Add targeted tests before replacing the current screen.

### Risk: PDF/invoice rendering incompatibility

Mitigation:

- Keep PDF views Blade-based unless there is a clear reason to change them.
- Treat PDF rendering as an explicit exception to the Vue migration.

### Risk: Styling churn from DaisyUI to `shadcn-vue`

Mitigation:

- Build shared wrappers early.
- Avoid mixing DaisyUI and `shadcn-vue` conventions longer than necessary.

### Risk: Controller payload sprawl

Mitigation:

- Introduce resource/transformer classes if props become inconsistent or too large.

### Risk: Inconsistent form behavior during hybrid period

Mitigation:

- Standardize on Inertia form handling for migrated pages.
- Keep unmigrated pages fully Blade-based until replaced.

## Suggested Folder Deliverables During Migration

Expected new frontend structure:

```text
resources/js/
  app.js
  layouts/
  pages/
  components/
    app/
    ui/
  composables/
  lib/
```

Planning/docs deliverables:

- `plans/vue3-shadcn-migration-plan.md`
- `plans/page-migration-checklist.md`
- `plans/component-mapping.md`
- `plans/testing-regression-checklist.md`

## Recommended Execution Order

1. Add Inertia + Vue + Vite support.
2. Establish `shadcn-vue` and shared layout/components.
3. Migrate dashboard/reports and read-mostly admin pages.
4. Migrate product/inventory/admin/profile CRUD screens.
5. Migrate transaction flows.
6. Migrate auth pages.
7. Remove DaisyUI/Alpine/obsolete Blade views.

## Clarifications Needed Before Implementation

These points should be confirmed before actual migration work begins:

1. Should the migration use Inertia.js as the Laravel-to-Vue bridge, or do you want a fully separate Vue SPA frontend? This plan assumes Inertia because it best preserves the backend.
2. Do you want invoice/PDF templates to stay Blade-based if that avoids unnecessary risk? This plan recommends yes.
3. Do you want auth pages included in the migration, or should the first implementation phase focus only on the authenticated app?
4. Is TypeScript desired for the new Vue frontend, or should the migration stay in JavaScript for lower change volume?
5. Do you want the final UI to stay close to the current admin layout/flow, or is a broader UX redesign acceptable as long as functionality is preserved?

## Recommendation

Proceed with:

- Laravel + Inertia.js + Vue 3
- `shadcn-vue` for UI primitives
- Blade retained only where server-rendered output is inherently appropriate, especially PDF invoices
- Minimal backend changes limited to response transport and shared props
- Page-by-page migration, with transactions treated as the final major interactive migration
