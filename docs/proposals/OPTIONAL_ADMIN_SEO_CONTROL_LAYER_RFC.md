# RFC: Optional Admin SEO Control Layer

**Status:** Proposed

**Type:** Optional Layer

## Current Package Capabilities

The current package already provides granular Admin-facing capabilities for SEO
overrides, redirects, SERP and social preview DTOs, metadata import/export, and
validation/reporting utilities. The Host owns entity and route lifecycle,
including slug generation, normalization, uniqueness, history, and old-slug
lookup; SEO does not require a Slug library. These package services and
factories return typed values; they do not provide an Admin UI, routes,
controllers, authentication, authorization, or application workflow. The Host
application owns those surfaces and decides how package results are presented
and applied.

The canonical [SEO_PACKAGE_REFERENCE.md](../../SEO_PACKAGE_REFERENCE.md)
describes the current runtime inventory. This RFC does not reclassify existing
capabilities as future work.

## Proposal

This RFC proposes an optional, higher-level orchestration/control API over the
package's existing granular capabilities. Its purpose is to let Host
applications compose related SEO operations through a cohesive service where
that is useful, while keeping direct use of the current builders, services,
validators, and Admin utilities available.

## Goals

- Provide an optional higher-level API for coordinating existing metadata,
  social, canonical, robots, structured-data, sitemap, and validation services.
- Reduce repeated orchestration code in Host applications that choose to use
  that API.
- Preserve framework neutrality and the current package/Host ownership boundary.
- Keep the layer optional and preserve direct access to existing package
  capabilities.

## Non-Goals

- **No UI or views:** Admin screens and presentation remain Host-owned.
- **No routes or controllers:** Request routing and HTTP delivery remain
  Host-owned.
- **No authentication or authorization:** Identity, permissions, and access
  decisions remain Host-owned.
- **No framework coupling:** The proposal adds no framework-specific request,
  response, container, or UI dependency.
- **No Host lifecycle ownership:** Entity lifecycle, product lifecycle, and
  application workflow remain Host-owned.
- The proposed layer does not make current Admin utilities conditional on a
  future API or replace their existing contracts.

## Possible Architecture

If approved for implementation, the optional layer could sit above the current
`Shared`, `Admin`, and `Web` services. It could accept package DTOs or mapped
Host values, coordinate existing operations, and return typed results for the
Host to present or persist. Its design must preserve the current PDO repository
and package-owned schema contracts where those are used; it must not require a
parallel Host ORM implementation.

## Possible Components

These are proposal examples, not implemented package APIs:

- **`AdminSeoMetadataManager`:** Coordinate existing metadata, social, canonical,
  and robots operations for an Admin workflow.
- **`AdminSitemapConfigurator`:** Coordinate current sitemap options and
  generation services where an application needs a higher-level control API.
- **`AdminJsonLdEditor`:** Map input into existing JSON-LD builders and return
  structured output for Host presentation.
- **`AdminSeoValidatorService`:** Compose current validation and reporting
  utilities for a Host dashboard or pre-publish workflow.
- **`AdminSeoImportExportService`:** Coordinate the existing metadata importer
  and exporter with other selected Admin operations.

## Open Questions

- Which operations need a single orchestration service, and which should remain
  direct calls to the existing granular APIs?
- Should a future control API coordinate existing package-owned repositories,
  or return values for the Host to persist through its selected package
  services?
- How should Host-specific forms map into the package's typed commands and DTOs
  without adding request or framework types to the package?

## Acceptance Criteria

Acceptance criteria will be defined if this proposal is approved for
implementation. Any resulting implementation must remain optional, framework
neutral, strictly typed, documented at the Host integration boundary, and
covered by the repository's standalone tests.
