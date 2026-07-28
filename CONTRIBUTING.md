# 🤝 Contributing Guide

> Development guidelines and contribution workflow for the AICO Platform.

Thank you for your interest in contributing to **AICO Platform**.

This document defines the development standards, workflow, and engineering practices that help keep the project consistent, maintainable, and scalable.

---

# 📖 Philosophy

The project follows one simple principle:

> **Build software like a real engineering team, even when developing alone.**

Every contribution should improve the project without sacrificing code quality, architecture, or maintainability.

---

# 🎯 Development Principles

Every contribution should follow these principles:

- Business value before technical complexity.
- Keep the architecture simple and modular.
- Prefer readability over clever code.
- Avoid premature optimization.
- Write reusable components whenever possible.
- Document significant architectural decisions.
- Keep the codebase clean and maintainable.

---

# 🌿 Git Workflow

## Main Branches

| Branch | Purpose |
|----------|----------|
| `main` | Stable production-ready code |
| `develop` | Active development |

---

## Feature Branch

Create a new branch for every feature.

Example:

```text
feature/authentication
feature/product-management
feature/file-storage
feature/ai-chatbot
feature/payment
```

---

## Bug Fix

```text
fix/login-validation
fix/order-status
```

---

## Hotfix

```text
hotfix/security-patch
```

---

# 💬 Commit Convention

Follow the **Conventional Commits** specification.

Examples:

```text
feat: add product search

fix: resolve JWT authentication issue

docs: update project roadmap

refactor: simplify authentication service

style: improve code formatting

test: add unit tests for order service

chore: update dependencies
```

---

# 📂 Project Structure

```text
aico/

├── backend/
├── frontend/
├── docs/
├── docker/
├── scripts/
└── README.md
```

Every directory should have a clear responsibility.

Avoid mixing unrelated modules.

---

# 🏗 Architecture Guidelines

The backend should follow a layered architecture.

```text
Controller
      ↓
Service
      ↓
Repository
      ↓
Database
```

### Responsibilities

**Controller**

- Handle HTTP requests.
- Validate request data.
- Return responses.

Do **not** place business logic here.

---

**Service**

Contains all business logic.

---

**Repository**

Responsible for database interaction only.

---

**Model**

Represents application entities.

Avoid placing business logic inside models.

---

# 🌐 API Guidelines

Use RESTful API principles.

Example:

```http
GET     /products
GET     /products/{id}

POST    /products

PUT     /products/{id}

DELETE  /products/{id}
```

Responses should use a consistent structure.

```json
{
    "success": true,
    "message": "Operation completed successfully.",
    "data": {}
}
```

---

# 🗄 Database Guidelines

- Use Laravel migrations.
- Use foreign keys whenever appropriate.
- Avoid duplicated data.
- Add indexes for searchable fields.
- Never modify production data manually.

---

# 🔒 Security Guidelines

Always consider security when implementing new features.

Minimum requirements:

- JWT Authentication
- RBAC
- Input Validation
- Password Hashing
- SQL Injection Prevention
- Secure File Upload

---

# 🧪 Testing

Whenever possible:

- Write Unit Tests.
- Test APIs before merging.
- Verify existing features are not broken.
- Keep new features backward compatible.

---

# 📝 Documentation

Documentation should evolve together with the project.

Whenever a major feature is introduced, update the relevant documentation.

Examples:

- README
- PROJECT_CHARTER
- PROJECT_STATE
- AI_ENGINEERING_GUIDE

---

# 🤖 Contributing to AI Features

Before working on any AI-related feature, read the full AI implementation blueprint:

- [docs/AI_ENGINEERING_GUIDE.md](docs/AI_ENGINEERING_GUIDE.md)

This document covers:

- The recommended AI service architecture (Python / FastAPI, separate from Laravel).
- Learning levels from Level 0 (foundations) to Level 11 (productionization).
- The full feature mapping for all 50 Admin and 17 Client AI features.
- The suggested **AI MVP Feature Set** for portfolio-focused development.

Do **not** add AI logic directly inside Laravel. All AI processing should live in the `ai-service` (Python / FastAPI) and communicate with Laravel via internal REST API.

---

# 🚀 Pull Request Checklist

Before opening a Pull Request, ensure that:

- [ ] The feature is completed.
- [ ] The code builds successfully.
- [ ] Existing functionality is not broken.
- [ ] Documentation has been updated (if necessary).
- [ ] Commit messages follow the convention.
- [ ] Code is clean and readable.

---

# 📌 Final Notes

AICO Platform is a long-term engineering project.

The objective is **not** to build software as quickly as possible.

The objective is to build software that can continue evolving over time while remaining maintainable, scalable, and production-ready.