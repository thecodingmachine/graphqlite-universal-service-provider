<?php

namespace TheCodingMachine;

use PHPUnit\Framework\TestCase;
use Simplex\Container;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;

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
        $container->set(AuthenticationServiceInterface::class, function() { return new FailAuthenticationService(); });
        $container->set(AuthorizationServiceInterface::class, function() { return new FailAuthorizationService(); });

        $schema = $container->get(Schema::class);
        $this->assertInstanceOf(Schema::class, $schema);
    }
}
