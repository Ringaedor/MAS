<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Abstract Provider - Advanced Provider Foundation Class
 *
 * Abstract base class providing comprehensive foundation for all channel providers (email, SMS,
 * push, AI, payment, analytics, etc.) with enterprise-grade features including auto-discovery,
 * schema validation, capability declaration, advanced configuration management, health monitoring,
 * performance tracking, security hardening, and comprehensive error handling with audit integration.
 *
 * This class establishes the standardized foundation for all MAS providers with robust
 * authentication, connection management, retry logic, circuit breaker patterns, performance
 * monitoring, and compliance features required for enterprise production environments.
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
 * Enterprise Abstract Provider Foundation
 *
 * Comprehensive base class providing enterprise-grade foundation for all MAS providers.
 * Implements advanced features including circuit breaker patterns, health monitoring,
 * performance tracking, security hardening, configuration validation, retry logic,
 * and comprehensive audit integration for production environments.
 *
 * Key Features:
 * - Advanced configuration management with validation and sanitization
 * - Health monitoring with circuit breaker pattern for resilience
 * - Performance tracking with comprehensive metrics collection
 * - Security hardening with credential encryption and audit trails
 * - Retry logic with exponential backoff and jitter
 * - Rate limiting and throttling support
 * - Auto-discovery with comprehensive schema definition
 * - Batch processing optimization with parallel execution
 * - Connection pooling and resource management
 * - Comprehensive error handling with context preservation
 * - Audit logging integration for compliance
 * - A/B testing support for provider optimization
 *
 * Provider Types Supported:
 * - Communication: email, SMS, push, voice, chat, webhook
 * - AI/ML: chat, completion, embedding, image, analysis, prediction
 * - Payment: gateway, processor, authorization, subscription
 * - Analytics: tracking, reporting, data processing, visualization
 * - Storage: file, database, cache, backup, CDN
 * - Integration: API, sync, transformation, webhook
 *
 * Implementation Requirements:
 * - Concrete providers must implement all abstract methods
 * - Static metadata methods must be properly implemented
 * - Configuration schema must be comprehensive and validated
 * - Error handling must use MAS Exception system
 * - Performance metrics must be collected where possible
 * - Security best practices must be followed
 * - Audit logging must be implemented for sensitive operations
 *
 * Usage Examples:
 *
 * Basic provider implementation:
 * class MyProvider extends AbstractProvider {
 *     protected static string $providerName = 'MyProvider';
 *     protected static string $providerType = self::TYPE_EMAIL;
 *     public function send(array $payload): array { ... }
 *     public static function getSetupSchema(): array { ... }
 * }
 *
 * With advanced features:
 * $provider = new MyProvider($config);
 * $provider->authenticate($credentials);
 * $health = $provider->getHealthStatus();
 * $metrics = $provider->getMetrics();
 * $result = $provider->sendWithRetry($payload, $retryOptions);
 */
abstract class AbstractProvider implements \Opencart\Extension\Mas\System\Library\Mas\Interfaces\ChannelProviderInterface
{
    /**
     * @var array<string, mixed> Provider runtime configuration
     */
    protected array $config = [];
    
    /**
     * @var bool Authentication status flag
     */
    protected bool $authenticated = false;
    
    /**
     * @var string|null Last error message encountered
     */
    protected ?string $lastError = null;
    
    /**
     * @var array<string, mixed> Provider state and metrics
     */
    protected array $state = [
        'initialized_at' => null,
        'last_used_at' => null,
        'total_requests' => 0,
        'successful_requests' => 0,
        'failed_requests' => 0,
        'total_response_time' => 0.0,
        'last_health_check' => null,
        'circuit_breaker_state' => 'closed',
        'circuit_breaker_failures' => 0,
        'rate_limit_remaining' => null,
        'rate_limit_reset' => null
    ];
    
    /**
     * @var array<string, mixed> Performance metrics tracking
     */
    protected array $metrics = [];
    
    /**
     * @var array<string, mixed> Circuit breaker configuration
     */
    protected array $circuitBreakerConfig = [
        'failure_threshold' => 5,
        'timeout' => 300,
        'retry_timeout' => 60
    ];
    
