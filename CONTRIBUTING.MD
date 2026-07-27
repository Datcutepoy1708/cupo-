# Contributing Guide

## Git Workflow
- `main` — production-ready code only
- `develop` — integration branch
- `feature/<name>` — feature branches (e.g. `feature/flash-sale-redis`)

## Process
1. Create a branch off `develop`
2. Implement and test your change locally
3. Run `./vendor/bin/pint --dirty` before committing
4. Open a Pull Request into `develop`, request review from another team member
5. Merge only after CI passes and the PR is approved

## Commit Message Convention
Format: `type: short description`

Examples:
- `feat: add flash sale stock locking`
- `fix: prevent oversell on concurrent checkout`
- `refactor: extract wallet logic into service class`
- `docs: update database schema`

## Code Review Checklist
See the full validation checklist in [AGENT.md](./AGENT.md#9-validation-checklist-before-marking-any-task-complete).
Reviewers should specifically check:
- [ ] Follows the architecture rules in AGENT.md
- [ ] No hardcoded credentials
- [ ] Ownership checks in place for seller/admin-scoped actions
- [ ] Tests added for any logic touching stock or payment