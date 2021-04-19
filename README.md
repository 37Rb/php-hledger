# Use HLedger from PHP

Installs [HLedger](https://hledger.org/) as a Composer dependency and provides a PHP API to easily use it from your app.

## Install

Add to composer.json
```json
{
    "repositories": [
        {
          "type": "vcs",
          "url": "https://github.com/37Rb/php-hledger.git"
        }
      ],
    "require": {
        "hledger/php-hledger": "^0.0"
    },
    "scripts": {
        "post-install-cmd": [
            "php vendor/hledger/php-hledger/install.php"
        ],
        "post-update-cmd": [
            "php vendor/hledger/php-hledger/install.php"
        ]
    }
}
```

Run composer install.
```
$ composer install
```

## Use

Load with PSR-4 autoload.

```php
$hledger = new HLedger([
    ['file', 'demo/bcexample.hledger']
]);

$is = $hledger->incomeStatement([
    ['monthly'],
    ['market'],
    ['begin', 'thisyear'],
    ['end', 'thismonth']
]);

$bs = $hledger->balanceSheet([
    ['begin', 'thisyear'],
    ['end', 'nextmonth']
]);

$bal = $hledger->balance([
    ['monthly'],
    ['market'],
    ['budget']
], [
    'not:desc:opening balances'
]);
```
