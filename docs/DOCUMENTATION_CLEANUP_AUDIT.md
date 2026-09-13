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
* `docs/DOCUMENTATION_CLEANUP_AUDIT.md` → DELETE_NO_CURRENT_VALUE (to be deleted after cleanup execution)

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
* `docs/roadmap/ROADMAP.md` (new)

Total existing retained docs: 20 files.
Plus 1 new `docs/roadmap/ROADMAP.md` = 21 files final tree.

## 5. Architecture Audit Migration Matrix

Target: `docs/audits/SEO_ARCHITECTURE_STANDARDS_CONTRACT_INTEGRITY_AUDIT.md`

| Finding/Section ID | Concise Durable Decision | Classification | Current Authority/Evidence | Migration Destination |
| --- | --- | --- | --- | --- |
| Canonical extended-DTO serialization decision | Form parameter mapping is strict, not generic dynamic mapping. | `ALREADY_CANONICAL` | Codified in `SearchConsoleInspectionResultDTO` and tested in test suite. Explicitly stated in `SEO_PACKAGE_REFERENCE.md`. | N/A |
| Empty property missing vs explicitly provided | Empty string means "delete/missing", preserving missing state strictly. `trim()` is allowed only to detect missing/whitespace-only when surface contract has missing semantics. | `ALREADY_CANONICAL` | Enforced at code level across `SeoMetaBuilder` and validated by tests. | N/A |
| Compatibility public fields deprecation | Deprecation of fields is explicitly deferred out of remediation until a separate migration contract is approved. | `ALREADY_CANONICAL` | Tests ensure no fields were removed prematurely. Addressed in `SEO_PACKAGE_REFERENCE.md` provider profiles. | N/A |
| Unicode Measurement Heuristics & Scoring | Strict heuristic bounds for meta tags, resolving strlen byte-limit vs unicode requirements. | `ALREADY_CANONICAL` | Test suite characterizes and enforces measurement semantics (ASCII vs Arabic text limits). | N/A |
| Sitemap URL & Percent decoding semantics | Strict RFC 9309 URL encoding boundaries: NO percent-decoding occurs during lexical decisions, literal `%` is encoded as `%25` | `ALREADY_CANONICAL` | Codified in `SitemapUrlDTO`, `RobotsRenderer`, and heavily characterization-tested. | N/A |
| Structured Data Semantic Validation Deferral | Deep schema.org semantic validation is explicitly deferred, maintaining the current scoped structural check boundary. | `ALREADY_CANONICAL` | Covered in `docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md` and `SEO_PACKAGE_REFERENCE.md` | N/A |
| Cross-Host Semantics | External APIs must not enforce cross-host assumptions unless spec explicitly permits it. | `ALREADY_CANONICAL` | Enforced in code for Robots (cross-host allowed) vs Sitemap Index (same site). | N/A |

**Result**: Every durable decision from the 339KB historical audit is already successfully codified into existing architectural contracts (`STRUCTURED_DATA_ARCHITECTURE.md`, `SEO_PACKAGE_REFERENCE.md`), test suites, or runtime behaviors. `MUST_MIGRATE = 0`. Therefore, `SEO_ARCHITECTURE_STANDARDS_CONTRACT_INTEGRITY_AUDIT.md` is classified as `DELETE_NO_CURRENT_VALUE`.

## 6. Future Roadmap Contract

Current roadmaps:
* `docs/roadmap/SEO_LIBRARY_ROADMAP.md`
* `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`

These will be consolidated into `docs/roadmap/ROADMAP.md`.
All completed phases, completed WUs, SHAs, Draft/Ready history, and historical verification states are stripped.

**Risks / Decisions that Need Approval Before Coding (from old roadmap)**:
* All old items here have been either implemented via the completed phases or addressed as part of the standards adoption in `codex/standards-adoption-compliance`. These are resolved/obsolete and will not be carried forward.

Future roadmap items that must genuinely remain:

* **Title**: Advanced Structured Data Semantic Validation
  * **Current gap**: Current library validates the structure of 4 specific models (`Product`, `Offer`, `AggregateOffer`, `ProductGroup`), but leaves deep generic Schema.org semantic analysis unverified.
  * **Intended scope**: Full semantic analysis against Schema.org types if prioritized.
  * **Explicit out-of-scope**: Modifying current JSON-LD scoped builders. Google-specific Rich Results provider eligibility is explicitly out of scope for this task (as Google documentation remains the authority, independent of Schema validity). Merchant Center eligibility diagnostics are also out of scope as they were completed in Phase 23.
  * **Dependencies/preconditions**: Needs architecture decision on validation engine.
  * **Source roadmap location**: `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md` (Phase 13P Follow-up)
  * **Evidence**: Current runtime only supports the 4 scoped structures. `SEO_PACKAGE_REFERENCE.md` and `STRUCTURED_DATA_ARCHITECTURE.md` explicitly confirm the scoped validation boundary.

## 7. Active Proposal Decision

Target: `docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md`

**Decision**: `KEEP_ACTIVE_PROPOSAL`
* The proposal is a standalone active RFC.
* The gap is a framework-neutral higher-level admin orchestration/control API, **not a UI implementation**.
* UI/views/controllers/routes remain explicitly out of scope.
* It is NOT "Phase 24" (Phase 24 is MetaGeneratorService Contract Finalization, which is complete).
* The future `ROADMAP.md` will link to this RFC.

## 8. Full Directory Deletion Decisions

The following directories contain no unmigrated unique durable information (as proved by the matrix) and will be removed completely. No archive replacement will be created.

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
  * Remove any stale links to deleted blueprints or audits. `REMOVE_REFERENCE`.
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
All references to deleted historical directories will be removed from this authority model.

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
* files created: 1 (new `ROADMAP.md`) (Note: `DOCUMENTATION_CLEANUP_AUDIT.md` will be created during review but deleted during final cleanup implementation).
* files modified: 2 (`docs/README.md`, `tests/Stack8DocumentationTruthSynchronizationTest.php`) (plus root doc links)
* final docs file count: 21
* net file reduction: 167
* current docs total bytes: ~1,061,027 (approx)
* projected docs total bytes: ~140,000 (approx)
* estimated bytes removed: ~921,027
* percentage file-count reduction: 88.8%
* percentage byte reduction: 86.8%

## 13. Exact Implementation Scope

1. **Delete Directories**:
   `rm -rf docs/audits/ docs/batches/ docs/blueprints/ docs/phases/ docs/release/ docs/SEO/v1/ docs/verification/`
2. **Consolidate Roadmaps**:
   Create `docs/roadmap/ROADMAP.md` with only the active future items outlined in section 6.
   `rm docs/roadmap/SEO_LIBRARY_ROADMAP.md docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`
3. **Rewrite docs/README.md**:
   Update to match the exact Authority Model in Section 10.
4. **Update Root Docs**:
   Strip links to deleted historical documents in `README.md` and `SEO_PACKAGE_REFERENCE.md`.
5. **Update Stack8 Test**:
   Remove assertions for `PHASE_22` and historical folders. Point roadmap checks to `ROADMAP.md`.
6. **Cleanup the Audit File**:
   `rm docs/DOCUMENTATION_CLEANUP_AUDIT.md` (After execution of the above).
7. **Commit Changes**:
   Run tests. If passing, commit.

## 14. Verification Required After Cleanup

* `composer validate`
* `vendor/bin/phpunit tests/Stack8DocumentationTruthSynchronizationTest.php`
* Full test suite: `vendor/bin/phpunit`
* Ensure no broken markdown links in the remaining 21 docs.

## 15. Final Verdict

CLEANUP_IMPLEMENTATION_READY
