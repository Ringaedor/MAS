<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Service Container - Advanced Dependency Injection System
 *
 * Provides a comprehensive dependency injection container for the MAS library with
 * enhanced service lifecycle management, automatic dependency resolution, performance
 * optimization, and enterprise-grade debugging capabilities for production environments.
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Opencart\Extension\Mas\System\Library\Mas;

/**
 * Enterprise Service Container
 *
 * Advanced dependency injection container with comprehensive service management
 * capabilities designed for enterprise-scale applications. Provides sophisticated
 * service lifecycle management, automatic dependency resolution, performance
 * monitoring, and extensive debugging capabilities.
 *
 * Key Features:
 * - Multiple instantiation patterns (singleton, factory, prototype, shared)
 * - Service tagging and grouping for discovery
 * - Service decoration and extension capabilities
 * - Performance monitoring with detailed statistics
 * - Circular dependency detection and prevention
 * - Comprehensive error handling with detailed context
 * - Memory usage tracking and optimization
 * - Debug mode for development and troubleshooting
 *
 * Usage Examples:
 *
 * Basic service registration:
 * $container->set('logger', MyLogger::class);
 *
 * Factory pattern:
 * $container->factory('connection', function($c) {
 *     return new DatabaseConnection($c->get('config'));
 * });
 *
 * Singleton with tags:
 * $container->singleton('cache', RedisCache::class, ['cache', 'redis']);
 *
 * Service decoration:
 * $container->extend('mailer', function($original, $c) {
 *     return new LoggingMailerDecorator($original, $c->get('logger'));
 * });
 */
class ServiceContainer
{
    /**
     * @var array<string, mixed> Registered services and their definitions
     */
    protected array $services = [];
    
    /**
     * @var array<string, mixed> Singleton instances cache
     */
    protected array $instances = [];
    
    /**
     * @var array<string, bool> Service singleton flags
     */
    protected array $singletons = [];
    
    /**
     * @var array<string, string> Service aliases mapping
     */
    protected array $aliases = [];
    
    /**
     * @var array<string, array<string>> Service tags for grouping and discovery
     */
    protected array $tags = [];
    
    /**
     * @var array<string, array<callable>> Service decorators chain
     */
    protected array $decorators = [];
    
    /**
     * @var array<string, array<string, mixed>> Service metadata for introspection
     */
    protected array $metadata = [];
    
    /**
     * @var array<string> Service resolution stack for circular dependency detection
     */
    protected array $resolutionStack = [];
    
    /**
     * @var bool Debug mode flag for enhanced diagnostics
     */
    protected bool $debugMode = false;
    
    /**
     * @var array<string, array<string, mixed>> Service resolution statistics
     */
    protected array $stats = [];
    
    /**
     * @var array<string> Available instantiation patterns
     */
    public const PATTERNS = ['singleton', 'factory', 'prototype', 'shared'];
    
    /**
     * Constructor - Initializes the service container
     *
     * Sets up the container with optional debug mode and initializes
     * internal tracking systems for comprehensive service management.
     *
     * @param bool $debugMode Enable debug mode for enhanced diagnostics
     */
    public function __construct(bool $debugMode = false)
    {
        $this->debugMode = $debugMode;
        $this->initializeStats();
        $this->debugLog('ServiceContainer initialized with debug mode: ' . ($debugMode ? 'enabled' : 'disabled'));
    }
    
    /**
     * Registers a service in the container with comprehensive validation
     *
     * Registers a service definition with full validation, conflict detection,
     * and comprehensive configuration options including tags and metadata.
     *
     * @param string $id Unique service identifier
     * @param mixed $definition Service definition (callable, object, or class name)
     * @param bool $singleton Whether to treat as singleton (default: true)
     * @param array<string> $tags Optional tags for service grouping
     * @param array<string, mixed> $metadata Optional service metadata
     * @throws Exception If service ID conflicts or definition is invalid
     */
    public function set(
        string $id,
        $definition,
        bool $singleton = true,
        array $tags = [],
        array $metadata = []
        ): void {
            if (isset($this->services[$id])) {
                throw Exception::create(
                    "Service '{$id}' is already registered. Use extend() to modify existing services.",
                    'config',
                    'medium',
                    [
                        'service_id' => $id,
                        'existing_singleton' => $this->singletons[$id] ?? false,
                        'new_singleton' => $singleton
                    ]
                    );
            }
            
            $this->validateDefinition($definition, $id);
            
            $this->services[$id] = $definition;
            $this->singletons[$id] = $singleton;
            
            if (!empty($tags)) {
                $this->tags[$id] = array_unique($tags);
            }
            
            if (!empty($metadata)) {
                $this->metadata[$id] = $metadata;
            }
            
            // Initialize service statistics
            $this->stats['services'][$id] = [
                'singleton' => $singleton,
                'registered_at' => microtime(true),
                'resolution_count' => 0,
                'total_resolution_time' => 0.0,
                'last_resolved_at' => null,
                'memory_usage' => 0,
                'tags' => $tags
            ];
            
            $this->debugLog("Service '{$id}' registered as " . ($singleton ? 'singleton' : 'factory') . " with tags: " . implode(', ', $tags));
    }
    
