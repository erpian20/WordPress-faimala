# Local-first release workflow

This store uses `http://powerup.local/` as its preview environment and
`https://faimala.com/` as its production environment.

## Required workflow

1. Make changes locally only.
2. Review the affected pages at `http://powerup.local/` on desktop and mobile.
3. Wait for the store owner to explicitly approve production release with
   `确认发布`.
4. After approval, commit and push the reviewed files to GitHub `main`.
5. Allow the existing GitHub Actions workflow to deploy the approved files to
   Cloudways.
6. Verify the affected production pages and the GitHub Actions result.

## Release gate

- Do not push to GitHub before explicit approval.
- Do not upload files directly to Cloudways during routine work.
- Emergency production changes still require explicit authorization.
- Keep each production release as a separate Git commit so it can be reverted.

## Deployment boundaries

The automated Cloudways deployment publishes tracked changes under:

- `app/public/wp-content/themes/powerup-industrial/`
- `app/public/wp-content/mu-plugins/`
- `app/public/wp-content/uploads/`

Local backups, source exports, logs, and database dumps must remain untracked.
