<?php

/**
 *  API KEY from tatum.io and binance.com
 *  TATUM connects to the Blockchain Nodes
 *  while Binance serves as the endpoint for Binance
 */

return [
    'tatum'=>[
        'isLive'=>1,
        'url'=>'https://api-eu1.tatum.io/',
        'testKey'=>'71afe6a1-e12a-4a0b-90dc-2069337eb7fc',
        'liveKey'=>'79344f6d-1ffa-4972-80f5-75861d1fd344'
    ],
    'binance'=>[
        'url'=>'',
        'testKey'=>'',
        'liveKey'=>'',
        'isLive'=>2
    ],
    'termii'=>[
        'url'=>'https://api.ng.termii.com/',
        'apiKey'=>'TLfK9042finW1GboCFPRlwLfd2rAIKLNIkJ1J8RAVYx8LcvBW99grBY8w4YIbV',
        'secKey'=>'tsk_my0m6345554720d8394876zeel',
        'isLive'=>'1'
    ],
    'oneLiquidity'=>[
        'id'=>'52876a70-bd7e-4f6f-b84f-90eb219cd370',
        'token'=>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpbnRlZ3JhdG9yUGsiOiI1Mjg3NmE3MC1iZDdlLTRmNmYtYjg0Zi05MGViMjE5Y2QzNzAiLCJzZXNzaW9uSWQiOiIwZjA5NDFlYy1jMjI0LTQ0ODMtYjNiYS1mODQ0NDU1ZGI4NjUiLCJhZG1pbiI6ZmFsc2UsInN0YWdlIjoic3RhZ2luZyIsInByb2dyYW1tYXRpYyI6dHJ1ZSwiaWF0IjoxNjg0Nzc0MTI0fQ.u9dyKw498-XtYn3oCDEEOYMQ3AYwW_UHTa_dqyivzMA',
        'isLive'=>2,
        'liveUrl'=>'https://api.oneliquidity.technology/',
        'testUrl'=>'https://sandbox-api.oneliquidity.technology/'
    ],
    'flutterwave'=>[
        'publicKey'   =>'FLWPUBK_TEST-efb266818beecd8080e26bad419572f5-X',
        'privateKey'  =>'FLWSECK_TEST-8e67dac80646f18c2e44ffa0225fb2ce-X',
        'url'=>'https://api.flutterwave.com/v3/'
    ],
    'paystack'=>[
    'publicKey'   =>'pk_live_ebc3b2f4a26986f18357629b2836b1f8cc19b7f9',
    'privateKey'  =>'sk_live_600b106dcdff41c5f97fa225f31c290f4e465cb2',
    'url'=>'https://api.paystack.co/'
    ]
];
