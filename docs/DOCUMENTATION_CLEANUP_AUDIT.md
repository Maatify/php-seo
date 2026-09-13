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

| Finding ID | Concise Durable Decision | Classification | Current Authority/Evidence | Migration Destination |
| --- | --- | --- | --- | --- |
| F-01 | Sitemap serialization has two independent implementation paths. (Unified architecture required). | `NO_LONGER_CURRENT` | Resolved by `SitemapGeneratorService` unifying generation. | N/A |
| F-02 | Base sitemap protocol rules and provider extension rules separated. | `ALREADY_CANONICAL` | Documented in `SEO_PACKAGE_REFERENCE.md` provider profiles and strict scope. | N/A |
| F-03 | Deprecated Google Image sitemap fields remain for compatibility. | `ALREADY_CANONICAL` | `SitemapImageDTO` continues to support legacy fields, covered by Stack8 tests. | N/A |
| F-04 | Google Video sitemap validation completeness (strict absolute URLs, http/https/ftp). | `ALREADY_CANONICAL` | `SitemapVideoDTO` validation rules in code enforce HTTP/HTTPS/FTP. | N/A |
| F-05 | Google News sitemap provider contract and ISO 639 exceptions. | `ALREADY_CANONICAL` | `SitemapNewsDTO` rules enforce this, documented in `SEO_PACKAGE_REFERENCE.md`. | N/A |
| F-06 | `robots.txt` DTOs conform to RFC 9309 and Google-specific contracts separated. | `ALREADY_CANONICAL` | `RobotsRenderer` handles base rules. `SEO_PACKAGE_REFERENCE.md` notes this. | N/A |
| F-07 | `crawl-delay` modeled as non-core behavior. | `ALREADY_CANONICAL` | Handled properly in `RobotsRenderer`. | N/A |
| F-08 | `MetaRobotsBuilder` allows Google `-1` semantics. | `ALREADY_CANONICAL` | Runtime builder allows -1. | N/A |
| F-09 | `indexifembedded` presence in typed Google robots helpers. | `ALREADY_CANONICAL` | Runtime builder implements this. | N/A |
| F-10 | `MetaRobotsBuilder::unavailableAfter(string $value)` remains a raw compatibility builder. The builder preserves caller-provided text and does not enforce a closed local date grammar. Missing or whitespace-only values are handled separately by the Google provider-validation path. For non-empty values, provider recognizability uses explicit evidence states (`recognized`, `unrecognized`, `unknown`). `unknown` represents an evidence gap and must not be converted into a fabricated pass or failure. No local parser may claim exhaustive Google-recognized date validation. | `ALREADY_CANONICAL` | Current runtime and provider-validation implementation support this exact contract. | N/A |
| F-11 | `noarchive` valid to preserve despite stale Google meaning. | `ALREADY_CANONICAL` | Builder supports noarchive. | N/A |
| F-12 | Core SEO validation conflates validity with heuristics (byte/length). | `ALREADY_CANONICAL` | Stack8/tests enforce Unicode heuristic rules explicitly. | N/A |
| GDC-01 | Global diagnostics contract for protocol/provider/context diagnostics. | `ALREADY_CANONICAL` | `SearchConsole` and `MerchantCenter` namespaces implement isolated boundaries. | N/A |
| F-13 | Open Graph required-field model mismatch and explicit protocol contract. | `ALREADY_CANONICAL` | `OpenGraphBuilder` enforces rules, documented in `SEO_PACKAGE_REFERENCE.md`. | N/A |
| F-14 | Canonical URL behavior permissive by design; relative canonical allowed. | `ALREADY_CANONICAL` | `CanonicalBuilder` handles validation correctly. | N/A |
| F-15 | Hreflang normalization paths and provider-aware cluster validation. | `ALREADY_CANONICAL` | Stack8 tests protect Hreflang ISO boundary wording. | N/A |
| F-16 | Structured Data correctly separates Schema.org from Google eligibility. | `ALREADY_CANONICAL` | `STRUCTURED_DATA_ARCHITECTURE.md` explicitly calls this out. | N/A |
| F-17 | Course / Book provider status deferred to capability matrix. | `ALREADY_CANONICAL` | `SEO_PACKAGE_REFERENCE.md` states capability matrix. | N/A |
| F-18 | `JsonLdSemanticValidator` is scoped type/range validation, not complete semantic. | `ALREADY_CANONICAL` | `STRUCTURED_DATA_ARCHITECTURE.md` and `SEO_PACKAGE_REFERENCE.md`. | N/A |
| F-19 | Documentation and CHANGELOG do not accurately describe behavior. | `NO_LONGER_CURRENT` | Resolved via `codex/standards-adoption-compliance` cleanup and CHANGELOG updates. | N/A |
| F-20 | Repository needs one normative documentation hierarchy. | `ALREADY_CANONICAL` | `docs/README.md` defines the new Authority model. | N/A |
| F-21 | Twitter/X Cards provider contract not source-verified by this audit. | `ALREADY_CANONICAL` | `SEO_PACKAGE_REFERENCE.md` states Twitter/X is explicitly not source-verified. | N/A |

