# Queues, Jobs, Events & Schedule

## Queue configuration

| Setting | `.env.example` default | Notes |
|---------|------------------------|-------|
| `QUEUE_CONNECTION` | `sync` | Jobs run inline during the request |
| Production recommendation | `database` or `redis` | Run `php artisan queue:work` under Supervisor |

Failed jobs table migration exists (`failed_jobs`). `jobs` table migration present for database driver.

---

## Jobs

| Job | Purpose | Retry notes |
|-----|---------|-------------|
| `SyncProductJob` | Upsert Shopify product into `dainely_products` | ShouldQueue when queue async |
| `SyncOrderToShopifyJob` | Push paid order to Shopify | Order sync path |
| `UpdateSearchIndexJob` | Rebuild `search_index` row for an entity | Triggered from model hooks / SearchService |
| `ProcessWebhookJob` | Process Judge / video-ai / wallpass | Marks webhook log processed or retry |
| `RetryFailedWebhooksJob` | Re-queue retryable `webhook_logs` | Scheduled every 5 minutes |
| `SendGa4MeasurementProtocolJob` | Server GA4 hit | Needs `GA4_*` |
| `SendMetaConversionJob` | Meta CAPI hit | Needs `META_*` |

Implementations live in `app/Jobs/`.

---

## Events & listeners

Registered in `app/Providers/EventServiceProvider.php`:

| Event | Listener |
|-------|----------|
| `AnalyticsEventOccurred` | `DispatchAnalyticsExportJobs` → GA4 + Meta jobs |
| `Registered` (Laravel) | Email verification (scaffold) |

`shouldDiscoverEvents()` is `false` — register new listeners explicitly.

---

## Queue names

No custom named queues are required by default (default queue). If you introduce priorities later, document them here.

---

## Scheduled tasks

`app/Console/Kernel.php`:

| Schedule | Task |
|----------|------|
| Hourly | `reviews:warm-cache` |
| Every 5 minutes | `RetryFailedWebhooksJob` |

Cron (production):

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

---

## Useful related Artisan commands

| Command | Role |
|---------|------|
| `shopify:sync-catalog` | Bulk catalog sync |
| `shopify:health` | Connectivity check |
| `shopify:sync-smoke` | Smoke test |
| `reviews:warm-cache` | Prime Judge.me cache |
| `search:verify` | Search health / reindex |
| `analytics:verify` | Analytics pipeline check |
| `webhooks:verify` | Webhook route/signature checks |
| `supabase:diagnose` | PDO / connection diagnostics |
| `admin:verify`, `bundles:verify`, `related:verify`, `landing:verify` | Feature verification helpers |

---

## Operational tips

1. With `sync`, a slow external API blocks the HTTP request — prefer async queues for webhooks and analytics in production.
2. Webhook retries depend on schedule + `webhook_logs` retry columns.
3. Search freshness depends on model `booted()` hooks dispatching `UpdateSearchIndexJob`.
