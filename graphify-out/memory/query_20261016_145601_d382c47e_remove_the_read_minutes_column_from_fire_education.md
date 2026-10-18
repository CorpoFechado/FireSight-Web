---
type: "query"
date: "2026-10-16T14:56:01.089376+00:00"
question: "Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend."
contributor: "graphify"
outcome: "useful"
source_nodes: ["Announcement", "AnnouncementController.php", "announcements/index.tsx", "fire-education/index.tsx"]
---

# Q: Remove the read_minutes column from fire_education_content and announcement_type from announcement, then fix the frontend.

## Answer

Expanded from the original request via graph vocabulary: announcement, announcements, content, education, fire, minutes, read. The graph identified AnnouncementController, Announcement, announcements/index.tsx, announcement-form-modal.tsx, fire-education/index.tsx, fire-education-form-modal.tsx, and fire-education-view-modal.tsx as the main affected paths. The implementation removed both database columns through reversible migrations and removed their backend, seed, test, and frontend dependencies.

## Outcome

- Signal: useful

## Source Nodes

- Announcement
- AnnouncementController.php
- announcements/index.tsx
- fire-education/index.tsx