<?php

namespace KeithBrink\PlainSqs\Tests;
use Aws\Sqs\SqsClient;
use KeithBrink\PlainSqs\Jobs\DispatcherJob;
use KeithBrink\PlainSqs\Sqs\Queue;

/**
 * Class QueueTest
 * @package KeithBrink\PlainSqs\Tests
 */
class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function class_named_is_derived_from_queue_name()
    {

        $content = [
            'test' => 'test'
        ];

        $job = new DispatcherJob($content);

        $queue = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = new \ReflectionMethod(
            'KeithBrink\PlainSqs\Sqs\Queue', 'createPayload'
        );

        $method->setAccessible(true);

        //$response = $method->invokeArgs($queue, [$job]);
    }
}