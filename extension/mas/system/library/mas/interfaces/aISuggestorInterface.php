<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise AI Suggestor Interface - Comprehensive AI Advisory Contract
 *
 * Defines the comprehensive contract for all AI-based suggestor, advisor, and
 * recommendation classes throughout the MAS ecosystem. This interface establishes
 * enterprise-grade standards for AI-powered suggestion systems including prediction,
 * recommendation, content generation, optimization, and decision support across
 * workflows, segmentation, campaigns, and customer experience optimization.
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
 * Enterprise AI Suggestor Interface
 *
 * Comprehensive contract defining all methods required for MAS AI suggestor implementations.
 * This interface ensures consistent behavior across all AI-powered suggestion systems while
 * supporting advanced enterprise features such as multi-model support, confidence scoring,
 * explanation generation, performance monitoring, and comprehensive analytics integration.
 *
 * Suggestor Categories:
 * - Content Generation: text, email, social media, product descriptions
 * - Recommendation Systems: product, content, customer, pricing recommendations
 * - Predictive Analytics: churn prediction, lifetime value, conversion probability
 * - Segmentation: customer clustering, behavior analysis, demographic insights
 * - Optimization: campaign optimization, send time optimization, content optimization
 * - Decision Support: approval workflows, escalation decisions, risk assessment
 * - Personalization: dynamic content, product recommendations, experience customization
 *
 * Key Features:
 * - Multi-model AI provider support with fallback chains
 * - Confidence scoring and explanation generation for transparency
 * - Real-time and batch processing capabilities
 * - Performance monitoring and accuracy tracking
 * - A/B testing support for suggestion effectiveness
 * - Contextual learning and model adaptation
 * - Enterprise security and compliance features
 * - Comprehensive audit trails and decision logging
 * - Integration with MAS analytics and reporting systems
 * - Support for human-in-the-loop workflows
 *
 * Implementation Requirements:
 * - All methods must be implemented by concrete suggestors
 * - Static methods provide metadata without instantiation
 * - Configuration must be validated against schema
 * - AI responses must include confidence scores and explanations
 * - Performance metrics must be tracked for optimization
 * - Security and privacy requirements must be enforced
 * - Audit logging must be implemented for compliance
 *
 * Usage Examples:
 *
 * Basic suggestion:
 * $suggestor = new ProductRecommendationSuggestor();
 * $suggestor->setConfig($config);
 * $suggestion = $suggestor->suggest($context);
 *
 * Batch processing:
 * $suggestions = $suggestor->suggestBatch($contexts);
 *
 * With confidence threshold:
 * $suggestion = $suggestor->suggestWithConfidence($context, 0.8);
 *
 * A/B testing:
 * $results = $suggestor->testSuggestions($contextA, $contextB);
 *
 * Learning from feedback:
 * $suggestor->learnFromFeedback($suggestionId, $outcome, $feedback);
 */
interface AiSuggestorInterface
{
    /**
     * @var string Content generation suggestor types
     */
    public const TYPE_TEXT_GENERATION = 'text_generation';
    public const TYPE_EMAIL_GENERATION = 'email_generation';
    public const TYPE_CONTENT_CREATION = 'content_creation';
    public const TYPE_SOCIAL_MEDIA = 'social_media';
    public const TYPE_PRODUCT_DESCRIPTION = 'product_description';
    
    /**
     * @var string Recommendation system types
     */
    public const TYPE_PRODUCT_RECOMMENDATION = 'product_recommendation';
    public const TYPE_CONTENT_RECOMMENDATION = 'content_recommendation';
    public const TYPE_CUSTOMER_RECOMMENDATION = 'customer_recommendation';
    public const TYPE_PRICING_RECOMMENDATION = 'pricing_recommendation';
    public const TYPE_CROSS_SELL = 'cross_sell';
    public const TYPE_UPSELL = 'upsell';
    
