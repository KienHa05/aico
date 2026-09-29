# Graph Report - aico  (2026-09-29)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 978 nodes · 1926 edges · 92 communities (47 shown, 45 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 12 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2a2e9c86`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- LoginPage.tsx
- AuthController
- User
- TestCase
- bootstrap/app.php
- render.tsx
- Illuminate\Database\Eloquent\Relations\HasMany
- Role
- components.json
- compilerOptions
- Illuminate\Database\Eloquent\Factories\HasFactory
- devDependencies
- frontend/package.json
- Illuminate\Database\Seeder
- api.ts
- authApi.ts
- compilerOptions
- Illuminate\Database\Eloquent\Relations\BelongsTo
- UserPolicyTest.php
- AppServiceProvider.php
- authSession.ts
- Illuminate\Database\Eloquent\SoftDeletes
- ProductVariant
- Illuminate\Database\Eloquent\Factories\Factory
- Illuminate\Database\Schema\Blueprint
- dependencies
- UserPolicyTest
- Cart
- Order
- Illuminate\Support\Str
- backend/package.json
- ForgotPasswordTest
- GoogleOAuthTest
- ProtectedRoute.test.tsx
- composer.json
- scripts
- Illuminate\Support\Facades\Schema
- Illuminate\Database\Migrations\Migration
- vite.config.ts
- tsconfig.test.json
- AttributeOption
- CartItem
- require-dev
- scripts
- Address
- Tag
- config
- UserFactory.php
- require
- devDependencies
- psr-4
- logging.php
- 0001_01_01_000001_create_cache_table.php
- 2026_07_11_031354_create_roles_table.php
- 2026_07_11_091821_create_permission_role_table.php
- 2026_07_14_083605_create_categories_table.php
- 2026_07_14_101854_create_products_table.php
- 2026_07_14_125702_create_attributes_table.php
- 2026_07_14_130531_create_attribute_options_table.php
- 2026_07_14_131143_create_variant_attribute_values_table.php
- 2026_07_14_132434_create_tags_table.php
- 2026_07_14_132955_create_product_tags_table.php
- 2026_07_14_134545_create_inventories_table.php
- 2026_07_14_135913_create_carts_table.php
- 2026_07_14_140204_create_cart_items_table.php
- 2026_07_14_140604_create_addresses_table.php
- 2026_07_14_141002_create_orders_table.php
- 2026_07_14_141333_create_order_items_table.php
- 2026_09_06_075857_create_social_accounts_table.php
- console.php
- ExampleTest
- artisan
- autoload-dev
- extra
- AddressFactory.php
- AttributeFactory.php
- AttributeOptionFactory
- CartFactory.php
- OrderFactory.php
- ProductVariantFactory
- RoleFactory.php
- VariantAttributeValueFactory
- tsconfig.json

## God Nodes (most connected - your core abstractions)
1. `User` - 90 edges
2. `Role` - 29 edges
3. `TestCase` - 24 edges
4. `Permission` - 23 edges
5. `cn()` - 20 edges
6. `compilerOptions` - 20 edges
7. `LoginPage()` - 18 edges
8. `AuthController` - 17 edges
9. `ProductVariant` - 17 edges
10. `GoogleOAuthCallbackPage()` - 17 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --references--> `User`  [EXTRACTED]
  backend/app/Providers/AppServiceProvider.php → backend/app/Models/User.php
- `{closure#2}()` --references--> `User`  [EXTRACTED]
  backend/app/Providers/AppServiceProvider.php → backend/app/Models/User.php
- `{closure#2}()` --references--> `User`  [EXTRACTED]
  backend/app/Services/AuthService.php → backend/app/Models/User.php
- `GET /api/v1/test/policy/users/{user}()` --references--> `User`  [EXTRACTED]
  backend/tests/Feature/Authorization/UserPolicyTest.php → backend/app/Models/User.php
- `PATCH /api/v1/test/policy/users/{user}()` --references--> `User`  [EXTRACTED]
  backend/tests/Feature/Authorization/UserPolicyTest.php → backend/app/Models/User.php

## Import Cycles
- None detected.

## Communities (92 total, 45 thin omitted)

### Community 0 - "LoginPage.tsx"
Cohesion: 0.08
Nodes (67): App(), Button, ButtonProps, buttonVariants, Card, CardContent, CardDescription, CardFooter (+59 more)

