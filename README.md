[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/graphqlite-universal-service-provider/v/stable)](https://packagist.org/packages/thecodingmachine/graphqlite-universal-service-provider)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/graphqlite-universal-service-provider/v/unstable)](https://packagist.org/packages/thecodingmachine/graphqlite-universal-service-provider)
[![License](https://poser.pugx.org/thecodingmachine/graphqlite-universal-service-provider/license)](https://packagist.org/packages/thecodingmachine/graphqlite-universal-service-provider)
[![Build Status](https://travis-ci.org/thecodingmachine/graphqlite-universal-service-provider.svg?branch=master)](https://travis-ci.org/thecodingmachine/graphqlite-universal-service-provider)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/graphqlite-universal-service-provider/badge.svg?branch=master&service=github)](https://coveralls.io/github/thecodingmachine/graphqlite-universal-service-provider?branch=master)

# WORK IN PROGRESS

# GraphQLite universal module

This package integrates GraphQLite in any [container-interop](https://github.com/container-interop/service-provider) compatible framework/container.

## Installation

```
composer require thecodingmachine/graphqlite-universal-service-provider
```

Once installed, you need to register the [`TheCodingMachine\GraphQLiteServiceProvider`](src/GraphQLiteServiceProvider.php) into your container.

If your container supports [thecodingmachine/discovery](https://github.com/thecodingmachine/discovery) integration, you have nothing to do. Otherwise, refer to your framework or container's documentation to learn how to register *service providers*.

## Introduction

This service provider is meant to **[fill purpose here]**.

## Expected values / services

This *service provider* expects the following configuration / services to be available:

| Name                        | Compulsory | Description                            |
|-----------------------------|------------|----------------------------------------|
| `graphqlite.namespace.controllers`       | *yes*       | An array containing the namespaces where GraphQL controllers are stored |
| `graphqlite.namespace.types`       | *yes*       | An array containing the namespaces where GraphQL types are stored |
| `Psr\SimpleCache\CacheInterface`              | *yes*      | A PSR-16 cache service |
| `Doctrine\Common\Annotations\Reader`              | *yes*      | A Doctrine annotation reader |
| `TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface`              | *no*      | A service to plug authentication to GraphQLite. If not passed, the `FailAuthenticationService` is used instead. |
| `TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface`              | *no*      | A service to plug authorization to GraphQLite. If not passed, the `FailAuthorizationService` is used instead. |


## Provided services

This *service provider* provides the following services:

| Service name                | Description                          |
|-----------------------------|--------------------------------------|
| `service_name`              | Definition                           |

## Extended services

This *service provider* extends those services:

| Name                        | Compulsory | Description                            |
|-----------------------------|------------|----------------------------------------|
| `service_name`              | *yes*      | Definition                             |


<small>Project template courtesy of <a href="https://github.com/thecodingmachine/service-provider-template">thecodingmachine/service-provider-template</a></small>