**Result**: Every durable decision from the historical audit is already successfully codified into existing architectural contracts (`STRUCTURED_DATA_ARCHITECTURE.md`, `SEO_PACKAGE_REFERENCE.md`), test suites, or runtime behaviors. `MUST_MIGRATE = 0`. Therefore, `SEO_ARCHITECTURE_STANDARDS_CONTRACT_INTEGRITY_AUDIT.md` is classified as `DELETE_NO_CURRENT_VALUE`.

## 6. Future Roadmap Contract

Current roadmaps:
* `docs/roadmap/SEO_LIBRARY_ROADMAP.md`
* `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`

These will be consolidated into `docs/roadmap/ROADMAP.md`.
All completed phases, completed WUs, SHAs, Draft/Ready history, and historical verification states are stripped.

### Pre-Coding Risks / Decisions Breakdown

Review of the three old `Risks / Decisions that Need Approval Before Coding` (from `SEO_LIBRARY_ROADMAP.md`):

1. **Entity Identifier Type** (uuid vs int):
   * **Classification**: `RESOLVED_CURRENT_CONTRACT`
   * **Evidence**: `schema/maa_seo_slug_history.sql` explicitly implements `entity_id VARCHAR(36) NOT NULL COMMENT 'Host-provided ID. No FK.'`. The database schema fully addresses this decision.

2. **Extensibility of `entity_type`**:
   * **Classification**: `RESOLVED_CURRENT_CONTRACT`
   * **Evidence**: `schema/maa_seo_slug_history.sql` defines `entity_type VARCHAR(50) NOT NULL COMMENT 'Host-defined entity type. No FK.'`. The host controls the enum strings dynamically, resolving the extensibility concern.

3. **Sitemap Generation Memory Constraints** (streaming vs memory):
   * **Classification**: `FUTURE_WORK`
   * **Evidence**: The current runtime `SitemapGeneratorService` signatures explicitly accept fully loaded arrays (`array $urls`) and return complete objects/strings, building XML strings entirely in memory. It does not provide a streaming API. There is no repository evidence proving the streaming concern was explicitly closed/rejected. Thus, memory handling for very large sitemaps remains future work.

### Genuine Future Roadmap Items

The only items that will be carried forward to `ROADMAP.md` as future work are:

* **Title**: Deeper Generic Schema.org Semantic Validation
  * **Current gap**: Current library strictly validates the structural shape and property-ranges of 4 scoped structures (`Product`, `Offer`, `AggregateOffer`, `ProductGroup`), but leaves deep Schema.org ontology unverified.
  * **Intended scope**: Full generic semantic analysis against Schema.org types if prioritized.
  * **Explicit out-of-scope**: Modifying current JSON-LD builders. Google-specific Rich Results provider eligibility is explicitly out of scope for this task. Merchant Center eligibility diagnostics are ALSO out of scope (completed in Phase 23).
  * **Dependencies/preconditions**: Architecture decision on a semantic validation engine.
  * **Source roadmap location**: `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md` (Phase 13P Follow-up).
  * **Evidence**: Current runtime supports only scoped structures. Explicitly deferred by `STRUCTURED_DATA_ARCHITECTURE.md`.

* **Title**: Google Rich Results / Provider-Specific Eligibility Profile
  * **Current gap**: While Phase 22 introduced external verification orchestration, an internal robust "eligibility prediction" engine specific strictly to Google's dynamic Rich Results guidelines (independent from generic Schema.org) does not exist natively in the library.
  * **Intended scope**: A distinct validation layer verifying structures explicitly against Google's feature guidelines.
  * **Explicit out-of-scope**: Mixing this with generic Schema.org validation. Merchant Center Diagnostics are ALSO out of scope here as they are already completed (Phase 23).
  * **Dependencies/preconditions**: Volatile provider mapping and capabilities matrix.
  * **Source roadmap location**: `docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`
  * **Evidence**: Explicitly kept separate as provider eligibility is highly volatile, as stated in `STRUCTURED_DATA_ARCHITECTURE.md`.

