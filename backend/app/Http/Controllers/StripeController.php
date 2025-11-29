<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

use Stripe\PaymentLink;
use Stripe\Checkout\Session as CheckoutSession;

class StripeController extends Controller
{
    public function createLink(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $amount      = (int) $request->input('amount', 2000); // AED fils
        $productName = $request->input('product', 'Test Product');

        // This must be a URL on YOUR domain
        // $successUrl = url('/stripe/session/') . '/{CHECKOUT_SESSION_ID}';
        $successUrl = $request->successReturnURL . '?session_id=' . '{CHECKOUT_SESSION_ID}';




        $paymentLink = PaymentLink::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'aed',
                    'unit_amount' => $amount,
                    'product_data' => ['name' => $productName],
                ],
                'quantity' => 1,
            ]],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => $successUrl],
            ],
        ]);

        return response()->json(['url' => $paymentLink->url]);

        // return response()->json([
        //     'url' => $paymentLink->url,
        // ]);
    }

    public function getSession($id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve rich details for your UI
        $session = CheckoutSession::retrieve([
            'id' => $id,
            'expand' => [
                'payment_intent.charges.data.balance_transaction',
                'line_items.data.price.product',
                'customer'
            ],
        ]);

        return response()->json($session);
    }
    public function createPaymentIntent(Request $request)
    {
        if ($request->filled("amount") && $request->filled("parking_log_id")) {
            // 100 AED in minor units (fils). AED has 2 decimals → 100.00 AED = 10000
            $amount = $request->amount * 100;



            Stripe::setApiKey(config('services.stripe.secret'));

            $intent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'aed',
                // This lets Stripe choose the best payment methods (card, etc.)
                'automatic_payment_methods' => ['enabled' => true],
                // Optional but useful:
                'metadata' => ['parking_log_id' => $request->parking_log_id],
            ]);

            return response()->json([
                'clientSecret'    => $intent->client_secret,
                'publishableKey'  => config('services.stripe.key'),
                'amount'          => $amount,
                'parking_log_id'          => $request->parking_log_id,
                'currency'        => 'AED',
            ]);
        }
    }
    public function webhook(Request $request)
    {
        $payload = @file_get_contents('php://input');
        $sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $pi = $event->data->object;
            // mark order as paid using $pi->id / $pi->metadata...
        }

        return response()->json(['received' => true]);
    }
}
