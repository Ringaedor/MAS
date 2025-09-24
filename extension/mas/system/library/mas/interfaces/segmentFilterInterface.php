<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Segment Filter Interface - Comprehensive Audience Segmentation Contract
 *
 * Defines the comprehensive enterprise contract for all segment filter classes used
 * for advanced audience segmentation in MAS. Each filter represents a distinct criterion
 * or strategy for creating customer segments (RFM, behavioral, predictive, demographic,
 * AI-powered, geographic, psychographic, lifecycle, value-based, etc.) with enterprise-grade
 * features including performance optimization, explainable filtering, and audit compliance.
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
 * Enterprise Segment Filter Interface
 *
 * Comprehensive contract defining all methods required for MAS segment filter implementations.
 * This interface ensures consistent behavior across all segmentation strategies while supporting
 * advanced enterprise features such as performance optimization, explainable segmentation,
 * real-time filtering, batch processing, and comprehensive analytics integration.
 *
 * Filter Categories:
 * - Behavioral: purchase patterns, engagement, activity, interaction history
 * - Demographic: age, gender, location, income, education, family status
 * - Psychographic: interests, values, lifestyle, personality traits
 * - Transactional: RFM analysis, purchase frequency, order value, payment methods
 * - Predictive: churn risk, lifetime value, conversion probability, propensity models
 * - Geographic: location-based, regional preferences, climate-based
 * - Temporal: seasonal patterns, time-based behaviors, lifecycle stages
 * - AI-Powered: machine learning clusters, similarity groups, recommendation-based
 * - Custom: business-specific criteria, complex rule combinations
 *
 * Key Features:
 * - High-performance filtering with query optimization
 * - Real-time and batch processing capabilities
 * - Explainable segmentation with reasoning chains
 * - Advanced validation with business rule checking
 * - Performance monitoring and optimization suggestions
 * - A/B testing support for filter effectiveness
 * - Integration with external data sources
 * - Compliance-ready with audit trails and data privacy
 * - Scalable processing for large customer databases
 * - Dynamic recalculation and cache management
 *
 * Implementation Requirements:
 * - All methods must be implemented by concrete filters
 * - Static methods provide metadata without instantiation
 * - Configuration must be validated against comprehensive schemas
 * - Performance metrics must be tracked for optimization
 * - Filtering logic must be explainable for compliance
 * - Results must be cacheable for performance
 * - Security and privacy requirements must be enforced
 *
 * Usage Examples:
 *
 * Basic filtering:
 * $filter = new RFMFilter();
 * $filter->setConfig($rfmConfig);
 * $customerIds = $filter->apply($context);
 *
 * Batch processing:
 * $results = $filter->applyBatch($contexts);
 *
 * With explanation:
 * $result = $filter->applyWithExplanation($context);
 *
 * Performance analysis:
 * $metrics = $filter->getPerformanceMetrics();
 *
 * A/B testing:
 * $comparison = $filter->compareWith($otherFilter, $testContext);
 */
interface SegmentFilterInterface
{
    /**
     * @var string Behavioral filter types
     */
    public const TYPE_BEHAVIORAL = 'behavioral';
    public const TYPE_PURCHASE_PATTERN = 'purchase_pattern';
    public const TYPE_ENGAGEMENT = 'engagement';
    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_INTERACTION = 'interaction';
    public const TYPE_BROWSING = 'browsing';
    
    /**
     * @var string Transactional filter types
     */
    public const TYPE_RFM = 'rfm';
    public const TYPE_MONETARY = 'monetary';
    public const TYPE_FREQUENCY = 'frequency';
    public const TYPE_RECENCY = 'recency';
    public const TYPE_ORDER_VALUE = 'order_value';
    public const TYPE_PAYMENT_METHOD = 'payment_method';
    
    /**
     * @var string Demographic filter types
     */
    public const TYPE_DEMOGRAPHIC = 'demographic';
    public const TYPE_AGE = 'age';
    public const TYPE_GENDER = 'gender';
    public const TYPE_LOCATION = 'location';
    public const TYPE_INCOME = 'income';
    public const TYPE_EDUCATION = 'education';
    public const TYPE_FAMILY_STATUS = 'family_status';
    
    /**
     * @var string Geographic filter types
     */
    public const TYPE_GEOGRAPHIC = 'geographic';
    public const TYPE_REGIONAL = 'regional';
    public const TYPE_CLIMATE = 'climate';
    public const TYPE_TIMEZONE = 'timezone';
    public const TYPE_LANGUAGE = 'language';
    
    /**
     * @var string Predictive filter types
     */
    public const TYPE_PREDICTIVE = 'predictive';
    public const TYPE_CHURN_RISK = 'churn_risk';
    public const TYPE_LIFETIME_VALUE = 'lifetime_value';
    public const TYPE_CONVERSION_PROBABILITY = 'conversion_probability';
    public const TYPE_PROPENSITY = 'propensity';
    
