# Maatify SEO Documentation

This is the official entry point for the Maatify SEO library documentation.
Use the current package sources and maintained contract documents for present
APIs, behavior, integration guidance, and limitations. Historical and planning
documents provide evidence or context, not a competing package contract.

## Documentation authority

### Current executable package contract

The executable package contract is the combination of current Composer metadata,
public runtime code, and shipped schema assets:

- [`composer.json`](../composer.json)
- [`src/`](../src/)
- [`schema/`](../schema/)

### Canonical human-readable package reference

The root [`SEO_PACKAGE_REFERENCE.md`](../SEO_PACKAGE_REFERENCE.md) is the single
canonical human-readable package contract. It summarizes the current runtime
surface and package boundaries; it does not replace the executable contract.

### Detailed current documentation

- [`README.md`](../README.md) — package overview and quick start.
- [`guides/`](guides/) — usage and Host integration guidance.
- [`SEO/library/`](SEO/library/) — narrower architecture and service contracts.
- [`CI.md`](CI.md) — configured quality and verification gates.

These documents add detail to the canonical package reference; they are not
competing top-level Package References.

### Historical implementation evidence

[`phases/`](phases/), [`verification/`](verification/), [`batches/`](batches/),
completed audit records under [`audits/`](audits/), completed documents under
[`blueprints/`](blueprints/), and legacy material under [`SEO/v1/`](SEO/v1/)
record work, verification, or decisions at a point in time. They do not override
the current executable package contract, current tests, or the canonical
reference.

### Future and planning material

- [`roadmap/`](roadmap/) records planned sequencing and lifecycle context.
- [`proposals/`](proposals/) records proposed designs and RFCs.
- An unstarted document in [`blueprints/`](blueprints/) remains planning material
  until its scope is implemented and accepted.

Roadmaps, proposals, and unstarted blueprints are not current API authority.

## Reading paths

- Start with the [package README](../README.md) for installation and a quick start.
- Use the root [canonical package reference](../SEO_PACKAGE_REFERENCE.md) for the
  current public runtime inventory and package boundaries.
- Use the [engineering handbook](SEO/library/README.md) for architecture and
  structured-data boundaries.
- Use the [usage guide](guides/USAGE_GUIDE.md) and [integration guide](guides/INTEGRATION_GUIDE.md)
  for runnable examples and host-application integration.

When documents disagree about current package behavior, check Composer metadata,
public runtime code, schema assets, and tests, then use the canonical reference
and maintained detailed contracts. Historical audits or implementation records
do not replace the current public package contract.
