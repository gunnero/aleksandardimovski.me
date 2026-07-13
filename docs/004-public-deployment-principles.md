# Public deployment principles

This document records public-safe release principles. Environment-specific hostnames, users, paths, service layouts, runtime inventories, certificate details, backup locations, and commands belong in private operational records.

## Release requirements

- Select the exact reviewed commit and confirm the source tree is clean.
- Prepare a rollback backup before changing the active release.
- Preserve protected environment configuration and public verification content.
- Install backend dependencies from the lock file and verify platform requirements.
- Install frontend dependencies from the lock file and build assets reproducibly.
- Run migrations safely and review their effect before activation.
- Rebuild application configuration, route, and view caches.
- Normalize ownership and permissions without world-writable access.
- Validate web-server configuration before any reload.
- Verify TLS, application health, public routes, metadata, assets, and logs.
- Keep the previous known-good release available until verification is complete.

## Rollback principle

If a critical check fails, restore the previous reviewed release, rebuild its caches, verify health, and record the failure privately. A deployment must not leave production partially updated.

## Public evidence boundary

Public release reports may identify the reviewed application commit and describe the validation categories above. They must not publish production topology or the operational instructions used to access and administer it.