* **Title**: Large Sitemap Memory / Streaming Strategy
  * **Current gap**: `SitemapGeneratorService` processes complete arrays and generates complete XML strings in memory. There is no streaming API for very large datasets, posing a risk of memory exhaustion.
  * **Intended scope**: Provide a memory-efficient strategy (such as `XMLWriter` streaming) to generate sitemaps without holding the entire XML in memory.
  * **Compatibility constraints**: Must not imply that the current in-memory API is invalid for typical use cases. Must preserve framework neutrality.
  * **Source roadmap location**: `docs/roadmap/SEO_LIBRARY_ROADMAP.md` (Risks).
  * **Evidence**: Current `SitemapGeneratorService` signatures and lack of explicit repository rejection of the streaming concern.

## 7. Active Proposal Decision

Target: `docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md`

**Decision**: `KEEP_ACTIVE_PROPOSAL`
* The proposal is a standalone active RFC.
* The gap is a framework-neutral higher-level admin orchestration/control API, **not a UI implementation**.
* UI/views/controllers/routes remain explicitly out of scope.
* It is NOT "Phase 24". Phase 24 was MetaGeneratorService Contract Finalization (which is complete).
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
  * `REMOVE_REFERENCE`: Must remove parenthetical links to deleted files (e.g. `(see docs/verification/...md)`). The historical changelog entries themselves must be fully preserved.
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
* files created: 1 (new `ROADMAP.md`)
* files modified: 2 (`docs/README.md`, `tests/Stack8DocumentationTruthSynchronizationTest.php`) (plus root doc links)
* final docs file count: 21 (20 existing keeps + 1 new roadmap)
* net file reduction: 167
* current docs total bytes: ~1,061,027 (approx)
* projected docs total bytes: ~140,000 (approx)
* estimated bytes removed: ~921,027
* percentage file-count reduction: 88.8%
* percentage byte reduction: 86.8%

*(Note: `DOCUMENTATION_CLEANUP_AUDIT.md` deletes itself at the end of execution and is excluded from the final permanent count).*

## 13. Exact Implementation Scope

1. **Delete Directories**:
   `rm -rf docs/audits/ docs/batches/ docs/blueprints/ docs/phases/ docs/release/ docs/SEO/v1/ docs/verification/`
2. **Consolidate Roadmaps**:
   Create `docs/roadmap/ROADMAP.md` with only the active future items outlined in section 6.
   `rm docs/roadmap/SEO_LIBRARY_ROADMAP.md docs/roadmap/SEO_LIBRARY_ENHANCEMENT_ROADMAP.md`
3. **Rewrite docs/README.md**:
   Update to match the exact Authority Model in Section 10.
4. **Update Root Docs**:
   Strip links to deleted historical documents in `README.md`, `SEO_PACKAGE_REFERENCE.md`, and `CHANGELOG.md` (removing only the link text/parentheticals from `CHANGELOG.md`, not the entries).
5. **Update Stack8 Test**:
   Remove assertions for `PHASE_22` and historical folders. Point roadmap checks to `ROADMAP.md`.
6. **Self-Delete Audit**:
   `rm docs/DOCUMENTATION_CLEANUP_AUDIT.md` (After execution of the above).
7. **Commit Changes**:
   Verify with checks. If passing, commit.

## 14. Verification Required After Cleanup

* `composer validate --strict`
* `vendor/bin/phpstan analyse`
* `php tests/Stack8DocumentationTruthSynchronizationTest.php`
* Run other standalone PHP tests using the existing repository convention while excluding `tests/Integration/**`
* `bash scripts/ci/actionlint.sh`
* `git diff --check`
* Ensure no broken markdown links in the remaining 21 docs. Perform a repository-wide search to confirm that no surviving tracked documentation/root documentation references any deleted path under `docs/audits/`, `docs/batches/`, `docs/blueprints/`, `docs/phases/`, `docs/release/`, `docs/SEO/v1/`, `docs/verification/`, or old roadmap filenames. The cleanup must leave zero broken references.

*(Do NOT add PHPUnit as a dependency)*

## 15. Final Verdict

CLEANUP_IMPLEMENTATION_READY
