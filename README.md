# TixSwap

A ticket resale marketplace for Kenya. Fans sell tickets they can't use; buyers pay with M-Pesa; TixSwap holds the money in escrow until after the event.

- **Buyers** browse events, pay with an M-Pesa STK prompt, get a QR ticket (works offline once opened), can report a problem before the seller is paid, and rate the seller afterwards.
- **Sellers** list tickets with a proof of purchase and face value, see their sales and escrow, and are paid to M-Pesa after the event.
- **Admins** review listings (with duplicate and AI checks), resolve disputes, send payouts and refunds, and see analytics and an audit log.
- **Gate staff** scan QR codes at the entrance; each ticket gets in once.

Built with Laravel 12 (PHP 8.2+), Blade, Tailwind CSS and Alpine.js.

## Getting started

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Sign up on the site, then make yourself an admin (admins can't be created from the website):

```bash
php artisan tixswap:make-admin you@example.com
```

Out of the box, payments, payouts, SMS and the AI check run in **safe test modes**: nothing is charged or sent. See [Going live](#going-live).

### Run the tests

```bash
./vendor/bin/phpunit
```

CI (`.github/workflows/tests.yml`) runs the suite and the asset build on every push.

## How money moves

1. A buyer taps **Pay**. The ticket is reserved for 10 minutes and an M-Pesa prompt goes to their phone. TixSwap never asks for an M-Pesa PIN.
2. Safaricom confirms the payment (callback, or TixSwap asks Safaricom directly). The ticket is sold and the money is held in **escrow**.
3. 24 hours after the event (72 hours for new sellers), escrow is released into a **payout** for the seller, minus the TixSwap fee.
4. If the buyer reports a problem first, escrow is frozen until an admin refunds the buyer or releases the money.

A payment that arrives after the ticket was sold to someone else is never lost: the order is flagged for a refund.

## Configuration

All settings live in `.env` (see `.env.example` for the full list).

| Setting | Default | What it does |
|---|---|---|
| `MPESA_DRIVER` | `fake` | `fake` approves every payment instantly (refused in production); `daraja` uses Safaricom |
| `MPESA_ENV` | `sandbox` | `sandbox` or `production` |
| `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`, `MPESA_SHORTCODE`, `MPESA_PASSKEY` | | From the [Daraja portal](https://developer.safaricom.co.ke) |
| `MPESA_CALLBACK_SECRET` | | Long random string; part of the callback URL Safaricom calls |
| `MPESA_CALLBACK_BASE_URL` | `APP_URL` | Public HTTPS address Safaricom can reach |
| `MPESA_B2C_ENABLED` | `false` | Automatic seller payouts via M-Pesa B2C (needs separate Safaricom approval) |
| `MPESA_B2C_AUTO` | `false` | Send payouts automatically after escrow release (otherwise an admin clicks Send) |
| `MPESA_B2C_SHORTCODE`, `MPESA_B2C_INITIATOR_NAME`, `MPESA_B2C_SECURITY_CREDENTIAL` | | B2C credentials from Daraja |
| `SMS_DRIVER` | `log` | `log` writes SMS to the log; `africastalking` sends them; empty = off |
| `AFRICASTALKING_USERNAME`, `AFRICASTALKING_API_KEY`, `AFRICASTALKING_SENDER_ID` | | From [Africa's Talking](https://africastalking.com) |
| `AI_PROOF_DRIVER` | `none` | `claude` reads proofs of purchase with the Anthropic API; `fake` for testing |
| `AI_PROOF_MODEL` | `claude-opus-5-5` | Model used for the proof check |
| `ANTHROPIC_API_KEY` | | Needed when `AI_PROOF_DRIVER=claude` |
| `TIXSWAP_COMMISSION_PERCENT` | `10` | Fee taken from the seller's price |
| `TIXSWAP_MAX_MARKUP_PERCENT` | `20` | Highest resale price above face value (empty = no cap) |
| `TIXSWAP_RESERVATION_MINUTES` | `10` | How long a ticket is held while the buyer pays |
| `TIXSWAP_ESCROW_RELEASE_HOURS` | `24` | Hours after the event before the seller is paid |
| `TIXSWAP_TRUSTED_SELLER_SALES` | `3` | Completed sales before a seller stops being "new" |
| `TIXSWAP_NEW_SELLER_EXTRA_HOLD_HOURS` | `48` | Extra escrow time for new sellers |

Email uses the normal Laravel `MAIL_*` settings.

## The scheduler

Some work runs on a schedule. Add this cron entry on the server:

```
* * * * * cd /path/to/tixswap && php artisan schedule:run >> /dev/null 2>&1
```

| Command | When | What |
|---|---|---|
| `tixswap:expire-reservations` | every minute | Releases tickets nobody paid for in time |
| `tixswap:release-escrow` | hourly | Turns escrow into seller payouts after events |
| `tixswap:send-payouts` | hourly, only if `MPESA_B2C_AUTO` | Sends payouts via M-Pesa B2C |

Other commands: `tixswap:make-admin {email}`, `tixswap:hash-proofs` (fingerprint older listings for duplicate detection), `tixswap:check-proofs` (run the AI check on listings waiting for review).

## Going live

1. **M-Pesa payments:** get a paybill or till with Lipa na M-Pesa Online, set `MPESA_DRIVER=daraja`, `MPESA_ENV=production`, the Daraja keys and `MPESA_CALLBACK_SECRET`. Test end to end in the sandbox first.
2. **Scheduler:** add the cron entry above.
3. **Email:** configure `MAIL_*` and a real `MAIL_FROM_ADDRESS`; create the `support@tixswap.com` inbox the site links to.
4. **Optional:** B2C payouts (after Safaricom approval), Africa's Talking SMS, the AI proof check.
5. **Legal:** have a lawyer review the Terms and Privacy Policy pages.

## Project layout

- `app/Services/CheckoutService.php`: reserve, pay, escrow, expiry
- `app/Services/PayoutService.php`: M-Pesa B2C payouts
- `app/Services/DisputeService.php`: buyer disputes
- `app/Services/Mpesa/`: Daraja client and fake driver
- `app/Services/ProofCheck/`: AI proof-of-purchase reading and comparison
- `app/Notifications/`: email, in-app and SMS notifications
- `resources/views/components/ui/`: the design-system components (button, card, badge, field, stat…)
- `public/sw.js`: service worker for offline tickets
