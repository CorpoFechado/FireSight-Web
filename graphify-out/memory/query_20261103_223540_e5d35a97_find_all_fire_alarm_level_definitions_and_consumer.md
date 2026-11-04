---
type: "query"
date: "2026-11-03T22:35:40.277438+00:00"
question: "Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them."
contributor: "graphify"
outcome: "useful"
source_nodes: ["AlarmLevel", "ALARM_LEVEL_CFG", "ALARM_LEVEL_MARKER_COLORS", "AnalyticsController"]
---

# Q: Find all Fire Alarm level definitions and consumers, and identify missing BFP Running Card System levels before adding them.

## Answer

Expanded from original query via graph vocab: [alarm, level, levels, color, colors, enum, badge, marker, legend, dashboard, incident, status]. Verified in source: AlarmLevel has 13 cases from 1st Alarm through General Alarm, including all seven Task Force stages. Verification, Fire Under Control, and Fire Out are absent from AlarmLevel, the TypeScript AlarmLevel union and color maps, the afor_report enum, the incident_record enum/check constraint, and controller whitelists. Fire Under Control and Fire Out exist only as AFOR timestamps; Verification exists as report workflow terminology. Existing color duplication is in AnalyticsController::ALARM_LEVEL_COLORS and resources/js/lib/fire-status.ts ALARM_LEVEL_CFG plus ALARM_LEVEL_MARKER_COLORS.

## Outcome

- Signal: useful

## Source Nodes

- AlarmLevel
- ALARM_LEVEL_CFG
- ALARM_LEVEL_MARKER_COLORS
- AnalyticsController