    /**
     * Retrieves a service from the container with performance tracking
     *
     * Resolves and returns a service instance with comprehensive tracking,
     * performance monitoring, and circular dependency detection.
     *
     * @param string $id Service identifier
     * @return mixed The resolved service instance
     * @throws Exception If service not found or resolution fails
     */
    public function get(string $id)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Detect circular dependencies
        if (in_array($id, $this->resolutionStack, true)) {
            throw Exception::create(
                "Circular dependency detected in service resolution",
                'config',
                'critical',
                [
                    'service_id' => $id,
                    'resolution_stack' => $this->resolutionStack,
                    'stack_depth' => count($this->resolutionStack)
                ]
                );
        }
        
        $this->resolutionStack[] = $id;
        
        // Resolve aliases
        $resolvedId = $this->resolveAlias($id);
        
        if (!isset($this->services[$resolvedId])) {
            array_pop($this->resolutionStack);
            throw Exception::create(
                "Service '{$id}' not found in container",
                'config',
                'high',
                [
                    'service_id' => $id,
                    'resolved_id' => $resolvedId,
                    'available_services' => array_keys($this->services),
                    'available_aliases' => array_keys($this->aliases)
                ]
                );
        }
        
        // Return singleton instance if already created
        if ($this->singletons[$resolvedId] && isset($this->instances[$resolvedId])) {
            array_pop($this->resolutionStack);
            $this->updateResolutionStats($resolvedId, $startTime, $startMemory, true);
            return $this->instances[$resolvedId];
        }
        
        $instance = $this->resolve($resolvedId);
        
        // Apply decorators if any exist
        $instance = $this->applyDecorators($resolvedId, $instance);
        
        // Cache singleton instances
        if ($this->singletons[$resolvedId]) {
            $this->instances[$resolvedId] = $instance;
        }
        
        $this->updateResolutionStats($resolvedId, $startTime, $startMemory, false);
        array_pop($this->resolutionStack);
        
