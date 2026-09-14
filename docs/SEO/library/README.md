# Maatify SEO Library Engineering Handbook

This directory is the current, maintained Maatify SEO Library Engineering
Handbook. Its documents provide narrower maintained architecture and service
guidance for current package contracts.

The root [SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md) remains
the canonical package-level contract. This handbook adds detail without
replacing or competing with that reference.

Historical implementation and execution evidence lives in Git and GitHub
history—commits, pull requests, tags, and releases—not in historical
documentation directories in the current tree.

The [current roadmap](../../roadmap/ROADMAP.md) and active proposals are planning
material. They do not override executable truth or current package contracts.

Host/application-specific SEO architecture is outside this package handbook
unless represented by an explicit current package contract. This includes
routing structure, product lifecycle, HTTP status decisions,
internal-linking strategy, and site-specific multilingual URL policy.

The Host owns entity URLs and slug lifecycle, including generation,
normalization, uniqueness, history, and old-slug lookup. SEO does not require a
Slug library; a Host-provided slug may be supplied only as input to its
`HostUrlGeneratorInterface`. Any integration with a separate Slug library
belongs to the Host or an adapter.

Use the [official documentation index](../../README.md) to see the complete
authority hierarchy.

## Contents

The Package Reference is the canonical inventory of current package contracts.
This map points to the most useful maintained entry point for each architecture
area without repeating that inventory.

| Area | Current entry point |
| --- | --- |
| Package authority and public contract | [SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md) |
| Metadata generation and override semantics | [MetaGeneratorService Contract](META_GENERATOR_SERVICE_CONTRACT.md), [Usage Guide](../../guides/USAGE_GUIDE.md) |
| HTML head rendering and social metadata | [Usage Guide](../../guides/USAGE_GUIDE.md), [Integration Guide](../../guides/INTEGRATION_GUIDE.md) |
| Canonical URLs and hreflang | [Usage Guide](../../guides/USAGE_GUIDE.md), [SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md) |
| Robots and sitemaps | [Usage Guide](../../guides/USAGE_GUIDE.md), [Integration Guide](../../guides/INTEGRATION_GUIDE.md) |
| Structured data and JSON-LD | [Structured Data Architecture](STRUCTURED_DATA_ARCHITECTURE.md), [Usage Guide](../../guides/USAGE_GUIDE.md) |
| Core validation and companion profiles | [Usage Guide](../../guides/USAGE_GUIDE.md), [SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md) |
| Redirects and SEO overrides | [Integration Guide](../../guides/INTEGRATION_GUIDE.md), [SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md) |
| Persistence and package-owned schemas | [Integration Guide](../../guides/INTEGRATION_GUIDE.md), [SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md) |
| Admin previews, operations, and import/export | [Usage Guide](../../guides/USAGE_GUIDE.md), [Integration Guide](../../guides/INTEGRATION_GUIDE.md) |
| Page presets and page rendering | [Usage Guide](../../guides/USAGE_GUIDE.md), [Integration Guide](../../guides/INTEGRATION_GUIDE.md) |
| Search Console | [Usage Guide](../../guides/USAGE_GUIDE.md), [Integration Guide](../../guides/INTEGRATION_GUIDE.md) |
| Merchant Center | [Usage Guide](../../guides/USAGE_GUIDE.md), [Integration Guide](../../guides/INTEGRATION_GUIDE.md) |
| CI and local verification | [CI operations](../../CI.md) |
| Future roadmap and active proposals | [Roadmap](../../roadmap/ROADMAP.md), [Optional Admin SEO Control Layer RFC](../../proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md) |