    /**
     * @var string Predictive analytics types
     */
    public const TYPE_CHURN_PREDICTION = 'churn_prediction';
    public const TYPE_LIFETIME_VALUE = 'lifetime_value';
    public const TYPE_CONVERSION_PREDICTION = 'conversion_prediction';
    public const TYPE_DEMAND_FORECASTING = 'demand_forecasting';
    public const TYPE_TREND_ANALYSIS = 'trend_analysis';
    
    /**
     * @var string Segmentation and analysis types
     */
    public const TYPE_CUSTOMER_SEGMENTATION = 'customer_segmentation';
    public const TYPE_BEHAVIOR_ANALYSIS = 'behavior_analysis';
    public const TYPE_DEMOGRAPHIC_INSIGHTS = 'demographic_insights';
    public const TYPE_SENTIMENT_ANALYSIS = 'sentiment_analysis';
    public const TYPE_MARKET_ANALYSIS = 'market_analysis';
    
    /**
     * @var string Optimization suggestor types
     */
    public const TYPE_CAMPAIGN_OPTIMIZATION = 'campaign_optimization';
    public const TYPE_SEND_TIME_OPTIMIZATION = 'send_time_optimization';
    public const TYPE_CONTENT_OPTIMIZATION = 'content_optimization';
    public const TYPE_PRICE_OPTIMIZATION = 'price_optimization';
    public const TYPE_INVENTORY_OPTIMIZATION = 'inventory_optimization';
    
    /**
     * @var string Decision support types
     */
    public const TYPE_APPROVAL_ADVISOR = 'approval_advisor';
    public const TYPE_ESCALATION_ADVISOR = 'escalation_advisor';
    public const TYPE_RISK_ASSESSMENT = 'risk_assessment';
    public const TYPE_COMPLIANCE_ADVISOR = 'compliance_advisor';
    
    /**
     * @var string Suggestion confidence levels
     */
    public const CONFIDENCE_LOW = 'low';
    public const CONFIDENCE_MEDIUM = 'medium';
    public const CONFIDENCE_HIGH = 'high';
    public const CONFIDENCE_VERY_HIGH = 'very_high';
    
    /**
     * @var string Processing modes
     */
    public const MODE_REAL_TIME = 'real_time';
    public const MODE_BATCH = 'batch';
    public const MODE_STREAMING = 'streaming';
    public const MODE_SCHEDULED = 'scheduled';
    
    /**
     * Returns the unique suggestor type identifier
     *
     * Provides the canonical type identifier for this suggestor using one of the
     * defined TYPE_* constants for proper categorization and routing.
     *
     * @return string Suggestor type (use TYPE_* constants)
     */
    public static function getType(): string;
    
    /**
     * Returns a human-readable label for display
     *
     * Provides a concise, user-friendly label for administrative interfaces
     * and suggestion system displays.
     *
     * @return string Human-readable suggestor label
     */
    public static function getLabel(): string;
    
    /**
     * Returns a comprehensive description of functionality
     *
     * Provides detailed information about the suggestor's purpose, AI models used,
     * processing pipeline, and expected use cases for documentation and help systems.
     *
     * @return string Detailed suggestor description
     */
    public static function getDescription(): string;
    
    /**
     * Returns the complete configuration schema
     *
     * Provides comprehensive schema for configuration validation, administrative
     * interface generation, and auto-discovery including AI model parameters,
     * processing options, and performance tuning settings.
     *
     * @return array{
     *     schema_version: string,
     *     properties: array<string, array<string, mixed>>,
     *     required: array<string>,
     *     ai_model_config: array<string, mixed>,
     *     performance_settings: array<string, mixed>,
     *     security_settings: array<string, mixed>
     * } Complete configuration schema
     */
    public static function getConfigSchema(): array;
    
