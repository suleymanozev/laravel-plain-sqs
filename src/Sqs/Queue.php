<?php

namespace KeithBrink\PlainSqs\Sqs;

use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use KeithBrink\PlainSqs\Jobs\DispatcherJob;

/**
 * Class CustomSqsQueue.
 */
class Queue extends SqsQueue
{
    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return string
     */
    protected function createPayload($job, $data = '', $queue = null)
    {
        if (! $job instanceof DispatcherJob) {
            return parent::createPayload($job, $data, $queue);
        }

        $handlerJob = $this->getClass($queue) . '@handle';

        return $job->isPlain() ? json_encode($job->getPayload()) : json_encode(['job' => $handlerJob, 'data' => $job->getPayload()]);
    }

    /**
     * @param $queue
     * @return string
     */
    private function getClass($queue = null)
    {
        if (! $queue) {
            return Config::get('sqs-plain.default-handler');
        }

        $queue = end(explode('/', $queue));

        return (array_key_exists($queue, Config::get('sqs-plain.handlers')))
            ? Config::get('sqs-plain.handlers')[$queue]
            : Config::get('sqs-plain.default-handler');
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (isset($response['Messages']) && count($response['Messages']) > 0) {
            $queueId = explode('/', $queue);
            $queueId = array_pop($queueId);

            $class = (array_key_exists($queueId, $this->container['config']->get('sqs-plain.handlers')))
                ? $this->container['config']->get('sqs-plain.handlers')[$queueId]
                : $this->container['config']->get('sqs-plain.default-handler');

            $response = $this->modifyPayload($response['Messages'][0], $class);

            return new SqsJob($this->container, $this->sqs, $response, $this->connectionName, $queue);
        }
    }

    /**
     * @param string|array $payload
     * @param string $class
     * @return array
     */
    public function modifyPayload($payload, $class)
    {
        if (! is_array($payload)) {
            $payload = json_decode($payload, true);
        }

        $body = json_decode($payload['Body'], true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $body = simplexml_load_string($payload['Body'], 'SimpleXMLElement', LIBXML_NOCDATA);

            $uuid = (string) $body->NotificationMetaData->UniqueId;
        } else {
            $uuid = Str::random(32);
        }

        $body = [
            'job' => $class . '@handle',
            'uuid' => $uuid,
            'data' => isset($body['data']) ? $body['data'] : $body,
        ];

        $payload['Body'] = json_encode($body);

        return $payload;
    }

    /**
     * @param string $payload
     * @param null $queue
     * @param array $options
     * @return mixed|null
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $payload = json_decode($payload, true);

        if (isset($payload['data']) && isset($payload['job'])) {
            $payload = $payload['data'];
        }

        return parent::pushRaw(json_encode($payload), $queue, $options);
    }
}
