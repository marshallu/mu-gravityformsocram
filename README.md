# mu-gravityformsocram

A Gravity Forms feed add-on for Marshall University's WordPress network that posts form submissions to [Ocram](https://www.ocram.io) as kanban board cards.

## Requirements

- PHP >= 8.3
- WordPress (MU plugin)
- Gravity Forms
- An Ocram board with a webhook token configured

## How it works

Install as a must-use plugin. For each form, go to **Settings → Ocram** and add a feed:

1. **Board Webhook Token** — paste the token from your Ocram board settings
2. **Card Title** — map a form field to use as the card title
3. **Card Description** — choose between an auto-generated summary of all submitted fields, a specific field, or none
4. New cards are placed in the "What's New" column, or the first column if that doesn't exist

## Development

```bash
composer install       # install dev dependencies
composer lint          # run PHP CodeSniffer
composer format        # auto-fix coding standards
composer analyse       # run PHPStan static analysis
```

## Ocram Webhook API

Cards are created via `POST https://www.ocram.io/api/webhooks/boards/{token}/cards`.

| Field | Required | Description |
|---|---|---|
| `title` | Yes | Card title (truncated to 255 chars) |
| `description` | No | Card body — accepts HTML or plain text |

See `webhooks.md` for the full API reference.
