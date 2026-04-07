## Summary

This PR fixes a critical form.json compatibility issue: IPS does not support the `TextField` element type. All text input fields have been changed to `ValidationTextBox`, which is the correct IPS form element type for string properties. Additionally, two new GitHub Actions validation steps have been added to prevent this class of error in the future.

## Changes

### form.json — TextField → ValidationTextBox
- Replaced all 5 occurrences of `"type": "TextField"` with `"type": "ValidationTextBox"`
- Affected fields: KNXSwitchAddress, KNXDimAddress, DALISwitchAddress, DALIDimAddress, WLEDIPAddress
- ValidationTextBox is the standard IPS element type for text input that maps to string properties registered via RegisterPropertyString()

### .github/workflows/test.yml — Form validation tests
- Added "Check form.json Element Types" step: fails CI if any unsupported type (like TextField) is found in form.json
- Added "Check form.json Property Names Match module.php" step: verifies every `name` field in form.json has a corresponding RegisterProperty call in module.php
- These checks prevent future form compatibility issues from reaching production

### docs/REQUIREMENTS.md — Form specification documentation
- Expanded section 2.3 with 11 form requirements (FR-301 through FR-311) documenting all valid IPS form element types
- Added FR-305: "Text fields must use ValidationTextBox (not TextField)"
- Added FR-310: "form.json must be valid JSON"
- Added FR-311: "Property names in form.json must match RegisterProperty calls in module.php"
- Added 3 new test cases (T-018 through T-020) for form validation
- Updated GitHub Actions test table to include Form Element Types and Property Name Match checks
- Updated change history with version 2.0.1

## Why These Changes Matter

The "nicht unterstützter Type: TextField" error prevented users from opening the module configuration form entirely. This was caused by using a non-existent IPS form element type. The IPS SDK documentation specifies these valid form element types: Select, SelectInstance, SelectVariable, ValidationTextBox, NumberSpinner, Label, Button, CheckBox, Switch, and a few others. TextField is not among them.

The new CI checks ensure this cannot happen again:
1. The element type check scans form.json for any unsupported types and fails the build if found
2. The property name match check ensures every form field has a corresponding RegisterProperty call, catching typos or missing property registrations

This fix is backward-compatible: ValidationTextBox reads and writes the same string properties that the TextField was attempting to use. Existing configurations are unaffected.
