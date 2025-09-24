<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Channel Provider Interface - Comprehensive Provider Contract
 *
 * Defines the comprehensive contract for all provider classes (email, SMS, push,
 * AI, payment, analytics, etc.) used throughout the MAS ecosystem. This interface
 * establishes enterprise-grade standards for provider implementation including
 * authentication, configuration management, capability discovery, health monitoring,
 * and advanced features like batch processing, rate limiting, and error handling.
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas\Interfaces
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Opencart\Extension\Mas\System\Library\Mas\Interfaces;

/**
 * Enterprise Channel Provider Interface
 *
 * Comprehensive contract defining all methods required for MAS provider implementations.
 * This interface ensures consistent behavior across all provider types while supporting
 * advanced enterprise features such as batch processing, health monitoring, rate limiting,
 * configuration management, and comprehensive error handling.
 *
 * Provider Types Supported:
 * - Communication: email, sms, push, voice, chat
 * - AI/ML: chat, completion, embedding, image, analysis
 * - Payment: authorize, capture, refund, subscription
 * - Analytics: tracking, reporting, data processing
 * - Storage: file, database, cache, backup
 * - Integration: webhook, api, sync, transformation
 *
 * Key Features:
 * - Standardized authentication and configuration management
 * - Health monitoring and connection testing
 * - Batch processing capabilities with rate limiting
 * - Comprehensive error handling and retry logic
 * - Auto-discovery with detailed capability reporting
 * - Configuration validation and sanitization
 * - Performance monitoring and metrics collection
 * - Security features including credential management
 * - Extensible metadata and custom properties support
 *
 * Implementation Requirements:
 * - All methods must be implemented by concrete providers
 * - Static methods provide metadata without instantiation
 * - Configuration must be validated and sanitized
 * - Error handling must use MAS Exception system
 * - Performance metrics should be tracked when possible
 * - Security best practices must be followed
 *
 * Usage Examples:
 *
 * Basic provider usage:
 * $provider = new SendGridProvider();
 * $provider->authenticate($config);
 * $result = $provider->send($payload);
 *
 * Batch processing:
 * $results = $provider->sendBatch($payloads, ['chunk_size' => 100]);
 *
 * Health monitoring:
 * $health = $provider->getHealthStatus();
 * if (!$health['healthy']) {
 *     // Handle provider issues
 * }
 *
 * Capability discovery:
 * $capabilities = MyProvider::getCapabilities();
 * if (in_array('bulk_send', $capabilities)) {
 *     // Use bulk sending features
 * }
 */
interface ChannelProviderInterface
{
    /**
     * @var string Communication provider types
     */
    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';
    public const TYPE_PUSH = 'push';
    public const TYPE_VOICE = 'voice';
    public const TYPE_CHAT = 'chat';
    public const TYPE_WEBHOOK = 'webhook';
    
    /**
     * @var string AI/ML provider types
     */
    public const TYPE_AI = 'ai';
    public const TYPE_AI_CHAT = 'ai_chat';
    public const TYPE_AI_COMPLETION = 'ai_completion';
    public const TYPE_AI_EMBEDDING = 'ai_embedding';
    public const TYPE_AI_IMAGE = 'ai_image';
    public const TYPE_AI_ANALYSIS = 'ai_analysis';
    
    /**
     * @var string Payment provider types
     */
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_PAYMENT_GATEWAY = 'payment_gateway';
    public const TYPE_PAYMENT_PROCESSOR = 'payment_processor';
    
    /**
     * @var string Analytics provider types
     */
    public const TYPE_ANALYTICS = 'analytics';
    public const TYPE_TRACKING = 'tracking';
    public const TYPE_REPORTING = 'reporting';
    
    /**
     * @var string Storage provider types
     */
    public const TYPE_STORAGE = 'storage';
    public const TYPE_DATABASE = 'database';
    public const TYPE_CACHE = 'cache';
    public const TYPE_FILE = 'file';
    
    /**
     * @var string Integration provider types
     */
    public const TYPE_API = 'api';
    public const TYPE_SYNC = 'sync';
    public const TYPE_TRANSFORMATION = 'transformation';
    
    /**
     * @var string Provider status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ERROR = 'error';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_DEPRECATED = 'deprecated';
    
    /**
     * @var string Provider capability constants
     */
    public const CAPABILITY_BULK_SEND = 'bulk_send';
    public const CAPABILITY_TRACKING = 'tracking';
    public const CAPABILITY_TEMPLATES = 'templates';
    public const CAPABILITY_PERSONALIZATION = 'personalization';
    public const CAPABILITY_SCHEDULING = 'scheduling';
    public const CAPABILITY_ATTACHMENTS = 'attachments';
    public const CAPABILITY_ANALYTICS = 'analytics';
    public const CAPABILITY_WEBHOOKS = 'webhooks';
    public const CAPABILITY_RATE_LIMITING = 'rate_limiting';
    public const CAPABILITY_RETRY_LOGIC = 'retry_logic';
    public const CAPABILITY_ENCRYPTION = 'encryption';
    public const CAPABILITY_COMPRESSION = 'compression';
    
