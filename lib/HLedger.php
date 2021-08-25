<?php declare(strict_types=1);
namespace HLedger;

class HLedger
{
    const OUTPUT_TABLE = 'table';
    const OUTPUT_DETAIL = 'detail';
    private $outputFormat;

    private $dataDir;
    private $options;

    private $hledgerExe;
    private $lastCommand;

    /**
     * @param $options Options applied to every command on this instance.
     */
    public function __construct(
        array $options,
        string $dataDir,
        string $output = Self::OUTPUT_TABLE
    ) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->hledgerExe = realpath(__DIR__ . '\..\bin\hledger.exe');
        } else {
            $this->hledgerExe = realpath(__DIR__ . '/../bin/hledger');
        }
        $this->options = $options;
        $this->dataDir = $dataDir;
        $this->outputFormat = $output;
        if ($output == Self::OUTPUT_TABLE) {
            array_push($this->options, ['output-format', 'csv']);
        } elseif ($output == Self::OUTPUT_DETAIL) {
            array_push($this->options, ['output-format', 'json']);
        }
    }

    public function makeTransaction(array $transaction): string
    {
        $t = $transaction['date']->format('Y-m-d');
        if (!empty($transaction['status'])) {
            $t .= ' ' . $transaction['status'];
        }
        if (!empty($transaction['code'])) {
            $t .= ' ' . $transaction['code'];
        }
        if (!empty($transaction['description'])) {
            $t .= ' ' . $transaction['description'];
        }
        if (!empty($transaction['comment'])) {
            $t .= '  ;  ' . $transaction['comment'];
        }
        $accountsWidth = 0;
        foreach ($transaction['postings'] as $posting) {
            $accountsWidth = max($accountsWidth, strlen($posting['account']));
        }
        foreach ($transaction['postings'] as $posting) {
            $p = PHP_EOL . '   ';
            if (!empty($posting['status'])) {
                $p .= ' ' . $posting['status'];
            }
            $p .= ' ' . $posting['account'];
            $spaces = $accountsWidth - strlen($posting['account']) + max(16 - strlen($posting['amount']), 4);
            $p .= str_repeat(' ', $spaces) . $posting['amount'];
            if (!empty($posting['comment'])) {
                $p .= '  ;  ' . $posting['comment'];
            }
            $t .= $p;
        }
        return $t;
    }

    public function addTransaction(array $transaction)
    {
        $files = array_filter($this->options, function ($option) {
            return $option[0] == 'file';
        });
        if (count($files) == 0) {
            throw new \Exception('Please specify a journal file in the contructor options.');
        }
        $file = $files[0][1];
        $data = PHP_EOL . $this->makeTransaction($transaction) . PHP_EOL;
        if (file_put_contents($file, $data, FILE_APPEND) != strlen($data)) {
            throw new \Exception('Failed to append transaction.');
        }
    }

    public function accounts(array $options = [], array $arguments = []): array
    {
        $options = array_filter(array_merge($this->options, $options), function ($option) {
            return $option[0] != 'output-format';
        });
        return array_map(function ($row) {
            return $row[0];
        }, $this->execute('accounts', $options, $arguments));
    }

    public function accountRegister(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('aregister', $options, $arguments);
    }

    public function balance(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('balance', $options, $arguments);
    }

    public function balanceSheet(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('balancesheet', $options, $arguments);
    }

    public function balanceSheetEquity(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('balancesheetequity', $options, $arguments);
    }

    public function cashFlow(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('cashflow', $options, $arguments);
    }

    public function incomeStatement(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('incomestatement', $options, $arguments);
    }

    public function print(array $options = [], array $arguments = []): array
    {
        return $this->execute('print', $options, $arguments);
    }

    public function register(array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute('register', $options, $arguments);
    }

    public function custom(string $argsFile, array $options = [], array $arguments = []): array
    {
        $options = array_merge($this->options, $options);
        return $this->execute(escapeshellarg('@' . $argsFile), $options, $arguments);
    }

    public function lastCommandExecuted()
    {
        return $this->lastCommand;
    }

    private function execute($command, $options, $arguments)
    {
        $ops = $this->renderOptions($options);
        $args = $this->renderArguments($arguments);
        $this->lastCommand = "cd \"$this->dataDir\" && \"$this->hledgerExe\" $command $ops $args 2>&1";
        $output = shell_exec($this->lastCommand);
        if ($this->outputFormat == Self::OUTPUT_TABLE) {
            return $this->parseCsvToTable($output);
        } elseif ($this->outputFormat == Self::OUTPUT_TABLE) {
            return $this->parseJsonToDetail($output);
        }
    }

    private function renderOptions($options)
    {
        return implode(' ', array_map(function ($option) {
            if (count($option) == 1) {
                return escapeshellarg('--' . $option[0]);
            } elseif (count($option) == 2) {
                return escapeshellarg('--' . $option[0] . '=' . $option[1]);
            } else {
                return 'Invalid Option' . implode(' ', $option);
            }
        }, $options));
    }

    private function renderArguments($arguments)
    {
        return implode(' ', array_map(function ($argument) {
            return escapeshellarg($argument);
        }, $arguments));
    }

    private function parseCsvToTable(string $csv): array
    {
        $table = [];
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $csv) as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            $line = str_replace('\\"\\"', '""', $line); // Work around https://github.com/simonmichael/hledger/issues/1508
            $row = str_getcsv($line);
            array_push($table, $row);
        }
        return $table;
    }

    private function parseJsonToDetail(string $json): array
    {
        return []; // TODO
    }
}
