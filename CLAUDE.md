# MU Gravity Forms Ocram

A Gravity Forms feed add-on that posts form submissions to [Ocram](https://www.ocram.io) as kanban board cards.

- **Package**: `marshallu/mu-gravityformsocram`
- **Type**: WordPress MU (must-use) plugin
- **Author**: Christopher McComas
- **Requires PHP**: >= 8.3

## Development Commands

```bash
# Install dependencies
composer install

# Run PHP CodeSniffer (lint)
composer lint

# Auto-fix coding standards violations
composer format

# Run PHPStan static analysis
composer analyse
```

## WordPress Coding Standards

This project uses [WordPress Coding Standards (WPCS)](https://github.com/WordPress/WordPress-Coding-Standards) enforced via PHP_CodeSniffer (`phpcs.xml`). All code must pass WPCS linting before being considered complete. PHPStan static analysis (`phpstan.neon`) is also required.

### Key rules to follow

- Use tabs for indentation, not spaces
- Use single quotes for strings unless interpolation is needed
- Prefix all functions, classes, hooks, and globals with `mu_gravityformsocram_` to avoid namespace collisions
- Use `snake_case` for functions and variables; `PascalCase` for class names
- Always sanitize input (`sanitize_text_field()`, `absint()`, etc.) and escape output (`esc_html()`, `esc_url()`, `esc_attr()`, etc.)
- Hook into WordPress actions/filters rather than executing logic at the top level of files
- Never use `extract()`, `eval()`, or short PHP open tags (`<?`)

### GFFeedAddOn conventions

- GFFeedAddOn requires underscore-prefixed properties (`$_version`, `$_slug`, etc.) — suppress the PHPCS warning with a `phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore` block
- GF framework properties use camelCase (e.g. `$field->displayOnly`) — suppress per-line with `// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase`
- Bootstrap via `GFForms::include_feed_addon_framework()` called inside the `gform_loaded` action (priority 5), then `GFAddOn::register()`
- `process_feed()` must return `true` on success or `false` on failure so GF records feed status on the entry
- Use `$this->get_field_value( $form, $entry, $field_id )` (not `rgar()`) to retrieve mapped field values
- Use `rgars( $feed, 'meta/key' )` to access feed settings inside `process_feed()`
- `get_menu_icon()` should return a raw SVG string — GF embeds it inline in the form settings menu
- `feed_list_columns()` must be `public` (GFFeedAddOn declares it public)

### File naming

- PHP files: `class-{name}.php` for class files, `{name}.php` for functional files (all lowercase, hyphen-separated)
- The main plugin file is `mu-gravityformsocram.php`

## Project Structure

```
mu-gravityformsocram/
├── mu-gravityformsocram.php              # Plugin entry point — defines constant, bootstraps main class
├── includes/
│   ├── class-mu-gravityformsocram.php       # Main class — hooks gform_loaded, calls include_feed_addon_framework, registers add-on
│   └── class-mu-gravityformsocram-feed.php  # GFFeedAddOn subclass — feed settings, process_feed, icon
├── phpcs.xml                             # PHPCS config (WordPress standard)
├── phpstan.neon                          # PHPStan config
├── composer.json
└── vendor/                               # Composer-managed (not committed)
```

## Ocram Webhook API

Cards are created via a `POST` to:

```
https://www.ocram.io/api/webhooks/boards/{token}/cards
```

The token identifies and authenticates the board — no additional headers required.

| Field | Required | Description |
|---|---|---|
| `title` | Yes | Card title. Truncated to 255 chars. |
| `description` | No | Card body. Accepts HTML or plain text. |

New cards are always placed in the "What's New" column, or the first column by position if that doesn't exist. Response `201` with `{ "card_number": N }` on success.

See `webhooks.md` for the full API reference.

## WordPress Best Practices

- **Use WordPress APIs** over native PHP equivalents (e.g. `wp_remote_post()` over `curl`, `wp_json_encode()` over `json_encode()`)
- **Late escaping** — escape as close to output as possible, not at input
- **`WP_DEBUG` compatibility** — code must run cleanly with `WP_DEBUG` and `WP_DEBUG_LOG` enabled
- **Avoid hardcoded URLs** — use `home_url()`, `admin_url()`, `plugins_url()`, etc.