    /**
     * Returns comprehensive suggestor capabilities
     *
     * Provides detailed information about supported features, processing modes,
     * AI model capabilities, and performance characteristics.
     *
     * @return array{
     *     supported_modes: array<string>,
     *     ai_models: array<string>,
     *     max_batch_size: int,
     *     supports_confidence_scoring: bool,
     *     supports_explanations: bool,
     *     supports_feedback_learning: bool,
     *     supports_ab_testing: bool,
     *     supported_languages: array<string>,
     *     rate_limits: array<string, mixed>
     * } Comprehensive capability information
     */
    public static function getCapabilities(): array;
    
    /**
     * Sets the complete suggestor configuration
     *
     * Updates the suggestor configuration including AI model settings,
     * processing parameters, and performance options. Configuration
     * is validated before being applied.
     *
     * @param array<string, mixed> $config Configuration array
     * @return void
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\ConfigException If configuration is invalid
     */
    public function setConfig(array $config): void;
    
    /**
     * Returns current configuration (sanitized)
     *
     * Provides the current configuration with sensitive data (API keys,
     * secrets) masked for security purposes.
     *
     * @param bool $includeSensitive Whether to include sensitive data
     * @return array<string, mixed> Current configuration (sanitized)
     */
    public function getConfig(bool $includeSensitive = false): array;
    
    /**
     * Checks if the suggestor is enabled and operational
     *
     * Verifies that the suggestor is properly configured, authenticated,
     * and ready for use. Includes health checks for AI services.
     *
     * @return array{
     *     enabled: bool,
     *     configured: bool,
     *     authenticated: bool,
     *     ai_service_available: bool,
     *     issues: array<string>
     * } Comprehensive status information
     */
    public function getStatus(): array;
    
    /**
     * Primary suggestion method with comprehensive context processing
     *
     * Processes the provided context through AI models to generate suggestions
     * with confidence scoring, explanations, and comprehensive metadata.
     *
     * @param array<string, mixed> $context Contextual input containing:
     *   - request_type: string - Type of suggestion requested
     *   - customer_data: array - Customer information and behavior
     *   - product_data: array - Product catalog and attributes
     *   - historical_data: array - Historical patterns and outcomes
     *   - business_rules: array - Business constraints and preferences
     *   - preferences: array - User or system preferences
     * @return array{
     *     success: bool,
     *     suggestions: array<array<string, mixed>>,
     *     confidence: float,
     *     explanation: string,
     *     alternatives: array<array<string, mixed>>,
     *     metadata: array<string, mixed>,
     *     performance: array<string, mixed>,
     *     model_info: array<string, mixed>
     * } Comprehensive suggestion response
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\AIException If suggestion generation fails
     */
    public function suggest(array $context): array;
    
    /**
     * Batch suggestion processing with optimization
     *
     * Processes multiple suggestion requests efficiently with batch optimization,
     * parallel processing, and comprehensive result aggregation.
     *
     * @param array<array<string, mixed>> $contexts Array of context inputs
     * @param array<string, mixed> $options Batch processing options:
     *   - batch_size: int - Size of processing batches
     *   - parallel: bool - Enable parallel processing
     *   - confidence_threshold: float - Minimum confidence level
     *   - include_alternatives: bool - Include alternative suggestions
     * @return array{
     *     success: bool,
     *     total_processed: int,
     *     successful: int,
     *     failed: int,
     *     results: array<array<string, mixed>>,
     *     performance: array<string, mixed>
     * } Batch processing results
     */
    public function suggestBatch(array $contexts, array $options = []): array;
    
    /**
     * Suggestion with confidence filtering
     *
     * Generates suggestions that meet or exceed the specified confidence threshold
     * with detailed confidence analysis and recommendation quality metrics.
     *
     * @param array<string, mixed> $context Input context
     * @param float $confidenceThreshold Minimum confidence level (0.0-1.0)
     * @return array<string, mixed> Filtered suggestions with confidence analysis
     */
    public function suggestWithConfidence(array $context, float $confidenceThreshold): array;
    
