<?php

namespace KeithBrink\PlainSqs\Tests;

use KeithBrink\PlainSqs\Jobs\DispatcherJob;
use KeithBrink\PlainSqs\Sqs\Queue;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueTest
 * @package KeithBrink\PlainSqs\Tests
 */
class QueueTest extends TestCase
{
    public function testXmlReturnsArray()
    {
        $xml = ['Body' =>
            '<?xml version="1.0" encoding="UTF-8"?>
            <Notification>
            <NotificationMetaData>        
                <NotificationType>AnyOfferChanged</NotificationType>        
                <PayloadVersion>1.0</PayloadVersion>        
                <UniqueId>a926ca8b-b8d5-4d1b-8ec4-e3c153873fef</UniqueId>
                <PublishTime>2018-05-19T01:27:16.717Z</PublishTime>        
                <SellerId>A2CQULG3DLLFK4</SellerId>        
                <MarketplaceId>ATVPDKIKX0DER</MarketplaceId>        
            </NotificationMetaData>
            </Notification>'
        ];
        $mock = \Mockery::mock('\Aws\Sqs\SqsClient');

        $class = new Queue($mock, 'default');
        $payload = $class->modifyPayload($xml, 'TestClass');
        $payload = json_decode($payload['Body'], true);

        $xml_array = json_decode(json_encode(simplexml_load_string($xml['Body'], "SimpleXMLElement", LIBXML_NOCDATA)), true);
        $this->assertEquals($xml_array, $payload['data']);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
