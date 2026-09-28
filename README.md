# WP Ninja Hub

Unified user dashboard that pulls data from multiple WPManageNinja plugins into a single view: Paymattic, Fluent Forms, FluentCRM, Fluent Boards, and Fluent Community.

## What it does

- Renders a dashboard via the `[wp_ninja_hub]` shortcode, showing per-product data for the logged-in user.
- Auto-detects which supported plugins are active and only shows menu entries for those.
- Exposes a small REST API under `wp-ninja-hub/v1` that the dashboard's Vue frontend consumes.
- Adds an admin page (WP Ninja Hub) with a shortcode helper and a live preview of the dashboard.

## Supported plugins

| Key               | Plugin           | Detected via                            |
| ----------------- | ---------------- | --------------------------------------- |
| `paymattic`       | Paymattic        | `wp-payment-form/wp-payment-form.php`   |
| `fluentform`      | Fluent Forms     | `fluentform/fluentform.php`             |
| `fluentcrm`       | FluentCRM        | `fluent-crm/fluent-crm.php`             |
| `fluentboard`     | Fluent Boards    | `fluent-boards/fluent-boards.php`       |
| `fluentcommunity` | Fluent Community | `fluent-community/fluent-community.php` |

Detection lives in [`src/Helpers/PluginChecker.php`](src/Helpers/PluginChecker.php).

## Usage

Add the shortcode to any page:

```text
[wp_ninja_hub]
```

Logged-out visitors see a "please log in" message instead of the dashboard.

## Frontend build

Requires Node and PHP/Composer.

```bash
composer install
npm install

npm run dev    # Vite dev server on :5173 with HMR
npm run build  # builds to assets/dist
```

In dev mode the plugin auto-detects a running Vite server (`http://localhost:5173`) and loads unbuilt assets with HMR; otherwise it loads the built files from `assets/dist`. To force dev-server asset loading, define `WPNINJA_HUB_DEV` as `true` (e.g. in `wp-config.php`).

## Requirements

- WordPress with an active user session for dashboard/API access.
- At least one of the supported WPManageNinja plugins, to have data to show.