    /**
     * @var string AI-powered filter types
     */
    public const TYPE_AI_CLUSTER = 'ai_cluster';
    public const TYPE_SIMILARITY = 'similarity';
    public const TYPE_RECOMMENDATION_BASED = 'recommendation_based';
    public const TYPE_ML_SEGMENT = 'ml_segment';
    
    /**
     * @var string Temporal filter types
     */
    public const TYPE_TEMPORAL = 'temporal';
    public const TYPE_SEASONAL = 'seasonal';
    public const TYPE_LIFECYCLE = 'lifecycle';
    public const TYPE_TIME_BASED = 'time_based';
    
    /**
     * @var string Custom and composite filter types
     */
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_COMPOSITE = 'composite';
    public const TYPE_RULE_BASED = 'rule_based';
    public const TYPE_DYNAMIC = 'dynamic';
    
    /**
     * @var string Filter complexity levels
     */
    public const COMPLEXITY_SIMPLE = 'simple';
    public const COMPLEXITY_MODERATE = 'moderate';
    public const COMPLEXITY_COMPLEX = 'complex';
    public const COMPLEXITY_ADVANCED = 'advanced';
    
    /**
     * @var string Processing modes
     */
    public const MODE_REAL_TIME = 'real_time';
    public const MODE_BATCH = 'batch';
    public const MODE_STREAMING = 'streaming';
    public const MODE_SCHEDULED = 'scheduled';
    
    /**
     * @var string Cache strategies
     */
    public const CACHE_NONE = 'none';
    public const CACHE_RESULT = 'result';
    public const CACHE_QUERY = 'query';
    public const CACHE_INTELLIGENT = 'intelligent';
    
    /**
     * Returns the unique filter type identifier
     *
     * Provides the canonical type identifier for this filter using one of the
     * defined TYPE_* constants for proper categorization and routing.
     *
     * @return string Filter type (use TYPE_* constants)
     */
    public static function getType(): string;
    
    /**
     * Returns the filter category for organization
     *
     * Provides a higher-level category for organizing filters in UI
     * and for feature grouping purposes.
     *
     * @return string Filter category (behavioral, demographic, predictive, etc.)
     */
    public static function getCategory(): string;
    
    /**
     * Returns a human-readable label for display
     *
     * Provides a concise, user-friendly label for administrative interfaces
     * and filter selection displays.
     *
     * @return string Human-readable filter label
     */
    public static function getLabel(): string;
    
    /**
     * Returns a comprehensive description of functionality
     *
     * Provides detailed information about the filter's purpose, methodology,
     * expected use cases, and business value for documentation and help systems.
     *
     * @return string Detailed filter description
     */
    public static function getDescription(): string;
    
    /**
     * Returns the complete configuration schema
     *
     * Provides comprehensive JSON schema for configuration validation,
     * administrative interface generation, and auto-discovery including
     * field definitions, validation rules, and UI hints.
     *
     * @return array{
     *     schema_version: string,
     *     properties: array<string, array<string, mixed>>,
     *     required: array<string>,
     *     conditional_fields: array<string, mixed>,
     *     ui_schema: array<string, mixed>,
     *     validation_rules: array<string, mixed>,
     *     examples: array<string, mixed>
     * } Complete configuration schema
     */
    public static function getConfigSchema(): array;
    
    /**
     * Returns comprehensive filter capabilities
     *
     * Provides detailed information about supported features, processing modes,
     * performance characteristics, and integration capabilities.
     *
     * @return array{
     *     complexity_level: string,
     *     supported_modes: array<string>,
     *     cache_strategies: array<string>,
     *     performance_tier: string,
     *     supports_real_time: bool,
     *     supports_explanation: bool,
     *     supports_preview: bool,
     *     external_dependencies: array<string>,
     *     data_requirements: array<string, mixed>,
     *     scalability_limits: array<string, mixed>
     * } Comprehensive capability information
     */
    public static function getCapabilities(): array;
    
    /**
     * Sets the complete filter configuration
     *
     * Updates the filter configuration with comprehensive validation
     * and optimization suggestions. Configuration is validated against
     * the schema and business rules before being applied.
     *
     * @param array<string, mixed> $config Configuration array
     * @return void
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\ValidationException If configuration is invalid
     */
    public function setConfig(array $config): void;
    
    /**
     * Returns current configuration (sanitized)
     *
     * Provides the current filter configuration with sensitive data
     * masked and additional metadata for debugging and optimization.
     *
     * @param bool $includeSensitive Whether to include sensitive data
     * @return array<string, mixed> Current configuration with metadata
     */
    public function getConfig(bool $includeSensitive = false): array;
    
