<?php

namespace TheCodingMachine;

use Doctrine\Common\Annotations\Reader;
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
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\QueryProviderInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

class GraphQLiteServiceProvider extends ServiceProvider
{
    /**
     * @Factory()
     */
    public static function getSchema(
        QueryProviderInterface $queryProvider,
        RecursiveTypeMapperInterface $recursiveTypeMapper,
        TypeResolver $typeResolver
    ): Schema {
        return new Schema($queryProvider, $recursiveTypeMapper, $typeResolver);
    }

    /**
     * @Factory(aliases={QueryProviderInterface::class})
     * @param QueryProviderInterface[] $queryProviders
     */
    public static function getQueryProvider(array $queryProviders): AggregateQueryProvider
    {
        return new AggregateQueryProvider($queryProviders);
    }

    /**
     * @Factory(name="queryProviders")
     * @return QueryProviderInterface[]
     */
    public static function getQueryProviders(
        FieldsBuilderFactory $fieldsBuilderFactory,
        RecursiveTypeMapperInterface $recursiveTypeMapper,
        ContainerInterface $container,
        CacheInterface $cache,
        LockFactory $lockFactory
    ): array {
        $namespaces = $container->get('graphqlite.namespace.controllers');
        $queryProviders = [];

        foreach ($namespaces as $namespace) {
            $queryProviders[] = new GlobControllerQueryProvider(
                $namespace,
                $fieldsBuilderFactory,
                $recursiveTypeMapper,
                $container,
                $lockFactory,
                $cache
            );
        }

        return $queryProviders;
    }

    /**
     * @Factory()
     */
    public static function getLockFactory(StoreInterface $store): LockFactory
    {
        return new LockFactory($store);
    }

    /**
     * @Factory()
     */
    public static function getStore(): StoreInterface
    {
        if (extension_loaded('sysvsem')) {
            return new SemaphoreStore();
        } else {
            return new FlockStore(sys_get_temp_dir());
        }
    }

    /**
     * @Factory()
     */
    public static function getFieldsBuilderFactory(
        AnnotationReader $annotationReader,
        HydratorInterface $hydrator,
        AuthenticationServiceInterface $authenticationService,
        AuthorizationServiceInterface $authorizationService,
        TypeResolver $typeResolver,
        CachedDocBlockFactory $cachedDocBlockFactory,
        NamingStrategyInterface $namingStrategy
    ): FieldsBuilderFactory {
        return new FieldsBuilderFactory(
            $annotationReader,
            $hydrator,
            $authenticationService,
            $authorizationService,
            $typeResolver,
            $cachedDocBlockFactory,
            $namingStrategy
        );
    }

    /**
     * @Factory()
     */
    public static function getTypeResolver(): TypeResolver
    {
        return new TypeResolver();
    }

    /**
     * @Factory(aliases={AuthenticationServiceInterface::class})
     */
    public static function getAuthenticationService(): FailAuthenticationService
    {
        return new FailAuthenticationService();
    }

    /**
     * @Factory(aliases={AuthorizationServiceInterface::class})
     */
    public static function getAuthorizationService(): FailAuthorizationService
    {
        return new FailAuthorizationService();
    }

    /**
     * @Factory(aliases={RecursiveTypeMapperInterface::class})
     */
    public static function getRecursiveTypeMapperInterface(
        TypeMapperInterface $typeMapper,
        NamingStrategyInterface $namingStrategy,
        CacheInterface $cache,
        TypeRegistry $typeRegistry
    ): RecursiveTypeMapper {
        return new RecursiveTypeMapper($typeMapper, $namingStrategy, $cache, $typeRegistry);
    }

    /**
     * @Factory(aliases={TypeMapperInterface::class})
     * @param TypeMapperInterface[] $typeMappers
     */
    public static function getTypeMapper(array $typeMappers): CompositeTypeMapper
    {
        return new CompositeTypeMapper($typeMappers);
    }

    /**
     * @Factory(name="typeMappers")
     * @return TypeMapperInterface[]
     */
    public static function getTypeMappers(
        ContainerInterface $container,
        TypeGenerator $typeGenerator,
        InputTypeGenerator $inputTypeGenerator,
        InputTypeUtils $inputTypeUtils,
        AnnotationReader $annotationReader,
        NamingStrategyInterface $namingStrategy,
        CacheInterface $cache,
        PorpaginasTypeMapper $porpaginasTypeMapper,
        LockFactory $lockFactory
    ): array {
        $namespaces = $container->get('graphqlite.namespace.types');
        $typeMappers = [];

        foreach ($namespaces as $namespace) {
            $typeMappers[] = new GlobTypeMapper(
                $namespace,
                $typeGenerator,
                $inputTypeGenerator,
                $inputTypeUtils,
                $container,
                $annotationReader,
                $namingStrategy,
                $lockFactory,
                $cache
            );
        }

        $typeMappers[] = $porpaginasTypeMapper;

        return $typeMappers;
    }

    /**
     * @Factory()
     */
    public static function getPorpaginasTypeMapper(): PorpaginasTypeMapper
    {
        return new PorpaginasTypeMapper();
    }

    /**
     * @Factory()
     */
    public static function getTypeGenerator(
        AnnotationReader $annotationReader,
        FieldsBuilderFactory $fieldsBuilderFactory,
        NamingStrategyInterface $namingStrategy,
        TypeRegistry $typeRegistry,
        ContainerInterface $container
    ): TypeGenerator {
        return new TypeGenerator($annotationReader, $fieldsBuilderFactory, $namingStrategy, $typeRegistry, $container);
    }

    /**
     * @Factory()
     */
    public static function getTypeRegistry(): TypeRegistry
    {
        return new TypeRegistry();
    }

    /**
     * @Factory()
     */
    public static function getInputTypeGenerator(
        InputTypeUtils $inputTypeUtils,
        FieldsBuilderFactory $fieldsBuilderFactory,
        ArgumentResolver $argumentResolver
    ): InputTypeGenerator {
        return new InputTypeGenerator($inputTypeUtils, $fieldsBuilderFactory, $argumentResolver);
    }

    /**
     * @Factory()
     */
    public static function getInputTypeUtils(
        AnnotationReader $annotationReader,
        NamingStrategyInterface $namingStrategy
    ): InputTypeUtils {
        return new InputTypeUtils($annotationReader, $namingStrategy);
    }

    /**
     * @Factory()
     */
    public static function getAnnotationReader(Reader $annotationReader): AnnotationReader
    {
        return new AnnotationReader($annotationReader);
    }

    /**
     * @Factory(aliases={HydratorInterface::class})
     */
    public static function getFactoryHydrator(): FactoryHydrator
    {
        return new FactoryHydrator();
    }

    /**
     * @Factory()
     */
    public static function getArgumentResolver(HydratorInterface $hydrator): ArgumentResolver
    {
        return new ArgumentResolver($hydrator);
    }

    /**
     * @Factory(aliases={NamingStrategyInterface::class})
     */
    public static function getNamingStrategy(): NamingStrategy
    {
        return new NamingStrategy();
    }

    /**
     * @Factory()
     */
    public static function getCachedDocBlockFactory(CacheInterface $cache): CachedDocBlockFactory
    {
        return new CachedDocBlockFactory($cache);
    }
}
