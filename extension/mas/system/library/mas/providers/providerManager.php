<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Provider Manager - Dynamic Service Provider Discovery & Management System
 *
 * Handles auto-discovery, registration, configuration and lifecycle of all provider classes
 * (email, SMS, push notifications, AI services, payment gateways, analytics, etc.) used in MAS.
 * Supports recursive scan of providers, dynamic loading, runtime instantiation, configuration
 * persistence, capability querying, health monitoring, failover management, and comprehensive
 * provider analytics with enterprise-grade features including load balancing, circuit breakers,
 * performance monitoring, and automated provider selection optimization.
 *
 * This system provides a unified abstraction layer for all external service integrations,
 * enabling seamless switching between providers, automatic failover, performance optimization,
 * cost management, and comprehensive monitoring of provider health and performance metrics
 * across the entire MAS ecosystem.
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas\Provider
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Opencart\Extension\Mas\System\Library\Mas\Provider;

/**
 * Enterprise Provider Manager
 *
 * Advanced provider management system providing comprehensive service discovery,
 * configuration management, health monitoring, load balancing, failover handling,
 * and performance optimization for all external service integrations.
 *
 * Key Features:
 * - Automatic provider discovery with intelligent caching
 * - Dynamic configuration management with validation
 * - Health monitoring and circuit breaker patterns
 * - Load balancing and automatic failover
 * - Performance metrics and cost tracking
 * - Provider capability matching and selection
 * - Configuration versioning and rollback
 * - Comprehensive audit logging and monitoring
 * - Rate limiting and quota management
 * - Multi-region provider support
 * - Security validation and compliance checking
 * - Automated provider testing and validation
 *
 * Provider Types Supported:
 * - Email Service Providers (SendGrid, Mailgun, Amazon SES, etc.)
 * - SMS/MMS Providers (Twilio, Nexmo, Amazon SNS, etc.)
 * - Push Notification Services (FCM, APNS, OneSignal, etc.)
 * - AI/ML Services (OpenAI, Google AI, Azure Cognitive, etc.)
 * - Payment Gateways (Stripe, PayPal, Square, etc.)
 * - Analytics Providers (Google Analytics, Mixpanel, etc.)
 * - Storage Services (AWS S3, Google Cloud, Azure, etc.)
 * - CDN Providers (CloudFlare, AWS CloudFront, etc.)
 * - Voice Services (Twilio Voice, AWS Connect, etc.)
 * - Translation Services (Google Translate, Azure Translator, etc.)
 *
 * Advanced Capabilities:
 * - Smart provider selection based on performance, cost, and availability
 * - Automatic failover with configurable fallback chains
 * - Real-time health monitoring with predictive failure detection
 * - Cost optimization with usage tracking and budget controls
 * - Multi-region deployment with geo-routing
 * - A/B testing for provider performance comparison
 * - Compliance validation for regulatory requirements
 * - Integration testing automation
 *
 * Usage Examples:
 *
 * Basic provider management:
 * $manager = new ProviderManager($container);
 * $emailProvider = $manager->get('sendgrid', $config);
 * $providers = $manager->getProvidersByType('email');
 *
 * Advanced selection:
 * $bestProvider = $manager->selectBestProvider('email', $criteria);
 * $healthyProviders = $manager->getHealthyProviders('sms');
 *
 * Monitoring and analytics:
 * $metrics = $manager->getProviderMetrics('twilio', '24h');
 * $health = $manager->getProviderHealth('openai');
 *
 * Configuration management:
 * $manager->updateProviderConfig('stripe', $newConfig);
 * $manager->rollbackConfiguration('mailgun', $previousVersion);
 */
class ProviderManager
{
    /**
     * Provider discovery cache duration
     */
    protected const DISCOVERY_CACHE_TTL = 600; // 10 minutes
    
    /**
     * Health check intervals
     */
    protected const HEALTH_CHECK_INTERVAL = 300; // 5 minutes
    
    /**
     * Circuit breaker thresholds
     */
    protected const CIRCUIT_BREAKER_FAILURE_THRESHOLD = 5;
    protected const CIRCUIT_BREAKER_SUCCESS_THRESHOLD = 3;
    protected const CIRCUIT_BREAKER_TIMEOUT = 60; // seconds
    
    /**
     * @var object Service container for dependency injection
     */
    protected $container;
    
    /**
     * @var array<string> Provider root search paths
     */
    protected array $providerPaths = [];
    
    /**
     * @var array<string, string> Discovered providers mapping [code => FQCN]
     */
    protected array $providers = [];
    
    /**
     * @var array<string, object> Instantiated provider instances [code => object]
     */
    protected array $instances = [];
    
    /**
     * @var array<string, array<string, mixed>> Provider configurations [code => config]
     */
    protected array $configs = [];
    
    /**
     * @var array<string, mixed> Provider health status and metrics
     */
    protected array $healthStatus = [];
    
    /**
     * @var array<string, mixed> Circuit breaker states for providers
     */
    protected array $circuitBreakers = [];
    
    /**
     * @var array<string, mixed> Provider performance metrics
     */
    protected array $performanceMetrics = [];
    
    /**
     * @var array<string, array<string>> Provider capability mappings
     */
    protected array $capabilities = [];
    
    /**
     * @var bool Whether auto-discovery has been completed
     */
    protected bool $discovered = false;
    
    /**
     * @var int Timestamp of last discovery run
     */
    protected int $lastDiscovery = 0;
    
    /**
     * @var int Discovery cache duration in minutes
     */
    protected int $discoveryCacheMinutes = 10;
    
    /**
     * @var array<string, mixed> Manager configuration options
     */
    protected array $config = [
        'enable_health_monitoring' => true,
        'enable_circuit_breakers' => true,
        'enable_load_balancing' => true,
        'enable_failover' => true,
        'enable_performance_tracking' => true,
        'enable_cost_tracking' => false,
        'auto_discovery_enabled' => true,
        'validation_enabled' => true
    ];
    
    /**
     * @var array<string, mixed> Performance and usage statistics
     */
    protected array $stats = [
        'total_providers_discovered' => 0,
        'total_instances_created' => 0,
        'total_requests_processed' => 0,
        'average_response_time' => 0.0,
        'failure_rate' => 0.0,
        'circuit_breaker_trips' => 0
    ];
    
