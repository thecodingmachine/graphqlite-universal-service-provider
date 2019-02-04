<?php

namespace TheCodingMachine;

use PHPUnit\Framework\TestCase;
use Simplex\Container;
use TheCodingMachine\GraphQLite\Schema;

class GraphQLiteServiceProviderTest extends TestCase
{
    public function testServiceProvider(): void
    {
        $container = new Container([
            new SymfonyCacheServiceProvider(),
            new DoctrineAnnotationsServiceProvider,
            new GraphQLiteServiceProvider()]);
        $container->set('graphqlite.namespace.types', ['App\\Types']);
        $container->set('graphqlite.namespace.controllers', ['App\\Controllers']);

        $schema = $container->get(Schema::class);
        $this->assertInstanceOf(Schema::class, $schema);
    }
}
