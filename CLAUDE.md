# WB Polls Lite

Free version of WB Polls — community polls for WordPress and BuddyPress.

## Structure

```
wb-polls-lite.php          # Main plugin file (entry point)
includes/                  # Core classes (loader, i18n, poll helper, activator/deactivator)
admin/                     # Admin-facing code (settings, poll management, assets)
public/                    # Frontend code (poll display, voting, dashboard, assets)
restapi/v1/                # REST API endpoints for AJAX poll operations
template/                  # Single poll template
languages/                 # Translation files
```

## Testing Locally

1. Copy or symlink into any WordPress `wp-content/plugins/` directory
2. Activate "WB Polls Lite" from WP Admin > Plugins
3. Create a poll at WP Admin > WB Polls > Add New
4. View the frontend dashboard via the `[wbcom_polls_dashboard]` shortcode

## WordPress.org SVN Deploy

Tags matching `v*` trigger `.github/workflows/wp-org-deploy.yml` which deploys to WordPress.org SVN using `10up/action-wordpress-plugin-deploy`.

Required GitHub secrets: `SVN_USERNAME`, `SVN_PASSWORD`

## Relationship to Pro

- Same database schema and post type (`wbcom_poll`)
- Same text domain (`buddypress-polls`)
- Pro adds: image/video/audio polls, surveys, CSV export, WP-CLI, EDD licensing
- Pro detects Lite on activation and deactivates it (and vice versa)
- Codebases are maintained separately — changes here do NOT auto-sync to Pro

## Git Commits

No co-author attribution in commits.
