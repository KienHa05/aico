# AICO Platform — Project State

> **Last updated:** 2026-09-12  
> **Current focus:** Phase 1 MVP — Business Backend  
> **Overall status:** In progress

---

## Purpose

This document is the current implementation snapshot for AICO Platform.

It records what is currently implemented and verified in the repository, the
major architectural decisions that have been made, and the next verified
development milestone.

This is **not** the long-term feature roadmap. Planned work belongs to the
project roadmap and architecture documentation.

---

## Executive Summary

The authentication and authorization foundation is complete through token
rotation and revocation.

The commerce database foundation is also in place, including the core schema
for categories, products, variants, attributes, inventory, carts, orders,
addresses, roles, permissions, and related entities.

The project is now moving from **security foundation work** into the
implementation of production business APIs.

The next milestone is the **Category API**, followed by the Product API and
the remaining commerce workflows.

The MVP is **not complete yet**. There are currently no production Category,
Product, Cart, Order, Checkout, or Admin APIs, and the frontend remains a
bootstrap application.

---

## Development Progress

| Phase | Status | Verified State |
|---|---|---|
| **Phase 1 — MVP** | In progress | Database foundation and Auth/Authz foundation are complete; production commerce APIs and frontend flows remain. |
| **Phase 2 — Core Platform** | In progress | Auth/Authz security foundation is complete; file storage, Redis cache, and Docker development infrastructure are not implemented. |
| **Phase 3 — Production Ready** | Planned | Queues, search, testing expansion, monitoring, centralized logging, CI/CD, payment integrations, and related production infrastructure are not implemented. |
| **Phase 4 — AI Commerce** | Planned | No AI service or AI commerce feature implementation yet. |
| **Phase 5 — System Evolution** | Planned | No distributed-service architecture, horizontal scaling, API gateway, or full observability implementation yet. |

---

## Verified Implementation

### Database Foundation

The current backend repository contains:

- **23 migrations**
- **19 Eloquent models**
- **18 factories**
- **20 seeders**

The commerce schema already contains foundational entities for:

- Categories
- Products
- Product variants / SKUs
- Product attributes
- Inventory
- Carts
- Orders
- Order-related addresses and snapshots
- Tags
- Roles
- Permissions
- Social accounts

The schema also establishes the planned foundations for:

- SPU/SKU product modeling
- EAV-style product attributes
- Inventory records
- Cart and order persistence
- Order snapshots
- Role and permission relationships
- Foreign-key constraints
- Soft deletes where applicable

These database structures are currently **foundational only**. Their
corresponding production business APIs have not yet been implemented.

---

## Authentication and Authorization Foundation

The authentication and authorization workstream is complete through token
rotation and revocation.

The following milestones are implemented and committed on `develop`:

| Milestone | Status | Capability |
|---|---|---|
| **#7–#10** | Complete | Registration validation, registration, login, and JWT foundation |
| **#12–#17** | Complete | JWT login, protected routes, logout, email verification, password reset, and basic RBAC |
| **#18** | Complete | JWT refresh endpoint |
| **#19** | Complete | Google OAuth2 account sign-in and account linking |
| **#20** | Complete | Fine-grained permissions and permission middleware |
| **#21** | Complete | Gate and `UserPolicy` authorization foundation |
| **#22** | Complete | Token rotation, blacklist-based revocation, and rolling refresh lifetime |

### Current JWT Behavior

The current JWT lifecycle is configured as follows:

- Access-token TTL: **60 minutes**
- Refresh window: **20,160 minutes (14 days)**
- `JWT_REFRESH_IAT=true`
- JWT blacklist: **enabled**
- Blacklist grace period: **0 seconds**
- Successful refresh rotates the token
- The previous token is immediately revoked after rotation
- Logout revokes the current token
- Refreshing a token produces a new token lifetime based on the current
  refresh time

Because `JWT_REFRESH_IAT=true`, the refresh window uses **rolling refresh
semantics** rather than an absolute 14-day session lifetime.

The refresh endpoint is intentionally available outside the `auth:api`
middleware:

```text
POST /api/v1/auth/refresh