<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Luigel\Paymongo\Signer\DefaultSigner;

class PayMongoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $rawBody = $request->getContent();

        // ── 1. Verify signature ──────────────────────────
        $signatureHeader = $request->header(config('paymongo.signature_header_name', 'paymongo-signature'));
        $secret          = config('services.paymongo.webhook_secret');

        if (! $signatureHeader || ! $secret) {
            Log::warning('PayMongo webhook: missing signature header or secret');
            return response()->json(['error' => 'Missing signature'], 401);
        }

        if (! $this->verifySignature($rawBody, $signatureHeader, $secret)) {
            Log::warning('PayMongo webhook: invalid signature', [
                'header' => $signatureHeader,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // ── 2. Parse the event ───────────────────────────
        $payload = json_decode($rawBody, true);
        $event   = $payload['data']['attributes']['type'] ?? null;

        Log::info('PayMongo webhook received', ['event' => $event]);

        if ($event === 'checkout_session.payment.paid') {
            $sessionData = $payload['data']['attributes']['data'] ?? [];
            $sessionId   = $sessionData['id'] ?? null;
            $reference   = $sessionData['attributes']['reference_number'] ?? null;

            $order = null;
            if ($reference) {
                $order = Order::where('order_number', $reference)->first();
            }
            if (! $order && $sessionId) {
                $order = Order::where('payment_reference', $sessionId)->first();
            }

            if (! $order) {
                Log::warning('PayMongo webhook: order not found', [
                    'reference' => $reference,
                    'session'   => $sessionId,
                ]);
                return response()->json(['status' => 'ok']);
            }

            if ($order->payment_status !== 'paid') {    
                $order->update([
                    'payment_status'    => 'paid',
                    'payment_reference' => $sessionId ?: $order->payment_reference,
                    'paid_at'           => now(),
                    'payment_meta'      => $sessionData,
                ]);

                Log::info('PayMongo: order marked paid', [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                ]);

                // NOTE: we do NOT clear the customer's cart here.
                // The cart is a session-thing; clearing it from a webhook
                // context (no session) is not possible. The cart was already
                // handled when placeOrder() was called — see the "cart cleanup"
                // note at the bottom of this file.
            }
        }

        // ── 3. Always reply 200 so PayMongo stops retrying ──
        return response()->json(['status' => 'ok']);
    }

    /**
     * PayMongo sends:   t=<timestamp>,te=<test_sig>,li=<live_sig>
     * The signature is HMAC-SHA256 of "<timestamp>.<raw_body>" using the signing secret.
     */
    private function verifySignature(string $rawBody, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $chunk) {
            if (str_contains($chunk, '=')) {
                [$k, $v] = explode('=', $chunk, 2);
                $parts[trim($k)] = trim($v);
            }
        }

        $timestamp = $parts['t']  ?? '';
        $signature = $parts['te'] ?? ($parts['li'] ?? '');

        if ($timestamp === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}