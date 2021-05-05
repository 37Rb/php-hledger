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
```

```php
/**
 * The following report commands all have the same syntax.
 * @param array $options Options applied to this command.
 * @param array $arguments Command arguments.
 * @return array Resulting report in table or detail form.
 */

// https://hledger.org/hledger.html#accounts
public function accounts(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#aregister
public function accountRegister(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#balance
public function balance(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#balancesheet
public function balanceSheet(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#balancesheetequity
public function balanceSheetEquity(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#cashflow
public function cashFlow(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#incomestatement
public function incomeStatement(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#print
public function print(array $options = [], array $arguments = []): array

// https://hledger.org/hledger.html#register
public function register(array $options = [], array $arguments = []): array
```

```PHP
/**
 * You can make a transaction which is returned as a string or you can add a
 * transaction which appends it to the journal file. Both functions take the
 * same parameter - a complex array representing the transaction.
 */

// https://hledger.org/1.0/journal.html#file-format
$transaction = [
	'date' => DateTime, // required, only date is used
	'status' => string, // ! or *
	'code' => string, // eg a check number
	'description' => string,
	'comment' => string,
	'postings' => [
		[
			'status' => string, // ! or *
			'account' => string,
			'amount' => string,
			'comment' => string
		],
		...
	]
 ]

// Return transaction as a strings
public function makeTransaction(array $transaction): string

// Append transaction to journal file
public function addTransaction(array $transaction)
```