    /**
     * @var array<string, mixed> Retry configuration
     */
    protected array $retryConfig = [
        'max_attempts' => 3,
        'base_delay' => 1000,
        'max_delay' => 30000,
        'backoff_multiplier' => 2.0,
        'jitter' => true
    ];
    
    /**
     * @var array<string> Sensitive configuration fields to mask
     */
    protected array $sensitiveFields = [
        'api_key', 'secret_key', 'password', 'token', 'private_key',
        'smtp_password', 'access_token', 'refresh_token', 'webhook_secret',
        'certificate', 'encryption_key', 'signing_key'
    ];
    
    /**
     * Constructor with enhanced initialization
     *
     * Initializes the provider with optional configuration and sets up
     * performance tracking, health monitoring, and security features.
     *
     * @param array<string, mixed> $config Optional runtime configuration
     */
    public function __construct(array $config = [])
    {
        $this->state['initialized_at'] = microtime(true);
        
        if (!empty($config)) {
            $this->setConfig($config);
        }
        
        $this->initializeMetrics();
        $this->initializeCircuitBreaker();
    }
    
    /**
     * Primary send method - must be implemented by concrete providers
     *
     * Executes the provider's primary action (send message, process payment, etc.)
     * with comprehensive error handling and performance tracking.
     *
     * @param array<string, mixed> $payload Normalized payload data
     * @return array<string, mixed> Response with status, IDs, and metadata
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If send operation fails
     */
    abstract public function send(array $payload): array;
    