    /**
     * Updates specific configuration values
     *
     * Updates individual configuration values without replacing the
     * entire configuration. Supports nested key updates and validation.
     *
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $value Value to set
     * @return void
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\ValidationException If value is invalid
     */
    public function setConfigValue(string $key, $value): void;
    
    /**
     * Validates configuration with comprehensive checking
     *
     * Performs thorough validation of the filter configuration against
     * schema, business rules, and performance guidelines. Returns detailed
     * validation results with suggestions for optimization.
     *
     * @return array{
     *     valid: bool,
     *     errors: array<string>,
     *     warnings: array<string>,
     *     suggestions: array<string>,
     *     performance_impact: array<string, mixed>,
     *     estimated_size: int,
     *     cache_recommendations: array<string>
     * } Comprehensive validation results
     */
    public function validate(): array;
    
    /**
     * Estimates filter performance and resource requirements
     *
     * Analyzes the current configuration and context to estimate
     * execution time, memory usage, and database load for optimization.
     *
     * @param array<string, mixed> $context Execution context
     * @return array{
     *     estimated_execution_time: float,
     *     estimated_memory_usage: int,
     *     estimated_result_size: int,
     *     database_complexity_score: float,
     *     optimization_recommendations: array<string>,
     *     cache_effectiveness: float
     * } Performance estimation and recommendations
     */
    public function estimatePerformance(array $context): array;
    
    /**
     * Applies the filter with comprehensive result information
     *
     * Executes the filter logic with performance monitoring, caching,
     * and detailed result information including execution statistics.
     *
     * @param array<string, mixed> $context Execution context containing:
     *   - db: object - Database connection
     *   - cache: object - Cache instance
     *   - customer_table: string - Customer table name
     *   - filters: array - Additional filter constraints
     *   - options: array - Processing options
     * @return array{
     *     customer_ids: array<int>,
     *     total_matches: int,
     *     execution_time: float,
     *     memory_used: int,
     *     query_stats: array<string, mixed>,
     *     cache_hit: bool,
     *     performance_score: float
     * } Filter results with comprehensive metrics
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\SegmentException If filter execution fails
     */
    public function apply(array $context): array;
    
    /**
     * Applies the filter with detailed explanation
     *
     * Executes the filter while generating detailed explanations of
     * the segmentation logic and decision process for transparency.
     *
     * @param array<string, mixed> $context Execution context
     * @return array{
     *     customer_ids: array<int>,
     *     total_matches: int,
     *     explanation: array<string, mixed>,
     *     criteria_breakdown: array<string, mixed>,
     *     decision_tree: array<array<string, mixed>>,
     *     exclusion_reasons: array<string, array<int>>
     * } Filter results with comprehensive explanation
     */
    public function applyWithExplanation(array $context): array;
    
    /**
     * Batch processing for multiple contexts
     *
     * Processes multiple filter contexts efficiently with batch optimization
     * and parallel processing where supported.
     *
     * @param array<array<string, mixed>> $contexts Array of execution contexts
     * @param array<string, mixed> $options Batch processing options
     * @return array{
     *     results: array<array<string, mixed>>,
     *     total_processed: int,
     *     total_execution_time: float,
     *     batch_efficiency: float,
     *     cache_hit_rate: float
     * } Batch processing results
     */
    public function applyBatch(array $contexts, array $options = []): array;
    
    /**
     * Previews filter results without full execution
     *
     * Provides a preview of filter results with sampling for large datasets
     * to allow users to validate filter configuration before full execution.
     *
     * @param array<string, mixed> $context Execution context
     * @param array<string, mixed> $previewOptions Preview configuration:
     *   - sample_size: int - Number of records to sample
     *   - sampling_method: string - Sampling methodology
     *   - include_stats: bool - Include statistical projections
     * @return array{
     *     preview_results: array<int>,
     *     sample_size: int,
     *     projected_total: int,
     *     confidence_interval: array<float>,
     *     sampling_method: string,
     *     preview_accuracy: float
     * } Preview results with statistical projections
     */
    public function preview(array $context, array $previewOptions = []): array;
    
    /**
     * Compares this filter with another for A/B testing
     *
     * Performs comparative analysis between this filter and another
     * filter implementation to evaluate effectiveness and performance.
     *
     * @param SegmentFilterInterface $otherFilter Filter to compare against
     * @param array<string, mixed> $context Comparison context
     * @return array{
     *     comparison_id: string,
     *     filter_a_results: array<string, mixed>,
     *     filter_b_results: array<string, mixed>,
     *     overlap_analysis: array<string, mixed>,
     *     performance_comparison: array<string, mixed>,
     *     recommendation: string
     * } Comprehensive filter comparison analysis
     */
    public function compareWith(SegmentFilterInterface $otherFilter, array $context): array;
    