    /**
     * Validates input context for suggestion processing
     *
     * Validates the provided context against requirements and returns
     * detailed validation results with suggestions for improvement.
     *
     * @param array<string, mixed> $context Context to validate
     * @return array{
     *     valid: bool,
     *     completeness_score: float,
     *     missing_fields: array<string>,
     *     quality_issues: array<string>,
     *     recommendations: array<string>
     * } Context validation results
     */
    public function validateContext(array $context): array;
    
    /**
     * Explains suggestion reasoning and decision process
     *
     * Provides detailed explanation of how suggestions were generated including
     * AI model reasoning, data factors, and decision logic for transparency.
     *
     * @param string $suggestionId Unique suggestion identifier
     * @param array<string, mixed> $options Explanation options:
     *   - detail_level: string - Level of detail (basic, detailed, technical)
     *   - include_data_sources: bool - Include data source information
     *   - include_model_details: bool - Include AI model information
     * @return array{
     *     explanation: string,
     *     reasoning_chain: array<array<string, mixed>>,
     *     data_factors: array<string, mixed>,
     *     model_decision_points: array<array<string, mixed>>,
     *     confidence_factors: array<string, mixed>
     * } Comprehensive explanation information
     */
    public function explainSuggestion(string $suggestionId, array $options = []): array;
    
    /**
     * Records feedback for continuous learning
     *
     * Captures feedback on suggestion quality and outcomes for model improvement
     * and learning system optimization. Supports various feedback types.
     *
     * @param string $suggestionId Unique suggestion identifier
     * @param array<string, mixed> $outcome Actual outcome data
     * @param array<string, mixed> $feedback User or system feedback:
     *   - rating: float - Suggestion quality rating
     *   - effectiveness: bool - Whether suggestion was effective
     *   - user_action: string - Action taken by user
     *   - business_impact: array - Measured business impact
     * @return array{
     *     recorded: bool,
     *     learning_applied: bool,
     *     model_updated: bool,
     *     improvement_metrics: array<string, mixed>
     * } Feedback processing results
     */
    public function recordFeedback(string $suggestionId, array $outcome, array $feedback): array;
    
    /**
     * Performs A/B testing for suggestion effectiveness
     *
     * Compares different suggestion approaches or configurations to determine
     * optimal performance with statistical significance testing.
     *
     * @param array<string, mixed> $variantA First suggestion variant
     * @param array<string, mixed> $variantB Second suggestion variant
     * @param array<string, mixed> $testConfig Test configuration:
     *   - sample_size: int - Required sample size
     *   - confidence_level: float - Statistical confidence level
     *   - duration: int - Test duration in seconds
     * @return array{
     *     test_id: string,
     *     variants_compared: array<string, mixed>,
     *     winner?: string,
     *     confidence_level: float,
     *     statistical_significance: bool,
     *     performance_metrics: array<string, mixed>
     * } A/B test results and analysis
     */
    public function performABTest(array $variantA, array $variantB, array $testConfig): array;
    
    /**
     * Gets suggestor performance metrics and analytics
     *
     * Returns comprehensive performance data including accuracy metrics,
     * response times, success rates, and business impact measurements.
     *
     * @param array<string, mixed> $options Metrics options:
     *   - time_range: string - Time range for metrics
     *   - include_breakdowns: bool - Include detailed breakdowns
     *   - benchmark_comparison: bool - Include benchmark comparisons
     * @return array{
     *     accuracy_metrics: array<string, mixed>,
     *     performance_metrics: array<string, mixed>,
     *     usage_statistics: array<string, mixed>,
     *     business_impact: array<string, mixed>,
     *     model_performance: array<string, mixed>,
     *     cost_efficiency: array<string, mixed>
     * } Comprehensive performance analytics
     */
    public function getPerformanceMetrics(array $options = []): array;
    
