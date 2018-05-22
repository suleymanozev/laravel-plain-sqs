<?php

namespace KeithBrink\PlainSqs\Sqs;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Queue\Jobs\SqsJob;

class Connector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        $queue = new Queue(
            new SqsClient($config), $config['queue'], Arr::get($config, 'prefix', '')
        );
        
        return $queue;
    }
}
