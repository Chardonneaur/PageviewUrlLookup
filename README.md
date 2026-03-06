# PageviewUrlLookup

**Look up the exact pageview count for any URL directly from the raw visit log — no archiving required.**

## Description

**PageviewUrlLookup** adds a dedicated admin page under **Administration → URL Pageview Lookup**. Select a site, a date range, a match mode, and a URL — the plugin queries `log_link_visit_action` in real time and instantly shows how many pageviews matched. Every result is automatically saved to a persistent report table so you can compare lookups over time.

### Features

- **Exact match** — matches the full stored URL (protocol prefix and leading `www.` stripped automatically, so `https://example.com/page` and `example.com/page` both work)
- **Contains match** — finds every URL whose path or domain includes your search string; LIKE metacharacters (`%`, `_`, `\`) are escaped so results are always literal
- **Timezone-aware** — start/end dates are converted to UTC using the site's configured timezone, so "yesterday" means yesterday in the site's local time
- **Date range guard** — ranges longer than 366 days are rejected to protect database performance
- **Persistent report table** — the 300 most recent lookups per site are stored and displayed below the search form
- **Admin-only** — protected by per-site admin permission checks and CSRF nonce validation

## Requirements

- Matomo >= 5.0
- PHP >= 8.1
- MySQL / MariaDB

## Installation

### From the Matomo Marketplace

1. Go to **Administration → Marketplace**.
2. Search for **PageviewUrlLookup**.
3. Click **Install** and then **Activate**.

### Manual Installation

1. Download the latest release archive from the [GitHub repository](https://github.com/Chardonneaur/PageviewUrlLookup/releases).
2. Extract it into your `matomo/plugins/` directory so that the path `matomo/plugins/PageviewUrlLookup/plugin.json` exists.
3. Go to **Administration → Plugins** and activate **PageviewUrlLookup**.

## Usage

1. Go to **Administration → URL Pageview Lookup**.
2. Select the website and date range (max 366 days).
3. Choose **Exact URL** or **Contains text** as the match type.
4. Enter the URL or substring to search.
5. Click **Run URL Lookup**.

The pageview count for the selected period appears immediately. The result is also appended to the saved report table at the bottom of the page.

**Exact mode tips:**
- The protocol (`https://`, `http://`) and leading `www.` are stripped automatically before matching, so you can paste a full browser URL directly.
- The value must match the URL exactly as Matomo stored it (including query string, if any).

**Contains mode tips:**
- Enter any substring, e.g. `/blog/` to match all blog posts, or `?ref=email` to match a specific campaign parameter.
- Wildcards are not supported; the search is always literal.

## Security

- All endpoints require site admin access
- CSRF protection via Matomo nonce on all mutating actions
- URL input limited to 2048 characters
- Date inputs validated for both format (`YYYY-MM-DD`) and calendar correctness (rejects e.g. February 30)
- LIKE metacharacters (`%`, `_`, `\`) escaped in contains mode
- All queries use parameterized statements — no SQL injection surface
- Twig auto-escaping active — no XSS surface in output

## FAQ

**Does this replace Matomo's built-in Pages report?**
No. The Pages report aggregates already-archived data. This tool queries the raw log in real time, which is useful for ad-hoc lookups before archiving runs, or for URLs that fall below the reporting threshold.

**Why is my exact-match count different from the Pages report?**
The Pages report counts unique page views within a visit; this tool counts every matching row in `log_link_visit_action`, which is a raw action count. Also confirm the date boundaries: this plugin applies the site timezone, while some Matomo reports operate on UTC.

**Can I export the saved report table?**
Not from the UI in v1.0.0. The data lives in the `matomo_pageview_url_lookup_report` database table and can be queried directly or exported via a database client.

**Is the lookup slow on large databases?**
The query joins `log_visit`, `log_link_visit_action`, and `log_action` filtered by `idsite` and `server_time`. Performance depends on the size of your log tables and the available indexes. Keep date ranges reasonably short for fast results.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full version history.

## License

GPL v3+. See [LICENSE](LICENSE) for details.

---

*This is a community plugin. It is not affiliated with or officially supported by Matomo / InnoCraft.*
