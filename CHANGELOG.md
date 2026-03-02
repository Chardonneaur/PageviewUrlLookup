# Changelog

All notable changes to PageviewUrlLookup are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-03-02

### Added
- Exact URL match and contains (substring) match against `log_link_visit_action`
- Date range picker with timezone-aware UTC conversion using the site's configured timezone
- 366-day maximum date range guard to protect database performance
- Persistent report table (`matomo_pageview_url_lookup_report`) storing the 300 most recent lookups per site
- Admin menu entry under Administration → URL Pageview Lookup
- CSRF nonce protection on all POST requests
- Per-site admin access control (requires site admin permission)
- LIKE metacharacter escaping (`%`, `_`, `\`) for safe contains-mode queries
