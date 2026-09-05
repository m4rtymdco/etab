# eTab HTTP API

All JSON endpoints require an authenticated session cookie (`etab_session`) from a browser login. Send `Accept: application/json`. Mutating JSON calls also need header `X-CSRF-TOKEN` matching the page token.

Base URL: `http://localhost/eTab` (or your `app_url`).

## GET /api/events/{id}/standings

Live rankings for one event.

**Query:** `round` (default `1`)

**Auth:** Admin always. Judge only if `results_published` is true.

**200**

```json
{
  "event": { "id": 1, "name": "Grand Talent Showcase 2026", "published": true },
  "round": 1,
  "updated_at": "2026-09-04T13:00:00+08:00",
  "groups": [
    {
      "key": "exclusive",
      "label": "Exclusive",
      "rows": [
        {
          "id": 1,
          "name": "Ava Santos",
          "category": "Exclusive",
          "division": "exclusive",
          "rank": 1,
          "average": 88.5,
          "judge_count": 3,
          "breakdown": [{ "criteria_id": 1, "name": "Technique", "avg": 90.0 }]
        }
      ]
    }
  ],
  "rows": []
}
```

**403** `{ "error": "Not published" }`  
**404** `{ "error": "Not found" }`

## POST /judge/events/{id}/contestants/{cid}/draft

Auto-save in-progress scores. Body JSON:

```json
{ "round": 1, "scores": { "1": "85.5", "2": "90" }, "comments": "Strong opening" }
```

**200** `{ "ok": true, "saved_at": "..." }`  
**409** already submitted.

## HTML routes (session)

| Method | Path | Role |
|--------|------|------|
| GET/POST | `/login` `/logout` `/forgot-password` `/reset-password` | Public |
| GET | `/admin` `/admin/events` `/admin/contestants` `/admin/judges` `/admin/results` `/admin/analytics` | Admin |
| POST | `/admin/events` `/admin/contestants` `/admin/judges` | Admin |
| GET | `/judge` `/judge/events/{id}` `/judge/scores` `/judge/profile` | Judge |
| POST | `/judge/events/{id}/contestants/{cid}` | Judge |
| GET | `/events/{id}/live` | Auth + published (or admin) |
| GET | `/admin/results/export.csv` `/admin/results/export.xls` | Admin |

Pretty URLs need Apache rewrite. Fallback: `/index.php?r=admin/results`.