    /**
     * Optimizes filter configuration based on usage patterns
     *
     * Analyzes historical usage and performance data to suggest
     * configuration optimizations for improved performance and accuracy.
     *
     * @param array<string, mixed> $usageData Historical usage and performance data
     * @return array{
     *     optimization_suggestions: array<string, mixed>,
     *     expected_improvements: array<string, mixed>,
     *     configuration_changes: array<string, mixed>,
     *     performance_impact: array<string, mixed>
     * } Optimization recommendations
     */
    public function optimizeConfiguration(array $usageData): array;
    
    /**
     * Gets comprehensive performance metrics
     *
     * Returns detailed performance analytics including execution statistics,
     * accuracy metrics, and usage patterns for monitoring and optimization.
     *
     * @param array<string, mixed> $options Metrics options:
     *   - time_range: string - Time range for metrics
     *   - include_breakdowns: bool - Include detailed breakdowns
     *   - benchmark_comparison: bool - Include benchmark data
     * @return array{
     *     execution_stats: array<string, mixed>,
     *     accuracy_metrics: array<string, mixed>,
     *     cache_performance: array<string, mixed>,
     *     usage_patterns: array<string, mixed>,
     *     performance_trends: array<string, mixed>,
     *     optimization_opportunities: array<string>
     * } Comprehensive performance metrics
     */
    public function getPerformanceMetrics(array $options = []): array;
    
    /**
     * Validates filter against data quality requirements
     *
     * Checks if the available data meets the quality requirements
     * for accurate filter execution and provides recommendations.
     *
     * @param array<string, mixed> $context Data context
     * @return array{
     *     data_quality_score: float,
     *     quality_issues: array<string>,
     *     completeness_score: float,
     *     accuracy_concerns: array<string>,
     *     recommendations: array<string>
     * } Data quality assessment
     */
    public function validateDataQuality(array $context): array;
    
    /**
     * Exports filter results for external analysis
     *
     * Exports segmentation results in various formats with metadata
     * for external analysis, reporting, or integration with other systems.
     *
     * @param array<string, mixed> $results Filter results to export
     * @param array<string, mixed> $exportOptions Export configuration:
     *   - format: string - Export format (csv, json, xml)
     *   - include_metadata: bool - Include execution metadata
     *   - anonymize_data: bool - Anonymize customer data
     * @return array{
     *     export_id: string,
     *     format: string,
     *     record_count: int,
     *     file_size: int,
     *     download_url?: string
     * } Export information
     */
    public function exportResults(array $results, array $exportOptions): array;
    
    /**
     * Serializes filter configuration and state
     *
     * Converts the filter instance including configuration, performance
     * history, and operational state to an array for storage or transfer.
     *
     * @return array{
     *     type: string,
     *     version: string,
     *     configuration: array<string, mixed>,
     *     performance_history: array<string, mixed>,
     *     metadata: array<string, mixed>,
     *     created_at: float,
     *     updated_at: float
     * } Serialized filter data
     */
    public function toArray(): array;
    
    /**
     * Creates filter instance from serialized data
     *
     * Factory method that creates and configures a filter instance from
     * previously serialized data with version compatibility and migration support.
     *
     * @param array<string, mixed> $data Serialized filter data
     * @return static Configured filter instance
     * @throws \\Opencart\Extension\Mas\System\Library\Mas\Exceptions\SegmentException If deserialization fails
     */
    public static function fromArray(array $data): self;
    
    /**
     * Clones filter with optional configuration overrides
     *
     * Creates a deep copy of the filter with optional configuration
     * changes for testing and template purposes.
     *
     * @param array<string, mixed> $configOverrides Configuration overrides
     * @return static Cloned filter instance
     */
    public function clone(array $configOverrides = []): self;
    
    /**
     * Returns filter documentation and help information
     *
     * Provides comprehensive documentation including usage examples,
     * configuration guides, and troubleshooting information.
     *
     * @return array{
     *     usage_examples: array<string, mixed>,
     *     configuration_guide: array<string, mixed>,
     *     troubleshooting: array<string, mixed>,
     *     best_practices: array<string>,
     *     performance_tips: array<string>
     * } Filter documentation
     */
    public static function getDocumentation(): array;
    
    /**
     * Returns version and compatibility information
     *
     * Provides version details, API compatibility, and migration
     * information for version management and upgrade planning.
     *
     * @return array{
     *     version: string,
     *     api_version: string,
     *     compatible_versions: array<string>,
     *     migration_support: array<string, mixed>,
     *     deprecation_info?: array<string, mixed>
     * } Version and compatibility information
     */
    public static function getVersionInfo(): array;
}