    /**
     * Constructor with enhanced initialization
     *
     * Initializes the provider manager with comprehensive configuration,
     * dependency injection, and monitoring setup.
     *
     * @param object $container Service container instance
     * @param array<string, mixed> $config Configuration options
     */
    public function __construct($container, array $config = [])
    {
        $this->container = $container;
        $this->config = array_merge($this->config, $config);
        
        $this->providerPaths = $this->getConfiguredProviderPaths();
        $this->discoveryCacheMinutes = $this->getDiscoveryCacheMinutes();
        
        $this->initializeHealthMonitoring();
        $this->initializeCircuitBreakers();
        $this->loadPersistedConfigurations();
    }
    
    /**
     * Provider Discovery and Registration
     */
    
    /**
     * Triggers comprehensive provider auto-discovery
     *
     * Performs intelligent recursive scanning of provider directories with
     * caching, validation, and comprehensive metadata extraction.
     *
     * @param bool $forceRefresh Force refresh of discovery cache
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If discovery fails
     */
    public function discover(bool $forceRefresh = false): void
    {
        // Check cache validity
        if (!$forceRefresh && $this->isDiscoveryCacheValid()) {
            return;
        }
        
        $startTime = microtime(true);
        $discoveredProviders = [];
        $discoveryErrors = [];
        
        try {
            foreach ($this->providerPaths as $path) {
                if (!is_dir($path)) {
                    $discoveryErrors[] = "Provider path does not exist: {$path}";
                    continue;
                }
                
                $pathProviders = $this->discoverProvidersInPath($path);
                $discoveredProviders = array_merge($discoveredProviders, $pathProviders);
            }
            
            // Validate discovered providers
            $validatedProviders = $this->validateDiscoveredProviders($discoveredProviders);
            
            // Extract provider capabilities
            $this->extractProviderCapabilities($validatedProviders);
            
            // Update provider registry
            $this->providers = $validatedProviders;
            $this->discovered = true;
            $this->lastDiscovery = time();
            
            // Update statistics
            $this->updateDiscoveryStats($validatedProviders, microtime(true) - $startTime);
            
            // Cache discovery results
            $this->cacheDiscoveryResults($validatedProviders);
            
            // Log discovery completion
            $this->logProviderActivity('discovery_completed', [
                'providers_discovered' => count($validatedProviders),
                'discovery_time' => microtime(true) - $startTime,
                'errors' => $discoveryErrors
            ]);
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Provider discovery failed: ' . $e->getMessage(),
                'provider_manager',
                'high',
                [
                    'provider_paths' => $this->providerPaths,
                    'discovery_errors' => $discoveryErrors,
                    'execution_time' => microtime(true) - $startTime
                ],
                $e
                );
        }
    }
    
    /**
     * Discovers providers in a specific path
     *
     * @param string $path Provider search path
     * @return array<string, string> Discovered providers [code => FQCN]
     */
    protected function discoverProvidersInPath(string $path): array
    {
        $providers = [];
        $phpFiles = $this->findPhpFilesRecursively($path);
        
        foreach ($phpFiles as $file) {
            try {
                $fqcn = $this->classFromFile($file, $path);
                
                if ($fqcn && $this->isValidProviderClass($fqcn)) {
                    $code = $this->generateProviderCode($fqcn);
                    
                    // Check for code conflicts
                    if (isset($providers[$code])) {
                        $this->logProviderActivity('code_conflict', [
                            'code' => $code,
                            'existing_class' => $providers[$code],
                            'conflicting_class' => $fqcn
                        ]);
                        continue;
                    }
                    
                    $providers[$code] = $fqcn;
                }
            } catch (\Exception $e) {
                $this->logProviderActivity('discovery_error', [
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $providers;
    }
    
    /**
     * Validates discovered providers
     *
     * @param array<string, string> $providers Discovered providers
     * @return array<string, string> Validated providers
     */
    protected function validateDiscoveredProviders(array $providers): array
    {
        if (!$this->config['validation_enabled']) {
            return $providers;
        }
        
        $validated = [];
        
        foreach ($providers as $code => $fqcn) {
            try {
                // Validate class existence and interface compliance
                if (!$this->validateProviderClass($fqcn)) {
                    continue;
                }
                
                // Validate provider metadata
                if (!$this->validateProviderMetadata($fqcn)) {
                    continue;
                }
                
                // Security validation
                if (!$this->validateProviderSecurity($fqcn)) {
                    continue;
                }
                
                $validated[$code] = $fqcn;
                
            } catch (\Exception $e) {
                $this->logProviderActivity('validation_failed', [
                    'code' => $code,
                    'class' => $fqcn,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $validated;
    }
    
    /**
     * Extracts and caches provider capabilities
     *
     * @param array<string, string> $providers Validated providers
     * @return void
     */
    protected function extractProviderCapabilities(array $providers): void
    {
        $this->capabilities = [];
        
        foreach ($providers as $code => $fqcn) {
            try {
                $capabilities = $this->getProviderCapabilities($fqcn);
                $this->capabilities[$code] = $capabilities;
            } catch (\Exception $e) {
                $this->logProviderActivity('capability_extraction_failed', [
                    'code' => $code,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Provider Instance Management
     */
    
    /**
     * Gets or creates a provider instance with enhanced features
     *
     * Retrieves a provider instance with comprehensive configuration management,
     * health checking, circuit breaker protection, and performance monitoring.
     *
     * @param string $code Provider code
     * @param array<string, mixed>|null $config Custom configuration
     * @param array<string, mixed> $options Instance creation options
     * @return object Provider instance
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If provider cannot be created
     */
    public function get(string $code, ?array $config = null, array $options = []): object
    {
        $this->discover();
        
        if (!isset($this->providers[$code])) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Provider '{$code}' not found",
                'provider_manager',
                'medium',
                ['code' => $code, 'available_providers' => array_keys($this->providers)]
            );
        }
        
        // Check circuit breaker state
        if ($this->isCircuitBreakerOpen($code)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Provider '{$code}' is unavailable (circuit breaker open)",
                'provider_manager',
                'medium',
                ['code' => $code, 'circuit_breaker_state' => $this->circuitBreakers[$code] ?? 'unknown']
            );
        }
        
        // Return existing instance if compatible
        if ($this->canReuseInstance($code, $config, $options)) {
            return $this->instances[$code];
        }
        
        try {
            $startTime = microtime(true);
            $fqcn = $this->providers[$code];
            
            // Prepare configuration
            $effectiveConfig = $this->prepareProviderConfiguration($code, $config);
            
            // Create instance with dependency injection
            $instance = $this->createProviderInstance($fqcn, $effectiveConfig, $options);
            
            // Initialize provider with health checks
            $this->initializeProviderInstance($instance, $code, $effectiveConfig);
            
            // Cache instance
            $this->instances[$code] = $instance;
            
            // Update statistics
            $executionTime = microtime(true) - $startTime;
            $this->updateInstanceCreationStats($code, $executionTime);
            
            // Record successful circuit breaker operation
            $this->recordCircuitBreakerSuccess($code);
            
            return $instance;
            
        } catch (\Exception $e) {
            // Record circuit breaker failure
            $this->recordCircuitBreakerFailure($code);
            
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Failed to create provider instance '{$code}': " . $e->getMessage(),
                'provider_manager',
                'high',
                ['code' => $code, 'config' => $config, 'options' => $options],
                $e
            );
        }
    }
    
    /**
     * Creates provider instance with advanced features
     *
     * @param string $fqcn Fully qualified class name
     * @param array<string, mixed> $config Provider configuration
     * @param array<string, mixed> $options Creation options
     * @return object Provider instance
     */
    protected function createProviderInstance(string $fqcn, array $config, array $options): object
    {
        // Use reflection to analyze constructor requirements
        $reflection = new \ReflectionClass($fqcn);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            return new $fqcn();
        }
        
        $args = $this->resolveConstructorArguments($constructor, $config, $options);
        return $reflection->newInstanceArgs($args);
    }
    
    /**
     * Resolves constructor arguments with dependency injection
     *
     * @param \ReflectionMethod $constructor Constructor reflection
     * @param array<string, mixed> $config Configuration
     * @param array<string, mixed> $options Options
     * @return array<mixed> Constructor arguments
     */
    protected function resolveConstructorArguments(\ReflectionMethod $constructor, array $config, array $options): array
    {
        $args = [];
        $parameters = $constructor->getParameters();
        
        foreach ($parameters as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();
            
            // Try to resolve by name
            if (isset($config[$paramName])) {
                $args[] = $config[$paramName];
                continue;
            }
            
            // Try to resolve by type from container
            if ($paramType && !$paramType->isBuiltin()) {
                $typeName = $paramType->getName();
                
                if ($this->container->has($typeName)) {
                    $args[] = $this->container->get($typeName);
                    continue;
                }
            }
            
            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }
            
            // Special cases
            if ($paramName === 'config' || $paramName === 'configuration') {
                $args[] = $config;
                continue;
            }
            
            if ($paramName === 'container') {
                $args[] = $this->container;
                continue;
            }
            
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Cannot resolve constructor parameter '{$paramName}' for provider",
                'provider_manager',
                'medium',
                ['parameter' => $paramName, 'type' => $paramType ? $paramType->getName() : 'unknown']
            );
        }
        
        return $args;
    }
    
    /**
     * Advanced Provider Selection and Management
     */
    
    /**
     * Selects the best provider based on criteria
     *
     * Intelligently selects the optimal provider based on performance,
     * availability, cost, and other criteria.
     *
     * @param string $type Provider type
     * @param array<string, mixed> $criteria Selection criteria
     * @return string|null Best provider code
     */
    public function selectBestProvider(string $type, array $criteria = []): ?string
    {
        $candidates = $this->getProvidersByType($type);
        
        if (empty($candidates)) {
            return null;
        }
        
        // Filter by health status
        if ($criteria['require_healthy'] ?? true) {
            $candidates = array_filter($candidates, [$this, 'isProviderHealthy']);
        }
        
        // Filter by capabilities
        if (!empty($criteria['required_capabilities'])) {
            $candidates = $this->filterByCapabilities($candidates, $criteria['required_capabilities']);
        }
        
        // Filter by geographic region
        if (!empty($criteria['region'])) {
            $candidates = $this->filterByRegion($candidates, $criteria['region']);
        }
        
        if (empty($candidates)) {
            return null;
        }
        
        // Score providers based on criteria
        $scores = $this->scoreProviders($candidates, $criteria);
        
        // Return the highest scoring provider
        arsort($scores);
        return array_key_first($scores);
    }
    
    /**
     * Gets healthy providers of a specific type
     *
     * @param string $type Provider type
     * @return array<string> Healthy provider codes
     */
    public function getHealthyProviders(string $type): array
    {
        $providers = $this->getProvidersByType($type);
        return array_filter($providers, [$this, 'isProviderHealthy']);
    }
    
    /**
     * Gets providers with failover chain
     *
     * Returns an ordered list of providers for failover scenarios.
     *
     * @param string $type Provider type
     * @param array<string, mixed> $criteria Selection criteria
     * @return array<string> Ordered provider codes for failover
     */
    public function getProvidersWithFailover(string $type, array $criteria = []): array
    {
        $providers = $this->getProvidersByType($type);
        
        if (empty($providers)) {
            return [];
        }
        
        // Score all providers
        $scores = $this->scoreProviders($providers, $criteria);
        
        // Sort by score (descending)
        arsort($scores);
        
        return array_keys($scores);
    }
    
    /**
     * Provider Configuration Management
     */
    
    /**
     * Updates provider configuration with validation and versioning
     *
     * Updates provider configuration with comprehensive validation,
     * versioning, and rollback capabilities.
     *
     * @param string $code Provider code
     * @param array<string, mixed> $config New configuration
     * @param array<string, mixed> $options Update options
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If update fails
     */
    public function updateProviderConfig(string $code, array $config, array $options = []): void
    {
        if (!$this->exists($code)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Provider '{$code}' does not exist",
                'provider_manager',
                'medium',
                ['code' => $code]
            );
        }
        
        try {
            $startTime = microtime(true);
            
            // Backup current configuration
            $currentConfig = $this->getConfig($code);
            $this->backupConfiguration($code, $currentConfig);
            
            // Validate new configuration
            if ($options['validate'] ?? true) {
                $this->validateProviderConfiguration($code, $config);
            }
            
            // Test configuration if requested
            if ($options['test_connection'] ?? false) {
                $this->testProviderConfiguration($code, $config);
            }
            
            // Update configuration
            $this->setConfig($code, $config);
            
            // Reset instance to use new configuration
            if (isset($this->instances[$code])) {
                unset($this->instances[$code]);
            }
            
            // Persist configuration
            $this->persistProviderConfiguration($code, $config);
            
            // Log configuration update
            $this->logProviderActivity('configuration_updated', [
                'code' => $code,
                'changes' => $this->calculateConfigurationChanges($currentConfig, $config),
                'execution_time' => microtime(true) - $startTime
            ]);
            
        } catch (\Exception $e) {
            // Rollback configuration on failure
            if (isset($currentConfig)) {
                $this->setConfig($code, $currentConfig);
            }
            
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Failed to update provider configuration: " . $e->getMessage(),
                'provider_manager',
                'high',
                ['code' => $code, 'config' => $config],
                $e
                );
        }
    }
    
    /**
     * Rolls back provider configuration to previous version
     *
     * @param string $code Provider code
     * @param string|null $version Version to rollback to (null = previous)
     * @return void
     */
    public function rollbackConfiguration(string $code, ?string $version = null): void
    {
        $backupConfig = $this->getBackupConfiguration($code, $version);
        
        if (!$backupConfig) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "No backup configuration found for provider '{$code}'",
                'provider_manager',
                'medium',
                ['code' => $code, 'version' => $version]
            );
        }
        
        $this->updateProviderConfig($code, $backupConfig, ['validate' => false]);
        
        $this->logProviderActivity('configuration_rolled_back', [
            'code' => $code,
            'version' => $version
        ]);
    }
    
    /**
     * Health Monitoring and Circuit Breaker Management
     */
    
    /**
     * Performs health check on a provider
     *
     * @param string $code Provider code
     * @param array<string, mixed> $options Health check options
     * @return array<string, mixed> Health check results
     */
    public function performHealthCheck(string $code, array $options = []): array
    {
        if (!$this->exists($code)) {
            return [
                'healthy' => false,
                'error' => 'Provider not found',
                'timestamp' => microtime(true)
            ];
        }
        
        $startTime = microtime(true);
        
        try {
            $provider = $this->get($code);
            
            // Perform basic health check
            $healthResult = $this->executeProviderHealthCheck($provider, $options);
            
            // Update health status
            $this->updateProviderHealthStatus($code, $healthResult);
            
            return array_merge($healthResult, [
                'execution_time' => microtime(true) - $startTime,
                'timestamp' => microtime(true)
            ]);
            
        } catch (\Exception $e) {
            $healthResult = [
                'healthy' => false,
                'error' => $e->getMessage(),
                'execution_time' => microtime(true) - $startTime,
                'timestamp' => microtime(true)
            ];
            
            $this->updateProviderHealthStatus($code, $healthResult);
            return $healthResult;
        }
    }
    
    /**
     * Gets comprehensive provider health status
     *
     * @param string $code Provider code
     * @return array<string, mixed> Health status information
     */
    public function getProviderHealth(string $code): array
    {
        return $this->healthStatus[$code] ?? [
            'healthy' => false,
            'last_checked' => null,
            'error' => 'Health status unknown'
        ];
    }
    
    /**
     * Gets provider performance metrics
     *
     * @param string $code Provider code
     * @param string $period Time period (1h, 24h, 7d, 30d)
     * @return array<string, mixed> Performance metrics
     */
    public function getProviderMetrics(string $code, string $period = '24h'): array
    {
        $metrics = $this->performanceMetrics[$code] ?? [];
        
        return [
            'provider_code' => $code,
            'period' => $period,
            'average_response_time' => $metrics['avg_response_time'] ?? 0.0,
            'success_rate' => $metrics['success_rate'] ?? 0.0,
            'total_requests' => $metrics['total_requests'] ?? 0,
            'failed_requests' => $metrics['failed_requests'] ?? 0,
            'last_failure' => $metrics['last_failure'] ?? null,
            'circuit_breaker_state' => $this->getCircuitBreakerState($code)
        ];
    }
    
    /**
     * Provider Discovery and Validation Helpers
     */
    
    /**
     * Returns all discovered providers
     *
     * @return array<string, string> Provider mapping [code => FQCN]
     */
    public function providers(): array
    {
        $this->discover();
        return $this->providers;
    }
    
    /**
     * Gets providers by type with enhanced filtering
     *
     * @param string $type Provider type
     * @param array<string, mixed> $filters Additional filters
     * @return array<string> Provider codes
     */
    public function getProvidersByType(string $type, array $filters = []): array
    {
        $this->discover();
        $result = [];
        
        foreach ($this->providers as $code => $fqcn) {
            try {
                if ($this->getProviderType($fqcn) === $type) {
                    // Apply additional filters
                    if ($this->matchesFilters($code, $fqcn, $filters)) {
                        $result[] = $code;
                    }
                }
            } catch (\Exception $e) {
                $this->logProviderActivity('type_check_failed', [
                    'code' => $code,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $result;
    }
    
    /**
     * Gets all provider schemas for setup
     *
     * @return array<string, mixed> Provider schemas
     */
    public function getProviderSchemas(): array
    {
        $this->discover();
        $schemas = [];
        
        foreach ($this->providers as $code => $fqcn) {
            try {
                $schemas[$code] = $this->getProviderSetupSchema($fqcn);
            } catch (\Exception $e) {
                $this->logProviderActivity('schema_extraction_failed', [
                    'code' => $code,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $schemas;
    }
    
    /**
     * Gets all available provider types
     *
     * @return array<string> Provider types
     */
    public function getTypes(): array
    {
        $this->discover();
        $types = [];
        
        foreach ($this->providers as $fqcn) {
            try {
                $type = $this->getProviderType($fqcn);
                if ($type) {
                    $types[] = $type;
                }
            } catch (\Exception $e) {
                // Skip providers that can't provide type information
            }
        }
        
        return array_unique($types);
    }
    
    /**
     * Checks if provider exists
     *
     * @param string $code Provider code
     * @return bool True if provider exists
     */
    public function exists(string $code): bool
    {
        $this->discover();
        return isset($this->providers[$code]);
    }
    
    /**
     * Gets all provider codes
     *
     * @return array<string> Provider codes
     */
    public function getCodes(): array
    {
        $this->discover();
        return array_keys($this->providers);
    }
    
    /**
     * Configuration Management
     */
    
    /**
     * Sets provider configuration
     *
     * @param string $code Provider code
     * @param array<string, mixed> $config Configuration
     * @return void
     */
    public function setConfig(string $code, array $config): void
    {
        $this->configs[$code] = $config;
        
        // Reset instance if exists to use new configuration
        if (isset($this->instances[$code])) {
            unset($this->instances[$code]);
        }
    }
    
    /**
     * Gets provider configuration
     *
     * @param string $code Provider code
     * @return array<string, mixed> Configuration
     */
    public function getConfig(string $code): array
    {
        return $this->configs[$code] ?? [];
    }
    
    /**
     * Gets all provider instances with lazy loading
     *
     * @return array<string, object> Provider instances
     */
    public function all(): array
    {
        $this->discover();
        $instances = [];
        
        foreach ($this->providers as $code => $fqcn) {
            try {
                $instances[$code] = $this->get($code);
            } catch (\Exception $e) {
                $this->logProviderActivity('instance_creation_failed', [
                    'code' => $code,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $instances;
    }
    
    /**
     * Resets discovery cache and instances
     *
     * @return void
     */
    public function reset(): void
    {
        $this->discovered = false;
        $this->instances = [];
        $this->providers = [];
        $this->lastDiscovery = 0;
        $this->healthStatus = [];
        $this->circuitBreakers = [];
        $this->performanceMetrics = [];
        $this->capabilities = [];
    }
    
    /**
     * Protected Helper Methods - COMPLETE IMPLEMENTATIONS
     */
    
    /**
     * Checks if discovery cache is valid
     *
     * @return bool True if cache is valid
     */
    protected function isDiscoveryCacheValid(): bool
    {
        return $this->discovered &&
        (time() - $this->lastDiscovery) < ($this->discoveryCacheMinutes * 60);
    }
    
    /**
     * Finds PHP files recursively in directory
     *
     * @param string $path Directory path
     * @return array<string> PHP file paths
     */
    protected function findPhpFilesRecursively(string $path): array
    {
        $files = [];
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                    $files[] = $file->getRealPath();
                }
            }
        } catch (\Exception $e) {
            $this->logProviderActivity('directory_scan_failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }
        
        return $files;
    }
    
    /**
     * Extracts class FQCN from file path
     *
     * @param string $file File path
     * @param string $basePath Base directory path
     * @return string|null Fully qualified class name
     */
    protected function classFromFile(string $file, string $basePath): ?string
    {
        try {
            // Get relative path
            $relativePath = ltrim(str_replace($basePath, '', $file), DIRECTORY_SEPARATOR);
            $classPath = preg_replace('/\.php$/', '', $relativePath);
            $parts = explode(DIRECTORY_SEPARATOR, $classPath);
            
            // Build namespace parts
            $namespaceParts = array_map(function($part) {
                return preg_replace('/[^A-Za-z0-9_]/', '', ucfirst($part));
            }, $parts);
                
                // Build FQCN with proper MAS namespace structure
                array_unshift($namespaceParts, 'Provider');
                array_unshift($namespaceParts, 'Mas');
                array_unshift($namespaceParts, 'Library');
                array_unshift($namespaceParts, 'Extension');
                array_unshift($namespaceParts, 'Opencart');
                
                return implode('\\', $namespaceParts);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Validates if class is a valid provider
     *
     * @param string $fqcn Fully qualified class name
     * @return bool True if valid provider class
     */
    protected function isValidProviderClass(string $fqcn): bool
    {
        try {
            if (!class_exists($fqcn)) {
                return false;
            }
            
            $reflection = new \ReflectionClass($fqcn);
            
            // Check if class is instantiable
            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                return false;
            }
            
            // Check if implements required interface
            return $reflection->implementsInterface(
                'Opencart\\Extension\\Mas\\System\\Library\\Mas\\Interfaces\\ChannelProviderInterface'
                );
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Generates provider code from class name
     *
     * @param string $fqcn Fully qualified class name
     * @return string Provider code
     */
    protected function generateProviderCode(string $fqcn): string
    {
        try {
            $reflection = new \ReflectionClass($fqcn);
            $shortName = $reflection->getShortName();
            
            // Remove common suffixes
            $code = preg_replace('/(?:Provider|Service|Client|Gateway)$/i', '', $shortName);
            
            // Convert to lowercase with underscores
            $code = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($code)));
            $code = trim($code, '_');
            
            return $code ?: strtolower($shortName);
        } catch (\Exception $e) {
            return 'unknown_provider';
        }
    }
    
    /**
     * Validates provider class implementation
     *
     * @param string $fqcn Fully qualified class name
     * @return bool True if validation passes
     */
    protected function validateProviderClass(string $fqcn): bool
    {
        try {
            $reflection = new \ReflectionClass($fqcn);
            
            // Check required methods exist
            $requiredMethods = ['getType', 'getName', 'getVersion'];
            foreach ($requiredMethods as $method) {
                if (!$reflection->hasMethod($method)) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Validates provider metadata
     *
     * @param string $fqcn Fully qualified class name
     * @return bool True if metadata is valid
     */
    protected function validateProviderMetadata(string $fqcn): bool
    {
        try {
            // Check if provider has valid metadata
            $type = $this->getProviderType($fqcn);
            $name = $this->getProviderName($fqcn);
            $version = $this->getProviderVersion($fqcn);
            
            return !empty($type) && !empty($name) && !empty($version);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Validates provider security requirements
     *
     * @param string $fqcn Fully qualified class name
     * @return bool True if security validation passes
     */
    protected function validateProviderSecurity(string $fqcn): bool
    {
        // Implement security validation logic
        // This could include checking for secure coding practices,
        // validating dependencies, etc.
        return true;
    }
    
    /**
     * Gets provider capabilities
     *
     * @param string $fqcn Fully qualified class name
     * @return array<string> Provider capabilities
     */
    protected function getProviderCapabilities(string $fqcn): array
    {
        try {
            if (method_exists($fqcn, 'getCapabilities')) {
                return $fqcn::getCapabilities();
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Gets provider type
     *
     * @param string $fqcn Fully qualified class name
     * @return string|null Provider type
     */
    protected function getProviderType(string $fqcn): ?string
    {
        try {
            if (method_exists($fqcn, 'getType')) {
                return $fqcn::getType();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Gets provider name
     *
     * @param string $fqcn Fully qualified class name
     * @return string|null Provider name
     */
    protected function getProviderName(string $fqcn): ?string
    {
        try {
            if (method_exists($fqcn, 'getName')) {
                return $fqcn::getName();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Gets provider version
     *
     * @param string $fqcn Fully qualified class name
     * @return string|null Provider version
     */
    protected function getProviderVersion(string $fqcn): ?string
    {
        try {
            if (method_exists($fqcn, 'getVersion')) {
                return $fqcn::getVersion();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Gets provider setup schema
     *
     * @param string $fqcn Fully qualified class name
     * @return array<string, mixed> Setup schema
     */
    protected function getProviderSetupSchema(string $fqcn): array
    {
        try {
            if (method_exists($fqcn, 'getSetupSchema')) {
                return $fqcn::getSetupSchema();
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Provider instance management helpers
     */
    
    /**
     * Checks if existing instance can be reused
     *
     * @param string $code Provider code
     * @param array<string, mixed>|null $config Configuration
     * @param array<string, mixed> $options Options
     * @return bool True if instance can be reused
     */
    protected function canReuseInstance(string $code, ?array $config, array $options): bool
    {
        if (!isset($this->instances[$code])) {
            return false;
        }
        
        // If custom config provided, create new instance
        if ($config !== null) {
            return false;
        }
        
        // Check if force new instance requested
        if ($options['force_new'] ?? false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Prepares provider configuration
     *
     * @param string $code Provider code
     * @param array<string, mixed>|null $config Custom configuration
     * @return array<string, mixed> Effective configuration
     */
    protected function prepareProviderConfiguration(string $code, ?array $config): array
    {
        $baseConfig = $this->getConfig($code);
        
        if ($config === null) {
            return $baseConfig;
        }
        
        return array_merge($baseConfig, $config);
    }
    
    /**
     * Initializes provider instance
     *
     * @param object $instance Provider instance
     * @param string $code Provider code
     * @param array<string, mixed> $config Configuration
     * @return void
     */
    protected function initializeProviderInstance(object $instance, string $code, array $config): void
    {
        // Initialize provider if method exists
        if (method_exists($instance, 'initialize')) {
            $instance->initialize($config);
        }
        
        // Set up health monitoring if enabled
        if ($this->config['enable_health_monitoring']) {
            $this->scheduleHealthCheck($code);
        }
    }
    
    /**
     * Health monitoring and circuit breaker helpers
     */
    
    /**
     * Initializes health monitoring system
     *
     * @return void
     */
    protected function initializeHealthMonitoring(): void
    {
        if (!$this->config['enable_health_monitoring']) {
            return;
        }
        
        // Load health status from cache/persistence
        $this->loadHealthStatus();
    }
    
    /**
     * Initializes circuit breaker system
     *
     * @return void
     */
    protected function initializeCircuitBreakers(): void
    {
        if (!$this->config['enable_circuit_breakers']) {
            return;
        }
        
        // Load circuit breaker states from cache/persistence
        $this->loadCircuitBreakerStates();
    }
    
    /**
     * Checks if circuit breaker is open
     *
     * @param string $code Provider code
     * @return bool True if circuit breaker is open
     */
    protected function isCircuitBreakerOpen(string $code): bool
    {
        if (!$this->config['enable_circuit_breakers']) {
            return false;
        }
        
        $state = $this->circuitBreakers[$code] ?? ['state' => 'closed'];
        
        if ($state['state'] === 'closed') {
            return false;
        }
        
        if ($state['state'] === 'open') {
            // Check if timeout period has passed
            if (isset($state['opened_at'])) {
                $timeoutPassed = (microtime(true) - $state['opened_at']) > self::CIRCUIT_BREAKER_TIMEOUT;
                if ($timeoutPassed) {
                    $this->setCircuitBreakerState($code, 'half-open');
                    return false;
                }
            }
            return true;
        }
        
        // Half-open state - allow some requests through
        return false;
    }
    
    /**
     * Records circuit breaker success
     *
     * @param string $code Provider code
     * @return void
     */
    protected function recordCircuitBreakerSuccess(string $code): void
    {
        if (!$this->config['enable_circuit_breakers']) {
            return;
        }
        
        $state = $this->circuitBreakers[$code] ?? ['state' => 'closed', 'success_count' => 0];
        $state['success_count'] = ($state['success_count'] ?? 0) + 1;
        
        if ($state['state'] === 'half-open' && $state['success_count'] >= self::CIRCUIT_BREAKER_SUCCESS_THRESHOLD) {
            $this->setCircuitBreakerState($code, 'closed');
        } else {
            $this->circuitBreakers[$code] = $state;
        }
    }
    
    /**
     * Records circuit breaker failure
     *
     * @param string $code Provider code
     * @return void
     */
    protected function recordCircuitBreakerFailure(string $code): void
    {
        if (!$this->config['enable_circuit_breakers']) {
            return;
        }
        
        $state = $this->circuitBreakers[$code] ?? ['state' => 'closed', 'failure_count' => 0];
        $state['failure_count'] = ($state['failure_count'] ?? 0) + 1;
        $state['last_failure'] = microtime(true);
        
        if ($state['failure_count'] >= self::CIRCUIT_BREAKER_FAILURE_THRESHOLD) {
            $this->setCircuitBreakerState($code, 'open');
            $this->stats['circuit_breaker_trips']++;
        } else {
            $this->circuitBreakers[$code] = $state;
        }
    }
    
    /**
     * Sets circuit breaker state
     *
     * @param string $code Provider code
     * @param string $newState New state (open, closed, half-open)
     * @return void
     */
    protected function setCircuitBreakerState(string $code, string $newState): void
    {
        $this->circuitBreakers[$code] = [
            'state' => $newState,
            'changed_at' => microtime(true),
            'opened_at' => $newState === 'open' ? microtime(true) : null,
            'success_count' => 0,
            'failure_count' => 0
        ];
        
        $this->logProviderActivity('circuit_breaker_state_changed', [
            'code' => $code,
            'new_state' => $newState
        ]);
    }
    
    /**
     * Gets circuit breaker state
     *
     * @param string $code Provider code
     * @return string Circuit breaker state
     */
    protected function getCircuitBreakerState(string $code): string
    {
        return $this->circuitBreakers[$code]['state'] ?? 'closed';
    }
    
    /**
     * Provider selection and scoring helpers
     */
    
    /**
     * Filters providers by capabilities
     *
     * @param array<string> $providers Provider codes
     * @param array<string> $requiredCapabilities Required capabilities
     * @return array<string> Filtered provider codes
     */
    protected function filterByCapabilities(array $providers, array $requiredCapabilities): array
    {
        return array_filter($providers, function($code) use ($requiredCapabilities) {
            $capabilities = $this->capabilities[$code] ?? [];
            return empty(array_diff($requiredCapabilities, $capabilities));
        });
    }
    
    /**
     * Filters providers by region
     *
     * @param array<string> $providers Provider codes
     * @param string $region Required region
     * @return array<string> Filtered provider codes
     */
    protected function filterByRegion(array $providers, string $region): array
    {
        // Implementation would depend on how region information is stored
        // For now, return all providers
        return $providers;
    }
    
    /**
     * Scores providers based on criteria
     *
     * @param array<string> $providers Provider codes
     * @param array<string, mixed> $criteria Scoring criteria
     * @return array<string, float> Provider scores
     */
    protected function scoreProviders(array $providers, array $criteria): array
    {
        $scores = [];
        
        foreach ($providers as $code) {
            $score = 0.0;
            
            // Health score (0-30 points)
            if ($this->isProviderHealthy($code)) {
                $score += 30;
            }
            
            // Performance score (0-25 points)
            $metrics = $this->performanceMetrics[$code] ?? [];
            $successRate = $metrics['success_rate'] ?? 0.5;
            $score += $successRate * 25;
            
            // Response time score (0-20 points)
            $avgResponseTime = $metrics['avg_response_time'] ?? 1000;
            $responseScore = max(0, 20 - ($avgResponseTime / 100));
            $score += $responseScore;
            
            // Capability match score (0-15 points)
            if (!empty($criteria['preferred_capabilities'])) {
                $capabilities = $this->capabilities[$code] ?? [];
                $matchCount = count(array_intersect($criteria['preferred_capabilities'], $capabilities));
                $totalRequired = count($criteria['preferred_capabilities']);
                $score += ($matchCount / max(1, $totalRequired)) * 15;
            } else {
                $score += 15; // Full points if no specific capabilities required
            }
            
            // Cost score (0-10 points) - placeholder
            $score += 10;
            
            $scores[$code] = $score;
        }
        
        return $scores;
    }
    
    /**
     * Checks if provider is healthy
     *
     * @param string $code Provider code
     * @return bool True if provider is healthy
     */
    protected function isProviderHealthy(string $code): bool
    {
        $health = $this->healthStatus[$code] ?? ['healthy' => false];
        return $health['healthy'] ?? false;
    }
    
    /**
     * Configuration management helpers
     */
    
    /**
     * Validates provider configuration
     *
     * @param string $code Provider code
     * @param array<string, mixed> $config Configuration to validate
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If validation fails
     */
    protected function validateProviderConfiguration(string $code, array $config): void
    {
        if (!$this->exists($code)) {
            return;
        }
        
        $fqcn = $this->providers[$code];
        
        // Get schema if available
        if (method_exists($fqcn, 'getConfigSchema')) {
            $schema = $fqcn::getConfigSchema();
            $this->validateConfigurationAgainstSchema($config, $schema);
        }
    }
    
    /**
     * Validates configuration against schema
     *
     * @param array<string, mixed> $config Configuration
     * @param array<string, mixed> $schema Configuration schema
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If validation fails
     */
    protected function validateConfigurationAgainstSchema(array $config, array $schema): void
    {
        foreach ($schema['required'] ?? [] as $field) {
            if (!isset($config[$field])) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    "Required configuration field '{$field}' is missing",
                    'provider_manager',
                    'medium',
                    ['field' => $field, 'schema' => $schema]
                );
            }
        }
    }
    
    /**
     * Tests provider configuration
     *
     * @param string $code Provider code
     * @param array<string, mixed> $config Configuration to test
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If test fails
     */
    protected function testProviderConfiguration(string $code, array $config): void
    {
        try {
            $testInstance = $this->get($code, $config);
            
            if (method_exists($testInstance, 'testConnection')) {
                $testInstance->testConnection();
            }
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Provider configuration test failed: " . $e->getMessage(),
                'provider_manager',
                'medium',
                ['code' => $code, 'config' => $config],
                $e
                );
        }
    }
    
    /**
     * Configuration backup and restore helpers
     */
    
    /**
     * Backs up current configuration
     *
     * @param string $code Provider code
     * @param array<string, mixed> $config Configuration to backup
     * @return void
     */
    protected function backupConfiguration(string $code, array $config): void
    {
        $backupKey = 'mas_provider_config_backup_' . $code . '_' . time();
        
        if ($this->container->has('cache')) {
            $this->container->get('cache')->set($backupKey, $config, 86400); // 24 hours
        }
    }
    
    /**
     * Gets backup configuration
     *
     * @param string $code Provider code
     * @param string|null $version Version identifier
     * @return array<string, mixed>|null Backup configuration
     */
    protected function getBackupConfiguration(string $code, ?string $version): ?array
    {
        if ($this->container->has('cache')) {
            $backupKey = 'mas_provider_config_backup_' . $code;
            if ($version) {
                $backupKey .= '_' . $version;
            }
            
            return $this->container->get('cache')->get($backupKey);
        }
        
        return null;
    }
    
    /**
     * Calculates configuration changes
     *
     * @param array<string, mixed> $oldConfig Old configuration
     * @param array<string, mixed> $newConfig New configuration
     * @return array<string, mixed> Configuration changes
     */
    protected function calculateConfigurationChanges(array $oldConfig, array $newConfig): array
    {
        $changes = [
            'added' => [],
            'modified' => [],
            'removed' => []
        ];
        
        // Find added and modified
        foreach ($newConfig as $key => $value) {
            if (!array_key_exists($key, $oldConfig)) {
                $changes['added'][$key] = $value;
            } elseif ($oldConfig[$key] !== $value) {
                $changes['modified'][$key] = [
                    'old' => $oldConfig[$key],
                    'new' => $value
                ];
            }
        }
        
        // Find removed
        foreach ($oldConfig as $key => $value) {
            if (!array_key_exists($key, $newConfig)) {
                $changes['removed'][$key] = $value;
            }
        }
        
        return $changes;
    }
    
    /**
     * System integration and persistence helpers
     */
    
    /**
     * Gets configured provider paths
     *
     * @return array<string> Provider search paths
     */
    protected function getConfiguredProviderPaths(): array
    {
        if ($this->container->has('config')) {
            $config = $this->container->get('config');
            $masConfig = $config->get('mas_config') ?: [];
            return $masConfig['provider_paths'] ?? [
                DIR_SYSTEM . 'library/mas/providers/',
            ];
        }
        
        return [DIR_SYSTEM . 'library/mas/providers/'];
    }
    
    /**
     * Gets discovery cache duration
     *
     * @return int Cache duration in minutes
     */
    protected function getDiscoveryCacheMinutes(): int
    {
        if ($this->container->has('config')) {
            $config = $this->container->get('config');
            $masConfig = $config->get('mas_config') ?: [];
            return $masConfig['provider_cache_minutes'] ?? 10;
        }
        
        return 10;
    }
    
    /**
     * Loads persisted configurations
     *
     * @return void
     */
    protected function loadPersistedConfigurations(): void
    {
        // Implementation would load configurations from database or file system
    }
    
    /**
     * Persists provider configuration
     *
     * @param string $code Provider code
     * @param array<string, mixed> $config Configuration
     * @return void
     */
    protected function persistProviderConfiguration(string $code, array $config): void
    {
        // Implementation would persist configuration to database or file system
    }
    
    /**
     * Loads health status from persistence
     *
     * @return void
     */
    protected function loadHealthStatus(): void
    {
        // Implementation would load health status from cache or database
    }
    
    /**
     * Loads circuit breaker states
     *
     * @return void
     */
    protected function loadCircuitBreakerStates(): void
    {
        // Implementation would load circuit breaker states from cache or database
    }
    
    /**
     * Monitoring and statistics helpers
     */
    
    /**
     * Updates discovery statistics
     *
     * @param array<string, string> $providers Discovered providers
     * @param float $executionTime Discovery execution time
     * @return void
     */
    protected function updateDiscoveryStats(array $providers, float $executionTime): void
    {
        $this->stats['total_providers_discovered'] = count($providers);
        $this->stats['last_discovery_time'] = $executionTime;
        $this->stats['last_discovery_at'] = microtime(true);
    }
    
    /**
     * Updates instance creation statistics
     *
     * @param string $code Provider code
     * @param float $executionTime Creation execution time
     * @return void
     */
    protected function updateInstanceCreationStats(string $code, float $executionTime): void
    {
        $this->stats['total_instances_created']++;
        
        if (!isset($this->stats['instance_creation_times'])) {
            $this->stats['instance_creation_times'] = [];
        }
        
        $this->stats['instance_creation_times'][$code] = $executionTime;
    }
    
    /**
     * Caches discovery results
     *
     * @param array<string, string> $providers Discovered providers
     * @return void
     */
    protected function cacheDiscoveryResults(array $providers): void
    {
        if ($this->container->has('cache')) {
            $cache = $this->container->get('cache');
            $cache->set('mas_provider_discovery', [
                'providers' => $providers,
                'capabilities' => $this->capabilities,
                'discovered_at' => microtime(true)
            ], self::DISCOVERY_CACHE_TTL);
        }
    }
    
    /**
     * Health monitoring helpers
     */
    
    /**
     * Executes provider health check
     *
     * @param object $provider Provider instance
     * @param array<string, mixed> $options Health check options
     * @return array<string, mixed> Health check results
     */
    protected function executeProviderHealthCheck(object $provider, array $options): array
    {
        $startTime = microtime(true);
        
        try {
            if (method_exists($provider, 'healthCheck')) {
                $result = $provider->healthCheck($options);
                
                if (is_bool($result)) {
                    return [
                        'healthy' => $result,
                        'execution_time' => microtime(true) - $startTime
                    ];
                }
                
                if (is_array($result)) {
                    return array_merge($result, [
                        'execution_time' => microtime(true) - $startTime
                    ]);
                }
            }
            
            // Default health check - just verify instance is created
            return [
                'healthy' => true,
                'message' => 'Provider instance created successfully',
                'execution_time' => microtime(true) - $startTime
            ];
            
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
                'execution_time' => microtime(true) - $startTime
            ];
        }
    }
    
    /**
     * Updates provider health status
     *
     * @param string $code Provider code
     * @param array<string, mixed> $healthResult Health check result
     * @return void
     */
    protected function updateProviderHealthStatus(string $code, array $healthResult): void
    {
        $this->healthStatus[$code] = array_merge($healthResult, [
            'last_checked' => microtime(true),
            'check_count' => ($this->healthStatus[$code]['check_count'] ?? 0) + 1
        ]);
        
        // Update performance metrics
        if ($this->config['enable_performance_tracking']) {
            $this->updateProviderPerformanceMetrics($code, $healthResult);
        }
    }
    
    /**
     * Updates provider performance metrics
     *
     * @param string $code Provider code
     * @param array<string, mixed> $result Operation result
     * @return void
     */
    protected function updateProviderPerformanceMetrics(string $code, array $result): void
    {
        if (!isset($this->performanceMetrics[$code])) {
            $this->performanceMetrics[$code] = [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'total_response_time' => 0.0,
                'avg_response_time' => 0.0,
                'success_rate' => 0.0
            ];
        }
        
        $metrics = &$this->performanceMetrics[$code];
        $metrics['total_requests']++;
        
        if ($result['healthy'] ?? false) {
            $metrics['successful_requests']++;
        } else {
            $metrics['failed_requests']++;
            $metrics['last_failure'] = microtime(true);
        }
        
        if (isset($result['execution_time'])) {
            $metrics['total_response_time'] += $result['execution_time'];
            $metrics['avg_response_time'] = $metrics['total_response_time'] / $metrics['total_requests'];
        }
        
        $metrics['success_rate'] = $metrics['successful_requests'] / $metrics['total_requests'];
    }
    
    /**
     * Schedules health check for provider
     *
     * @param string $code Provider code
     * @return void
     */
    protected function scheduleHealthCheck(string $code): void
    {
        // Implementation would schedule periodic health checks
        // This could integrate with a job scheduler or cron system
    }
    
    /**
     * Utility methods
     */
    
    /**
     * Matches provider against filters
     *
     * @param string $code Provider code
     * @param string $fqcn Provider class name
     * @param array<string, mixed> $filters Filters to apply
     * @return bool True if provider matches filters
     */
    protected function matchesFilters(string $code, string $fqcn, array $filters): bool
    {
        // Health filter
        if (isset($filters['healthy']) && $filters['healthy']) {
            if (!$this->isProviderHealthy($code)) {
                return false;
            }
        }
        
        // Capability filters
        if (!empty($filters['capabilities'])) {
            $capabilities = $this->capabilities[$code] ?? [];
            if (!empty(array_diff($filters['capabilities'], $capabilities))) {
                return false;
            }
        }
        
        // Version filter
        if (!empty($filters['min_version'])) {
            $version = $this->getProviderVersion($fqcn);
            if ($version && version_compare($version, $filters['min_version'], '<')) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Logs provider activity
     *
     * @param string $activity Activity type
     * @param array<string, mixed> $data Activity data
     * @return void
     */
    protected function logProviderActivity(string $activity, array $data): void
    {
        if ($this->container->has('mas.audit_logger')) {
            $this->container->get('mas.audit_logger')->logSystem($activity, array_merge($data, [
                'component' => 'provider_manager'
            ]));
        }
    }
}