### Community 1 - "AuthController"
Cohesion: 0.05
Nodes (18): AuthController, Controller, ForgotPasswordRequest, LoginRequest, RegisterRequest, ResetPasswordRequest, AuthService, {closure#2}() (+10 more)

### Community 2 - "User"
Cohesion: 0.08
Nodes (8): User, EmailVerificationTest, RefreshTokenTest, Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject

### Community 3 - "TestCase"
Cohesion: 0.09
Nodes (9): LoginTest, LogoutTest, ProtectedRouteTest, RegisterTest, ExampleTest, TestCase, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase (+1 more)

### Community 4 - "bootstrap/app.php"
Cohesion: 0.12
Nodes (20): PermissionMiddleware, Response, RoleMiddleware, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+12 more)

### Community 5 - "render.tsx"
Cohesion: 0.13
Nodes (15): forgotPasswordMock, getCurrentUserMock, mocks, mocks, registerMock, resetPasswordMock, AuthState, createAxiosError() (+7 more)

### Community 6 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.10
Nodes (5): Category, Product, CategorySeeder, ProductSeeder, Illuminate\Database\Eloquent\Relations\HasMany

### Community 7 - "Role"
Cohesion: 0.18
Nodes (4): Permission, Role, PermissionTest, Illuminate\Database\Eloquent\Relations\BelongsToMany

### Community 8 - "components.json"
Cohesion: 0.09
Nodes (21): aliases, components, hooks, lib, ui, utils, iconLibrary, menuAccent (+13 more)

### Community 9 - "compilerOptions"
Cohesion: 0.09
Nodes (21): compilerOptions, allowArbitraryExtensions, allowImportingTsExtensions, erasableSyntaxOnly, jsx, lib, module, moduleDetection (+13 more)

