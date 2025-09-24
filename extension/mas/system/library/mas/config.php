<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Configuration Management System
 *
 * Comprehensive configuration file providing enterprise-grade settings for all
 * MAS components including gateways, services, providers, security, performance,
 * and debugging capabilities. This configuration system supports environment-specific
 * overrides, validation, caching, and real-time configuration updates.
 *
 * Configuration Structure:
 * - Gateway configurations for AI, messaging, and payment systems
 * - Service configurations for workflow, segmentation, and analytics
 * - Provider configurations with auto-discovery and fallback chains
 * - Security and compliance settings with audit capabilities
 * - Performance optimization settings with caching and limits
 * - Development and debugging configurations for troubleshooting
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

/**
 * MAS Enterprise Configuration Array
 *
 * This configuration provides comprehensive settings for all MAS components
 * with enterprise-grade features including high availability, failover,
 * performance optimization, security hardening, and compliance support.
 *
 * All configuration values support environment variable overrides using
 * the pattern MAS_{SECTION}_{KEY} for deployment flexibility.
 *
 * Configuration Sections:
 * - AI Gateway: Artificial intelligence service configuration
 * - Message Gateway: Multi-channel messaging configuration
 * - Payment Gateway: Payment processing and financial services
 * - Event System: Event-driven architecture configuration
 * - Audit & Compliance: Logging, tracking, and GDPR compliance
 * - Reporting: Analytics, dashboards, and data export
 * - Segmentation: Customer segmentation and AI suggestions
 * - Workflow Engine: Business process automation
 * - Provider Management: Service provider auto-discovery
 * - Campaign Management: Marketing campaign execution
 * - Template Engine: Dynamic content generation
 * - Analytics: Real-time data processing and insights
 * - Security: Authentication, authorization, and protection
 * - Performance: Caching, optimization, and resource limits
 * - Integration: APIs, webhooks, and third-party services
 * - Debug: Development tools and troubleshooting
 *
 * Usage Examples:
 *
 * Environment override:
 * export MAS_AI_GATEWAY_DEFAULT_PROVIDER=anthropic
 *
 * Runtime configuration access:
 * $container->get('config')->get('ai_gateway.default_provider')
 *
 * Dynamic configuration update:
 * $container->get('config')->set('debug.enabled', true)
 */
