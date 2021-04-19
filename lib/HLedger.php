<?php declare(strict_types=1);
namespace HLedger;

class HLedger
{
    const OUTPUT_TABLE = 'table';
    const OUTPUT_DETAIL = 'detail';
    private $outputFormat;

    private $options;

    private $hledgerExe;

    /**
     * @param $options Options applied to every command on this instance.
     */
    public function __construct(
        array $options,
        string $output = Self::OUTPUT_TABLE
    ) {
        $this->hledgerExe = realpath(__DIR__ . '/../bin/hledger');
        $this->options = $options;
        $this->outputFormat = $output;
        if ($output == Self::OUTPUT_TABLE) {
            array_push($this->options, ['output-format', 'csv']);
        } elseif ($output == Self::OUTPUT_DETAIL) {
            array_push($this->options, ['output-format', 'json']);
        }
    }

    /**
     * @param $options Options applied to this command only.
     * @param $arguments Command arguments.
     */
    public function balance(array $options = [], array $arguments = []): array
    {
        return $this->execute('balance', $options, $arguments);
    }

    /**
     * @param $options Options applied to this command only.
     * @param $arguments Command arguments.
     */
    public function balanceSheet(array $options = [], array $arguments = []): array
    {
        return $this->execute('balancesheet', $options, $arguments);
    }

    /**
     * @param $options Options applied to this command only.
     * @param $arguments Command arguments.
     */
    public function incomeStatement(array $options = [], array $arguments = []): array
    {
        return $this->execute('incomestatement', $options, $arguments);
    }

    private function execute($command, $options, $arguments)
    {
        $ops = $this->renderOptions($options);
        $args = $this->renderArguments($arguments);
        $command = "$this->hledgerExe balance $ops $args 2>&1";
        $output = shell_exec($command);
        if ($this->outputFormat == Self::OUTPUT_TABLE) {
            return $this->parseCsvToTable($output);
        } elseif ($this->outputFormat == Self::OUTPUT_TABLE) {
            return $this->parseJsonToDetail($output);
        }
    }

    private function renderOptions($commandOptions)
    {
        $options = array_merge($this->options, $commandOptions);
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
