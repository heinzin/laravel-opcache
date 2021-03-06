<?php

namespace Appstract\Opcache\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Appstract\Opcache\OpcacheFacade         as OPcache;

class Status extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'opcache:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show state information, memory usage, etc..';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->get(config('app.url').'/opcache-api/status');
        $response = json_decode($response->getBody()->getContents());

        if ($response->result !== false) {
            $this->line('General:');
            $general = (array) $response->result;
            unset($general['memory_usage'], $general['interned_strings_usage'], $general['opcache_statistics']);
            $this->table(['key', 'value'], $this->parseTable($general));

            $this->line(PHP_EOL.'Memory usage:');
            $this->table(['key', 'value'], $this->parseTable($response->result->memory_usage));

            $this->line(PHP_EOL.'Interned strings usage:');
            $this->table(['key', 'value'], $this->parseTable($response->result->interned_strings_usage));

            $this->line(PHP_EOL.'Statistics:');
            $this->table(['option', 'value'], $this->parseTable($response->result->opcache_statistics));
        } else {
            $this->error('No opcode cache status information available');
        }
    }

    /**
     * Make up the table for console display.
     *
     * @param $input
     *
     * @return array
     */
    protected function parseTable($input)
    {
        $input = (array) $input;

        return array_map(function ($key, $value) {
            return [
                'key'       => $key,
                'value'     => $value,
            ];
        }, array_keys($input), $input);
    }
}