return [
    
    /* ========================= AI GATEWAY CONFIGURATION ========================= */
    'ai_gateway' => [
        // Primary AI provider for general operations
        'default_provider' => $_ENV['MAS_AI_GATEWAY_DEFAULT_PROVIDER'] ?? 'openai',
        
        // Performance and reliability settings
        'enable_cache' => (bool)($_ENV['MAS_AI_GATEWAY_ENABLE_CACHE'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_AI_GATEWAY_CACHE_TTL'] ?? 3600),
        'cache_namespace' => 'mas_ai_gateway',
        
        // Retry and circuit breaker configuration
        'max_retries' => (int)($_ENV['MAS_AI_GATEWAY_MAX_RETRIES'] ?? 3),
        'retry_backoff_multiplier' => (float)($_ENV['MAS_AI_GATEWAY_BACKOFF_MULTIPLIER'] ?? 2.0),
        'retry_base_delay' => (int)($_ENV['MAS_AI_GATEWAY_BASE_DELAY'] ?? 1000), // milliseconds
        'circuit_breaker_threshold' => (int)($_ENV['MAS_AI_GATEWAY_CIRCUIT_THRESHOLD'] ?? 5),
        'circuit_breaker_timeout' => (int)($_ENV['MAS_AI_GATEWAY_CIRCUIT_TIMEOUT'] ?? 300),
        'circuit_breaker_recovery_timeout' => (int)($_ENV['MAS_AI_GATEWAY_RECOVERY_TIMEOUT'] ?? 60),
        
        // Resource and performance limits
        'request_timeout' => (int)($_ENV['MAS_AI_GATEWAY_REQUEST_TIMEOUT'] ?? 60),
        'max_concurrent_requests' => (int)($_ENV['MAS_AI_GATEWAY_MAX_CONCURRENT'] ?? 10),
        'rate_limit_requests_per_minute' => (int)($_ENV['MAS_AI_GATEWAY_RATE_LIMIT'] ?? 100),
        'max_tokens_per_request' => (int)($_ENV['MAS_AI_GATEWAY_MAX_TOKENS'] ?? 4096),
        'max_batch_size' => (int)($_ENV['MAS_AI_GATEWAY_MAX_BATCH_SIZE'] ?? 20),
        
        // Provider paths and discovery
        'ai_providers_path' => $_ENV['MAS_AI_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/ai/',
        'enable_auto_discovery' => (bool)($_ENV['MAS_AI_AUTO_DISCOVERY'] ?? true),
        'provider_config_cache_ttl' => (int)($_ENV['MAS_AI_PROVIDER_CACHE_TTL'] ?? 7200),
        
        // Intelligent fallback chains by operation type
        'fallback_order' => [
            'chat' => ['openai', 'anthropic', 'gemini', 'cohere', 'local_ml'],
            'completion' => ['openai', 'anthropic', 'gemini', 'cohere', 'local_ml'],
            'embedding' => ['openai', 'gemini', 'cohere', 'local_ml'],
            'image_generation' => ['openai', 'stable_diffusion', 'midjourney', 'dall_e'],
            'image_analysis' => ['openai', 'google_vision', 'anthropic', 'local_ml'],
            'text_analysis' => ['openai', 'anthropic', 'google_nlp', 'local_ml'],
            'sentiment_analysis' => ['openai', 'google_nlp', 'anthropic', 'local_ml'],
            'language_detection' => ['google_nlp', 'openai', 'local_ml'],
            'translation' => ['google_translate', 'openai', 'deepl'],
            'prediction' => ['local_ml', 'openai', 'anthropic', 'gemini'],
            'clustering' => ['local_ml', 'openai', 'gemini'],
            'classification' => ['openai', 'anthropic', 'local_ml'],
            'recommendation' => ['local_ml', 'openai', 'anthropic'],
            'summarization' => ['openai', 'anthropic', 'gemini', 'local_ml']
        ],
        
        // Model-specific configurations
        'model_configs' => [
            'openai' => [
                'default_model' => 'gpt-4-turbo',
                'temperature' => 0.3,
                'max_tokens' => 2048,
                'top_p' => 0.9,
                'frequency_penalty' => 0.1,
                'presence_penalty' => 0.1
            ],
            'anthropic' => [
                'default_model' => 'claude-3-opus',
                'temperature' => 0.3,
                'max_tokens' => 2048,
                'top_p' => 0.9
            ],
            'gemini' => [
                'default_model' => 'gemini-1.5-pro',
                'temperature' => 0.3,
                'max_tokens' => 2048,
                'top_p' => 0.9
            ]
        ],
        
        // Monitoring and analytics
        'enable_usage_tracking' => (bool)($_ENV['MAS_AI_USAGE_TRACKING'] ?? true),
        'track_token_usage' => (bool)($_ENV['MAS_AI_TRACK_TOKENS'] ?? true),
        'track_response_time' => (bool)($_ENV['MAS_AI_TRACK_RESPONSE_TIME'] ?? true),
        'enable_cost_tracking' => (bool)($_ENV['MAS_AI_COST_TRACKING'] ?? true)
    ],
    
    /* ========================= MESSAGE GATEWAY CONFIGURATION ========================= */
    'message_gateway' => [
        // Primary messaging provider
        'default_provider' => $_ENV['MAS_MESSAGE_DEFAULT_PROVIDER'] ?? 'sendgrid',
        
        // Performance settings
        'enable_cache' => (bool)($_ENV['MAS_MESSAGE_ENABLE_CACHE'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_MESSAGE_CACHE_TTL'] ?? 1800),
        'cache_namespace' => 'mas_message_gateway',
        
        // Reliability and retry configuration
        'max_retries' => (int)($_ENV['MAS_MESSAGE_MAX_RETRIES'] ?? 3),
        'retry_backoff_multiplier' => (float)($_ENV['MAS_MESSAGE_BACKOFF_MULTIPLIER'] ?? 2.0),
        'retry_base_delay' => (int)($_ENV['MAS_MESSAGE_BASE_DELAY'] ?? 1000),
        'circuit_breaker_threshold' => (int)($_ENV['MAS_MESSAGE_CIRCUIT_THRESHOLD'] ?? 5),
        'circuit_breaker_timeout' => (int)($_ENV['MAS_MESSAGE_CIRCUIT_TIMEOUT'] ?? 300),
        
        // Batch processing and throttling
        'default_batch_size' => (int)($_ENV['MAS_MESSAGE_BATCH_SIZE'] ?? 100),
        'max_batch_size' => (int)($_ENV['MAS_MESSAGE_MAX_BATCH_SIZE'] ?? 1000),
        'throttle_delay' => (int)($_ENV['MAS_MESSAGE_THROTTLE_DELAY'] ?? 100), // milliseconds
        'max_concurrent_batches' => (int)($_ENV['MAS_MESSAGE_MAX_CONCURRENT_BATCHES'] ?? 5),
        
        // Queue and processing settings
        'enable_queue' => (bool)($_ENV['MAS_MESSAGE_ENABLE_QUEUE'] ?? true),
        'queue_driver' => $_ENV['MAS_MESSAGE_QUEUE_DRIVER'] ?? 'database',
        'queue_connection' => $_ENV['MAS_MESSAGE_QUEUE_CONNECTION'] ?? 'default',
        'queue_retry_attempts' => (int)($_ENV['MAS_MESSAGE_QUEUE_RETRIES'] ?? 3),
        'queue_retry_delay' => (int)($_ENV['MAS_MESSAGE_QUEUE_RETRY_DELAY'] ?? 60),
        
        // Provider-specific fallback chains
        'fallback_order' => [
            'email' => ['sendgrid', 'mailgun', 'amazon_ses', 'smtp', 'postmark', 'mailhog'],
            'sms' => ['twilio', 'nexmo', 'messagebird', 'clickatell', 'plivo'],
            'push' => ['onesignal', 'pusher', 'firebase', 'apns', 'fcm'],
            'whatsapp' => ['twilio', 'whatsapp_business_api', 'gupshup'],
            'telegram' => ['telegram_bot_api', 'telegram_webhook'],
            'slack' => ['slack_api', 'slack_webhook'],
            'discord' => ['discord_webhook', 'discord_bot'],
            'webhook' => ['guzzle_http', 'curl', 'stream_context']
        ],
        
        // Channel-specific configurations
        'channel_configs' => [
            'email' => [
                'default_from' => $_ENV['MAS_EMAIL_DEFAULT_FROM'] ?? 'noreply@example.com',
                'default_from_name' => $_ENV['MAS_EMAIL_DEFAULT_FROM_NAME'] ?? 'MAS System',
                'enable_tracking' => (bool)($_ENV['MAS_EMAIL_ENABLE_TRACKING'] ?? true),
                'enable_unsubscribe' => (bool)($_ENV['MAS_EMAIL_ENABLE_UNSUBSCRIBE'] ?? true),
                'max_recipients_per_batch' => (int)($_ENV['MAS_EMAIL_MAX_RECIPIENTS'] ?? 100)
            ],
            'sms' => [
                'default_from' => $_ENV['MAS_SMS_DEFAULT_FROM'] ?? null,
                'enable_delivery_reports' => (bool)($_ENV['MAS_SMS_DELIVERY_REPORTS'] ?? true),
                'max_message_length' => (int)($_ENV['MAS_SMS_MAX_LENGTH'] ?? 160),
                'enable_unicode' => (bool)($_ENV['MAS_SMS_ENABLE_UNICODE'] ?? true)
            ],
            'push' => [
                'default_badge_count' => (int)($_ENV['MAS_PUSH_DEFAULT_BADGE'] ?? 1),
                'enable_sound' => (bool)($_ENV['MAS_PUSH_ENABLE_SOUND'] ?? true),
                'default_ttl' => (int)($_ENV['MAS_PUSH_DEFAULT_TTL'] ?? 2419200) // 28 days
            ]
        ],
        
        // Analytics and monitoring
        'enable_delivery_tracking' => (bool)($_ENV['MAS_MESSAGE_DELIVERY_TRACKING'] ?? true),
        'track_open_rates' => (bool)($_ENV['MAS_MESSAGE_TRACK_OPENS'] ?? true),
        'track_click_rates' => (bool)($_ENV['MAS_MESSAGE_TRACK_CLICKS'] ?? true),
        'track_bounce_rates' => (bool)($_ENV['MAS_MESSAGE_TRACK_BOUNCES'] ?? true),
        'analytics_retention_days' => (int)($_ENV['MAS_MESSAGE_ANALYTICS_RETENTION'] ?? 365)
    ],
    
    /* ========================= PAYMENT GATEWAY CONFIGURATION ========================= */
    'payment_gateway' => [
        // Primary payment provider
        'default_provider' => $_ENV['MAS_PAYMENT_DEFAULT_PROVIDER'] ?? 'stripe',
        
        // Reliability settings
        'max_retries' => (int)($_ENV['MAS_PAYMENT_MAX_RETRIES'] ?? 2),
        'retry_backoff_multiplier' => (float)($_ENV['MAS_PAYMENT_BACKOFF_MULTIPLIER'] ?? 2.0),
        'retry_base_delay' => (int)($_ENV['MAS_PAYMENT_BASE_DELAY'] ?? 1000),
        'circuit_breaker_threshold' => (int)($_ENV['MAS_PAYMENT_CIRCUIT_THRESHOLD'] ?? 3),
        'circuit_breaker_timeout' => (int)($_ENV['MAS_PAYMENT_CIRCUIT_TIMEOUT'] ?? 300),
        
        // Security and rate limiting
        'enable_rate_limiting' => (bool)($_ENV['MAS_PAYMENT_ENABLE_RATE_LIMIT'] ?? true),
        'rate_limit_window' => (int)($_ENV['MAS_PAYMENT_RATE_WINDOW'] ?? 60),
        'rate_limit_max' => (int)($_ENV['MAS_PAYMENT_RATE_MAX'] ?? 100),
        'enable_fraud_detection' => (bool)($_ENV['MAS_PAYMENT_FRAUD_DETECTION'] ?? true),
        'max_transaction_amount' => (float)($_ENV['MAS_PAYMENT_MAX_AMOUNT'] ?? 10000.00),
        
        // Transaction settings
        'default_currency' => $_ENV['MAS_PAYMENT_DEFAULT_CURRENCY'] ?? 'USD',
        'enable_multi_currency' => (bool)($_ENV['MAS_PAYMENT_MULTI_CURRENCY'] ?? true),
        'transaction_timeout' => (int)($_ENV['MAS_PAYMENT_TIMEOUT'] ?? 30),
        'enable_webhooks' => (bool)($_ENV['MAS_PAYMENT_ENABLE_WEBHOOKS'] ?? true),
        
        // Operation-specific fallback chains
        'fallback_order' => [
            'authorize' => ['stripe', 'paypal', 'authorizenet', 'braintree', 'square'],
            'capture' => ['stripe', 'paypal', 'braintree', 'authorizenet'],
            'refund' => ['stripe', 'paypal', 'braintree', 'authorizenet'],
            'void' => ['stripe', 'authorizenet', 'braintree'],
            'subscribe' => ['stripe', 'paypal', 'braintree', 'recurly'],
            'cancel_subscription' => ['stripe', 'paypal', 'braintree', 'recurly'],
            'partial_refund' => ['stripe', 'paypal', 'braintree'],
            'recurring_payment' => ['stripe', 'paypal', 'braintree', 'recurly']
        ],
        
        // Provider-specific configurations
        'provider_configs' => [
            'stripe' => [
                'api_version' => '2023-10-16',
                'capture_method' => 'automatic',
                'confirmation_method' => 'automatic'
            ],
            'paypal' => [
                'mode' => $_ENV['MAS_PAYPAL_MODE'] ?? 'sandbox',
                'intent' => 'capture'
            ]
        ],
        
        // Compliance and logging
        'enable_pci_logging' => (bool)($_ENV['MAS_PAYMENT_PCI_LOGGING'] ?? true),
        'log_retention_days' => (int)($_ENV['MAS_PAYMENT_LOG_RETENTION'] ?? 2555),
        'enable_audit_trail' => (bool)($_ENV['MAS_PAYMENT_AUDIT_TRAIL'] ?? true)
    ],
    
    /* ========================= EVENT SYSTEM CONFIGURATION ========================= */
    'event_dispatcher' => [
        'enabled' => (bool)($_ENV['MAS_EVENTS_ENABLED'] ?? true),
        'max_listeners_per_event' => (int)($_ENV['MAS_EVENTS_MAX_LISTENERS'] ?? 100),
        'enable_priority_sorting' => (bool)($_ENV['MAS_EVENTS_PRIORITY_SORTING'] ?? true),
        'default_priority' => (int)($_ENV['MAS_EVENTS_DEFAULT_PRIORITY'] ?? 0),
        'enable_event_profiling' => (bool)($_ENV['MAS_EVENTS_PROFILING'] ?? false),
        'max_execution_time_per_listener' => (int)($_ENV['MAS_EVENTS_MAX_EXECUTION_TIME'] ?? 30)
    ],
    
    'event_queue' => [
        'enabled' => (bool)($_ENV['MAS_EVENT_QUEUE_ENABLED'] ?? true),
        'driver' => $_ENV['MAS_EVENT_QUEUE_DRIVER'] ?? 'database',
        'connection' => $_ENV['MAS_EVENT_QUEUE_CONNECTION'] ?? 'default',
        'batch_size' => (int)($_ENV['MAS_EVENT_QUEUE_BATCH_SIZE'] ?? 100),
        'max_attempts' => (int)($_ENV['MAS_EVENT_QUEUE_MAX_ATTEMPTS'] ?? 5),
        'initial_backoff' => (int)($_ENV['MAS_EVENT_QUEUE_INITIAL_BACKOFF'] ?? 30),
        'max_backoff' => (int)($_ENV['MAS_EVENT_QUEUE_MAX_BACKOFF'] ?? 3600),
        'backoff_multiplier' => (float)($_ENV['MAS_EVENT_QUEUE_BACKOFF_MULTIPLIER'] ?? 2.0),
        'worker_timeout' => (int)($_ENV['MAS_EVENT_QUEUE_WORKER_TIMEOUT'] ?? 60),
        'max_workers' => (int)($_ENV['MAS_EVENT_QUEUE_MAX_WORKERS'] ?? 5),
        'enable_dead_letter_queue' => (bool)($_ENV['MAS_EVENT_QUEUE_DEAD_LETTER'] ?? true),
        'dead_letter_retention_days' => (int)($_ENV['MAS_EVENT_QUEUE_DLQ_RETENTION'] ?? 30)
    ],
    
    /* ========================= AUDIT & COMPLIANCE CONFIGURATION ========================= */
    'audit_logger' => [
        'enabled' => (bool)($_ENV['MAS_AUDIT_ENABLED'] ?? true),
        'log_level' => $_ENV['MAS_AUDIT_LOG_LEVEL'] ?? 'info',
        'driver' => $_ENV['MAS_AUDIT_DRIVER'] ?? 'database',
        'retention_days' => (int)($_ENV['MAS_AUDIT_RETENTION_DAYS'] ?? 2555),
        'batch_size' => (int)($_ENV['MAS_AUDIT_BATCH_SIZE'] ?? 1000),
        'flush_interval' => (int)($_ENV['MAS_AUDIT_FLUSH_INTERVAL'] ?? 60),
        'enable_real_time_alerts' => (bool)($_ENV['MAS_AUDIT_REAL_TIME_ALERTS'] ?? true),
        'enable_encryption' => (bool)($_ENV['MAS_AUDIT_ENABLE_ENCRYPTION'] ?? true),
        'encryption_key' => $_ENV['MAS_AUDIT_ENCRYPTION_KEY'] ?? null,
        
        // Critical events that trigger immediate alerts
        'alert_events' => [
            'security.login_failed_multiple',
            'security.unauthorized_access',
            'security.privilege_escalation',
            'security.suspicious_activity',
            'security.account_lockout',
            'data.mass_deletion',
            'data.unauthorized_export',
            'data.integrity_violation',
            'system.critical_error',
            'system.service_unavailable',
            'system.memory_exhaustion',
            'compliance.gdpr_violation',
            'compliance.consent_revoked',
            'compliance.data_breach_detected',
            'payment.fraud_detected',
            'payment.chargeback_received',
            'payment.high_risk_transaction',
            'workflow.execution_failed',
            'campaign.delivery_failed',
            'provider.service_down'
        ],
        
        // Event categories with different retention policies
        'category_retention' => [
            'security' => 2555, // 7 years
            'compliance' => 2555, // 7 years
            'payment' => 2555, // 7 years
            'data' => 1825, // 5 years
            'system' => 365, // 1 year
            'user' => 730, // 2 years
            'workflow' => 365, // 1 year
            'campaign' => 730 // 2 years
        ],
        
        // Sensitive fields to exclude or mask in logs
        'sensitive_fields' => [
            'password', 'password_hash', 'api_key', 'secret', 'token',
            'credit_card', 'ssn', 'bank_account', 'personal_id',
            'private_key', 'certificate'
        ]
    ],
    
    'consent_manager' => [
        'enabled' => (bool)($_ENV['MAS_CONSENT_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_CONSENT_CACHE_TTL'] ?? 3600),
        'default_language' => $_ENV['MAS_CONSENT_DEFAULT_LANGUAGE'] ?? 'en-gb',
        'supported_languages' => ['en-gb', 'en-us', 'fr-fr', 'de-de', 'es-es', 'it-it'],
        'consent_retention_days' => (int)($_ENV['MAS_CONSENT_RETENTION_DAYS'] ?? 2555),
        'enable_consent_versioning' => (bool)($_ENV['MAS_CONSENT_VERSIONING'] ?? true),
        'require_explicit_consent' => (bool)($_ENV['MAS_CONSENT_EXPLICIT_REQUIRED'] ?? true),
        'enable_consent_withdrawal' => (bool)($_ENV['MAS_CONSENT_WITHDRAWAL_ENABLED'] ?? true),
        'grace_period_days' => (int)($_ENV['MAS_CONSENT_GRACE_PERIOD'] ?? 30),
        
        // Default consent types
        'default_consent_types' => [
            'marketing_email' => [
                'required' => false,
                'category' => 'marketing',
                'description' => 'Receive marketing emails and newsletters'
            ],
            'marketing_sms' => [
                'required' => false,
                'category' => 'marketing',
                'description' => 'Receive marketing SMS messages'
            ],
            'analytics' => [
                'required' => false,
                'category' => 'analytics',
                'description' => 'Allow collection of analytics data'
            ],
            'personalization' => [
                'required' => false,
                'category' => 'personalization',
                'description' => 'Enable personalized content and recommendations'
            ]
        ]
    ],
    
    /* ========================= REPORTING CONFIGURATION ========================= */
    'dashboard_service' => [
        'enabled' => (bool)($_ENV['MAS_DASHBOARD_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_DASHBOARD_CACHE_TTL'] ?? 900),
        'cache_namespace' => 'mas_dashboard',
        'supported_grains' => ['hour', 'day', 'week', 'month', 'quarter', 'year'],
        'default_grain' => $_ENV['MAS_DASHBOARD_DEFAULT_GRAIN'] ?? 'day',
        'max_date_range_days' => (int)($_ENV['MAS_DASHBOARD_MAX_DATE_RANGE'] ?? 365),
        'enable_real_time_updates' => (bool)($_ENV['MAS_DASHBOARD_REAL_TIME'] ?? true),
        'real_time_update_interval' => (int)($_ENV['MAS_DASHBOARD_UPDATE_INTERVAL'] ?? 30),
        'enable_drill_down' => (bool)($_ENV['MAS_DASHBOARD_DRILL_DOWN'] ?? true),
        'max_drill_down_depth' => (int)($_ENV['MAS_DASHBOARD_MAX_DRILL_DEPTH'] ?? 3),
        
        // Available KPI metrics
        'available_kpis' => [
            'revenue', 'orders', 'aov', 'conversion_rate', 'new_customers',
            'returning_customers', 'customer_lifetime_value', 'churn_rate',
            'email_open_rate', 'email_click_rate', 'sms_delivery_rate',
            'campaign_roi', 'segment_growth', 'workflow_completion_rate'
        ],
        
        // Widget configurations
        'widget_configs' => [
            'refresh_intervals' => [300, 600, 900, 1800, 3600], // seconds
            'chart_types' => ['line', 'bar', 'pie', 'area', 'gauge', 'table'],
            'max_data_points' => (int)($_ENV['MAS_DASHBOARD_MAX_DATA_POINTS'] ?? 1000)
        ]
    ],
    
    'csv_exporter' => [
        'enabled' => (bool)($_ENV['MAS_CSV_EXPORTER_ENABLED'] ?? true),
        'delimiter' => $_ENV['MAS_CSV_DELIMITER'] ?? ',',
        'enclosure' => $_ENV['MAS_CSV_ENCLOSURE'] ?? '"',
        'escape' => $_ENV['MAS_CSV_ESCAPE'] ?? '"',
        'add_bom' => (bool)($_ENV['MAS_CSV_ADD_BOM'] ?? true),
        'batch_size' => (int)($_ENV['MAS_CSV_BATCH_SIZE'] ?? 1000),
        'max_file_size_mb' => (int)($_ENV['MAS_CSV_MAX_FILE_SIZE'] ?? 100),
        'temp_directory' => $_ENV['MAS_CSV_TEMP_DIR'] ?? sys_get_temp_dir(),
        'enable_compression' => (bool)($_ENV['MAS_CSV_ENABLE_COMPRESSION'] ?? true),
        'compression_level' => (int)($_ENV['MAS_CSV_COMPRESSION_LEVEL'] ?? 6),
        'enable_streaming' => (bool)($_ENV['MAS_CSV_ENABLE_STREAMING'] ?? true),
        'chunk_size' => (int)($_ENV['MAS_CSV_CHUNK_SIZE'] ?? 8192),
        
        // Security settings
        'allowed_fields' => [], // Empty = all fields allowed, populate to restrict
        'forbidden_fields' => ['password', 'password_hash', 'api_key', 'secret'],
        'enable_field_validation' => (bool)($_ENV['MAS_CSV_FIELD_VALIDATION'] ?? true)
    ],
    
    /* ========================= SEGMENTATION CONFIGURATION ========================= */
    'segment_manager' => [
        'enabled' => (bool)($_ENV['MAS_SEGMENTATION_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_SEGMENTATION_CACHE_TTL'] ?? 3600),
        'cache_namespace' => 'mas_segmentation',
        'batch_size' => (int)($_ENV['MAS_SEGMENTATION_BATCH_SIZE'] ?? 1000),
        'auto_refresh' => (bool)($_ENV['MAS_SEGMENTATION_AUTO_REFRESH'] ?? true),
        'refresh_interval_hours' => (int)($_ENV['MAS_SEGMENTATION_REFRESH_INTERVAL'] ?? 24),
        'max_segment_size' => (int)($_ENV['MAS_SEGMENTATION_MAX_SIZE'] ?? 100000),
        'enable_real_time_updates' => (bool)($_ENV['MAS_SEGMENTATION_REAL_TIME'] ?? false),
        'max_segments_per_customer' => (int)($_ENV['MAS_SEGMENTATION_MAX_PER_CUSTOMER'] ?? 50),
        
        // Segment types and configurations
        'segment_types' => [
            'static' => ['refresh_required' => false, 'real_time_updates' => false],
            'dynamic' => ['refresh_required' => true, 'real_time_updates' => true],
            'ai_generated' => ['refresh_required' => true, 'real_time_updates' => false],
            'behavioral' => ['refresh_required' => true, 'real_time_updates' => true],
            'demographic' => ['refresh_required' => false, 'real_time_updates' => false]
        ],
        
        // Performance optimization
        'enable_query_optimization' => (bool)($_ENV['MAS_SEGMENTATION_QUERY_OPTIMIZATION'] ?? true),
        'enable_parallel_processing' => (bool)($_ENV['MAS_SEGMENTATION_PARALLEL_PROCESSING'] ?? true),
        'max_parallel_workers' => (int)($_ENV['MAS_SEGMENTATION_MAX_WORKERS'] ?? 4),
        'query_timeout' => (int)($_ENV['MAS_SEGMENTATION_QUERY_TIMEOUT'] ?? 300)
    ],
    
    'segment_suggestor' => [
        'enabled' => (bool)($_ENV['MAS_SEGMENT_SUGGESTOR_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_SEGMENT_SUGGESTOR_CACHE_TTL'] ?? 3600),
        'ai_providers_path' => $_ENV['MAS_AI_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/ai/',
        'default_ai_provider' => $_ENV['MAS_SEGMENT_SUGGESTOR_AI_PROVIDER'] ?? 'openai',
        'max_suggestions_per_request' => (int)($_ENV['MAS_SEGMENT_SUGGESTOR_MAX_SUGGESTIONS'] ?? 10),
        'min_confidence_threshold' => (float)($_ENV['MAS_SEGMENT_SUGGESTOR_MIN_CONFIDENCE'] ?? 0.7),
        'enable_suggestion_validation' => (bool)($_ENV['MAS_SEGMENT_SUGGESTOR_VALIDATION'] ?? true),
        
        // Supported suggestion types with configurations
        'supported_types' => [
            'auto_discover' => ['min_customers' => 50, 'confidence_threshold' => 0.8],
            'rfm_optimization' => ['min_customers' => 100, 'confidence_threshold' => 0.7],
            'behavioral_patterns' => ['min_customers' => 25, 'confidence_threshold' => 0.6],
            'conversion_prediction' => ['min_customers' => 200, 'confidence_threshold' => 0.8],
            'churn_prediction' => ['min_customers' => 100, 'confidence_threshold' => 0.75],
            'engagement_optimization' => ['min_customers' => 50, 'confidence_threshold' => 0.7],
            'demographic_insights' => ['min_customers' => 30, 'confidence_threshold' => 0.6],
            'seasonal_patterns' => ['min_customers' => 100, 'confidence_threshold' => 0.7],
            'product_affinity' => ['min_customers' => 50, 'confidence_threshold' => 0.65],
            'cross_sell_opportunities' => ['min_customers' => 75, 'confidence_threshold' => 0.75],
            'retention_strategies' => ['min_customers' => 100, 'confidence_threshold' => 0.7],
            'lifecycle_stages' => ['min_customers' => 50, 'confidence_threshold' => 0.8],
            'geographic_clustering' => ['min_customers' => 25, 'confidence_threshold' => 0.6],
            'value_based_segments' => ['min_customers' => 100, 'confidence_threshold' => 0.8]
        ],
        
        // AI model configurations for different suggestion types
        'ai_model_configs' => [
            'clustering' => ['algorithm' => 'kmeans', 'max_clusters' => 20, 'min_cluster_size' => 10],
            'classification' => ['algorithm' => 'random_forest', 'n_estimators' => 100, 'max_depth' => 10],
            'prediction' => ['algorithm' => 'xgboost', 'n_estimators' => 200, 'learning_rate' => 0.1]
        ]
    ],
    
    /* ========================= AI SUGGESTORS CONFIGURATION ========================= */
    'openai_suggester' => [
        'enabled' => (bool)($_ENV['MAS_OPENAI_SUGGESTER_ENABLED'] ?? true),
        'model' => $_ENV['MAS_OPENAI_MODEL'] ?? 'gpt-4-turbo',
        'temperature' => (float)($_ENV['MAS_OPENAI_TEMPERATURE'] ?? 0.3),
        'max_tokens' => (int)($_ENV['MAS_OPENAI_MAX_TOKENS'] ?? 2000),
        'top_p' => (float)($_ENV['MAS_OPENAI_TOP_P'] ?? 0.9),
        'frequency_penalty' => (float)($_ENV['MAS_OPENAI_FREQUENCY_PENALTY'] ?? 0.1),
        'presence_penalty' => (float)($_ENV['MAS_OPENAI_PRESENCE_PENALTY'] ?? 0.1),
        'timeout' => (int)($_ENV['MAS_OPENAI_TIMEOUT'] ?? 60),
        
        // Enhanced prompt templates
        'system_prompt' => 'You are an expert marketing automation consultant specializing in customer segmentation and data analysis. Analyze customer data patterns and provide actionable marketing insights with clear, measurable criteria for each segment.',
        
        'user_prompt_template' => 'GOAL: {{goal}}
        
CUSTOMER DATA ANALYSIS:
Total Customers: {{total_customers}}
Date Range: {{date_range}}
Sample Data: {{data_sample}}
        
BUSINESS CONTEXT:
Industry: {{industry}}
Business Model: {{business_model}}
Target Market: {{target_market}}
        
REQUIREMENTS:
- Provide 3-7 distinct customer segments
- Each segment must have at least {{min_segment_size}} customers
- Focus on actionable insights and clear criteria
- Include recommended marketing actions
        
Return a JSON array with this exact structure:
[
  {
    "id": "unique_segment_id",
    "name": "Segment Name",
    "description": "Detailed description of segment characteristics",
    "criteria": {
      "conditions": ["condition1", "condition2"],
      "filters": {"field": "value"}
    },
    "characteristics": ["trait1", "trait2", "trait3"],
    "size_estimate": estimated_number_of_customers,
    "value_score": confidence_score_0_to_100,
    "recommended_actions": ["action1", "action2", "action3"],
    "expected_outcomes": ["outcome1", "outcome2"]
  }
]',
        
        // Validation and quality control
        'enable_response_validation' => (bool)($_ENV['MAS_OPENAI_RESPONSE_VALIDATION'] ?? true),
        'max_response_size' => (int)($_ENV['MAS_OPENAI_MAX_RESPONSE_SIZE'] ?? 10000),
        'enable_content_filtering' => (bool)($_ENV['MAS_OPENAI_CONTENT_FILTERING'] ?? true),
        'retry_on_invalid_json' => (bool)($_ENV['MAS_OPENAI_RETRY_INVALID_JSON'] ?? true),
        'max_json_retry_attempts' => (int)($_ENV['MAS_OPENAI_MAX_JSON_RETRIES'] ?? 3)
    ],
    
    /* ========================= WORKFLOW ENGINE CONFIGURATION ========================= */
    'workflow_engine' => [
        'enabled' => (bool)($_ENV['MAS_WORKFLOW_ENGINE_ENABLED'] ?? true),
        'max_execution_time' => (int)($_ENV['MAS_WORKFLOW_MAX_EXECUTION_TIME'] ?? 300),
        'batch_size' => (int)($_ENV['MAS_WORKFLOW_BATCH_SIZE'] ?? 100),
        'retry_attempts' => (int)($_ENV['MAS_WORKFLOW_RETRY_ATTEMPTS'] ?? 3),
        'retry_backoff_multiplier' => (float)($_ENV['MAS_WORKFLOW_BACKOFF_MULTIPLIER'] ?? 2.0),
        'enable_logging' => (bool)($_ENV['MAS_WORKFLOW_ENABLE_LOGGING'] ?? true),
        'log_level' => $_ENV['MAS_WORKFLOW_LOG_LEVEL'] ?? 'info',
        'enable_queue' => (bool)($_ENV['MAS_WORKFLOW_ENABLE_QUEUE'] ?? true),
        'queue_driver' => $_ENV['MAS_WORKFLOW_QUEUE_DRIVER'] ?? 'database',
        
        // Performance and resource limits
        'max_concurrent_executions' => (int)($_ENV['MAS_WORKFLOW_MAX_CONCURRENT'] ?? 10),
        'memory_limit' => $_ENV['MAS_WORKFLOW_MEMORY_LIMIT'] ?? '256M',
        'enable_execution_tracking' => (bool)($_ENV['MAS_WORKFLOW_EXECUTION_TRACKING'] ?? true),
        'execution_history_retention_days' => (int)($_ENV['MAS_WORKFLOW_HISTORY_RETENTION'] ?? 90),
        
        // Node execution settings
        'node_timeout' => (int)($_ENV['MAS_WORKFLOW_NODE_TIMEOUT'] ?? 30),
        'max_nodes_per_workflow' => (int)($_ENV['MAS_WORKFLOW_MAX_NODES'] ?? 100),
        'enable_node_caching' => (bool)($_ENV['MAS_WORKFLOW_NODE_CACHING'] ?? true),
        'node_cache_ttl' => (int)($_ENV['MAS_WORKFLOW_NODE_CACHE_TTL'] ?? 300),
        
        // Error handling and recovery
        'enable_auto_recovery' => (bool)($_ENV['MAS_WORKFLOW_AUTO_RECOVERY'] ?? true),
        'recovery_strategies' => ['retry', 'skip', 'fallback', 'escalate'],
        'enable_rollback' => (bool)($_ENV['MAS_WORKFLOW_ENABLE_ROLLBACK'] ?? true),
        'checkpoint_interval' => (int)($_ENV['MAS_WORKFLOW_CHECKPOINT_INTERVAL'] ?? 10)
    ],
    
    'workflow_manager' => [
        'enabled' => (bool)($_ENV['MAS_WORKFLOW_MANAGER_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_WORKFLOW_MANAGER_CACHE_TTL'] ?? 3600),
        'auto_save_interval' => (int)($_ENV['MAS_WORKFLOW_AUTO_SAVE_INTERVAL'] ?? 30),
        'max_workflow_size_nodes' => (int)($_ENV['MAS_WORKFLOW_MAX_SIZE'] ?? 50),
        'enable_versioning' => (bool)($_ENV['MAS_WORKFLOW_ENABLE_VERSIONING'] ?? true),
        'max_versions_per_workflow' => (int)($_ENV['MAS_WORKFLOW_MAX_VERSIONS'] ?? 10),
        'enable_draft_mode' => (bool)($_ENV['MAS_WORKFLOW_ENABLE_DRAFT'] ?? true),
        'enable_workflow_validation' => (bool)($_ENV['MAS_WORKFLOW_ENABLE_VALIDATION'] ?? true),
        
        // Workflow templates and library
        'enable_template_library' => (bool)($_ENV['MAS_WORKFLOW_TEMPLATE_LIBRARY'] ?? true),
        'template_cache_ttl' => (int)($_ENV['MAS_WORKFLOW_TEMPLATE_CACHE_TTL'] ?? 7200),
        'enable_workflow_sharing' => (bool)($_ENV['MAS_WORKFLOW_SHARING'] ?? false),
        'enable_workflow_import_export' => (bool)($_ENV['MAS_WORKFLOW_IMPORT_EXPORT'] ?? true),
        
        // Analytics and monitoring
        'enable_workflow_analytics' => (bool)($_ENV['MAS_WORKFLOW_ANALYTICS'] ?? true),
        'analytics_retention_days' => (int)($_ENV['MAS_WORKFLOW_ANALYTICS_RETENTION'] ?? 365),
        'enable_performance_monitoring' => (bool)($_ENV['MAS_WORKFLOW_PERFORMANCE_MONITORING'] ?? true)
    ],
    
    /* ========================= PROVIDER MANAGEMENT CONFIGURATION ========================= */
    'provider_manager' => [
        'enabled' => (bool)($_ENV['MAS_PROVIDER_MANAGER_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_PROVIDER_MANAGER_CACHE_TTL'] ?? 3600),
        'auto_discovery' => (bool)($_ENV['MAS_PROVIDER_AUTO_DISCOVERY'] ?? true),
        'discovery_cache_ttl' => (int)($_ENV['MAS_PROVIDER_DISCOVERY_CACHE_TTL'] ?? 7200),
        'enable_provider_health_checks' => (bool)($_ENV['MAS_PROVIDER_HEALTH_CHECKS'] ?? true),
        'health_check_interval' => (int)($_ENV['MAS_PROVIDER_HEALTH_CHECK_INTERVAL'] ?? 300),
        'health_check_timeout' => (int)($_ENV['MAS_PROVIDER_HEALTH_CHECK_TIMEOUT'] ?? 10),
        
        // Provider discovery paths
        'discovery_paths' => [
            'ai' => $_ENV['MAS_AI_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/ai/',
            'message' => $_ENV['MAS_MESSAGE_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/message/',
            'payment' => $_ENV['MAS_PAYMENT_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/payment/',
            'analytics' => $_ENV['MAS_ANALYTICS_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/analytics/',
            'storage' => $_ENV['MAS_STORAGE_PROVIDERS_PATH'] ?? DIR_SYSTEM . 'library/mas/providers/storage/'
        ],
        
        // Provider validation and security
        'enable_provider_validation' => (bool)($_ENV['MAS_PROVIDER_VALIDATION'] ?? true),
        'require_provider_signature' => (bool)($_ENV['MAS_PROVIDER_REQUIRE_SIGNATURE'] ?? false),
        'allowed_provider_sources' => [], // Empty = allow all, populate to restrict
        'enable_provider_sandboxing' => (bool)($_ENV['MAS_PROVIDER_SANDBOXING'] ?? false),
        
        // Load balancing and failover
        'enable_load_balancing' => (bool)($_ENV['MAS_PROVIDER_LOAD_BALANCING'] ?? true),
        'load_balancing_strategy' => $_ENV['MAS_PROVIDER_LB_STRATEGY'] ?? 'round_robin', // round_robin, weighted, least_connections
        'enable_automatic_failover' => (bool)($_ENV['MAS_PROVIDER_AUTO_FAILOVER'] ?? true),
        'failover_threshold' => (int)($_ENV['MAS_PROVIDER_FAILOVER_THRESHOLD'] ?? 3),
        'provider_circuit_breaker_timeout' => (int)($_ENV['MAS_PROVIDER_CIRCUIT_TIMEOUT'] ?? 300)
    ],
    
    /* ========================= CAMPAIGN MANAGEMENT CONFIGURATION ========================= */
    'campaign_manager' => [
        'enabled' => (bool)($_ENV['MAS_CAMPAIGN_MANAGER_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_CAMPAIGN_CACHE_TTL'] ?? 1800),
        'batch_size' => (int)($_ENV['MAS_CAMPAIGN_BATCH_SIZE'] ?? 500),
        'max_recipients' => (int)($_ENV['MAS_CAMPAIGN_MAX_RECIPIENTS'] ?? 10000),
        'max_concurrent_campaigns' => (int)($_ENV['MAS_CAMPAIGN_MAX_CONCURRENT'] ?? 5),
        'throttle_rate_per_second' => (int)($_ENV['MAS_CAMPAIGN_THROTTLE_RATE'] ?? 100),
        'enable_tracking' => (bool)($_ENV['MAS_CAMPAIGN_ENABLE_TRACKING'] ?? true),
        'tracking_retention_days' => (int)($_ENV['MAS_CAMPAIGN_TRACKING_RETENTION'] ?? 365),
        
        // Campaign types and configurations
        'campaign_types' => [
            'email' => ['max_recipients' => 50000, 'throttle_rate' => 1000],
            'sms' => ['max_recipients' => 10000, 'throttle_rate' => 100],
            'push' => ['max_recipients' => 100000, 'throttle_rate' => 5000],
            'webhook' => ['max_recipients' => 1000, 'throttle_rate' => 50]
        ],
        
        // Scheduling and automation
        'enable_scheduling' => (bool)($_ENV['MAS_CAMPAIGN_SCHEDULING'] ?? true),
        'min_schedule_advance_minutes' => (int)($_ENV['MAS_CAMPAIGN_MIN_SCHEDULE_ADVANCE'] ?? 15),
        'enable_timezone_support' => (bool)($_ENV['MAS_CAMPAIGN_TIMEZONE_SUPPORT'] ?? true),
        'enable_send_time_optimization' => (bool)($_ENV['MAS_CAMPAIGN_SEND_TIME_OPTIMIZATION'] ?? true),
        
        // A/B testing and optimization
        'enable_ab_testing' => (bool)($_ENV['MAS_CAMPAIGN_AB_TESTING'] ?? true),
        'ab_test_min_sample_size' => (int)($_ENV['MAS_CAMPAIGN_AB_MIN_SAMPLE'] ?? 100),
        'ab_test_confidence_level' => (float)($_ENV['MAS_CAMPAIGN_AB_CONFIDENCE'] ?? 0.95),
        'ab_test_max_variants' => (int)($_ENV['MAS_CAMPAIGN_AB_MAX_VARIANTS'] ?? 5),
        
        // Quality control and compliance
        'enable_content_validation' => (bool)($_ENV['MAS_CAMPAIGN_CONTENT_VALIDATION'] ?? true),
        'enable_spam_checking' => (bool)($_ENV['MAS_CAMPAIGN_SPAM_CHECKING'] ?? true),
        'enable_suppression_list' => (bool)($_ENV['MAS_CAMPAIGN_SUPPRESSION_LIST'] ?? true),
        'enable_frequency_capping' => (bool)($_ENV['MAS_CAMPAIGN_FREQUENCY_CAPPING'] ?? true),
        'default_frequency_cap_per_day' => (int)($_ENV['MAS_CAMPAIGN_FREQUENCY_CAP'] ?? 3)
    ],
    
    /* ========================= TEMPLATE ENGINE CONFIGURATION ========================= */
    'template_engine' => [
        'enabled' => (bool)($_ENV['MAS_TEMPLATE_ENGINE_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_TEMPLATE_CACHE_TTL'] ?? 7200),
        'compile_check' => (bool)($_ENV['MAS_TEMPLATE_COMPILE_CHECK'] ?? true),
        'auto_escape' => (bool)($_ENV['MAS_TEMPLATE_AUTO_ESCAPE'] ?? true),
        'enable_caching' => (bool)($_ENV['MAS_TEMPLATE_ENABLE_CACHING'] ?? true),
        'cache_directory' => $_ENV['MAS_TEMPLATE_CACHE_DIR'] ?? sys_get_temp_dir() . '/mas_templates',
        
        // Template discovery paths
        'template_paths' => [
            DIR_SYSTEM . 'library/mas/templates/',
            DIR_EXTENSION . 'mas/catalog/view/template/',
            DIR_EXTENSION . 'mas/admin/view/template/'
        ],
        
        // Template processing settings
        'max_template_size' => (int)($_ENV['MAS_TEMPLATE_MAX_SIZE'] ?? 1048576), // 1MB
        'max_include_depth' => (int)($_ENV['MAS_TEMPLATE_MAX_INCLUDE_DEPTH'] ?? 10),
        'enable_template_inheritance' => (bool)($_ENV['MAS_TEMPLATE_INHERITANCE'] ?? true),
        'enable_template_macros' => (bool)($_ENV['MAS_TEMPLATE_MACROS'] ?? true),
        
        // Security settings
        'allowed_functions' => [], // Empty = allow all, populate to restrict
        'forbidden_functions' => ['exec', 'system', 'shell_exec', 'passthru', 'eval'],
        'enable_php_execution' => (bool)($_ENV['MAS_TEMPLATE_PHP_EXECUTION'] ?? false),
        'sandbox_mode' => (bool)($_ENV['MAS_TEMPLATE_SANDBOX_MODE'] ?? true),
        
        // Internationalization
        'enable_i18n' => (bool)($_ENV['MAS_TEMPLATE_I18N'] ?? true),
        'default_locale' => $_ENV['MAS_TEMPLATE_DEFAULT_LOCALE'] ?? 'en_US',
        'fallback_locale' => $_ENV['MAS_TEMPLATE_FALLBACK_LOCALE'] ?? 'en_US'
    ],
    
    /* ========================= ANALYTICS CONFIGURATION ========================= */
    'analytics_manager' => [
        'enabled' => (bool)($_ENV['MAS_ANALYTICS_ENABLED'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_ANALYTICS_CACHE_TTL'] ?? 1800),
        'retention_days' => (int)($_ENV['MAS_ANALYTICS_RETENTION_DAYS'] ?? 365),
        'batch_size' => (int)($_ENV['MAS_ANALYTICS_BATCH_SIZE'] ?? 1000),
        'enable_real_time' => (bool)($_ENV['MAS_ANALYTICS_REAL_TIME'] ?? true),
        'real_time_buffer_size' => (int)($_ENV['MAS_ANALYTICS_RT_BUFFER_SIZE'] ?? 100),
        'real_time_flush_interval' => (int)($_ENV['MAS_ANALYTICS_RT_FLUSH_INTERVAL'] ?? 5),
        
        // Data aggregation settings
        'aggregation_interval' => $_ENV['MAS_ANALYTICS_AGGREGATION_INTERVAL'] ?? 'hourly',
        'supported_intervals' => ['minutely', 'hourly', 'daily', 'weekly', 'monthly'],
        'enable_custom_dimensions' => (bool)($_ENV['MAS_ANALYTICS_CUSTOM_DIMENSIONS'] ?? true),
        'max_custom_dimensions' => (int)($_ENV['MAS_ANALYTICS_MAX_CUSTOM_DIMENSIONS'] ?? 20),
        
        // Event tracking configuration
        'auto_track_events' => (bool)($_ENV['MAS_ANALYTICS_AUTO_TRACK'] ?? true),
        'tracked_events' => [
            'page_view', 'session_start', 'session_end', 'user_login', 'user_logout',
            'purchase', 'add_to_cart', 'remove_from_cart', 'checkout_start', 'checkout_complete',
            'email_open', 'email_click', 'email_bounce', 'email_unsubscribe',
            'campaign_view', 'campaign_click', 'campaign_conversion',
            'workflow_start', 'workflow_complete', 'workflow_abandon'
        ],
        
        // Privacy and compliance
        'enable_ip_anonymization' => (bool)($_ENV['MAS_ANALYTICS_ANONYMIZE_IP'] ?? true),
        'enable_gdpr_mode' => (bool)($_ENV['MAS_ANALYTICS_GDPR_MODE'] ?? true),
        'data_retention_policy' => $_ENV['MAS_ANALYTICS_RETENTION_POLICY'] ?? 'automatic',
        'enable_data_export' => (bool)($_ENV['MAS_ANALYTICS_DATA_EXPORT'] ?? true),
        
        // Performance optimization
        'enable_data_sampling' => (bool)($_ENV['MAS_ANALYTICS_DATA_SAMPLING'] ?? false),
        'sampling_rate' => (float)($_ENV['MAS_ANALYTICS_SAMPLING_RATE'] ?? 0.1),
        'enable_query_optimization' => (bool)($_ENV['MAS_ANALYTICS_QUERY_OPTIMIZATION'] ?? true),
        'query_timeout' => (int)($_ENV['MAS_ANALYTICS_QUERY_TIMEOUT'] ?? 30)
    ],
    
    /* ========================= SECURITY & PERFORMANCE CONFIGURATION ========================= */
    'security' => [
        // Access control
        'enable_ip_whitelist' => (bool)($_ENV['MAS_SECURITY_IP_WHITELIST'] ?? false),
        'allowed_ips' => explode(',', $_ENV['MAS_SECURITY_ALLOWED_IPS'] ?? ''),
        'enable_ip_blacklist' => (bool)($_ENV['MAS_SECURITY_IP_BLACKLIST'] ?? true),
        'blocked_ips' => explode(',', $_ENV['MAS_SECURITY_BLOCKED_IPS'] ?? ''),
        
        // Rate limiting
        'rate_limit_enabled' => (bool)($_ENV['MAS_SECURITY_RATE_LIMIT'] ?? true),
        'max_requests_per_minute' => (int)($_ENV['MAS_SECURITY_MAX_REQUESTS_MINUTE'] ?? 60),
        'max_requests_per_hour' => (int)($_ENV['MAS_SECURITY_MAX_REQUESTS_HOUR'] ?? 3600),
        'rate_limit_whitelist' => explode(',', $_ENV['MAS_SECURITY_RATE_LIMIT_WHITELIST'] ?? ''),
        
        // Authentication and session management
        'enable_csrf_protection' => (bool)($_ENV['MAS_SECURITY_CSRF_PROTECTION'] ?? true),
        'csrf_token_lifetime' => (int)($_ENV['MAS_SECURITY_CSRF_LIFETIME'] ?? 3600),
        'session_timeout' => (int)($_ENV['MAS_SECURITY_SESSION_TIMEOUT'] ?? 7200),
        'max_concurrent_sessions' => (int)($_ENV['MAS_SECURITY_MAX_SESSIONS'] ?? 5),
        'enable_session_regeneration' => (bool)($_ENV['MAS_SECURITY_SESSION_REGENERATION'] ?? true),
        
        // Password and encryption
        'password_min_length' => (int)($_ENV['MAS_SECURITY_PASSWORD_MIN_LENGTH'] ?? 8),
        'password_require_mixed_case' => (bool)($_ENV['MAS_SECURITY_PASSWORD_MIXED_CASE'] ?? true),
        'password_require_numbers' => (bool)($_ENV['MAS_SECURITY_PASSWORD_NUMBERS'] ?? true),
        'password_require_symbols' => (bool)($_ENV['MAS_SECURITY_PASSWORD_SYMBOLS'] ?? true),
        'encryption_algorithm' => $_ENV['MAS_SECURITY_ENCRYPTION_ALGORITHM'] ?? 'AES-256-GCM',
        'encryption_key' => $_ENV['MAS_SECURITY_ENCRYPTION_KEY'] ?? null,
        
        // Intrusion detection and prevention
        'enable_intrusion_detection' => (bool)($_ENV['MAS_SECURITY_INTRUSION_DETECTION'] ?? true),
        'max_failed_login_attempts' => (int)($_ENV['MAS_SECURITY_MAX_FAILED_LOGINS'] ?? 5),
        'account_lockout_duration' => (int)($_ENV['MAS_SECURITY_LOCKOUT_DURATION'] ?? 900),
        'enable_suspicious_activity_detection' => (bool)($_ENV['MAS_SECURITY_SUSPICIOUS_ACTIVITY'] ?? true),
        
        // Content security
        'enable_xss_protection' => (bool)($_ENV['MAS_SECURITY_XSS_PROTECTION'] ?? true),
        'enable_sql_injection_protection' => (bool)($_ENV['MAS_SECURITY_SQL_INJECTION_PROTECTION'] ?? true),
        'enable_file_upload_scanning' => (bool)($_ENV['MAS_SECURITY_FILE_UPLOAD_SCANNING'] ?? true),
        'allowed_file_extensions' => explode(',', $_ENV['MAS_SECURITY_ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,txt'),
        'max_file_upload_size' => (int)($_ENV['MAS_SECURITY_MAX_UPLOAD_SIZE'] ?? 10485760) // 10MB
    ],
    
    'performance' => [
        // Query optimization
        'enable_query_cache' => (bool)($_ENV['MAS_PERFORMANCE_QUERY_CACHE'] ?? true),
        'query_cache_ttl' => (int)($_ENV['MAS_PERFORMANCE_QUERY_CACHE_TTL'] ?? 300),
        'query_cache_size' => (int)($_ENV['MAS_PERFORMANCE_QUERY_CACHE_SIZE'] ?? 64), // MB
        'enable_query_optimization' => (bool)($_ENV['MAS_PERFORMANCE_QUERY_OPTIMIZATION'] ?? true),
        'slow_query_threshold' => (float)($_ENV['MAS_PERFORMANCE_SLOW_QUERY_THRESHOLD'] ?? 2.0),
        
        // Caching strategies
        'enable_redis_cache' => (bool)($_ENV['MAS_PERFORMANCE_REDIS_CACHE'] ?? false),
        'redis_cache_ttl' => (int)($_ENV['MAS_PERFORMANCE_REDIS_TTL'] ?? 3600),
        'enable_memcached' => (bool)($_ENV['MAS_PERFORMANCE_MEMCACHED'] ?? false),
        'enable_file_cache' => (bool)($_ENV['MAS_PERFORMANCE_FILE_CACHE'] ?? true),
        'cache_compression' => (bool)($_ENV['MAS_PERFORMANCE_CACHE_COMPRESSION'] ?? true),
        
        // Resource management
        'memory_limit' => $_ENV['MAS_PERFORMANCE_MEMORY_LIMIT'] ?? '512M',
        'execution_time_limit' => (int)($_ENV['MAS_PERFORMANCE_EXECUTION_TIME_LIMIT'] ?? 300),
        'max_input_vars' => (int)($_ENV['MAS_PERFORMANCE_MAX_INPUT_VARS'] ?? 10000),
        'enable_compression' => (bool)($_ENV['MAS_PERFORMANCE_ENABLE_COMPRESSION'] ?? true),
        'compression_level' => (int)($_ENV['MAS_PERFORMANCE_COMPRESSION_LEVEL'] ?? 6),
        
        // Database optimization
        'enable_database_connection_pooling' => (bool)($_ENV['MAS_PERFORMANCE_DB_POOLING'] ?? false),
        'database_connection_pool_size' => (int)($_ENV['MAS_PERFORMANCE_DB_POOL_SIZE'] ?? 10),
        'enable_read_write_splitting' => (bool)($_ENV['MAS_PERFORMANCE_DB_RW_SPLITTING'] ?? false),
        'database_query_timeout' => (int)($_ENV['MAS_PERFORMANCE_DB_QUERY_TIMEOUT'] ?? 30),
        
        // Background processing
        'enable_background_processing' => (bool)($_ENV['MAS_PERFORMANCE_BACKGROUND_PROCESSING'] ?? true),
        'max_background_workers' => (int)($_ENV['MAS_PERFORMANCE_MAX_BG_WORKERS'] ?? 5),
        'background_worker_timeout' => (int)($_ENV['MAS_PERFORMANCE_BG_WORKER_TIMEOUT'] ?? 300),
        
        // Monitoring and profiling
        'enable_performance_monitoring' => (bool)($_ENV['MAS_PERFORMANCE_MONITORING'] ?? true),
        'enable_memory_profiling' => (bool)($_ENV['MAS_PERFORMANCE_MEMORY_PROFILING'] ?? false),
        'enable_query_profiling' => (bool)($_ENV['MAS_PERFORMANCE_QUERY_PROFILING'] ?? false),
        'performance_log_threshold' => (float)($_ENV['MAS_PERFORMANCE_LOG_THRESHOLD'] ?? 1.0)
    ],
    
    /* ========================= INTEGRATION CONFIGURATION ========================= */
    'webhooks' => [
        'enabled' => (bool)($_ENV['MAS_WEBHOOKS_ENABLED'] ?? true),
        'timeout' => (int)($_ENV['MAS_WEBHOOKS_TIMEOUT'] ?? 30),
        'retry_attempts' => (int)($_ENV['MAS_WEBHOOKS_RETRY_ATTEMPTS'] ?? 3),
        'retry_backoff_multiplier' => (float)($_ENV['MAS_WEBHOOKS_BACKOFF_MULTIPLIER'] ?? 2.0),
        'verify_ssl' => (bool)($_ENV['MAS_WEBHOOKS_VERIFY_SSL'] ?? true),
        'max_payload_size' => (int)($_ENV['MAS_WEBHOOKS_MAX_PAYLOAD_SIZE'] ?? 1048576), // 1MB
        'enable_signature_verification' => (bool)($_ENV['MAS_WEBHOOKS_SIGNATURE_VERIFICATION'] ?? true),
        'signature_algorithm' => $_ENV['MAS_WEBHOOKS_SIGNATURE_ALGORITHM'] ?? 'sha256',
        
        // Queue and processing
        'enable_webhook_queue' => (bool)($_ENV['MAS_WEBHOOKS_ENABLE_QUEUE'] ?? true),
        'queue_driver' => $_ENV['MAS_WEBHOOKS_QUEUE_DRIVER'] ?? 'database',
        'max_concurrent_requests' => (int)($_ENV['MAS_WEBHOOKS_MAX_CONCURRENT'] ?? 10),
        'rate_limit_per_endpoint' => (int)($_ENV['MAS_WEBHOOKS_RATE_LIMIT'] ?? 100),
        
        // Monitoring and logging
        'enable_webhook_logging' => (bool)($_ENV['MAS_WEBHOOKS_LOGGING'] ?? true),
        'log_retention_days' => (int)($_ENV['MAS_WEBHOOKS_LOG_RETENTION'] ?? 30),
        'enable_webhook_analytics' => (bool)($_ENV['MAS_WEBHOOKS_ANALYTICS'] ?? true),
        
        // Supported events
        'supported_events' => [
            'customer.created', 'customer.updated', 'customer.deleted',
            'order.created', 'order.updated', 'order.completed', 'order.cancelled',
            'campaign.started', 'campaign.completed', 'campaign.failed',
            'workflow.started', 'workflow.completed', 'workflow.failed',
            'segment.created', 'segment.updated', 'segment.materialized',
            'consent.given', 'consent.withdrawn', 'consent.updated',
            'system.error', 'system.warning', 'system.maintenance'
        ]
    ],
    
    'api' => [
        'enabled' => (bool)($_ENV['MAS_API_ENABLED'] ?? true),
        'version' => $_ENV['MAS_API_VERSION'] ?? 'v2',
        'base_path' => $_ENV['MAS_API_BASE_PATH'] ?? '/api/mas',
        
        // Rate limiting
        'rate_limit_enabled' => (bool)($_ENV['MAS_API_RATE_LIMIT'] ?? true),
        'requests_per_minute' => (int)($_ENV['MAS_API_REQUESTS_PER_MINUTE'] ?? 100),
        'requests_per_hour' => (int)($_ENV['MAS_API_REQUESTS_PER_HOUR'] ?? 5000),
        'rate_limit_by_ip' => (bool)($_ENV['MAS_API_RATE_LIMIT_BY_IP'] ?? true),
        'rate_limit_by_user' => (bool)($_ENV['MAS_API_RATE_LIMIT_BY_USER'] ?? true),
        
        // CORS configuration
        'enable_cors' => (bool)($_ENV['MAS_API_ENABLE_CORS'] ?? false),
        'allowed_origins' => explode(',', $_ENV['MAS_API_ALLOWED_ORIGINS'] ?? '*'),
        'allowed_methods' => explode(',', $_ENV['MAS_API_ALLOWED_METHODS'] ?? 'GET,POST,PUT,PATCH,DELETE,OPTIONS'),
        'allowed_headers' => explode(',', $_ENV['MAS_API_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization,X-API-Key'),
        'max_age' => (int)($_ENV['MAS_API_CORS_MAX_AGE'] ?? 3600),
        
        // Authentication and authorization
        'enable_auth' => (bool)($_ENV['MAS_API_ENABLE_AUTH'] ?? true),
        'auth_methods' => explode(',', $_ENV['MAS_API_AUTH_METHODS'] ?? 'api_key,oauth,bearer_token'),
        'token_lifetime' => (int)($_ENV['MAS_API_TOKEN_LIFETIME'] ?? 3600),
        'refresh_token_lifetime' => (int)($_ENV['MAS_API_REFRESH_TOKEN_LIFETIME'] ?? 2592000), // 30 days
        'enable_api_key_rotation' => (bool)($_ENV['MAS_API_KEY_ROTATION'] ?? true),
        'api_key_rotation_interval_days' => (int)($_ENV['MAS_API_KEY_ROTATION_DAYS'] ?? 90),
        
        // Request/Response handling
        'max_request_size' => (int)($_ENV['MAS_API_MAX_REQUEST_SIZE'] ?? 10485760), // 10MB
        'enable_request_compression' => (bool)($_ENV['MAS_API_REQUEST_COMPRESSION'] ?? true),
        'enable_response_compression' => (bool)($_ENV['MAS_API_RESPONSE_COMPRESSION'] ?? true),
        'default_response_format' => $_ENV['MAS_API_DEFAULT_FORMAT'] ?? 'json',
        'supported_formats' => explode(',', $_ENV['MAS_API_SUPPORTED_FORMATS'] ?? 'json,xml,csv'),
        
        // Caching and performance
        'enable_response_caching' => (bool)($_ENV['MAS_API_RESPONSE_CACHING'] ?? true),
        'cache_ttl' => (int)($_ENV['MAS_API_CACHE_TTL'] ?? 300),
        'enable_etag_caching' => (bool)($_ENV['MAS_API_ETAG_CACHING'] ?? true),
        'enable_conditional_requests' => (bool)($_ENV['MAS_API_CONDITIONAL_REQUESTS'] ?? true),
        
        // Documentation and discovery
        'enable_api_documentation' => (bool)($_ENV['MAS_API_DOCUMENTATION'] ?? true),
        'documentation_path' => $_ENV['MAS_API_DOCS_PATH'] ?? '/api/docs',
        'enable_openapi_spec' => (bool)($_ENV['MAS_API_OPENAPI_SPEC'] ?? true),
        'openapi_version' => $_ENV['MAS_API_OPENAPI_VERSION'] ?? '3.0.3',
        
        // Monitoring and analytics
        'enable_api_analytics' => (bool)($_ENV['MAS_API_ANALYTICS'] ?? true),
        'analytics_retention_days' => (int)($_ENV['MAS_API_ANALYTICS_RETENTION'] ?? 90),
        'enable_performance_tracking' => (bool)($_ENV['MAS_API_PERFORMANCE_TRACKING'] ?? true),
        'enable_error_tracking' => (bool)($_ENV['MAS_API_ERROR_TRACKING'] ?? true)
    ],
    
    /* ========================= DEBUGGING & DEVELOPMENT CONFIGURATION ========================= */
    'debug' => [
        // General debugging
        'enabled' => (bool)($_ENV['MAS_DEBUG_ENABLED'] ?? false),
        'log_level' => $_ENV['MAS_DEBUG_LOG_LEVEL'] ?? 'info', // emergency, alert, critical, error, warning, notice, info, debug
        'log_channel' => $_ENV['MAS_DEBUG_LOG_CHANNEL'] ?? 'mas',
        'log_max_files' => (int)($_ENV['MAS_DEBUG_LOG_MAX_FILES'] ?? 10),
        'log_max_file_size' => (int)($_ENV['MAS_DEBUG_LOG_MAX_SIZE'] ?? 10485760), // 10MB
        
        // Query debugging
        'log_queries' => (bool)($_ENV['MAS_DEBUG_LOG_QUERIES'] ?? false),
        'log_slow_queries' => (bool)($_ENV['MAS_DEBUG_LOG_SLOW_QUERIES'] ?? false),
        'slow_query_threshold' => (float)($_ENV['MAS_DEBUG_SLOW_QUERY_THRESHOLD'] ?? 2.0),
        'explain_slow_queries' => (bool)($_ENV['MAS_DEBUG_EXPLAIN_SLOW_QUERIES'] ?? false),
        
        // Performance debugging
        'log_performance' => (bool)($_ENV['MAS_DEBUG_LOG_PERFORMANCE'] ?? false),
        'enable_profiler' => (bool)($_ENV['MAS_DEBUG_ENABLE_PROFILER'] ?? false),
        'profile_memory_usage' => (bool)($_ENV['MAS_DEBUG_PROFILE_MEMORY'] ?? false),
        'profile_execution_time' => (bool)($_ENV['MAS_DEBUG_PROFILE_EXECUTION'] ?? false),
        'profiler_output_format' => $_ENV['MAS_DEBUG_PROFILER_FORMAT'] ?? 'json', // json, html, text
        
        // Request/Response debugging
        'log_requests' => (bool)($_ENV['MAS_DEBUG_LOG_REQUESTS'] ?? false),
        'log_responses' => (bool)($_ENV['MAS_DEBUG_LOG_RESPONSES'] ?? false),
        'log_headers' => (bool)($_ENV['MAS_DEBUG_LOG_HEADERS'] ?? false),
        'mask_sensitive_data' => (bool)($_ENV['MAS_DEBUG_MASK_SENSITIVE'] ?? true),
        'max_request_log_size' => (int)($_ENV['MAS_DEBUG_MAX_REQUEST_LOG_SIZE'] ?? 1048576), // 1MB
        
        // Service debugging
        'log_service_calls' => (bool)($_ENV['MAS_DEBUG_LOG_SERVICE_CALLS'] ?? false),
        'log_provider_calls' => (bool)($_ENV['MAS_DEBUG_LOG_PROVIDER_CALLS'] ?? false),
        'log_workflow_execution' => (bool)($_ENV['MAS_DEBUG_LOG_WORKFLOW_EXECUTION'] ?? false),
        'log_segment_materialization' => (bool)($_ENV['MAS_DEBUG_LOG_SEGMENT_MATERIALIZATION'] ?? false),
        
        // Error debugging
        'enable_detailed_errors' => (bool)($_ENV['MAS_DEBUG_DETAILED_ERRORS'] ?? false),
        'include_stack_traces' => (bool)($_ENV['MAS_DEBUG_STACK_TRACES'] ?? false),
        'log_exceptions' => (bool)($_ENV['MAS_DEBUG_LOG_EXCEPTIONS'] ?? true),
        'enable_error_reporting' => (bool)($_ENV['MAS_DEBUG_ERROR_REPORTING'] ?? false),
        
        // Development tools
        'enable_debug_toolbar' => (bool)($_ENV['MAS_DEBUG_TOOLBAR'] ?? false),
        'enable_query_debugger' => (bool)($_ENV['MAS_DEBUG_QUERY_DEBUGGER'] ?? false),
        'enable_mail_debugging' => (bool)($_ENV['MAS_DEBUG_MAIL'] ?? false),
        'mail_debug_to_file' => (bool)($_ENV['MAS_DEBUG_MAIL_TO_FILE'] ?? false),
        
        // Test mode
        'test_mode' => (bool)($_ENV['MAS_TEST_MODE'] ?? false),
        'mock_external_services' => (bool)($_ENV['MAS_MOCK_EXTERNAL_SERVICES'] ?? false),
        'disable_external_calls' => (bool)($_ENV['MAS_DISABLE_EXTERNAL_CALLS'] ?? false),
        'test_data_seed' => $_ENV['MAS_TEST_DATA_SEED'] ?? null,
        
        // Environment information
        'show_environment_info' => (bool)($_ENV['MAS_DEBUG_SHOW_ENV_INFO'] ?? false),
        'allowed_debug_ips' => explode(',', $_ENV['MAS_DEBUG_ALLOWED_IPS'] ?? '127.0.0.1,::1'),
        'debug_session_key' => $_ENV['MAS_DEBUG_SESSION_KEY'] ?? null
    ]
];
