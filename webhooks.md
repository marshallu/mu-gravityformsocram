# Webhooks

## Board Card Creation

Create a kanban card on a board from an external source (e.g. a Gravity Forms WordPress plugin).

### Endpoint

```
POST /api/webhooks/boards/{token}/cards
```

The `{token}` in the URL both identifies and authenticates the board — no additional headers required. Each board has its own unique token stored in `boards.webhook_token`.

### Generating a Token

Tokens are not auto-generated. To enable the webhook for a board, set `webhook_token` to a random 40-character string (e.g. `Str::random(40)`). The board settings UI should eventually expose a "Generate Token" button and display the full webhook URL.

### Request

Content-Type can be `application/json` or `application/x-www-form-urlencoded`.

| Field | Required | Description |
|---|---|---|
| `title` | Yes | Card title. Truncated to 255 characters. |
| `description` | No | Card body. Accepts HTML or plain text. |

All new cards are placed in the "What's New" column, or the first column by position if "What's New" doesn't exist.

#### Example (JSON)

```json
{
  "title": "New form submission: Contact Us",
  "description": "<p>Name: Jane Smith</p><p>Email: jane@example.com</p>"
}
```

### Response

| Status | Body | Meaning |
|---|---|---|
| `201` | `{ "card_number": 42 }` | Card created successfully |
| `401` | `{ "error": "Invalid token." }` | Token not found |
| `422` | `{ "error": "title is required." }` | Missing title |
| `422` | `{ "error": "Board has no columns." }` | Board exists but has no columns |

### Implementation

- **Route:** `routes/api.php`
- **Controller:** `app/Http/Controllers/WebhookController.php`
- **Migration:** `database/migrations/2026_03_31_171708_add_webhook_token_to_boards_table.php`

### Gravity Forms WordPress Plugin

POST to the endpoint from a GF feed. Map form fields to `title`, `description`, and optionally `column`. The token comes from the board settings page once that UI is built.

```
https://www.ocram.io/api/webhooks/boards/{token}/cards
```