        return $instance;
    }
    
    /**
     * Checks if a service is registered in the container
     *
     * Verifies service existence including aliases with comprehensive checking.
     *
     * @param string $id Service identifier
     * @return bool True if service exists, false otherwise
     */
    public function has(string $id): bool
    {
        $resolvedId = $this->resolveAlias($id);
        return isset($this->services[$resolvedId]);
    }
    
    /**
     * Registers a service alias with comprehensive validation
     *
     * Creates an alias for an existing service with validation and
     * circular alias detection.
     *
     * @param string $alias Alias name
     * @param string $id Target service ID
     * @throws Exception If alias conflicts or target service doesn't exist
     */
    public function alias(string $alias, string $id): void
    {
        if (isset($this->aliases[$alias])) {
            throw Exception::create(
                "Alias '{$alias}' already exists for service '{$this->aliases[$alias]}'",
                'config',
                'medium',
                [
                    'alias' => $alias,
                    'existing_target' => $this->aliases[$alias],
                    'new_target' => $id
                ]
                );
        }
        
        if (!isset($this->services[$id])) {
            throw Exception::create(
                "Cannot create alias '{$alias}' for non-existent service '{$id}'",
                'config',
                'high',
                [
                    'alias' => $alias,
                    'target_service' => $id,
                    'available_services' => array_keys($this->services)
                ]
                );
        }
        
        // Check for circular alias chains
        $this->validateAliasChain($alias, $id);
        
        $this->aliases[$alias] = $id;
        $this->debugLog("Alias '{$alias}' created for service '{$id}'");
    }
    
    /**
     * Registers a factory service that creates new instances
     *
     * Convenience method for registering factory pattern services.
     *
     * @param string $id Service identifier
     * @param callable $factory Factory function
     * @param array<string> $tags Optional tags for service grouping
     */
    public function factory(string $id, callable $factory, array $tags = []): void
    {
        $this->set($id, $factory, false, $tags, ['pattern' => 'factory']);
    }
    
    /**
     * Registers a singleton service
     *
     * Convenience method for registering singleton pattern services.
     *
     * @param string $id Service identifier
     * @param mixed $definition Service definition
     * @param array<string> $tags Optional tags for service grouping
     */
    public function singleton(string $id, $definition, array $tags = []): void
    {
        $this->set($id, $definition, true, $tags, ['pattern' => 'singleton']);
    }
    
    /**
     * Gets all services matching specific tags
     *
     * Retrieves all services that have been tagged with the specified
     * tag, useful for service discovery and batch operations.
     *
     * @param string $tag Tag name to search for
     * @return array<string, mixed> Services matching the tag
     */
    public function getByTag(string $tag): array
    {
        $services = [];
        
        foreach ($this->tags as $serviceId => $serviceTags) {
            if (in_array($tag, $serviceTags, true)) {
                $services[$serviceId] = $this->get($serviceId);
            }
        }
        
        $this->debugLog("Retrieved " . count($services) . " services for tag '{$tag}'");
        
        return $services;
    }
    
    /**
     * Extends an existing service definition with decoration
     *
     * Modifies an existing service definition by wrapping it with
     * additional functionality using the decorator pattern.
     *
     * @param string $id Service identifier
     * @param callable $extender Extension function
     * @throws Exception If service not found
     */
    public function extend(string $id, callable $extender): void
    {
        if (!isset($this->services[$id])) {
            throw Exception::create(
                "Cannot extend non-existent service '{$id}'",
                'config',
                'high',
                [
                    'service_id' => $id,
                    'available_services' => array_keys($this->services)
                ]
                );
        }
        
        $originalDefinition = $this->services[$id];
        
        $this->services[$id] = function(ServiceContainer $container) use ($originalDefinition, $extender, $id) {
            $service = $this->resolveDefinition($originalDefinition);
            return $extender($service, $container);
        };
        
        // Clear singleton instance if it exists to force recreation
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
        
        $this->debugLog("Service '{$id}' extended with custom decorator");
    }
    
    /**
     * Removes a service and all its associated data
     *
     * Completely removes a service from the container including
     * its definition, instances, metadata, and related configurations.
     *
     * @param string $id Service identifier
     */
    public function remove(string $id): void
    {
        unset($this->services[$id]);
        unset($this->instances[$id]);
        unset($this->singletons[$id]);
        unset($this->tags[$id]);
        unset($this->decorators[$id]);
        unset($this->metadata[$id]);
        unset($this->stats['services'][$id]);
        
        // Remove aliases pointing to this service
        foreach ($this->aliases as $alias => $serviceId) {
            if ($serviceId === $id) {
                unset($this->aliases[$alias]);
            }
        }
        
        $this->debugLog("Service '{$id}' removed from container");
    }
    
    /**
     * Clears all services and resets container state
     *
     * Completely resets the container to its initial state.
     */
    public function clear(): void
    {
        $serviceCount = count($this->services);
        
        $this->services = [];
        $this->instances = [];
        $this->singletons = [];
        $this->aliases = [];
        $this->tags = [];
        $this->decorators = [];
        $this->metadata = [];
        $this->resolutionStack = [];
        
        $this->initializeStats();
        
        $this->debugLog("Container cleared, removed {$serviceCount} services");
    }
    
    /**
     * Gets all registered service identifiers
     *
     * @return array<string> List of service identifiers
     */
    public function getServiceIds(): array
    {
        return array_keys($this->services);
    }
    
    /**
     * Gets all registered aliases
     *
     * @return array<string, string> Alias to service ID mapping
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
    
    /**
     * Gets comprehensive container statistics
     *
     * Returns detailed statistics about container usage including
     * resolution counts, timing, memory usage, and performance metrics.
     *
     * @return array<string, mixed> Container statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }
    
    /**
     * Gets service metadata for introspection
     *
     * @param string|null $id Optional service ID for specific metadata
     * @return array<string, mixed> Service metadata
     */
    public function getMetadata(?string $id = null): array
    {
        if ($id !== null) {
            return $this->metadata[$id] ?? [];
        }
        
        return $this->metadata;
    }
    
    /**
     * Enables or disables debug mode
     *
     * @param bool $enabled True to enable debug mode
     */
    public function setDebugMode(bool $enabled): void
    {
        $this->debugMode = $enabled;
        $this->debugLog("Debug mode " . ($enabled ? 'enabled' : 'disabled'));
    }
    
    /**
     * Resolves a service definition into an instance
     *
     * @param string $id Service identifier
     * @return mixed Service instance
     * @throws Exception If service cannot be resolved
     */
    protected function resolve(string $id)
    {
        $this->debugLog("Resolving service '{$id}'");
        
        if (!isset($this->services[$id])) {
            throw Exception::create(
                "Service '{$id}' not found during resolution",
                'config',
                'high',
                ['service_id' => $id]
            );
        }
        
        return $this->resolveDefinition($this->services[$id]);
    }
    
    /**
     * Resolves a service definition to an actual instance
     *
     * @param mixed $definition Service definition
     * @return mixed Resolved service instance
     * @throws Exception If definition cannot be resolved
     */
    protected function resolveDefinition($definition)
    {
        if ($definition instanceof \Closure) {
            return $definition($this);
        }
        
        if (is_callable($definition)) {
            return call_user_func($definition, $this);
        }
        
        if (is_object($definition)) {
            return $definition;
        }
        
        if (is_string($definition) && class_exists($definition)) {
            return $this->instantiateClass($definition);
        }
        
        if (is_array($definition)) {
            return $this->resolveArrayDefinition($definition);
        }
        
        throw Exception::create(
            "Invalid service definition type: " . gettype($definition),
            'config',
            'high',
            [
                'definition_type' => gettype($definition),
                'definition_value' => is_scalar($definition) ? $definition : 'non-scalar'
            ]
            );
    }
    
    /**
     * Instantiates a class with automatic dependency injection
     *
     * @param string $className Class name to instantiate
     * @return object Class instance
     * @throws Exception If class cannot be instantiated
     */
    protected function instantiateClass(string $className): object
    {
        if (!class_exists($className)) {
            throw Exception::create(
                "Class '{$className}' does not exist",
                'config',
                'high',
                ['class_name' => $className]
            );
        }
        
        $reflectionClass = new \ReflectionClass($className);
        
        if (!$reflectionClass->isInstantiable()) {
            throw Exception::create(
                "Class '{$className}' is not instantiable",
                'config',
                'high',
                [
                    'class_name' => $className,
                    'is_abstract' => $reflectionClass->isAbstract(),
                    'is_interface' => $reflectionClass->isInterface()
                ]
                );
        }
        
        $constructor = $reflectionClass->getConstructor();
        
        if ($constructor === null) {
            return new $className();
        }
        
        $parameters = $constructor->getParameters();
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter);
        }
        
        return $reflectionClass->newInstanceArgs($dependencies);
    }
    
    /**
     * Resolves a constructor parameter dependency
     *
     * @param \ReflectionParameter $parameter Parameter reflection
     * @return mixed Resolved parameter value
     * @throws Exception If parameter cannot be resolved
     */
    protected function resolveParameter(\ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        
        if ($type && !$type->isBuiltin()) {
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : (string)$type;
            
            if ($this->has($typeName)) {
                return $this->get($typeName);
            }
            
            if (class_exists($typeName)) {
                return $this->instantiateClass($typeName);
            }
        }
        
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        
        throw Exception::create(
            "Cannot resolve parameter '{$parameter->getName()}' for dependency injection",
            'config',
            'high',
            [
                'parameter' => $parameter->getName(),
                'type' => $type ? (string)$type : 'untyped',
                'has_default' => $parameter->isDefaultValueAvailable()
            ]
            );
    }
    
    /**
     * Resolves array-based service definitions
     *
     * @param array<string, mixed> $definition Array definition
     * @return mixed Resolved service
     * @throws Exception If array definition is invalid
     */
    protected function resolveArrayDefinition(array $definition)
    {
        if (!isset($definition['class'])) {
            throw Exception::create(
                "Array-based definition must include 'class' key",
                'config',
                'high',
                ['definition_keys' => array_keys($definition)]
                );
        }
        
        $className = $definition['class'];
        $arguments = $definition['arguments'] ?? [];
        
        // Resolve argument dependencies
        $resolvedArguments = [];
        foreach ($arguments as $argument) {
            if (is_string($argument) && strpos($argument, '@') === 0) {
                $serviceId = substr($argument, 1);
                $resolvedArguments[] = $this->get($serviceId);
            } else {
                $resolvedArguments[] = $argument;
            }
        }
        
        $reflectionClass = new \ReflectionClass($className);
        return $reflectionClass->newInstanceArgs($resolvedArguments);
    }
    
    /**
     * Resolves service aliases to actual service IDs
     *
     * @param string $id Service identifier or alias
     * @return string Resolved service ID
     */
    protected function resolveAlias(string $id): string
    {
        $visited = [];
        $current = $id;
        
        while (isset($this->aliases[$current])) {
            if (isset($visited[$current])) {
                throw Exception::create(
                    "Circular alias dependency detected",
                    'config',
                    'critical',
                    [
                        'alias_chain' => array_keys($visited),
                        'current' => $current
                    ]
                    );
            }
            
            $visited[$current] = true;
            $current = $this->aliases[$current];
        }
        
        return $current;
    }
    
    /**
     * Validates alias chain to prevent circular references
     *
     * @param string $alias New alias
     * @param string $target Target service
     * @throws Exception If circular reference would be created
     */
    protected function validateAliasChain(string $alias, string $target): void
    {
        $current = $target;
        $chain = [$alias];
        
        while (isset($this->aliases[$current])) {
            $chain[] = $current;
            $current = $this->aliases[$current];
            
            if ($current === $alias) {
                throw Exception::create(
                    "Creating alias '{$alias}' for '{$target}' would create circular reference",
                    'config',
                    'high',
                    [
                        'alias' => $alias,
                        'target' => $target,
                        'chain' => $chain
                    ]
                    );
            }
        }
    }
    
    /**
     * Applies decorators to a service instance
     *
     * @param string $id Service identifier
     * @param mixed $instance Original service instance
     * @return mixed Decorated service instance
     */
    protected function applyDecorators(string $id, $instance)
    {
        if (!isset($this->decorators[$id])) {
            return $instance;
        }
        
        foreach ($this->decorators[$id] as $decorator) {
            $instance = $decorator($instance, $this);
        }
        
        return $instance;
    }
    
    /**
     * Updates resolution statistics for a service
     *
     * @param string $id Service identifier
     * @param float $startTime Resolution start time
     * @param int $startMemory Memory usage at start
     * @param bool $fromCache Whether resolved from cache
     */
    protected function updateResolutionStats(string $id, float $startTime, int $startMemory, bool $fromCache): void
    {
        if (!isset($this->stats['services'][$id])) {
            return;
        }
        
        $resolutionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;
        
        $this->stats['services'][$id]['resolution_count']++;
        $this->stats['services'][$id]['total_resolution_time'] += $resolutionTime;
        $this->stats['services'][$id]['last_resolved_at'] = microtime(true);
        $this->stats['services'][$id]['memory_usage'] += $memoryUsed;
        $this->stats['services'][$id]['from_cache'] = $fromCache;
        
        $this->stats['global']['total_resolutions']++;
        $this->stats['global']['total_resolution_time'] += $resolutionTime;
        $this->stats['global']['cache_hits'] += $fromCache ? 1 : 0;
    }
    
    /**
     * Validates service definition
     *
     * @param mixed $definition Definition to validate
     * @param string $id Service identifier
     * @throws Exception If definition is invalid
     */
    protected function validateDefinition($definition, string $id): void
    {
        if ($definition === null) {
            throw Exception::create(
                "Service definition cannot be null for '{$id}'",
                'config',
                'high',
                ['service_id' => $id]
            );
        }
        
        if (is_array($definition) && !isset($definition['class'])) {
            throw Exception::create(
                "Array-based definition for '{$id}' must include 'class' key",
                'config',
                'high',
                [
                    'service_id' => $id,
                    'definition_keys' => array_keys($definition)
                ]
                );
        }
        
        if (is_string($definition) && !class_exists($definition) && !is_callable($definition)) {
            throw Exception::create(
                "String definition for '{$id}' must be a valid class name or callable",
                'config',
                'high',
                [
                    'service_id' => $id,
                    'definition' => $definition
                ]
                );
        }
    }
    
    /**
     * Initializes container statistics tracking
     */
    protected function initializeStats(): void
    {
        $this->stats = [
            'global' => [
                'container_created_at' => microtime(true),
                'total_resolutions' => 0,
                'total_resolution_time' => 0.0,
                'cache_hits' => 0,
            ],
            'services' => []
        ];
    }
    
    /**
     * Logs debug messages when debug mode is enabled
     *
     * @param string $message Debug message
     */
    protected function debugLog(string $message): void
    {
        if ($this->debugMode) {
            error_log("[MAS ServiceContainer DEBUG] " . date('Y-m-d H:i:s') . " - {$message}");
        }
    }
    
    /**
     * Magic method to access services as properties
     *
     * @param string $id Service identifier
     * @return mixed Service instance
     */
    public function __get(string $id)
    {
        return $this->get($id);
    }
    
    /**
     * Magic method to check if service exists
     *
     * @param string $id Service identifier
     * @return bool True if service exists
     */
    public function __isset(string $id): bool
    {
        return $this->has($id);
    }
}
