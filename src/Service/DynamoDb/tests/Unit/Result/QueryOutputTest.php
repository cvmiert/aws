<?php

namespace AsyncAws\DynamoDb\Tests\Unit\Result;

use AsyncAws\Core\Response;
use AsyncAws\Core\Test\Http\SimpleMockedResponse;
use AsyncAws\Core\Test\TestCase;
use AsyncAws\DynamoDb\Result\QueryOutput;
use AsyncAws\DynamoDb\ValueObject\ConsumedCapacity;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;

class QueryOutputTest extends TestCase
{
    public function testQueryOutput(): void
    {
        // see example-1.json from SDK
        $response = new SimpleMockedResponse('{
            "ConsumedCapacity": {
                "CapacityUnits": 1,
                "TableName": "Reply"
            },
            "Count": 2,
            "Items": [
                {
                    "SongTitle": {
                        "S": "Call Me Today"
                    }
                }
            ],
            "ScannedCount": 3
        }');

        $client = new MockHttpClient($response);
        $result = new QueryOutput(new Response($client->request('POST', 'http://localhost'), $client, new NullLogger()));

        $items = $result->getItems(true);
        foreach ($items as $name => $item) {
            self::assertArrayHasKey('SongTitle', $item);
            self::assertEquals('Call Me Today', $item['SongTitle']->getS());
        }
        self::assertInstanceOf(ConsumedCapacity::class, $result->getConsumedCapacity());
        self::assertSame(2, $result->getCount());
        self::assertSame(3, $result->getScannedCount());
    }
}
