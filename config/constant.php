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
    ]
];