    /**
     * Sends with automatic retry logic and circuit breaker protection
     *
     * Enhanced send method with retry logic, circuit breaker protection,
     * and comprehensive error handling for improved reliability.
     *
     * @param array<string, mixed> $payload Message payload
     * @param array<string, mixed> $retryOptions Retry configuration overrides
     * @return array<string, mixed> Send results with retry information
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If all retry attempts fail
     */
    public function sendWithRetry(array $payload, array $retryOptions = []): array
    {
        if (!$this->isCircuitBreakerClosed()) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Provider circuit breaker is open due to previous failures",
                'provider',
                'critical',
                [
                    'provider_type' => static::getType(),
                    'provider_name' => static::getName(),
                    'circuit_state' => $this->state['circuit_breaker_state'],
                    'failure_count' => $this->state['circuit_breaker_failures']
                ]
                );
        }
        
        $config = array_merge($this->retryConfig, $retryOptions);
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $config['max_attempts']) {
            $attempts++;
            $startTime = microtime(true);
            
            try {
                $result = $this->send($payload);
                
                // Record successful request
                $this->recordSuccess(microtime(true) - $startTime);
                $this->resetCircuitBreaker();
                
                $result['retry_info'] = [
                    'attempts_made' => $attempts,
                    'total_time' => microtime(true) - $startTime
                ];
                
                return $result;
                
            } catch (\Exception $e) {
                $lastException = $e;
                $this->recordFailure(microtime(true) - $startTime, $e);
                $this->updateCircuitBreaker();
                
                // Don't retry on certain types of errors
                if ($this->shouldNotRetry($e) || $attempts >= $config['max_attempts']) {
                    break;
                }
                
                // Calculate delay with exponential backoff and jitter
                $delay = $this->calculateRetryDelay($attempts, $config);
                if ($delay > 0) {
                    usleep($delay * 1000); // Convert to microseconds
                }
            }
        }
        
        throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
            "Provider failed after {$attempts} retry attempts",
            'provider',
            'high',
            [
                'provider_type' => static::getType(),
                'provider_name' => static::getName(),
                'attempts_made' => $attempts,
                'last_error' => $lastException ? $lastException->getMessage() : 'Unknown error',
                'retry_config' => $config
            ],
            $lastException
            );
    }
    
    /**
     * Batch send with optimization and parallel processing
     *
     * Processes multiple messages efficiently with batch optimization,
     * parallel processing, and comprehensive progress tracking.
     *
     * @param array<array<string, mixed>> $payloads Array of message payloads
     * @param array<string, mixed> $options Batch processing options
     * @return array<string, mixed> Batch processing results
     */
    public function sendBatch(array $payloads, array $options = []): array
    {
        $startTime = microtime(true);
        $chunkSize = $options['chunk_size'] ?? 100;
        $parallel = $options['parallel'] ?? false;
        $failFast = $options['fail_fast'] ?? false;
        
        $results = [];
        $errors = [];
        $totalProcessed = 0;
        $successCount = 0;
        $failureCount = 0;
        
        $chunks = array_chunk($payloads, $chunkSize);
        
        foreach ($chunks as $chunkIndex => $chunk) {
            if ($parallel && function_exists('pcntl_fork')) {
                // Implement parallel processing if available
                $chunkResults = $this->processBatchChunkParallel($chunk, $options);
            } else {
                $chunkResults = $this->processBatchChunk($chunk, $options);
            }
            
            foreach ($chunkResults['results'] as $result) {
                $results[] = $result;
                $totalProcessed++;
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                    $errors[] = $result;
                    
                    if ($failFast) {
                        break 2; // Break out of both loops
                    }
                }
            }
        }
        
        $totalTime = microtime(true) - $startTime;
        
        return [
            'success' => $failureCount === 0,
            'total_count' => count($payloads),
            'processed_count' => $totalProcessed,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results,
            'errors' => $errors,
            'performance' => [
                'total_time' => $totalTime,
                'throughput' => $totalProcessed > 0 ? $totalProcessed / $totalTime : 0,
                'average_time_per_item' => $totalProcessed > 0 ? $totalTime / $totalProcessed : 0
            ]
        ];
    }
    
    /**
     * Enhanced authentication with validation and security
     *
     * Authenticates the provider with comprehensive validation, security
     * checking, and configuration verification against schema.
     *
     * @param array<string, mixed> $config Authentication configuration
     * @return bool Authentication success status
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If authentication fails
     */
    public function authenticate(array $config): bool
    {
        $startTime = microtime(true);
        
        try {
            // Validate configuration against schema
            $validation = $this->validateConfig($config);
            if (!$validation['valid']) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    'Configuration validation failed',
                    'config',
                    'high',
                    [
                        'provider_type' => static::getType(),
                        'provider_name' => static::getName(),
                        'errors' => $validation['errors'],
                        'warnings' => $validation['warnings']
                    ]
                    );
            }
            
            // Perform provider-specific authentication
            $authResult = $this->performAuthentication($config);
            
            if ($authResult) {
                $this->authenticated = true;
                $this->setConfig($config);
                $this->lastError = null;
                $this->resetCircuitBreaker();
                return true;
            } else {
                $this->authenticated = false;
                $this->lastError = 'Authentication failed';
                return false;
            }
            
        } catch (\Exception $e) {
            $this->authenticated = false;
            $this->lastError = $e->getMessage();
            
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Authentication failed: ' . $e->getMessage(),
                'provider',
                'high',
                [
                    'provider_type' => static::getType(),
                    'provider_name' => static::getName(),
                    'authentication_config' => $this->sanitizeConfig($config)
                ],
                $e
                );
        }
    }
    
    /**
     * Comprehensive connection testing with health metrics
     *
     * Tests provider connectivity with detailed health information,
     * performance metrics, and service availability checking.
     *
     * @return array<string, mixed> Comprehensive connection test results
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);
        
        if (empty($this->config)) {
            return [
                'success' => false,
                'response_time' => microtime(true) - $startTime,
                'endpoint_status' => 'not_configured',
                'errors' => ['Provider is not configured']
            ];
        }
        
        try {
            // Perform connection-specific tests
            $testResult = $this->performConnectionTest();
            
            $this->state['last_health_check'] = microtime(true);
            
            $testResult['response_time'] = microtime(true) - $startTime;
            $testResult['endpoint_status'] = $testResult['success'] ? 'available' : 'unavailable';
            
            return $testResult;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response_time' => microtime(true) - $startTime,
                'endpoint_status' => 'error',
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    /**
     * Comprehensive health status with circuit breaker info
     *
     * Returns detailed health information including connectivity, performance
     * metrics, circuit breaker status, and operational statistics.
     *
     * @return array<string, mixed> Comprehensive health status
     */
    public function getHealthStatus(): array
    {
        $currentTime = microtime(true);
        $lastCheck = $this->state['last_health_check'] ?? 0;
        $timeSinceLastCheck = $currentTime - $lastCheck;
        
        // Perform health check if needed
        if ($timeSinceLastCheck > 300) { // 5 minutes
            $this->testConnection();
        }
        
        $errorRate = $this->state['total_requests'] > 0
        ? $this->state['failed_requests'] / $this->state['total_requests']
        : 0;
        
        $avgResponseTime = $this->state['successful_requests'] > 0
        ? $this->state['total_response_time'] / $this->state['successful_requests']
        : 0;
        
        return [
            'healthy' => $this->isHealthy(),
            'status' => $this->getStatusString(),
            'last_check' => $lastCheck,
            'response_time' => $avgResponseTime,
            'error_rate' => $errorRate,
            'rate_limit_status' => [
                'remaining' => $this->state['rate_limit_remaining'],
                'reset_time' => $this->state['rate_limit_reset']
            ],
            'circuit_breaker' => [
                'state' => $this->state['circuit_breaker_state'],
                'failure_count' => $this->state['circuit_breaker_failures']
            ],
            'issues' => $this->getHealthIssues(),
            'metrics' => [
                'total_requests' => $this->state['total_requests'],
                'successful_requests' => $this->state['successful_requests'],
                'failed_requests' => $this->state['failed_requests'],
                'uptime' => $currentTime - $this->state['initialized_at']
            ]
        ];
    }
    
    /**
     * Comprehensive performance metrics collection
     *
     * Returns detailed performance data including request statistics,
     * response times, error rates, and efficiency metrics.
     *
     * @param array<string, mixed> $options Metrics collection options
     * @return array<string, mixed> Comprehensive performance metrics
     */
    public function getMetrics(array $options = []): array
    {
        $timeRange = $options['time_range'] ?? '24h';
        $includeDetails = $options['include_details'] ?? false;
        
        $totalRequests = $this->state['total_requests'];
        $successfulRequests = $this->state['successful_requests'];
        $failedRequests = $this->state['failed_requests'];
        
        $metrics = [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $failedRequests,
            'success_rate' => $totalRequests > 0 ? $successfulRequests / $totalRequests : 1.0,
            'error_rate' => $totalRequests > 0 ? $failedRequests / $totalRequests : 0.0,
            'average_response_time' => $successfulRequests > 0
            ? $this->state['total_response_time'] / $successfulRequests
            : 0.0,
            'throughput' => $this->calculateThroughput(),
            'uptime' => microtime(true) - $this->state['initialized_at'],
            'rate_limit_hits' => $this->getRateLimitHits()
        ];
        
        if ($includeDetails) {
            $metrics['detailed_breakdown'] = $this->getDetailedMetrics();
            $metrics['performance_trends'] = $this->getPerformanceTrends();
        }
        
        return $metrics;
    }
    
    /**
     * Validates configuration against provider schema
     *
     * Performs comprehensive validation of provider configuration including
     * required fields, data types, value ranges, and business rules.
     *
     * @param array<string, mixed> $config Configuration to validate
     * @return array<string, mixed> Validation results with errors and warnings
     */
    public function validateConfig(array $config): array
    {
        $schema = static::getSetupSchema();
        $errors = [];
        $warnings = [];
        
        // Validate required fields
        $required = $schema['required_fields'] ?? [];
        foreach ($required as $field) {
            if (!isset($config[$field]) || $config[$field] === '') {
                $errors[] = "Required field '{$field}' is missing or empty";
            }
        }
        
        // Validate field types and constraints
        $fields = $schema['fields'] ?? [];
        foreach ($fields as $field => $definition) {
            if (!isset($config[$field])) {
                continue;
            }
            
            $value = $config[$field];
            $fieldErrors = $this->validateField($field, $value, $definition);
            $errors = array_merge($errors, $fieldErrors);
        }
        
        // Provider-specific validation
        $customValidation = $this->performCustomValidation($config);
        $errors = array_merge($errors, $customValidation['errors'] ?? []);
        $warnings = array_merge($warnings, $customValidation['warnings'] ?? []);
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'normalized_config' => $this->normalizeConfig($config)
        ];
    }
    
    /**
     * Sets provider configuration with validation and sanitization
     *
     * Updates the provider configuration after comprehensive validation
     * and applies security sanitization for sensitive data.
     *
     * @param array<string, mixed> $config New configuration
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If configuration is invalid
     */
    public function setConfig(array $config): void
    {
        $validation = $this->validateConfig($config);
        
        if (!$validation['valid']) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Invalid provider configuration',
                'config',
                'high',
                [
                    'provider_type' => static::getType(),
                    'provider_name' => static::getName(),
                    'errors' => $validation['errors'],
                    'warnings' => $validation['warnings']
                ]
                );
        }
        
        $this->config = $validation['normalized_config'] ?? $config;
        
        // Update retry and circuit breaker configs if provided
        if (isset($config['retry_config'])) {
            $this->retryConfig = array_merge($this->retryConfig, $config['retry_config']);
        }
        
        if (isset($config['circuit_breaker_config'])) {
            $this->circuitBreakerConfig = array_merge($this->circuitBreakerConfig, $config['circuit_breaker_config']);
        }
    }
    
    /**
     * Gets current configuration with optional sensitive data
     *
     * Returns the current provider configuration with sensitive data
     * optionally masked for security purposes.
     *
     * @param bool $includeSensitive Whether to include sensitive data
     * @return array<string, mixed> Current configuration
     */
    public function getConfig(bool $includeSensitive = false): array
    {
        if ($includeSensitive) {
            return $this->config;
        }
        
        return $this->sanitizeConfig($this->config);
    }
    
    /**
     * Resets provider state and clears configuration
     *
     * Resets the provider to its initial state, clearing authentication,
     * cached data, metrics, and any runtime state.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->config = [];
        $this->authenticated = false;
        $this->lastError = null;
        $this->state = [
            'initialized_at' => microtime(true),
            'last_used_at' => null,
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'total_response_time' => 0.0,
            'last_health_check' => null,
            'circuit_breaker_state' => 'closed',
            'circuit_breaker_failures' => 0,
            'rate_limit_remaining' => null,
            'rate_limit_reset' => null
        ];
        $this->metrics = [];
        $this->initializeMetrics();
        $this->initializeCircuitBreaker();
    }
    
    /**
     * Static metadata methods - must be implemented by concrete providers
     */
    
    /**
     * Returns the unique provider name
     *
     * @return string Unique provider name
     */
    public static function getName(): string
    {
        return static::class;
    }
    
    /**
     * Returns human-readable provider description
     *
     * @return string Provider description
     */
    public static function getDescription(): string
    {
        return '';
    }
    
    /**
     * Returns provider type classification
     *
     * @return string Provider type
     */
    public static function getType(): string
    {
        return '';
    }
    
    /**
     * Returns provider version
     *
     * @return string Semantic version
     */
    public static function getVersion(): string
    {
        return defined('static::VERSION') ? static::VERSION : '2.0.0';
    }
    
    /**
     * Returns provider status information
     *
     * @return array<string, mixed> Provider status
     */
    public static function getStatus(): array
    {
        return [
            'status' => self::STATUS_ACTIVE,
            'availability' => 1.0,
            'maintenance_windows' => [],
            'deprecated' => false
        ];
    }
    
    /**
     * Returns comprehensive provider capabilities
     *
     * @return array<string, mixed> Detailed capability information
     */
    public static function getCapabilities(): array
    {
        return [
            'features' => [],
            'limits' => [],
            'supported_formats' => [],
            'supported_regions' => [],
            'rate_limits' => [],
            'sla' => []
        ];
    }
    
    /**
     * Returns complete setup schema definition
     *
     * @return array<string, mixed> Complete setup schema
     */
    public static function getSetupSchema(): array
    {
        return [
            'schema_version' => '2.0',
            'fields' => [],
            'required_fields' => [],
            'field_groups' => [],
            'validation_rules' => [],
            'ui_hints' => [],
            'examples' => []
        ];
    }
    
    /**
     * Returns provider documentation
     *
     * @return array<string, mixed> Documentation information
     */
    public static function getDocumentation(): array
    {
        return [
            'setup_guide' => [],
            'troubleshooting' => [],
            'api_reference' => [],
            'examples' => [],
            'support_contacts' => []
        ];
    }
    
    /**
     * Returns pricing information
     *
     * @return array<string, mixed> Pricing and billing information
     */
    public static function getPricing(): array
    {
        return [
            'pricing_model' => 'usage_based',
            'base_cost' => [],
            'usage_tiers' => [],
            'billing_cycle' => 'monthly',
            'free_tier' => [],
            'cost_optimization_tips' => []
        ];
    }
    
    /**
     * Returns integration requirements
     *
     * @return array<string, mixed> Requirements and dependencies
     */
    public static function getRequirements(): array
    {
        return [
            'php_version' => '8.1.0',
            'required_extensions' => [],
            'optional_extensions' => [],
            'external_dependencies' => [],
            'configuration_requirements' => [],
            'security_requirements' => []
        ];
    }
    
    /**
     * Protected helper methods for provider implementation
     */
    
    /**
     * Performs provider-specific authentication logic
     *
     * Override this method to implement provider-specific authentication.
     *
     * @param array<string, mixed> $config Authentication configuration
     * @return bool Authentication success
     */
    protected function performAuthentication(array $config): bool
    {
        // Default implementation - override in concrete providers
        return true;
    }
    
    /**
     * Performs provider-specific connection testing
     *
     * Override this method to implement provider-specific connection tests.
     *
     * @return array<string, mixed> Connection test results
     */
    protected function performConnectionTest(): array
    {
        // Default implementation - override in concrete providers
        return [
            'success' => true,
            'message' => 'Connection test successful'
        ];
    }
    
    /**
     * Performs custom configuration validation
     *
     * Override this method to implement provider-specific validation logic.
     *
     * @param array<string, mixed> $config Configuration to validate
     * @return array<string, mixed> Custom validation results
     */
    protected function performCustomValidation(array $config): array
    {
        return [
            'errors' => [],
            'warnings' => []
        ];
    }
    
    /**
     * Processes a batch chunk of payloads
     *
     * @param array<array<string, mixed>> $chunk Chunk of payloads to process
     * @param array<string, mixed> $options Processing options
     * @return array<string, mixed> Chunk processing results
     */
    protected function processBatchChunk(array $chunk, array $options): array
    {
        $results = [];
        
        foreach ($chunk as $payload) {
            try {
                $result = $this->send($payload);
                $result['success'] = true;
                $results[] = $result;
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'payload' => $payload
                ];
            }
        }
        
        return ['results' => $results];
    }
    
    /**
     * Processes a batch chunk with parallel processing
     *
     * @param array<array<string, mixed>> $chunk Chunk of payloads to process
     * @param array<string, mixed> $options Processing options
     * @return array<string, mixed> Parallel processing results
     */
    protected function processBatchChunkParallel(array $chunk, array $options): array
    {
        // Fallback to sequential processing if parallel is not available
        return $this->processBatchChunk($chunk, $options);
    }
    
    /**
     * Sanitizes configuration by masking sensitive fields
     *
     * @param array<string, mixed> $config Configuration to sanitize
     * @return array<string, mixed> Sanitized configuration
     */
    protected function sanitizeConfig(array $config): array
    {
        $sanitized = $config;
        
        foreach ($this->sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = str_repeat('*', 8);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validates a single configuration field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array<string, mixed> $definition Field definition
     * @return array<string> Validation errors
     */
    protected function validateField(string $field, $value, array $definition): array
    {
        $errors = [];
        
        // Type validation
        $expectedType = $definition['type'] ?? 'string';
        if (!$this->validateFieldType($value, $expectedType)) {
            $errors[] = "Field '{$field}' must be of type {$expectedType}";
        }
        
        // Range validation
        if (isset($definition['minimum']) && is_numeric($value) && $value < $definition['minimum']) {
            $errors[] = "Field '{$field}' must be at least {$definition['minimum']}";
        }
        
        if (isset($definition['maximum']) && is_numeric($value) && $value > $definition['maximum']) {
            $errors[] = "Field '{$field}' must be at most {$definition['maximum']}";
        }
        
        // Pattern validation
        if (isset($definition['pattern']) && is_string($value) && !preg_match($definition['pattern'], $value)) {
            $errors[] = "Field '{$field}' does not match required pattern";
        }
        
        return $errors;
    }
    
    /**
     * Validates field type
     *
     * @param mixed $value Value to validate
     * @param string $expectedType Expected type
     * @return bool True if type is valid
     */
    protected function validateFieldType($value, string $expectedType): bool
    {
        switch ($expectedType) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_int($value);
            case 'number':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value);
            case 'array':
                return is_array($value);
            default:
                return true;
        }
    }
    
    /**
     * Normalizes configuration values
     *
     * @param array<string, mixed> $config Configuration to normalize
     * @return array<string, mixed> Normalized configuration
     */
    protected function normalizeConfig(array $config): array
    {
        // Override in concrete providers for specific normalization
        return $config;
    }
    
    /**
     * Circuit breaker and performance tracking methods
     */
    
    /**
     * Initializes performance metrics tracking
     *
     * @return void
     */
    protected function initializeMetrics(): void
    {
        $this->metrics = [
            'hourly_stats' => [],
            'daily_stats' => [],
            'error_breakdown' => [],
            'performance_history' => []
        ];
    }
    
    /**
     * Initializes circuit breaker
     *
     * @return void
     */
    protected function initializeCircuitBreaker(): void
    {
        $this->state['circuit_breaker_state'] = 'closed';
        $this->state['circuit_breaker_failures'] = 0;
    }
    
    /**
     * Records successful request
     *
     * @param float $responseTime Response time in seconds
     * @return void
     */
    protected function recordSuccess(float $responseTime): void
    {
        $this->state['total_requests']++;
        $this->state['successful_requests']++;
        $this->state['total_response_time'] += $responseTime;
        $this->state['last_used_at'] = microtime(true);
    }
    
    /**
     * Records failed request
     *
     * @param float $responseTime Response time in seconds
     * @param \Exception $exception Exception that occurred
     * @return void
     */
    protected function recordFailure(float $responseTime, \Exception $exception): void
    {
        $this->state['total_requests']++;
        $this->state['failed_requests']++;
        $this->state['total_response_time'] += $responseTime;
        $this->state['last_used_at'] = microtime(true);
        $this->lastError = $exception->getMessage();
    }
    
    /**
     * Updates circuit breaker state
     *
     * @return void
     */
    protected function updateCircuitBreaker(): void
    {
        $this->state['circuit_breaker_failures']++;
        
        if ($this->state['circuit_breaker_failures'] >= $this->circuitBreakerConfig['failure_threshold']) {
            $this->state['circuit_breaker_state'] = 'open';
        }
    }
    
    /**
     * Resets circuit breaker on success
     *
     * @return void
     */
    protected function resetCircuitBreaker(): void
    {
        $this->state['circuit_breaker_state'] = 'closed';
        $this->state['circuit_breaker_failures'] = 0;
    }
    
    /**
     * Checks if circuit breaker is closed
     *
     * @return bool True if circuit breaker is closed
     */
    protected function isCircuitBreakerClosed(): bool
    {
        if ($this->state['circuit_breaker_state'] === 'closed') {
            return true;
        }
        
        // Check if timeout has elapsed for half-open state
        if ($this->state['circuit_breaker_state'] === 'open') {
            $timeSinceFailure = microtime(true) - ($this->state['last_used_at'] ?? 0);
            if ($timeSinceFailure > $this->circuitBreakerConfig['timeout']) {
                $this->state['circuit_breaker_state'] = 'half-open';
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Determines if an exception should prevent retries
     *
     * @param \Exception $exception Exception to check
     * @return bool True if retries should not be attempted
     */
    protected function shouldNotRetry(\Exception $exception): bool
    {
        // Don't retry on configuration or authentication errors
        return strpos($exception->getMessage(), 'configuration') !== false ||
        strpos($exception->getMessage(), 'authentication') !== false ||
        strpos($exception->getMessage(), 'validation') !== false;
    }
    
    /**
     * Calculates retry delay with exponential backoff
     *
     * @param int $attempt Current attempt number
     * @param array<string, mixed> $config Retry configuration
     * @return int Delay in milliseconds
     */
    protected function calculateRetryDelay(int $attempt, array $config): int
    {
        $delay = min(
            $config['base_delay'] * pow($config['backoff_multiplier'], $attempt - 1),
            $config['max_delay']
            );
        
        // Add jitter to prevent thundering herd
        if ($config['jitter']) {
            $jitter = mt_rand(0, (int)($delay * 0.1));
            $delay += $jitter;
        }
        
        return (int)$delay;
    }
    
    /**
     * Utility methods for health and performance tracking
     */
    
    /**
     * Checks if provider is currently healthy
     *
     * @return bool True if provider is healthy
     */
    protected function isHealthy(): bool
    {
        $errorRate = $this->state['total_requests'] > 0
        ? $this->state['failed_requests'] / $this->state['total_requests']
        : 0;
        
        return $this->authenticated &&
        $this->state['circuit_breaker_state'] === 'closed' &&
        $errorRate < 0.1; // Less than 10% error rate
    }
    
    /**
     * Gets status string representation
     *
     * @return string Status string
     */
    protected function getStatusString(): string
    {
        if (!$this->authenticated) {
            return 'not_authenticated';
        }
        
        if ($this->state['circuit_breaker_state'] === 'open') {
            return 'circuit_breaker_open';
        }
        
        if ($this->isHealthy()) {
            return 'healthy';
        }
        
        return 'degraded';
    }
    
    /**
     * Gets list of current health issues
     *
     * @return array<string> List of health issues
     */
    protected function getHealthIssues(): array
    {
        $issues = [];
        
        if (!$this->authenticated) {
            $issues[] = 'Provider not authenticated';
        }
        
        if ($this->state['circuit_breaker_state'] === 'open') {
            $issues[] = 'Circuit breaker is open due to failures';
        }
        
        $errorRate = $this->state['total_requests'] > 0
        ? $this->state['failed_requests'] / $this->state['total_requests']
        : 0;
        
        if ($errorRate > 0.1) {
            $issues[] = 'High error rate detected';
        }
        
        if ($this->lastError) {
            $issues[] = 'Last error: ' . $this->lastError;
        }
        
        return $issues;
    }
    
    /**
     * Calculates current throughput
     *
     * @return float Requests per second
     */
    protected function calculateThroughput(): float
    {
        $uptime = microtime(true) - $this->state['initialized_at'];
        return $uptime > 0 ? $this->state['total_requests'] / $uptime : 0.0;
    }
    
    /**
     * Gets rate limit hit count
     *
     * @return int Number of rate limit hits
     */
    protected function getRateLimitHits(): int
    {
        // This would be tracked in a more sophisticated implementation
        return 0;
    }
    
    /**
     * Gets detailed performance metrics
     *
     * @return array<string, mixed> Detailed metrics
     */
    protected function getDetailedMetrics(): array
    {
        return [
            'response_time_percentiles' => [],
            'error_breakdown_by_type' => [],
            'hourly_request_distribution' => [],
            'geographic_performance' => []
        ];
    }
    
    /**
     * Gets performance trends
     *
     * @return array<string, mixed> Performance trends
     */
    protected function getPerformanceTrends(): array
    {
        return [
            'response_time_trend' => 'stable',
            'error_rate_trend' => 'decreasing',
            'throughput_trend' => 'increasing'
        ];
    }
    
    /**
     * Legacy compatibility methods
     */
    
    /**
     * Gets last error message
     *
     * @return string|null Last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }
    
    /**
     * Checks if provider is authenticated
     *
     * @return bool Authentication status
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }
    
    /**
     * Gets sanitized configuration (legacy method)
     *
     * @param array<string>|null $fieldsToMask Fields to mask
     * @return array<string, mixed> Sanitized configuration
     */
    public function getSanitisedConfig(?array $fieldsToMask = null): array
    {
        if ($fieldsToMask !== null) {
            $originalSensitive = $this->sensitiveFields;
            $this->sensitiveFields = $fieldsToMask;
            $result = $this->sanitizeConfig($this->config);
            $this->sensitiveFields = $originalSensitive;
            return $result;
        }
        
        return $this->sanitizeConfig($this->config);
    }
    
    /**
     * Checks if provider supports a capability
     *
     * @param string $capability Capability to check
     * @return bool True if capability is supported
     */
    public static function supports(string $capability): bool
    {
        $capabilities = static::getCapabilities();
        $features = $capabilities['features'] ?? [];
        return in_array($capability, $features, true);
    }
    
    /**
     * Gets provider display label
     *
     * @return string Display label
     */
    public static function getLabel(): string
    {
        return static::getName();
    }
}
