---
type: "query"
date: "2026-10-15T02:35:46.800292+00:00"
question: "Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button."
contributor: "graphify"
outcome: "useful"
source_nodes: ["Welcome()", "Login()", "welcome.tsx", "login.tsx", "portal-layout.tsx", "risk-analytics/index.tsx", "notifications/index.tsx", "personnel/index.tsx"]
---

# Q: Create a prompt to implement the FireSight landing page based on the attached design, without arrows in Key Features and Built for Everyone, recommend essential system features, disable downloads while mobile is under development, and add an admin portal login button.

## Answer

Expanded from original query via graph vocab: [welcome, landing, login, authentication, routes, portal, features, incidents, reports, risk, notifications, personnel]. Produced a repository-tailored implementation prompt for the existing Inertia React welcome page. It directs the implementer to preserve the reference layout, add an Admin Portal login CTA through the existing login route, render download actions as accessible disabled Coming Soon controls, omit arrows specifically from Key Features and Built for Everyone, feature AI-assisted detection, live incident mapping, alerts, response coordination, risk analytics, and preparedness content, reuse project logo/UI assets, use Wayfinder and existing conventions, and add/run focused Pest tests plus frontend checks.

## Outcome

- Signal: useful

## Source Nodes

- Welcome()
- Login()
- welcome.tsx
- login.tsx
- portal-layout.tsx
- risk-analytics/index.tsx
- notifications/index.tsx
- personnel/index.tsx