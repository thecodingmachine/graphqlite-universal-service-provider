<?php

namespace TheCodingMachine;

use Doctrine\Common\Annotations\Reader;
use Psr\Http\Server\MiddlewareInterface;
use TheCodingMachine\GraphQLite\Http\Psr15GraphQLMiddlewareBuilder;
use TheCodingMachine\GraphQLite\Http\WebonyxGraphqlMiddleware;
use function extension_loaded;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Lock\StoreInterface;
use function sys_get_temp_dir;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\ServiceProvider;
use TheCodingMachine\GraphQLite\AggregateQueryProvider;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\FieldsBuilderFactory;
use TheCodingMachine\GraphQLite\GlobControllerQueryProvider;
use TheCodingMachine\GraphQLite\Hydrators\FactoryHydrator;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\QueryProviderInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Schema;
use GraphQL\Type\Schema as WebonyxSchema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\Funky\Annotations\Tag;
use TheCodingMachine\MiddlewareListServiceProvider;
use TheCodingMachine\MiddlewareOrder;

class GraphQLiteServiceProvider extends ServiceProvider
{
    /**
     * @Factory(aliases={WebonyxSchema::class})
     */
    public static function getSchema(
        SchemaFactory $schemaFactory
    ): Schema {
        return $schemaFactory->createSchema();
    }

    /**
     * @Factory()
     * @param CacheInterface $cache
     * @param ContainerInterface $container
     * @param Reader|null $annotationReader
     * @return SchemaFactory
     */
    public static function getSchemaFactory(
        CacheInterface $cache,
        ContainerInterface $container,
        ?Reader $annotationReader = null,
        ?AuthenticationServiceInterface $authenticationService = null,
        ?AuthorizationServiceInterface $authorizationService = null
    ): SchemaFactory {
        $schemaFactory = new SchemaFactory($cache, $container);

        $controllerNamespaces = $container->get('graphqlite.namespace.controllers');
        $typeNamespaces = $container->get('graphqlite.namespace.types');

        foreach ($controllerNamespaces as $controllerNamespace) {
            $schemaFactory->addControllerNamespace($controllerNamespace);
        }
        foreach ($typeNamespaces as $typeNamespace) {
            $schemaFactory->addTypeNamespace($typeNamespace);
        }

        if ($annotationReader !== null) {
            $schemaFactory->setDoctrineAnnotationReader($annotationReader);
        }
        if ($authenticationService !== null) {
            $schemaFactory->setAuthenticationService($authenticationService);
        }
        if ($authorizationService !== null) {
            $schemaFactory->setAuthorizationService($authorizationService);
        }

        return $schemaFactory;
    }

    /**
     * @Factory()
     */
    public static function getMiddlewareBuilder(Schema $schema): Psr15GraphQLMiddlewareBuilder
    {
        return new Psr15GraphQLMiddlewareBuilder($schema);
    }

    /**
     * @Factory(name=WebonyxGraphqlMiddleware::class,tags={@Tag(name=MiddlewareListServiceProvider::MIDDLEWARES_QUEUE, priority=MiddlewareOrder::ROUTER)})
     */
    public static function getMiddleware(Psr15GraphQLMiddlewareBuilder $builder): MiddlewareInterface
    {
        return $builder->createMiddleware();
    }
}
