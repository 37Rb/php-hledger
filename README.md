# Use HLedger from PHP

Installs [HLedger](https://hledger.org/) as a Composer dependency and provides a PHP API to easily use it from your app.

This package uses [shell_exec](https://www.php.net/manual/en/function.shell-exec.php) and therefore shell_exec must be enabled in your server.

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

## Demo

```
$ php vendor/hledger/php-hledger/demo/demo.php
```

## Example

Load with PSR-4 autoload or require lib/HLedger.php.

```php
use HLedger\HLedger;

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

To begin, create a new HLedger instance. Then run commands on it. The API is directly mapped to the [hledger command line interface](https://hledger.org/hledger.html). Commands accept options and arguments.

## Options

Options can either be [general options](https://hledger.org/hledger.html#general-options) that work with most commands or [command options](https://hledger.org/hledger.html#command-options) that only work with a specific command.

Options are passed as an array of arrays. Each inner-array is one option, using the long name (the one that begins with -- on the command line) of the option. Single options are a one-item array. Options with a name and value are a two-item array. For example:

```php
$hledger->someCommand([
    ['option1'],           // --option1
    ['option2'],           // --option2
    ['option3', 'value3'], // --option3=value3
    ['option4', 'value4']  // --option4=value4
]);
```

General options can be passed to the HLedger constructor and will be applied to every command on that instance. General options can also be passed to individual commands, in which case they're only applied to that command.

Command options can only be passed to individual commands.

## Command Arguments

Some hledger commands accept [command arguments](https://hledger.org/hledger.html#command-arguments), which are often a query, filtering the data in some way.

Command arguments are passed as an array of strings. Each string is one argument. For example:

```php
$hledger->someCommand([
    // ...options
], [
    'argument 1'
]);
```

## API

```php
/**
 * Create a new HLedger instance.
 * @param $options General options applied to every command on this instance.
 * @param $output OUTPUT_TABLE|OUTPUT_DETAIL Chooses whether commands will return output as a simple table (2-dimensional array) or as a detailed tree (of associative arrays). Defaults to OUTPUT_TABLE.
 */
public function __construct(
    array $options,
    string $output = Self::OUTPUT_TABLE
)

/**
 * Get accounts and their balances.
 * https://hledger.org/hledger.html#balance
 * @param $options Options applied to this command.
 * @param $arguments Command arguments.
 */
public function balance(array $options = [], array $arguments = []): array


/**
 * Get a balance sheet, showing historical ending balances of asset and liability accounts.
 * https://hledger.org/hledger.html#balancesheet
 * @param $options Options applied to this command.
 * @param $arguments Command arguments.
 */
public function balanceSheet(array $options = [], array $arguments = []): array

/**
 * Get an income statement, showing revenues and expenses during one or more periods.
 * https://hledger.org/hledger.html#incomestatement
 * @param $options Options applied to this command.
 * @param $arguments Command arguments.
 */
public function incomeStatement(array $options = [], array $arguments = []): array
```
