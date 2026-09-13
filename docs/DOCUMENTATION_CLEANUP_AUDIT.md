# Documentation Cleanup Audit

## 1. Baseline

This audit targets `codex/standards-adoption-compliance` at SHA `3fa498896da608ab435c990e0f40c56a3ecb345e`.

The repository currently contains 188 tracked files in the `docs/` tree. A significant portion of this is historical execution evidence (completed audits, phases, blueprints, verification reports) which causes discoverability noise and contradicts the owner policy.

## 2. Owner Policy

The repository working tree must describe the **current package**, **current durable architecture/contracts**, and **future work**.

Git history, commits, PRs, tags, and releases are the historical record.

A file must NOT remain in the current repository merely because it documents something that happened previously.

Historical execution evidence with no continuing operational, contractual, architectural, legal, security, contribution, or future-planning value should be deleted from the current tree.

We will not solve clutter by creating `docs/archive/`, `docs/history/`, or `docs/legacy/`.

## 3. Executive Decision

Following the owner policy, all directories containing historical execution evidence, legacy v1 material, completed planning documents, and historical release artifacts will be completely removed. We will consolidate future planning into a single roadmap and retain only currently active architecture contracts, standard governance, and guides.

* `docs/audits/**` → DELETE_NO_CURRENT_VALUE
* `docs/verification/**` → DELETE_NO_CURRENT_VALUE
* `docs/phases/**` → DELETE_NO_CURRENT_VALUE
* `docs/batches/**` → DELETE_NO_CURRENT_VALUE
* `docs/blueprints/**` → DELETE_NO_CURRENT_VALUE
* `docs/release/**` → DELETE_NO_CURRENT_VALUE
* `docs/SEO/v1/**` → DELETE_NO_CURRENT_VALUE
* `docs/roadmap/**` → MIGRATE_THEN_DELETE into one `docs/roadmap/ROADMAP.md`

## 4. Final Keep Set

The following documents represent the active architecture, governance, and integrations that will remain:

* `docs/CI.md`
* `docs/README.md`
* `docs/guides/INTEGRATION_GUIDE.md`
* `docs/guides/USAGE_GUIDE.md`
* `docs/SEO/library/META_GENERATOR_SERVICE_CONTRACT.md`
* `docs/SEO/library/README.md`
* `docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md`
* `docs/php-engineering-standards/**` (all 12 files)
* `docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md`

Root documents remaining intact:
* `README.md`
* `SEO_PACKAGE_REFERENCE.md`
* `CHANGELOG.md`
* `SECURITY.md`
* `CONTRIBUTING.md`
* `CODE_OF_CONDUCT.md`
* `AGENTS.md`

Total `KEEP_CURRENT`: 20 files (19 existing + 1 new roadmap).

## 5. Architecture Audit Migration Matrix

Target: `docs/audits/SEO_ARCHITECTURE_STANDARDS_CONTRACT_INTEGRITY_AUDIT.md`

Review of the historical audit against `SEO_PACKAGE_REFERENCE.md`, `docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md`, `docs/SEO/library/META_GENERATOR_SERVICE_CONTRACT.md` and `docs/guides/USAGE_GUIDE.md` confirms that the **durable architectural decisions**—such as the JSON-LD scoped structural validation boundary, empty vs. non-empty property evaluations, URL validation constraints (RFC 9309, percentage decoding rules), and strict exclusion of automatic provider migration—are either already reflected in the current authoritative sources (e.g. `SEO_PACKAGE_REFERENCE.md` defines the scope and provider boundary, and `Stack8DocumentationTruthSynchronizationTest.php` protects these statements), or they have been explicitly coded into the test suites that govern the package.

**Decision**:
Every durable decision is already canonically represented or codified in `src/`/`tests/`.
`docs/audits/SEO_ARCHITECTURE_STANDARDS_CONTRACT_INTEGRITY_AUDIT.md` = `DELETE_NO_CURRENT_VALUE`.
No migration is required.

## 6. Future Roadmap Contract

Current roadmaps:
* `docs/roadmap/SEO_LIBRARY_ROADMAP.md`
* `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`

These will be consolidated into `docs/roadmap/ROADMAP.md`.
All completed phases, WUs, SHAs, and verification history will be stripped.

Future roadmap items that must remain:

* **Item**: Advanced Structured Data Semantic Validation (Phase 13P Follow-up)
  * **Current gap**: Current library validates structure but leaves deep Schema.org semantics/ontology unverified.
  * **Intended scope**: Full semantic analysis against Schema.org types if prioritized.
  * **Explicit out-of-scope**: Modifying current JSON-LD scoped builders.
  * **Dependencies/preconditions**: Needs architecture decision on validation engine.
  * **Source roadmap location**: `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`
  * **Evidence**: Current runtime only supports the 4 scoped structures. `SEO_PACKAGE_REFERENCE.md` explicitly calls this out.

