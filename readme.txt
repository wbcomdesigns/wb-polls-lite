=== WB Polls Lite — Community Polls for WordPress ===
Contributors: wbcomdesigns, developer27
Tags: polls, buddypress, voting, community, survey
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create polls on your WordPress site or in BuddyPress activity feeds and groups. Frontend dashboard, poll creation, guest voting, scheduling, shortcodes. The only free poll plugin with BuddyPress integration.

== Description ==

**WB Polls Lite** is the only free WordPress poll plugin with native BuddyPress and BuddyBoss activity feed integration. Create engaging polls right where your members are — in the activity stream, inside groups, or on any page of your WordPress site.

= Works 3 Ways =

1. **Activity Feed Polls** — Create polls in BuddyPress/BuddyBoss activity streams. Members vote without leaving the feed.
2. **Group Polls** — Polls inside BuddyPress groups for team decisions and community engagement.
3. **Standalone Polls** — Full WordPress custom post type with archive and single pages. Works without BuddyPress.

= Key Features (Free) =

* **Text Polls** — Multiple choice with customizable options
* **Frontend Poll Dashboard** — Members manage their polls from a dedicated frontend page
* **Frontend Poll Creation** — Let members create polls without admin access via `[wbpoll_create]` shortcode
* **[wbpoll_list] Shortcode** — Display polls anywhere with filtering by category, author, status, and pagination
* **REST API** — 8 poll endpoints for create, list, manage, and delete operations
* **AJAX Voting** — Real-time results without page reload
* **Guest Voting** — Allow visitors to vote without an account
* **Poll Scheduling** — Set start and end dates, or never-expire
* **Multi-Select** — Let voters choose multiple options
* **Result Bars** — Visual percentage bars after voting
* **Approval Workflow** — Admin review before polls go live (pending/publish status)
* **Role-Based Creation** — Restrict who can create and vote on polls by WordPress role
* **BuddyPress Activity Integration** — Polls appear as native activity posts
* **BuddyBoss Compatible** — Works with BuddyBoss Platform
* **Youzify Compatible** — Full compatibility with Youzify
* **Dark Mode** — Automatic dark mode support
* **RTL Support** — Full right-to-left language support
* **Voter Display** — Show who voted on each option
* **Custom Colors** — Match poll colors to your brand

= Why WB Polls? =

Every other poll plugin is generic — they embed forms on separate pages. WB Polls puts polls inside the activity feed where your members already spend their time. Like Facebook or LinkedIn polls, but self-hosted on your own site with your own data.

* **No other free plugin** offers BuddyPress activity feed polls
* **No ecosystem lock-in** — works with BuddyPress, BuddyBoss, or standalone
* **Frontend dashboard included** — members manage their own polls, no admin access needed
* **Same database as Pro** — upgrade anytime without migration

= Shortcodes =

* `[wbpoll id="123"]` — Display a single poll
* `[wbpoll_list count="10" columns="2" pagination="true"]` — Display a grid of polls with filtering
* `[wbpoll_create]` — Embed a frontend poll creation form on any page

= Need More? Upgrade to Pro =

[WB Polls Pro](https://wbcomdesigns.com/downloads/buddypress-polls/) adds:

* **Image Polls** — Visual options with lightbox preview
* **Video Polls** — YouTube, Vimeo, TikTok, Twitch, Dailymotion
* **Audio Polls** — Spotify, SoundCloud, Mixcloud, Bandcamp
* **HTML Polls** — Rich content with the full WordPress editor
* **Multi-Question Surveys** — Chain polls into step-by-step surveys
* **Lead Capture** — Collect emails with GDPR consent
* **Survey Analytics** — Completion rates, drop-off, time-series charts
* **CSV Export** — Download voter data and survey responses
* **WP-CLI** — Generate, reset, stats from the command line
* **User-Added Options** — Members propose new poll options
* **Re-Voting** — Allow changing votes
* **Admin Dashboard Widgets** — Poll stats and graphs at a glance

[Upgrade to Pro →](https://wbcomdesigns.com/downloads/buddypress-polls/)

= Seamless Upgrade Path =

WB Polls Lite uses the **exact same database tables and meta keys** as Pro. When you upgrade:

1. Deactivate WB Polls Lite
2. Install and activate WB Polls Pro
3. All your polls, votes, and settings are there. Zero migration.

== Installation ==

1. Upload the `wb-polls-lite` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **WB Polls > Settings** to configure

= With BuddyPress =
If BuddyPress or BuddyBoss is active, polls automatically appear in the activity "What's new" form. Members can create polls from the activity stream.

= Without BuddyPress =
Polls work as a standalone custom post type. Create them from **WB Polls > Add New** in the admin, or let members create polls from the frontend dashboard. They have their own archive and single-post pages.

= Frontend Dashboard =
On activation, two pages are created automatically: "Poll Dashboard" and "Create Poll". Members can manage their polls, create new ones, edit, pause, unpublish, or delete — all from the frontend.

== Frequently Asked Questions ==

= Does WB Polls Lite work without BuddyPress? =

Yes. WB Polls works as a fully standalone WordPress plugin with its own custom post type, archive, single-post templates, frontend dashboard, and poll creation form.

= Is it compatible with BuddyBoss Platform? =

Yes. WB Polls has explicit BuddyBoss compatibility and works with the BuddyBoss activity feed and groups.

= Can guests vote without creating an account? =

Yes. Guest voting uses cookie and IP-based tracking to prevent duplicate votes while keeping the experience frictionless.

= Can members create polls from the frontend? =

Yes. WB Polls Lite includes a frontend poll creation form and dashboard. Members can create, edit, pause, and delete polls without needing WordPress admin access.

= How do I display polls on any page? =

Use the `[wbpoll_list]` shortcode. It supports attributes like `count`, `columns`, `category`, `author`, `status`, `orderby`, `order`, and `pagination`.

= Does it work with caching plugins? =

Yes. All voting is AJAX-based, so it works correctly with Cloudflare, LiteSpeed Cache, W3 Total Cache, WP Super Cache, and others.

= Will I lose data if I upgrade to Pro? =

No. The Lite and Pro versions use the exact same database tables and post meta keys. Upgrading is seamless — deactivate Lite, activate Pro, and all your data is there.

= Can I use both Lite and Pro at the same time? =

No. Only one version should be active. If Pro is detected, Lite will show a notice and not load.

== Screenshots ==

1. Poll in BuddyPress activity feed
2. Frontend poll dashboard — members manage their polls
3. Frontend poll creation form
4. Standalone poll page with text options
5. Voting with real-time result bars
6. [wbpoll_list] shortcode grid display
7. Admin settings page
8. Poll inside a BuddyPress group

== Changelog ==

= 1.0.0 =
* Initial release
* Text polls (standalone custom post type)
* Frontend poll dashboard — members manage polls from the frontend
* Frontend poll creation form with `[wbpoll_create]` shortcode
* `[wbpoll_list]` shortcode with filtering and pagination
* Poll REST API (8 endpoints)
* Approval workflow (pending/publish status control)
* Role-based poll creation and voting restrictions
* BuddyPress activity feed polls
* BuddyBoss Platform compatibility
* Youzify compatibility
* Guest voting with anti-duplicate protection
* Poll scheduling (start/end dates)
* Multi-select voting
* AJAX voting with real-time results
* Voter avatar display
* Custom color picker
* Dark mode support
* RTL support
* Admin settings page

== Upgrade Notice ==

= 1.0.0 =
Initial release of WB Polls Lite. Full-featured community polling with frontend dashboard, poll creation, BuddyPress integration, shortcodes, and REST API.
