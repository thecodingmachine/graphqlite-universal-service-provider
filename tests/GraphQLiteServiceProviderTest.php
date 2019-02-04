<?php

namespace TheCodingMachine;

use PHPUnit\Framework\TestCase;
use Simplex\Container;

class GraphQLiteServiceProviderTest extends TestCase
{
    public function testServiceProvider(): void
    {
        $container = new Container([new GraphQLiteServiceProvider()]);
    }
}
