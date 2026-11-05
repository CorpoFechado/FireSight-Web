# Chapter 4 Playwright execution results

Run: 5 November 2026 at 11:46:48 AM (Asia/Manila), local Laravel Herd site, Chromium. Duration: approximately 2 minutes 20 seconds. Existing MySQL data was queried read-only. No temporary business records were created. The administrator login uses an existing development account; credentials are supplied through environment variables and are not included here.

The latest HTML report contains **19 browser checks: 17 passed, 2 failed, 0 skipped**. These are checks, not 19 fully verified Chapter 4 cases. Several passing checks cover only part of a case. The status below reflects the complete detailed case, including required samples and mobile steps. “Not Implemented” includes cases with only a partial script.

| HTML report group | Executed checks | Passed | Failed | Skipped |
|---|---:|---:|---:|---:|
| Authentication | 3 | 3 | 0 | 0 |
| Retrieval, analytics and GIS | 16 | 14 | 2 | 0 |
| **Total** | **19** | **17** | **2** | **0** |

The failed checks are **RET-012** (reference lookup) and **GIS-003** (barangay filter on the incident map). A passing browser check may still be partial or require mobile verification, as noted below.

| Test Case ID | Test Scenario | Test Data Used | Expected Result | Actual Result | Status | Evidence |
|---|---|---|---|---|---|---|
| AUTH-003 | Administrator valid login | Existing seeded administrator | Dashboard and administrator functions available | Login and analytics navigation passed | Pass | HTML: AUTH-003 |
| AUTH-004 | Wrong password | Existing administrator and deliberately wrong password | Generic error; no session | Error shown; protected page redirects to login | Pass | HTML: AUTH-004 |
| AUTH-009 | Logout ends session | Administrator session | Web and mobile sessions end; protected data inaccessible | Web logout, back and reload passed; BFP mobile not tested | Blocked | HTML: AUTH-009 (web only) |
| RET-002 | Retrieve a specific incident | Pending report 1256, Puting Kahoy | Complete incident details agree with database | Core reporter information, description, status and photo count matched; AI, location, contacts and other displayed data remain | Not Implemented | HTML: RET-002 (partial) |
| RET-003 | Filter by barangay | Resolved: Balibago 3, Cumba 5, Barangay 4 6, San Diego 7, Barangay 2 8 | Matching rows and SQL counts for five barangays | Counts and all 29 visible rows matched SQL for selected fields | Pass | HTML: RET-003 and barangay-samples.json |
| RET-004 | Filter by date range | Existing pending report date | Only records within inclusive dates returned | One-day inclusive range, SQL count and visible dates matched | Pass | HTML: RET-004 |
| RET-005 | Retrieve incident history | Existing report with multiple history entries | Stored history appears in defined order | Displayed timestamps matched database order | Pass | HTML: RET-005 |
| RET-011 | Content verification of retrieved data | 20 resolved incidents, IDs listed below | Every displayed field correct and complete | Reporter information, status, description, photo counts and selected assessment fields matched; several displayed fields and mobile remain | Not Implemented | HTML: RET-011 and incident-sample-ids.json |
| RET-012 | Invalid Reference ID | FS-000000 and valid reference INC-2026-1256 | Safe not-found response, no server error | Invalid input gave empty results, but positive control could not retrieve the valid reference; list search has no reference lookup | Fail | HTML: RET-012 screenshot and trace |
| ADM-001 | Create BFP personnel account | New personnel and mobile login required | Account stored and mobile login succeeds | Account creation and mobile login not exercised | Not Implemented | None |
| ADM-003 | View analytics dashboard | 2026 incident dataset | Charts equal SQL aggregates | IA-001 verifies type percentages, but monthly, barangay, alarm and response values not fully compared | Not Implemented | HTML: IA-001 (partial) |
| ADM-004 | View risk map/risk ranking | Existing latest risk assessments | Barangay risk levels match stored values | Ranking scores and colors matched stored risk assessments | Pass | HTML: ADM-004 |
| GIS-001 | Incident marker location | Ten resolved incident IDs listed below | Marker position matches database coordinates | Selected marker rendered at map centre; tile-derived map centre was within 10 m of each stored coordinate | Pass | HTML: GIS-001 and coordinate-samples.json |
| GIS-003 | Barangay filter on map | Map filter | Matching markers and SQL count for five barangays | No barangay filter found on incident map; count comparison could not proceed | Fail | HTML: GIS-003 screenshot and trace |
| GIS-004 | Date filter on map | 2026-11-02: 30; 2026-11-01: 32; 2026-10-03: 6 | Only incidents in range displayed | Counts matched SQL and all 68 popup timestamps matched the selected dates | Pass | HTML: GIS-004 and map-records attachments |
| GIS-005 | Risk display per barangay | All barangays; web and mobile | Map colors/levels match stored model output | Map polygon and mobile comparison not automated | Not Implemented | None |
| GIS-006 | Heatmap consistency | Barangay 1: 83 incidents; stored high risk | Highest density areas correspond to SQL counts | Highest-count barangay had high-risk polygon and ranking; remaining density areas not compared | Not Implemented | HTML: GIS-006 (partial) |
| GIS-007 | Marker details | Ten resolved IDs listed below | Ten marker popups match their records | Ten web popups matched reference, type, alarm, barangay and timestamp; mobile remains | Blocked | HTML: GIS-007 and marker-samples.json |
| IA-001 | GIS analytics accessible (web) | 2026 type aggregates listed below | Charts/tables render correctly and completely | Four chart headings rendered and type percentages matched SQL; other charts not fully checked | Not Implemented | HTML: IA-001 and analytics-type-aggregates.json |
| IA-003 | Risk map accessible (web) | Barangay risk layer and legend | Risk information displays correctly | Layer and legend rendered; all polygons not checked against SQL | Not Implemented | HTML: IA-003 (partial) |
| IA-005 | Incident details accessible (web) | Resolved report 1269 | Correct details display | Core fields and selected assessment values matched; remaining fields not checked | Not Implemented | HTML: IA-005 (partial) |
| IA-006 | Filtered information (web) | Puting Kahoy, 2026-11-03, one pending report | Displayed data matches barangay and date filters | Count and displayed identity, barangay and date matched SQL | Pass | HTML: IA-006 and filter-sample.json |
| IA-007 | Repeated retrieval | 100 web/mobile attempts | Retrieval success rate and counts reported | Combined attempt loop and mobile verification not implemented | Not Implemented | None |
| REL-001 | Repeat-run consistency | Frozen suite, repeated runs | Identical passing runs divided by total runs | Repeated frozen-suite measurement not performed | Not Implemented | None |
| E2E-006 | Verified incident to GIS | Verified incident, web and BFP mobile | Incident appears according to map rules on both | Web/mobile workflow not fully automated | Not Implemented | GIS-007 is a separate partial sample |
| E2E-012 | Complete critical workflow | Full resident-to-BFP-to-admin incident flow | Entire workflow and checkpoints verified | Mobile and complete workflow not exercised | Not Implemented | None |

