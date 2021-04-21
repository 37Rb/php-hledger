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
