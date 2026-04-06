## Summary

This PR establishes the development infrastructure for future backend implementations: mandatory REQUIREMENTS.md updates on every change, automated test cases via GitHub Actions, and extended roadmap with Prio 1-3 backends (KNX, HomeMatic IP, Philips Hue, DALI, Tasmota, WLED).

## Changes

### .qwen/commands/ips-dev.md — Skill extensions
- Added section 3f: REQUIREMENTS.md update policy — mandates updating docs/REQUIREMENTS.md on every module.php, form.json, or module.json change
- Added section 3g: Test case implementation policy — mandates manual test cases in REQUIREMENTS.md and automated tests via GitHub Actions for every functional change

### .github/workflows/test.yml — New GitHub Actions workflow
- PHP syntax validation (php -l) on module.php
- JSON validation for form.json and module.json
- Structure checks: backend constants, ApplyChanges cases, RequestAction idents, public API functions
- Form validation: BackendType select with minimum 3 options, status codes 102 and 201
- Requirements doc check: verifies docs/REQUIREMENTS.md exists with all required sections
- Runs on every push and pull request to master

### docs/REQUIREMENTS.md — Extended documentation
- Added 6 planned backends to section 1.1 (KNX, HomeMatic IP, Philips Hue, DALI, Tasmota, WLED) with priorities and IPS module references
- Added 30+ functional requirements (FR-311 through FR-814) for planned backends
- Added GitHub Actions test section (7.1) documenting the automated test pipeline
- Extended manual test cases (T-001 through T-203) with status tracking
- Updated roadmap (section 8) with prioritized backend list

## Why These Changes Matter

Currently there is no enforcement that documentation stays in sync with code changes, and no automated testing to catch regressions before they reach production. This PR establishes both practices as mandatory workflow steps, ensuring that future backend implementations (KNX, HomeMatic, etc.) are properly documented and validated from the start.

The GitHub Actions workflow provides immediate feedback on code quality (PHP syntax, JSON validity) and structural integrity (all backends validated, all API functions present) without requiring a running IPS instance. This is critical for a module that interfaces with hardware-specific APIs that cannot be tested in CI.