    /**
     * Sends a message or executes the provider's primary action
     *
     * Processes a single message or action with comprehensive error handling,
     * performance tracking, and detailed response information. Supports various
     * payload formats and provides extensive metadata in the response.
     *
     * @param array<string, mixed> $payload Normalized payload containing:
     *   - recipient: array with contact information
     *   - content: array with message content
     *   - options: array with delivery options
     *   - metadata: array with additional data
     * @return array{
     *     success: bool,
     *     message_id?: string,
     *     provider_id?: string,
     *     status?: string,
     *     error?: string,
     *     error_code?: string,
     *     meta?: array<string, mixed>,
     *     performance?: array<string, mixed>,
     *     timestamp?: float
     * } Comprehensive response with status, IDs, and metadata
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ProviderException If critical error occurs
     */
    public function send(array $payload): array;
    
    /**
     * Sends multiple messages in batch with optimization
     *
     * Processes multiple messages efficiently with batch optimization,
     * rate limiting, and detailed progress reporting. Supports chunking
     * and parallel processing where available.
     *
     * @param array<array<string, mixed>> $payloads Array of message payloads
     * @param array<string, mixed> $options Batch processing options:
     *   - chunk_size: int - Size of each batch chunk
     *   - parallel: bool - Enable parallel processing
     *   - rate_limit: int - Rate limit per second
     *   - fail_fast: bool - Stop on first failure
     * @return array{
     *     success: bool,
     *     total_count: int,
     *     success_count: int,
     *     failure_count: int,
     *     results: array<array<string, mixed>>,
     *     errors: array<array<string, mixed>>,
     *     performance: array<string, mixed>
     * } Batch processing results with comprehensive statistics
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ProviderException If batch processing fails
     */
    public function sendBatch(array $payloads, array $options = []): array;
    
    /**
     * Authenticates or initializes the provider with configuration
     *
     * Establishes authentication with the provider service using provided
     * credentials and configuration. Validates credentials, establishes
     * connections, and prepares the provider for use.
     *
     * @param array<string, mixed> $config Configuration array containing:
     *   - credentials: array with API keys, tokens, etc.
     *   - endpoints: array with service URLs
     *   - options: array with provider-specific settings
     * @return bool True if authentication successful, false otherwise
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ProviderException If authentication fails
     */
    public function authenticate(array $config): bool;
    
    /**
     * Tests connectivity and credentials without sending real messages
     *
     * Performs a lightweight test of provider connectivity and credential
     * validity without triggering actual message delivery or billable actions.
     * Useful for health checks and configuration validation.
     *
     * @return array{
     *     success: bool,
     *     response_time: float,
     *     endpoint_status: string,
     *     rate_limit_remaining?: int,
     *     account_status?: string,
     *     errors?: array<string>
     * } Connection test results with detailed status information
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ProviderException If test fails critically
     */
    public function testConnection(): array;
    
    /**
     * Gets comprehensive provider health status
     *
     * Returns detailed health information including connectivity, performance
     * metrics, rate limiting status, and any ongoing issues. Used for monitoring
     * and auto-failover decisions.
     *
     * @return array{
     *     healthy: bool,
     *     status: string,
     *     last_check: float,
     *     response_time: float,
     *     error_rate: float,
     *     rate_limit_status: array<string, mixed>,
     *     issues: array<string>,
     *     metrics: array<string, mixed>
     * } Comprehensive health status information
     */
    public function getHealthStatus(): array;
    
    /**
     * Gets provider performance metrics and statistics
     *
     * Returns detailed performance data including request counts, response times,
     * error rates, and other relevant metrics for monitoring and optimization.
     *
     * @param array<string, mixed> $options Metrics options:
     *   - time_range: string - Time range for metrics ('1h', '24h', '7d', '30d')
     *   - include_details: bool - Include detailed breakdowns
     * @return array{
     *     total_requests: int,
     *     successful_requests: int,
     *     failed_requests: int,
     *     average_response_time: float,
     *     error_rate: float,
     *     throughput: float,
     *     rate_limit_hits: int,
     *     last_error?: array<string, mixed>
     * } Performance metrics and statistics
     */
    public function getMetrics(array $options = []): array;
    
    /**
     * Validates provider configuration
     *
     * Validates the provided configuration against the provider's schema
     * and requirements. Returns detailed validation results with any errors
     * or warnings identified.
     *
     * @param array<string, mixed> $config Configuration to validate
     * @return array{
     *     valid: bool,
     *     errors: array<string>,
     *     warnings: array<string>,
     *     normalized_config?: array<string, mixed>
     * } Validation results with errors and warnings
     */
    public function validateConfig(array $config): array;
    
