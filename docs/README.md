# Maatify SEO Documentation

This index points to the current package contract, maintained usage guidance,
architecture documents, and active future planning for the Maatify SEO library.

## Documentation authority

The repository documentation follows this authority model:

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

## Reading paths

- Start with the [package README](../README.md) for an overview and quick start.
- Use the root [SEO_PACKAGE_REFERENCE.md](../SEO_PACKAGE_REFERENCE.md) for the
  canonical package contract and public runtime inventory.
- Follow the [usage guide](guides/USAGE_GUIDE.md) and
  [integration guide](guides/INTEGRATION_GUIDE.md) for maintained usage and host
  integration guidance.
- Use the [SEO library handbook](SEO/library/README.md) and its architecture
  contracts for detailed current behavior.
- See [CI operations](CI.md) for local verification commands and configured
  checks.
- Review the [roadmap](roadmap/ROADMAP.md) for the accepted future work and its
  link to the active admin control-layer proposal.

When a claim concerns current package behavior, check the executable truth and
the canonical package contract. Supporting documentation explains those
contracts and gives integration guidance.