    /**
     * Optimizes suggestor configuration based on performance data
     *
     * Analyzes performance data and suggests configuration optimizations
     * to improve accuracy, speed, and cost efficiency.
     *
     * @param array<string, mixed> $performanceData Historical performance data
     * @return array{
     *     optimization_recommendations: array<string, mixed>,
     *     expected_improvements: array<string, mixed>,
     *     configuration_changes: array<string, mixed>,
     *     risk_assessment: array<string, mixed>
     * } Optimization recommendations and analysis
     */
    public function optimizeConfiguration(array $performanceData): array;
    
    /**
     * Retrains or updates AI models with new data
     *
     * Triggers model retraining or fine-tuning with new data to improve
     * suggestion quality and adapt to changing patterns.
     *
     * @param array<string, mixed> $trainingData New training data
     * @param array<string, mixed> $trainingOptions Training configuration:
     *   - training_mode: string - Training approach (full, incremental, fine_tune)
     *   - validation_split: float - Validation data percentage
     *   - epochs: int - Training epochs
     * @return array{
     *     training_started: bool,
     *     training_id: string,
     *     estimated_completion: float,
     *     current_model_backup: string,
     *     validation_metrics: array<string, mixed>
     * } Training process information
     */
    public function retrain(array $trainingData, array $trainingOptions = []): array;
    
    /**
     * Exports suggestion data for analysis or backup
     *
     * Exports suggestion history, performance data, and model information
     * in various formats for external analysis, backup, or migration.
     *
     * @param array<string, mixed> $exportOptions Export configuration:
     *   - format: string - Export format (json, csv, xml, parquet)
     *   - date_range: array - Date range for export
     *   - include_model_data: bool - Include model information
     *   - anonymize_data: bool - Anonymize personal data
     * @return array{
     *     export_started: bool,
     *     export_id: string,
     *     estimated_size: int,
     *     download_url?: string,
     *     expiration_time?: float
     * } Export process information
     */
    public function exportData(array $exportOptions): array;
    
    /**
     * Imports suggestion data or model configurations
     *
     * Imports previously exported data or transfers configurations
     * from other suggestor instances for migration or backup restoration.
     *
     * @param string $importSource Source of import data (file path, URL, etc.)
     * @param array<string, mixed> $importOptions Import configuration:
     *   - format: string - Source data format
     *   - merge_strategy: string - How to handle conflicts
     *   - validate_data: bool - Validate data before import
     * @return array{
     *     import_started: bool,
     *     import_id: string,
     *     records_imported: int,
     *     conflicts_resolved: int,
     *     validation_results: array<string, mixed>
     * } Import process results
     */
    public function importData(string $importSource, array $importOptions): array;
    
    /**
     * Serializes suggestor configuration and state
     *
     * Converts the suggestor instance including configuration, trained models,
     * and operational state to an array suitable for storage or transfer.
     *
     * @return array{
     *     type: string,
     *     version: string,
     *     configuration: array<string, mixed>,
     *     model_state: array<string, mixed>,
     *     performance_history: array<string, mixed>,
     *     metadata: array<string, mixed>
     * } Serialized suggestor data
     */
    public function toArray(): array;
    
    /**
     * Creates suggestor instance from serialized data
     *
     * Factory method that creates and configures a suggestor instance from
     * previously serialized data with version compatibility and migration support.
     *
     * @param array<string, mixed> $data Serialized suggestor data
     * @return static Configured suggestor instance
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\AIException If deserialization fails
     */
    public static function fromArray(array $data): self;
    
    /**
     * Returns suggestor version and compatibility information
     *
     * Provides version information, API compatibility, and migration support
     * details for version management and upgrade planning.
     *
     * @return array{
     *     version: string,
     *     api_version: string,
     *     compatible_versions: array<string>,
     *     model_versions: array<string, mixed>,
     *     migration_support: array<string, mixed>
     * } Version and compatibility information
     */
    public static function getVersionInfo(): array;
}
