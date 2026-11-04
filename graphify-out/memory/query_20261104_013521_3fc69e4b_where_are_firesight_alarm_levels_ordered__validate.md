---
type: "code"
date: "2026-11-04T01:35:21.071750+00:00"
question: "Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?"
contributor: "graphify"
outcome: "useful"
source_nodes: ["AlarmLevel", "ALARM_LEVEL_CFG", "AnalyticsController", "FireMapController"]
---

# Q: Where are FireSight alarm levels ordered, validated, colored, filtered, and escalated?

## Answer

AlarmLevel is the backend order and validation source; ALARM_LEVEL_CFG is the frontend order, badge, and marker source. Analytics and dashboard derive from the enum, and map filters validate against AlarmLevel::values(). No numeric or next-level escalation implementation exists. Verification was added first and remains noncritical.

## Outcome

- Signal: useful

## Source Nodes

- AlarmLevel
- ALARM_LEVEL_CFG
- AnalyticsController
- FireMapController