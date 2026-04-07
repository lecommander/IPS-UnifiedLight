## Summary

This PR implements the DALI via KNX backend (Prio 2) using KNX-DALI gateways such as BEG Luxomat and Lunatone. DALI shares the KNXInstanceID with the KNX backend but uses separate DALI-specific group addresses for switching and dimming. This brings the total number of supported backends to 9 across 7 protocols. Additionally, this PR fills in missing test cases that were not added during previous backend implementations.

## Changes

### module.php — DALI via KNX backend implementation
- Added BACKEND_DALI constant (value 8)
- Added three new properties: DALISwitchAddress (string), DALIDimAddress (string), DALIGatewayType (integer: 0=Auto, 1=BEG Luxomat, 2=Lunatone)
- DALI shares KNXInstanceID with the KNX backend since both route through the same KNX/IP gateway
- Extended ApplyChanges() with DALI validation: requires valid KNX instance, non-empty DALI switch address, and non-empty DALI dim address
- Extended SetPower() with DALI case: calls EIB_Switch(daliSwitchAddress, on)
- Extended SetBrightness() with DALI case: calls EIB_DimValue(daliDimAddress, level)
- Updated module header docblock to list DALI via KNX as a supported backend
- DALI uses the same EIB_Switch/EIB_DimValue API as KNX since DALI telegrams are encapsulated in KNX frames by the gateway

### form.json — DALI configuration form
- Added DALI option to BackendType select (value 8, caption: "DALI via KNX (KNX-DALI-Gateway)")
- Added DALI Settings section with four fields: KNX Instance (SelectInstance, shared with KNX backend), DALI Gateway Type (Select: Auto/BEG Luxomat/Lunatone), DALI Switch Group Address (TextField), DALI Dim Group Address (TextField)
- All DALI fields are only visible when BackendType == 8

### docs/REQUIREMENTS.md — Documentation updates
- Marked DALI via KNX as implemented in backend overview table
- Updated DALI functional requirements (FR-611 through FR-617) from planned to implemented
- Added DALI properties to API specification (DALISwitchAddress, DALIDimAddress, DALIGatewayType)
- Extended ApplyChanges validation pseudocode with DALI branch
- Added 5 DALI test cases (T-601 through T-605)
- Updated roadmap: RM-004 DALI via KNX marked as implemented
- Updated change history with version 1.6.0
- Added missing test cases from previous implementations: T-003a (KNX visibility), T-003b (HomeMatic visibility), T-008c (HomeMatic instance validation), T-008d (HomeMatic device validation), T-013 (Toggle API), T-014 (FadeTo DMX), T-015 (FadeTo HomeMatic), T-016 (FadeTo KNX/Hue/Shelly), T-017 (invalid RequestAction ident)

### .github/workflows/test.yml — CI updates
- Added BACKEND_DALI constant check to structure validation job
- Added case self::BACKEND_DALI check to ApplyChanges validation job

## Why These Changes Matter

DALI (Digital Addressable Lighting Interface) is the professional lighting control standard for commercial buildings, hotels, hospitals, and high-end residential installations in Europe. Unlike KNX which controls individual actuators, DALI controls lighting groups (up to 16 groups per DALI bus) with individual addressing of up to 64 devices per group. This makes DALI ideal for scenarios where fine-grained lighting control is needed without the wiring complexity of individual KNX actuators per luminaire.

The KNX-DALI gateway approach (BEG Luxomat, Lunatone) allows DALI installations to integrate with existing KNX infrastructure. The gateway translates KNX group addresses to DALI group commands, enabling UnifiedLight to control DALI luminaires using the same EIB_Switch/EIB_DimValue API used for native KNX. This means DALI support required minimal code changes — essentially the same pattern as the KNX backend with separate address properties.

The test case additions address a gap in the development process: previous backend implementations (KNX, HomeMatic, Hue) added some test cases but missed several important scenarios including form visibility tests for each backend, validation tests for all new properties, and API function tests (Toggle, FadeTo per backend). This PR retroactively adds those missing test cases to ensure the test specification is complete for all implemented backends.
