<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MockPaymentServer
{
    // public function handleRequest(Request $request)
    public function handleRequest()
    {
        // Simulate a payment processing delay
        sleep(2);       

        // Payment success
        return new Response('Payment successful', 200);

        // Payment error
        // return new Response('Payment failed', 402);
    }
}