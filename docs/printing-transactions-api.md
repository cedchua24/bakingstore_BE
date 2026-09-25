# Printing transactions API

Apply the schema with `php artisan migrate`. The coordinator and comment author reference `users.id`; the shop order references `shop_order_transaction.id`. Deleting a printing transaction also deletes its comments. Referenced users and shop orders cannot be deleted while these records reference them.

These routes follow the existing transaction APIs' access convention (no route-level authentication middleware).

| Method | Endpoint | Action |
| --- | --- | --- |
| GET | `/api/printingTransaction` | List newest first, joining coordinator to users and returning `order_coordinator_name` |
| GET | `/api/printingTransaction/fetchByShopOrderTransactionId/{id}` | List printing transactions for a shop order, newest first, with coordinator and comments with authors; returns `[]` when none match |
| POST | `/api/printingTransaction` | Create |
| GET | `/api/printingTransaction/{id}` | Full detail including customer, shop transaction, all order items with products/categories, coordinator, and comments with authors |
| PUT/PATCH | `/api/printingTransaction/{id}` | Update supplied fields |
| DELETE | `/api/printingTransaction/{id}` | Delete transaction and comments |
| GET | `/api/printingTransactionComment` | List comments, oldest first |
| POST | `/api/printingTransactionComment` | Create comment |
| GET | `/api/printingTransactionComment/{id}` | Get comment with author |
| PUT/PATCH | `/api/printingTransactionComment/{id}` | Update supplied fields |
| DELETE | `/api/printingTransactionComment/{id}` | Delete comment |

Transaction list accepts exact-match query filters: `shop_order_transaction_id`, `order_coordinator_id`, `order_status`, `mock_up_status`, `order_priority`, `sales_channel`, and `order_date`. It includes coordinator details and `comments_count`. Comment list accepts `printing_transaction_id`.

Create a transaction:

For the edit screen, call `GET /api/printingTransaction/{id}` using the **printing transaction ID**. Printing fields are at the root; related data is nested as `shop_order_transaction.customer` and `shop_order_transaction.shop_orders[].product.category`. All columns from the customer, shop transaction, shop order, product, and category records are included. All linked order items are returned, including non-printing products. Missing relationships return null (or an empty array for order items). A missing printing transaction returns 404. Save printing fields with the existing `PATCH /api/printingTransaction/{id}` endpoint; it does not update nested records.

The list also includes `customer_name` (first and last name) from `shop_order_transaction.requestor → customer.id`, `tags`, and `shop_order_total_price` summed over shop order items whose product category has `tags = 'printing'`. Each printing transaction appears once. If no printing items match, `tags` is null and the total is 0. If the linked customer is missing, `customer_name` is null.

`logo` is optional and defaults to `PENDING` when omitted on creation. It accepts `PENDING`, `OLD`, `NEW`, or explicit `null`.

```json
{
  "shop_order_transaction_id": 1,
  "order_coordinator_id": 1,
  "logo": "NEW",
  "sales_channel": "FACEBOOK",
  "order_date": "2026-09-21"
}
```

Use existing database IDs. Optional fields: `plate` (boolean, default false), `mock_up_status` (PENDING/APPROVED/REJECTED, default PENDING), `order_priority` (NORMAL/RUSH, default NORMAL), `order_status` (PENDING/COMPLETED, default PENDING), `sent_date` and `received_date` (nullable YYYY-MM-DD). `logo` accepts PENDING/OLD/NEW or null; `sales_channel` accepts FACEBOOK/VIBER. All dates use YYYY-MM-DD. Updates retain omitted fields; send null to clear optional dates.

Create a comment:

```json
{
  "printing_transaction_id": 1,
  "user_id": 1,
  "comment": "Please review the new logo."
}
```

Responses are JSON objects for individual records and arrays for lists. Creation returns 201, reads/updates 200, deletion 204, invalid input 422 with field errors, and missing records 404.

## Update page: Save Changes

The detail response also includes root-level `payment_history`, `total_payment`, and `balance`, using the same shared query and calculation as `fetchPaymentTypeByShopTransactionIdV2`. History contains payment/account/bank/payment-term details and timestamps. Totals cover the full linked shop order transaction, not just printing products; `total_payment` sums all payment amounts (including entries regardless of `is_paid`, matching V2), and `balance` is the shop transaction total minus that sum. Without payments, history is empty and total payment is 0.

Send `PUT /api/printingTransaction/6` (or PATCH), using the printing transaction ID from the page. Set `Content-Type: application/json` and `Accept: application/json`.

```json
{
  "logo": "PENDING",
  "mock_up_status": "PENDING",
  "sales_channel": "FACEBOOK",
  "order_priority": "NORMAL",
  "order_status": "PENDING",
  "order_date": "2026-09-21",
  "sent_date": null,
  "received_date": null,
  "plate": false
}
```

Send dates as YYYY-MM-DD regardless of their display format. Send null for cleared optional dates and true/false for the plate checkbox. The response is the saved printing transaction with coordinator details (HTTP 200). Omitted fields keep their values; shop order and coordinator IDs do not need to be resent. Validation failures return HTTP 422 with an `errors` object keyed by field name.
