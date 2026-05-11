# Freeze Policy (2026-04-19)

This workspace state is user-approved and frozen.

Rules:
- Do not change PDP module order, marketplace visual style, or key layout blocks unless user explicitly requests.
- Keep Amazon marketplace button style/color as currently stabilized.
- Keep mobile/pad PDP order as: gallery -> summary -> About this item.
- Before any future visual/structure change, create a new timestamped backup snapshot.

Snapshot:
- backups/20260419-214853/theme-freeze

Quick restore:
1. Copy snapshot files back to app/public/wp-content/themes/powerup-industrial/
2. Run: app/public/deployment/clear-fastcgi-cache.sh
