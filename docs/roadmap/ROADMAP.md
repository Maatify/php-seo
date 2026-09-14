# SEO Library Roadmap

This roadmap contains only the accepted future work for the SEO library. Each
item describes a current capability boundary and a possible future scope;
none changes the current package contract.

## 1. Deeper Generic Schema.org Semantic Validation

**Current gap:** Deep validation remains scoped to selected structures:
`Product`, `Offer`, `AggregateOffer`, and `ProductGroup`.

**Future scope:** Design a separate capability for deeper, generic Schema.org
semantic validation.

This future work does not imply that current JSON-LD generation is invalid.
Generic Schema.org validation remains separate from Google-specific eligibility.

## 2. Google Rich Results / Provider-Specific Eligibility Profile

**Current gap:** The package does not provide a complete internal Google Rich
Results eligibility prediction or profile.

**Future scope:** Design a distinct provider-specific eligibility layer based
on explicitly maintained Google feature contracts. Keep it separate from
generic Schema.org validation. Merchant Center diagnostics are already
implemented and are not future work.

## 3. Large Sitemap Memory / Streaming Strategy

**Current gap:** `SitemapGeneratorService` accepts complete arrays and builds
complete XML strings in memory. It has no streaming API for very large datasets.

**Future scope:** Evaluate and design a memory-efficient generation strategy,
potentially using streaming or `XMLWriter` output.

The current in-memory API remains valid. Any future strategy must preserve
framework neutrality; streaming is not implemented by this roadmap change.

## Active proposal

The [Optional Admin SEO Control Layer RFC](../proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md)
remains a standalone active proposal for a framework-neutral higher-level admin
orchestration/control API. It is not a UI implementation; views, controllers,
and routes remain outside the proposal's package scope.
