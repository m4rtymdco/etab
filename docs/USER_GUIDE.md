# User guide

## Roles

| Role | Access |
|------|--------|
| Admin | Events, contestants, judges, criteria, results, analytics |
| Judge | Assigned events only; submit and review own scores; profile |

## Admin flow

1. Sign in → **Dashboard** (event counts, scoring progress, recent scores).
2. **Create event** → set date, venue, status, score range, rounds, optional drop high/low.
3. **Criteria** on the event page: add lines until weights equal 100%. Save as a template or apply an existing one.
4. **Contestants**: add individually (name, Exclusive or Open category, photo, events) or import CSV. Archive instead of delete when you want history kept.
5. **Judges**: create accounts, assign to events, review average scores given.
6. Monitor **Results**. Exclusive and Open are ranked separately. The total is a percentage; each contestant’s judge scores are listed underneath.
7. **Publish results** so judges can open the live board. Until then, standings stay hidden from judges.
8. Export **CSV** / **Excel**, **Print** (browser Save as PDF), or **Certificates** for ranks 1–3 in each category.

## Judge flow

1. Sign in → dashboard lists assigned events and how many sheets are done.
2. Open an event → pick a contestant.
3. Enter a score for every criterion (decimals allowed, e.g. `85.5`). The **live total** is the weighted sum.
4. Drafts save automatically while you type.
5. Check **I confirm these scores are final** and submit. After that the sheet is read-only.
6. **My scores** lists only your submissions. Other judges’ numbers are never shown.
7. **Profile** updates name, phone, bio, and password.

## Live standings

Open **Live board** from Results (admin) or from the judge dashboard after publish. The table refreshes every few seconds. Top three rows are highlighted.

## CSV import format

Header row, then: `name,category,status,notes,event_id,entry_number`

`category` is `Exclusive` or `Open`. `status` is `active` or `archived`. If you pick a default event on the import form, that event is used when `event_id` is empty.