### Community 10 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.16
Nodes (8): Inventory, ProductTag, SocialAccount, {closure#1}(), ProductTagSeeder, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model

### Community 11 - "devDependencies"
Cohesion: 0.10
Nodes (21): devDependencies, eslint, @eslint/js, eslint-plugin-react-hooks, eslint-plugin-react-refresh, globals, jsdom, tailwindcss (+13 more)

### Community 12 - "frontend/package.json"
Cohesion: 0.13
Nodes (18): vite, name, private, type, version, clsx, eslint, @eslint/js (+10 more)

### Community 13 - "Illuminate\Database\Seeder"
Cohesion: 0.15
Nodes (8): DatabaseSeeder, InventorySeeder, PermissionSeeder, RolePermissionSeeder, RoleSeeder, UserSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 14 - "api.ts"
Cohesion: 0.18
Nodes (14): getCurrentUser(), initialize(), api, AUTH_ENDPOINTS_WITHOUT_REFRESH, AuthRequestConfig, refreshAccessToken(), clearStoredAccessToken(), getStoredAccessToken() (+6 more)

### Community 15 - "authApi.ts"
Cohesion: 0.21
Nodes (14): logout(), authSlice, AuthState, initialState, ApiResponse, AuthStatus, AuthTokenData, AuthUser (+6 more)

### Community 16 - "compilerOptions"
Cohesion: 0.12
Nodes (16): compilerOptions, allowImportingTsExtensions, erasableSyntaxOnly, lib, module, moduleDetection, noEmit, noFallthroughCasesInSwitch (+8 more)

### Community 17 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.17
Nodes (5): OrderItem, VariantAttributeValue, OrderItemSeeder, VariantAttributeValueSeeder, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 18 - "UserPolicyTest.php"
Cohesion: 0.13
Nodes (9): UserPolicy, GET /(), GET /api/v1/test/policy/users/{user}(), PATCH /api/v1/test/policy/users/{user}(), Carbon\Carbon, Illuminate\Routing\Middleware\SubstituteBindings, Illuminate\Support\Facades\Gate, Illuminate\Support\Facades\Route (+1 more)

### Community 19 - "AppServiceProvider.php"
Cohesion: 0.16
Nodes (9): AppServiceProvider, {closure#1}(), {closure#2}(), {closure#1}(), Illuminate\Auth\Notifications\ResetPassword, Illuminate\Auth\Notifications\VerifyEmail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\URL (+1 more)

### Community 20 - "authSession.ts"
Cohesion: 0.26
Nodes (11): frontend_src_features_auth_authslice_clearauth, frontend_src_features_auth_authslice_setaccesstoken, frontend_src_features_auth_authslice_setcredentials, frontend_src_features_auth_authslice_setinitialized, frontend_src_features_auth_authslice_setstatus, frontend_src_features_auth_authslice_setuser, AppDispatch, RootState (+3 more)

### Community 21 - "Illuminate\Database\Eloquent\SoftDeletes"
Cohesion: 0.19
Nodes (5): Attribute, ProductImage, AttributeSeeder, ProductImageSeeder, Illuminate\Database\Eloquent\SoftDeletes

### Community 22 - "ProductVariant"
Cohesion: 0.18
Nodes (4): ProductVariant, InventoryFactory, ProductVariantSeeder, Illuminate\Database\Eloquent\Relations\HasOne

### Community 23 - "Illuminate\Database\Eloquent\Factories\Factory"
Cohesion: 0.21
Nodes (5): PermissionFactory, ProductFactory, ProductImageFactory, ProductTagFactory, Illuminate\Database\Eloquent\Factories\Factory

### Community 24 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.23
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), Illuminate\Database\Schema\Blueprint

### Community 25 - "dependencies"
Cohesion: 0.15
Nodes (13): dependencies, axios, class-variance-authority, clsx, @hookform/resolvers, react, react-dom, react-hook-form (+5 more)

### Community 27 - "Cart"
Cohesion: 0.22
Nodes (3): Cart, CartItemFactory, CartSeeder

### Community 28 - "Order"
Cohesion: 0.22
Nodes (3): Order, OrderItemFactory, OrderSeeder

### Community 29 - "Illuminate\Support\Str"
Cohesion: 0.18
Nodes (4): CategoryFactory, TagFactory, Illuminate\Support\Str, Pdo\Mysql

### Community 30 - "backend/package.json"
Cohesion: 0.18
Nodes (10): tailwindcss, @tailwindcss/vite, vite, private, $schema, scripts, build, dev (+2 more)

### Community 33 - "ProtectedRoute.test.tsx"
Cohesion: 0.22
Nodes (8): frontend_src_index, rootElement, store, renderRoute(), user, createAuthTestStore(), react-dom, react-redux

### Community 34 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 35 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 36 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema

### Community 37 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.22
Nodes (3): {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 38 - "vite.config.ts"
Cohesion: 0.25
Nodes (6): laravel-vite-plugin, ref_node_url, ref_path, ref_tailwindcss_vite, ref_vite, @vitejs/plugin-react

### Community 39 - "tsconfig.test.json"
Cohesion: 0.22
Nodes (8): compilerOptions, noEmit, paths, tsBuildInfoFile, extends, include, @tests/*, ./tsconfig.app.json

### Community 42 - "require-dev"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 43 - "scripts"
Cohesion: 0.25
Nodes (8): scripts, build, dev, lint, preview, test, test:typecheck, test:watch

### Community 46 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 47 - "UserFactory.php"
Cohesion: 0.33
Nodes (3): UserFactory, Illuminate\Support\Facades\Hash, static

### Community 48 - "require"
Cohesion: 0.33
Nodes (6): require, laravel/framework, laravel/socialite, laravel/tinker, php, php-open-source-saver/jwt-auth

### Community 49 - "devDependencies"
Cohesion: 0.33
Nodes (6): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite

### Community 50 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 51 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 72 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 73 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **201 isolated node(s):** `ButtonProps`, `EmptyStateProps`, `ErrorStateProps`, `InputProps`, `LabelProps` (+196 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 369 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **45 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `GoogleOAuthTest`, `AuthController`, `TestCase`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Role`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `AddressFactory.php`, `CartFactory.php`, `OrderFactory.php`, `UserFactory.php`, `Illuminate\Database\Seeder`, `UserPolicyTest.php`, `AppServiceProvider.php`, `UserPolicyTest`, `ForgotPasswordTest`?**
  _High betweenness centrality (0.134) - this node is a cross-community bridge._
- **Why does `Role` connect `Role` to `GoogleOAuthTest`, `AuthController`, `TestCase`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Database\Seeder`, `RoleFactory.php`, `UserPolicyTest.php`, `UserPolicyTest`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Why does `devDependencies` connect `devDependencies` to `frontend/package.json`?**
  _High betweenness centrality (0.011) - this node is a cross-community bridge._
- **What connects `ButtonProps`, `EmptyStateProps`, `ErrorStateProps` to the rest of the system?**
  _201 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `LoginPage.tsx` be split into smaller, more focused modules?**
  _Cohesion score 0.07692307692307693 - nodes in this community are weakly interconnected._
- **Should `AuthController` be split into smaller, more focused modules?**
  _Cohesion score 0.05201636469900643 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.08408408408408409 - nodes in this community are weakly interconnected._