**Case summary:** 26 cases with detailed procedures and Playwright assignment: 19 have at least one browser check; 9 fully passed, 2 failed, 2 are blocked by outstanding mobile verification, and 13 are not fully implemented (including six with partial checks). Execution coverage is 19/26 cases with at least a partial browser check (73.1%); full-case pass/fail coverage is 11/26 (42.3%). The full-case pass rate among completed pass/fail cases is 9/11 (81.8%); unexecuted and partial cases are excluded.

The 20 resolved incident IDs sampled were 1269, 1289, 1309, 1315, 1275, 1295, 1301, 1321, 1261, 1281, 1287, 1307, 1267, 1273, 1313, 1319, 1279, 1299, 1305, and 1265. The first ten were used for GIS-001 and GIS-007. SQL's 2026 type counts were residential 132, commercial 50, vehicular 34, storage 27, rubbish 69, and other 22.

**Functional findings:** GIS-003: the incident map offers period, type and alarm filters, but no barangay filter; the barangay dropdown is on the separate incident list and cannot filter map markers. RET-012: the invalid input produces a safe empty list, but the same list search does not find an existing reference ID, so this is not a working reference lookup. GIS-006: the rendered layer represents stored risk classifications; the sample does not establish an incident-density heatmap for all barangays. No application functionality was changed.

**Automation fixes:** The expanded RET-003 originally filtered before the Resolved tab finished navigating, and GIS popup checks initially matched multiple popup elements during transitions. Waiting for navigation and selecting the popup by reference fixed those test-script errors before the final run.

The PDF's detailed administrator table defines ADM-001 as personnel creation, ADM-003 as analytics and ADM-004 as risk ranking. Its tool list also names ADM-006, ADM-007 and ADM-009 without detailed procedures, while later summary tables reuse ADM-006/ADM-007 for scenarios already defined under other IDs. These are discrepancies, not additional invented cases. RET-011 is assigned to both Playwright and Maestro in the detailed tool list. E2E-006 and E2E-012 explicitly require both web and mobile.

The browser report is in `playwright-report/chapter4`. Login tests disable screenshots and traces to avoid recording credentials. Other browser failures retain screenshots and traces, which can contain application data and authenticated session information; keep the report private. The automation reads the local `.env` database settings and defaults to `D:/xampp/mysql/bin/mysql.exe`; set `FIRESIGHT_MYSQL_CLIENT` if the client is elsewhere.
