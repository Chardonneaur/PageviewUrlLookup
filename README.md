# PageviewUrlLookup

A Matomo admin tool that lets you look up the exact pageview count for any URL
directly from the raw visit log — no archiving required.

> **Community plugin:** This plugin is not developed or maintained by Matomo
> (InnoCraft). It is provided as-is under the GPL v3+ licence. For support,
> please use the [GitHub issue tracker](https://github.com/Chardonneaur/PageviewUrlLookup/issues).

## Description

**PageviewUrlLookup** adds a dedicated admin page under **Administration →
URL Pageview Lookup**. Select a site, a date range, a match mode, and a URL —
the plugin queries `log_link_visit_action` in real time and instantly shows how
many pageviews matched. Every result is automatically saved to a persistent
report table so you can compare lookups over time.

**Key features:**

- **Exact match** — matches the full stored URL (protocol prefix stripped
  automatically so `https://example.com/page` and `example.com/page` both work)
- **Contains match** — finds every URL whose path or domain includes your
  search string; LIKE metacharacters (`%`, `_`) are escaped so results are
  always literal
- **Timezone-aware** — start/end dates are converted to UTC using the site's
  configured timezone, so "yesterday" means yesterday in the site's local time
- **Date range guard** — ranges longer than 366 days are rejected to protect
  database performance
- **Persistent report table** — the 300 most recent lookups per site are stored
  and displayed below the search form
- **Admin-only** — protected by per-site admin permission checks and CSRF nonce
  validation

## Installation

### Via the Matomo Marketplace

Search for **PageviewUrlLookup** in **Administration → Marketplace** and click
**Install**.

### Manual installation

1. Download or clone this repository into your Matomo `plugins/` directory:

   ```bash
   cd /path/to/matomo/plugins
   git clone https://github.com/Chardonneaur/PageviewUrlLookup.git
   ```

2. Activate the plugin in **Administration → Plugins**, or run:

   ```bash
   ./console plugin:activate PageviewUrlLookup
   ```

## Usage

1. Go to **Administration → URL Pageview Lookup**.
2. Select the website and date range (max 366 days).
3. Choose **Exact URL** or **Contains text** as the match type.
4. Enter the URL or substring to search.
5. Click **Run URL Lookup**.

The pageview count for the selected period appears immediately. The result is
also appended to the saved report table at the bottom of the page.

**Exact mode tips:**
- The protocol (`https://`, `http://`) and leading `www.` are stripped
  automatically before matching, so you can paste a full browser URL directly.
- The value must match the URL exactly as Matomo stored it (including query
  string, if any).

**Contains mode tips:**
- Enter any substring, e.g. `/blog/` to match all blog posts, or `?ref=email`
  to match a specific campaign parameter.
- Wildcards are not supported; the search is always literal.

## Requirements

- Matomo **5.x** (tested on 5.0+)
- PHP 8.0+
- MySQL / MariaDB

## FAQ

**Does this replace Matomo's built-in Pages report?**
No. The Pages report aggregates already-archived data. This tool queries the
raw log in real time, which is useful for ad-hoc lookups before archiving
runs, or for URLs that fall below the reporting threshold.

**Why is my exact-match count different from the Pages report?**
The Pages report counts unique page views within a visit; this tool counts
every matching row in `log_link_visit_action`, which is a raw action count.
Also confirm the date boundaries: this plugin applies the site timezone, while
some Matomo reports operate on UTC.

**Can I export the saved report table?**
Not from the UI in v1.0.0. The data lives in the
`matomo_pageview_url_lookup_report` database table and can be queried directly
or exported via a database client.

**Is the lookup slow on large databases?**
The query joins `log_visit`, `log_link_visit_action`, and `log_action` filtered
by `idsite` and `server_time`. Performance depends on the size of your log
tables and the available indexes. Keep date ranges reasonably short for fast
results.

## Changelog

### 1.0.0 — 2026-03-02

- Initial release
- Exact and contains URL matching against `log_link_visit_action`
- Timezone-aware UTC date conversion
- 366-day date range cap
- Persistent report table (300 rows per site)
- CSRF nonce protection
- Per-site admin access control

## Support

- **Bug reports & feature requests:** [GitHub Issues](https://github.com/Chardonneaur/PageviewUrlLookup/issues)
- **Source code:** [GitHub](https://github.com/Chardonneaur/PageviewUrlLookup)