    /**
     * Sets or updates runtime configuration after authentication
     *
     * Updates the provider's runtime configuration with new settings.
     * Configuration is validated and sanitized before being applied.
     *
     * @param array<string, mixed> $config New configuration settings
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ConfigException If configuration is invalid
     */
    public function setConfig(array $config): void;
    
    /**
     * Returns current runtime configuration (sanitized)
     *
     * Returns the current provider configuration with sensitive data
     * (passwords, API keys) masked or removed for security.
     *
     * @param bool $includeSensitive Whether to include sensitive data (use with caution)
     * @return array<string, mixed> Current configuration (sanitized)
     */
    public function getConfig(bool $includeSensitive = false): array;
    
    /**
     * Resets provider state and configuration
     *
     * Resets the provider to its initial state, clearing authentication,
     * cached data, and any runtime state. Useful for testing and cleanup.
     *
     * @return void
     */
    public function reset(): void;
    
    /**
     * Returns the unique provider name
     *
     * Provides the canonical name for this provider (e.g., "SendGrid", "Twilio").
     * Must be unique within the provider ecosystem.
     *
     * @return string Unique provider name
     */
    public static function getName(): string;
    
    /**
     * Returns a human-readable provider description
     *
     * Provides a concise description of the provider's functionality
     * and use cases for display in administrative interfaces.
     *
     * @return string Provider description
     */
    public static function getDescription(): string;
    
    /**
     * Returns the provider type classification
     *
     * Returns the primary type classification for this provider using
     * one of the defined TYPE_* constants.
     *
     * @return string Provider type (use TYPE_* constants)
     */
    public static function getType(): string;
    
    /**
     * Returns the provider version
     *
     * Returns the semantic version of this provider implementation
     * for compatibility and feature detection purposes.
     *
     * @return string Semantic version (e.g., "2.1.0")
     */
    public static function getVersion(): string;
    
    /**
     * Returns provider status information
     *
     * Returns the current operational status of the provider including
     * availability, maintenance windows, and deprecation status.
     *
     * @return array{
     *     status: string,
     *     availability: float,
     *     maintenance_windows: array<array<string, mixed>>,
     *     deprecated: bool,
     *     deprecation_date?: string,
     *     replacement_provider?: string
     * } Provider status information
     */
    public static function getStatus(): array;
    
    /**
     * Returns comprehensive provider capabilities
     *
     * Returns a detailed array of provider capabilities, features, and
     * limitations for feature detection and optimization decisions.
     *
     * @return array{
     *     features: array<string>,
     *     limits: array<string, mixed>,
     *     supported_formats: array<string>,
     *     supported_regions: array<string>,
     *     rate_limits: array<string, mixed>,
     *     sla: array<string, mixed>
     * } Detailed capability information
     */
    public static function getCapabilities(): array;
    
    /**
     * Returns the complete setup schema definition
     *
     * Returns comprehensive schema for auto-discovery, configuration
     * validation, and administrative interface generation. Includes
     * field definitions, validation rules, and UI hints.
     *
     * @return array{
     *     schema_version: string,
     *     fields: array<string, array<string, mixed>>,
     *     required_fields: array<string>,
     *     field_groups: array<string, array<string, mixed>>,
     *     validation_rules: array<string, mixed>,
     *     ui_hints: array<string, mixed>,
     *     examples: array<string, mixed>
     * } Complete setup schema definition
     */
    public static function getSetupSchema(): array;
    
    /**
     * Returns provider documentation and help information
     *
     * Provides comprehensive documentation including setup instructions,
     * troubleshooting guides, and API references for administrative
     * interfaces and developer tools.
     *
     * @return array{
     *     setup_guide: array<string, mixed>,
     *     troubleshooting: array<string, mixed>,
     *     api_reference: array<string, mixed>,
     *     examples: array<string, mixed>,
     *     support_contacts: array<string, mixed>
     * } Provider documentation and help information
     */
    public static function getDocumentation(): array;
    
    /**
     * Returns provider pricing and billing information
     *
     * Provides information about provider pricing, billing models, and
     * cost optimization recommendations for budget planning and cost
     * management features.
     *
     * @return array{
     *     pricing_model: string,
     *     base_cost: array<string, mixed>,
     *     usage_tiers: array<array<string, mixed>>,
     *     billing_cycle: string,
     *     free_tier: array<string, mixed>,
     *     cost_optimization_tips: array<string>
     * } Pricing and billing information
     */
    public static function getPricing(): array;
    
    /**
     * Returns provider integration requirements and dependencies
     *
     * Specifies technical requirements, dependencies, and integration
     * details needed for proper provider operation including PHP
     * extensions, external services, and configuration requirements.
     *
     * @return array{
     *     php_version: string,
     *     required_extensions: array<string>,
     *     optional_extensions: array<string>,
     *     external_dependencies: array<string, mixed>,
     *     configuration_requirements: array<string, mixed>,
     *     security_requirements: array<string>
     * } Integration requirements and dependencies
     */
    public static function getRequirements(): array;
}
