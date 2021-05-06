<?php

require(__DIR__ . '/../lib/HLedger.php');

use HLedger\HLedger;

$hledger = new HLedger([
    ['file', realpath(__DIR__ . '/bcexample.hledger')]
]);

print_r($hledger->incomeStatement([
    ['monthly'],
    ['market'],
    ['begin', 'thisyear'],
    ['end', 'thismonth']
]));

print_r($hledger->balanceSheet([
    ['monthly'],
    ['market'],
    ['begin', 'thisyear'],
    ['end', 'nextmonth']
]));

print_r($hledger->balance([
    ['monthly'],
    ['market'],
    ['begin', 'lastmonth'],
    ['end', 'nextmonth'],
    ['budget']
], [
    'not:desc:opening balances'
]));

print(PHP_EOL . $hledger->makeTransaction([
    'date' => new DateTime(),
    'description' => 'Opening Balance for checking account',
    'postings' => [
        [
            'account' => 'assets:US:BofA:Checking',
            'amount' => '3077.70'
        ],
        [
            'account' => 'equity:Opening Balances',
            'amount' => '-3077.70'
        ]
    ]
]) . PHP_EOL);

$hledger->addTransaction([
    'date' => new DateTime(),
    'status' => '*',
    'code' => '123',
    'description' => 'Opening Balance for checking account',
    'comment' => 'This is a comment',
    'postings' => [
        [
            'status' => '!',
            'account' => 'assets:US:BofA:Checking',
            'amount' => '3077.70',
            'comment' => 'This is a comment'
        ],
        [
            'status' => '*',
            'account' => 'equity:Opening Balances',
            'amount' => '-3077.70',
            'comment' => 'This is a comment'
        ]
    ]
]);