* **Item**: Optional Admin SEO Control Layer (Phase 24 / RFC)
  * **Current gap**: No UI/admin integration standard.
  * **Intended scope**: Standardized control layer interfaces.
  * **Explicit out-of-scope**: Concrete framework views.
  * **Dependencies/preconditions**: Current proposed RFC.
  * **Source roadmap location**: `docs/roadmap/SEO_LIBRARY_ROADMAP.md`
  * **Evidence**: Proposed in `docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md`.

## 7. Active Proposal Decision

Target: `docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md`

**Decision**: `KEEP_ACTIVE_PROPOSAL`
The proposal is currently marked as 'Proposed' and the runtime has no concrete admin view layer implemented. The future `ROADMAP.md` will link to this RFC.

## 8. Full Directory Deletion Decisions

The following directories contain no unmigrated unique durable information and will be removed completely. No archive replacement will be created.

* `docs/audits/`
* `docs/batches/`
* `docs/blueprints/`
* `docs/phases/`
* `docs/release/`
* `docs/SEO/v1/`
* `docs/verification/`

## 9. Reference and Link Impact

* `README.md`:
  * Remove links to historical execution evidence (audits/verification). `REMOVE_REFERENCE`.
* `SEO_PACKAGE_REFERENCE.md`:
  * Remove any stale links to deleted blueprints. `REMOVE_REFERENCE`.
* `CHANGELOG.md`:
  * Unaffected (does not link to `docs/verification/`).
* `docs/README.md`:
  * Rebuild entirely to match the new Authority Model (see section 10). `REMOVE_REFERENCE` for all deleted folders. `RETARGET_REFERENCE` for roadmap.
* `tests/Stack8DocumentationTruthSynchronizationTest.php`:
  * `BLOCKS_DELETION_UNTIL_MIGRATED` - Must be updated to stop asserting the presence of `docs/phases/*` and `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`.

## 10. Documentation Authority Model

The new `docs/README.md` will enforce this exact authority model:

```text
Executable truth
- composer.json
- src/**
- schema/**
- tests/**

Canonical package contract
- SEO_PACKAGE_REFERENCE.md

Maintained documentation
- README.md
- docs/guides/**
- docs/SEO/library/**
- docs/CI.md

Future planning
- docs/roadmap/ROADMAP.md
- active proposals only

Governance
- docs/php-engineering-standards/**
```
All references to historical directories will be removed.

## 11. Stack8 Test Impact

Target: `tests/Stack8DocumentationTruthSynchronizationTest.php`

**Must remain**:
* Assertions ensuring `SEO_PACKAGE_REFERENCE.md` exists and is authoritative.
* Assertions verifying the presence of critical terms in `STRUCTURED_DATA_ARCHITECTURE.md` and `USAGE_GUIDE.md`.

**Must be removed**:
* `stack8Read('docs/phases/PHASE_22_SEARCH_CONSOLE_EXTERNAL_VERIFICATION.md');` and associated assertions checking for "Phase 22 Final Review passed".
* Assertions verifying the exact historical breakdown of `docs/README.md` that list `phases/`, `verification/`, `batches/`, `audits/`.

**Must be retargeted**:
* `stack8Read('docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md');` → retargeted to `docs/roadmap/ROADMAP.md`.

## 12. Cleanup Arithmetic

* current docs file count: 188
* files deleted: 168 (166 DELETE_NO_CURRENT_VALUE + 2 old roadmaps)
* files created: 2 (`ROADMAP.md` and this `DOCUMENTATION_CLEANUP_AUDIT.md`)
* files modified: 2 (`docs/README.md`, `tests/Stack8DocumentationTruthSynchronizationTest.php`) (plus root doc links)
* final docs file count: 22
* net file reduction: 166
* current docs total bytes: 1,061,027 (approx)
* projected docs total bytes: ~140,000 (approx)
* estimated bytes removed: ~921,027
* percentage file-count reduction: 88.3%
* percentage byte reduction: 86.8%

*(Note: directories are not counted as files in the above metrics)*

## 13. Exact Implementation Scope

1. **Delete Directories**:
   `rm -rf docs/audits/ docs/batches/ docs/blueprints/ docs/phases/ docs/release/ docs/SEO/v1/ docs/verification/`
2. **Consolidate Roadmaps**:
   Create `docs/roadmap/ROADMAP.md` with active future items.
   `rm docs/roadmap/SEO_LIBRARY_ROADMAP.md docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`
3. **Rewrite docs/README.md**:
   Update to match the exact Authority Model in Section 10.
4. **Update Root Docs**:
   Strip links to deleted historical documents in `README.md` and `SEO_PACKAGE_REFERENCE.md`.
5. **Update Stack8 Test**:
   Remove assertions for `PHASE_22` and historical folders. Point roadmap checks to `ROADMAP.md`.
6. **Commit Changes**:
   Run tests. If passing, commit.

## 14. Verification Required After Cleanup

* `composer validate`
* `vendor/bin/phpunit tests/Stack8DocumentationTruthSynchronizationTest.php`
* Full test suite: `vendor/bin/phpunit`
* Ensure no broken markdown links in the remaining 22 docs.

## 15. Final Verdict

CLEANUP_IMPLEMENTATION_READY
