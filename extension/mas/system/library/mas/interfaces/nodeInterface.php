<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Workflow Node Interface - Comprehensive Node Contract
 *
 * Defines the comprehensive contract for all workflow node types (trigger, action,
 * delay, condition, gateway, parallel, loop, etc.) used throughout the MAS Workflow Engine.
 * This interface establishes enterprise-grade standards for node implementation including
 * execution, validation, serialization, error handling, performance monitoring, and
 * advanced workflow features like parallel processing, conditional branching, and retry logic.
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
 * Enterprise Workflow Node Interface
 *
 * Comprehensive contract defining all methods required for MAS workflow node implementations.
 * This interface ensures consistent behavior across all node types while supporting advanced
 * enterprise features such as parallel execution, conditional branching, error recovery,
 * performance monitoring, and comprehensive state management.
 *
 * Node Types Supported:
 * - Control Flow: trigger, condition, gateway, parallel, loop, delay
 * - Actions: email, sms, api_call, data_transform, calculation
 * - Data Operations: filter, transform, aggregate, validate, merge
 * - External Integration: webhook, database, file_operation, service_call
 * - AI/ML: ai_analysis, recommendation, prediction, classification
 * - Business Logic: approval, escalation, notification, audit
 *
 * Key Features:
 * - Standardized execution with comprehensive error handling
 * - Configuration validation with schema-based validation
 * - State management for complex workflow scenarios
 * - Performance monitoring and metrics collection
 * - Retry logic with exponential backoff
 * - Conditional execution with complex rule support
 * - Parallel processing capabilities
 * - Rollback and compensation transaction support
 * - Debug and audit trail integration
 * - Dynamic configuration and runtime modification
 *
 * Implementation Requirements:
 * - All methods must be implemented by concrete nodes
 * - Static methods provide metadata without instantiation
 * - Configuration must be validated against schema
 * - Error handling must use MAS Exception system
 * - State changes must be tracked for audit purposes
 * - Performance metrics should be collected when possible
 * - Rollback operations must be supported where applicable
 *
 * Usage Examples:
 *
 * Basic node execution:
 * $node = new EmailActionNode();
 * $node->setConfig($config);
 * $result = $node->execute($context);
 *
 * Conditional execution:
 * if ($node->shouldExecute($context)) {
 *     $result = $node->execute($context);
 * }
 *
 * Parallel processing:
 * $results = $node->executeParallel($contexts);
 *
 * With retry logic:
 * $result = $node->executeWithRetry($context, $retryOptions);
 *
 * Rollback on failure:
 * if (!$result['success']) {
 *     $node->rollback($context, $result);
 * }
 */
interface NodeInterface
{
    /**
     * @var string Control flow node types
     */
    public const TYPE_TRIGGER = 'trigger';
    public const TYPE_CONDITION = 'condition';
    public const TYPE_GATEWAY = 'gateway';
    public const TYPE_PARALLEL = 'parallel';
    public const TYPE_LOOP = 'loop';
    public const TYPE_DELAY = 'delay';
    public const TYPE_MERGE = 'merge';
    public const TYPE_SPLIT = 'split';
    
    /**
     * @var string Action node types
     */
    public const TYPE_ACTION = 'action';
    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';
    public const TYPE_PUSH = 'push';
    public const TYPE_API_CALL = 'api_call';
    public const TYPE_WEBHOOK = 'webhook';
    public const TYPE_DATABASE = 'database';
    public const TYPE_FILE_OPERATION = 'file_operation';
    
    /**
     * @var string Data processing node types
     */
    public const TYPE_TRANSFORM = 'transform';
    public const TYPE_FILTER = 'filter';
    public const TYPE_AGGREGATE = 'aggregate';
    public const TYPE_VALIDATE = 'validate';
    public const TYPE_CALCULATE = 'calculate';
    public const TYPE_ENRICH = 'enrich';
    
    /**
     * @var string AI/ML node types
     */
    public const TYPE_AI_ANALYSIS = 'ai_analysis';
    public const TYPE_RECOMMENDATION = 'recommendation';
    public const TYPE_PREDICTION = 'prediction';
    public const TYPE_CLASSIFICATION = 'classification';
    public const TYPE_SENTIMENT = 'sentiment';
    
    /**
     * @var string Business logic node types
     */
    public const TYPE_APPROVAL = 'approval';
    public const TYPE_ESCALATION = 'escalation';
    public const TYPE_NOTIFICATION = 'notification';
    public const TYPE_AUDIT = 'audit';
    public const TYPE_COMPLIANCE = 'compliance';
    
    /**
     * @var string Node execution status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETRYING = 'retrying';
    public const STATUS_ROLLED_BACK = 'rolled_back';
    
    /**
     * @var string Node execution priority levels
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';
    
    /**
     * Returns the unique identifier of the node instance
     *
     * Provides a unique identifier for this specific node instance within
     * the workflow, used for tracking, logging, and state management.
     *
     * @return string Unique node instance identifier
     */
    public function getId(): string;
    
    /**
     * Sets the unique identifier for this node instance
     *
     * Assigns a unique identifier to this node instance. Used during
     * workflow construction and deserialization.
     *
     * @param string $id Unique identifier to assign
     * @return void
     */
    public function setId(string $id): void;
    
    /**
     * Returns the node type classification
     *
     * Provides the type classification for this node using one of the
     * defined TYPE_* constants for proper categorization and handling.
     *
     * @return string Node type (use TYPE_* constants)
     */
    public static function getType(): string;
    
    /**
     * Returns the node category for grouping
     *
     * Provides a higher-level category for organizing nodes in UI
     * and for feature grouping purposes.
     *
     * @return string Node category (control_flow, action, data, ai, business)
     */
    public static function getCategory(): string;
    
    /**
     * Returns a human-readable label for the node
     *
     * Provides a short, descriptive label for display in workflow
     * designers and administrative interfaces.
     *
     * @return string Human-readable node label
     */
    public function getLabel(): string;
    
    /**
     * Sets the human-readable label for the node
     *
     * Updates the display label for this node instance.
     *
     * @param string $label New label to set
     * @return void
     */
    public function setLabel(string $label): void;
    
    /**
     * Returns a detailed description of the node
     *
     * Provides comprehensive information about the node's functionality,
     * use cases, and behavior for documentation and help systems.
     *
     * @return string Detailed node description
     */
    public function getDescription(): string;
    
    /**
     * Returns the current node configuration
     *
     * Provides the complete configuration array for this node including
     * all settings, parameters, and options.
     *
     * @return array<string, mixed> Current node configuration
     */
    public function getConfig(): array;
    
    /**
     * Sets the complete node configuration
     *
     * Updates the node configuration with new settings. Configuration
     * is validated before being applied.
     *
     * @param array<string, mixed> $config New configuration to set
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ValidationException If configuration is invalid
     */
    public function setConfig(array $config): void;
    
    /**
     * Updates specific configuration values
     *
     * Updates individual configuration values without replacing the
     * entire configuration. Supports nested key updates using dot notation.
     *
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $value Value to set
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\ValidationException If value is invalid
     */
    public function setConfigValue(string $key, $value): void;
    
    /**
     * Gets a specific configuration value
     *
     * Retrieves a specific configuration value with optional default.
     * Supports nested key access using dot notation.
     *
     * @param string $key Configuration key (supports dot notation)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value or default
     */
    public function getConfigValue(string $key, $default = null);
    
    /**
     * Validates the current node configuration
     *
     * Performs comprehensive validation of the node configuration against
     * the schema and business rules. Returns detailed validation results.
     *
     * @return array{
     *     valid: bool,
     *     errors: array<string>,
     *     warnings: array<string>,
     *     suggestions: array<string>
     * } Validation results with errors and warnings
     */
    public function validate(): array;
    
    /**
     * Returns the configuration schema definition
     *
     * Provides the complete JSON schema for validating node configuration
     * including field definitions, validation rules, and UI hints.
     *
     * @return array{
     *     type: string,
     *     properties: array<string, array<string, mixed>>,
     *     required: array<string>,
     *     additionalProperties: bool,
     *     ui_schema?: array<string, mixed>
     * } Configuration schema definition
     */
    public static function getConfigSchema(): array;
    
    /**
     * Returns node capabilities and features
     *
     * Provides information about the node's capabilities, limitations,
     * and supported features for workflow planning and optimization.
     *
     * @return array{
     *     supports_parallel: bool,
     *     supports_retry: bool,
     *     supports_rollback: bool,
     *     supports_conditional: bool,
     *     timeout_configurable: bool,
     *     resource_intensive: bool,
     *     external_dependencies: array<string>
     * } Node capabilities information
     */
    public static function getCapabilities(): array;
    
    /**
     * Executes the node logic with comprehensive error handling
     *
     * Performs the primary node execution with comprehensive context handling,
     * error management, performance tracking, and detailed result reporting.
     *
     * @param array<string, mixed> $context Workflow execution context containing:
     *   - workflow_id: string - ID of the executing workflow
     *   - execution_id: string - ID of this execution instance
     *   - customer_data: array - Customer/user data
     *   - payload: array - Data being processed
     *   - state: array - Workflow state variables
     *   - metadata: array - Additional execution metadata
     * @return array{
     *     success: bool,
     *     status: string,
     *     output?: array<string, mixed>,
     *     state_changes?: array<string, mixed>,
     *     error?: string,
     *     error_code?: string,
     *     performance?: array<string, mixed>,
     *     next_nodes?: array<string>,
     *     delay?: int
     * } Execution results with comprehensive status information
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\WorkflowException If critical execution error occurs
     */
    public function execute(array $context): array;
    
    /**
     * Executes the node with retry logic
     *
     * Executes the node with configurable retry logic including exponential
     * backoff, retry conditions, and maximum attempt limits.
     *
     * @param array<string, mixed> $context Execution context
     * @param array<string, mixed> $retryOptions Retry configuration:
     *   - max_attempts: int - Maximum retry attempts
     *   - base_delay: int - Base delay in seconds
     *   - max_delay: int - Maximum delay in seconds
     *   - backoff_multiplier: float - Backoff multiplier
     *   - retry_conditions: array - Conditions for retry
     * @return array<string, mixed> Execution results including retry information
     */
    public function executeWithRetry(array $context, array $retryOptions = []): array;
    
    /**
     * Checks if the node should execute based on conditions
     *
     * Evaluates execution conditions to determine if the node should
     * execute in the current context. Supports complex conditional logic.
     *
     * @param array<string, mixed> $context Execution context
     * @return array{
     *     should_execute: bool,
     *     reason?: string,
     *     conditions_met: array<string, bool>,
     *     skip_reason?: string
     * } Execution decision with reasoning
     */
    public function shouldExecute(array $context): array;
    
    /**
     * Performs rollback or compensation operations
     *
     * Executes rollback logic to undo the effects of a previous execution.
     * Used in error recovery and transaction compensation scenarios.
     *
     * @param array<string, mixed> $context Original execution context
     * @param array<string, mixed> $executionResult Results of the failed execution
     * @return array{
     *     success: bool,
     *     rollback_actions: array<string>,
     *     errors?: array<string>
     * } Rollback operation results
     */
    public function rollback(array $context, array $executionResult): array;
    
    /**
     * Pauses node execution with state preservation
     *
     * Pauses execution and preserves the current state for later resumption.
     * Used for long-running operations and user interaction scenarios.
     *
     * @param array<string, mixed> $context Current execution context
     * @return array{
     *     paused: bool,
     *     pause_token: string,
     *     resume_instructions: array<string, mixed>
     * } Pause operation results
     */
    public function pause(array $context): array;
    
    /**
     * Resumes paused node execution
     *
     * Resumes execution from a previously paused state using the provided
     * pause token and any additional resume data.
     *
     * @param string $pauseToken Token from previous pause operation
     * @param array<string, mixed> $resumeData Additional data for resumption
     * @return array<string, mixed> Execution results
     */
    public function resume(string $pauseToken, array $resumeData = []): array;
    
    /**
     * Cancels node execution
     *
     * Cancels ongoing or scheduled execution and performs any necessary cleanup.
     * Used for workflow termination and resource cleanup scenarios.
     *
     * @param array<string, mixed> $context Current execution context
     * @param string $reason Cancellation reason
     * @return array{
     *     cancelled: bool,
     *     cleanup_actions: array<string>,
     *     final_state: array<string, mixed>
     * } Cancellation operation results
     */
    public function cancel(array $context, string $reason): array;
    
    /**
     * Gets current node execution status
     *
     * Returns the current execution status and state information for
     * monitoring and debugging purposes.
     *
     * @return array{
     *     status: string,
     *     progress: float,
     *     start_time?: float,
     *     end_time?: float,
     *     duration?: float,
     *     resource_usage?: array<string, mixed>,
     *     state: array<string, mixed>
     * } Current execution status and metrics
     */
    public function getStatus(): array;
    
    /**
     * Gets node performance metrics
     *
     * Returns performance metrics for this node including execution times,
     * resource usage, success rates, and other relevant statistics.
     *
     * @param array<string, mixed> $options Metrics options:
     *   - time_range: string - Time range for metrics
     *   - include_details: bool - Include detailed breakdowns
     * @return array{
     *     total_executions: int,
     *     successful_executions: int,
     *     failed_executions: int,
     *     average_duration: float,
     *     success_rate: float,
     *     resource_usage: array<string, mixed>,
     *     error_breakdown: array<string, int>
     * } Performance metrics and statistics
     */
    public function getMetrics(array $options = []): array;
    
    /**
     * Estimates node execution time and resources
     *
     * Provides estimates for execution time and resource requirements
     * based on the current configuration and context.
     *
     * @param array<string, mixed> $context Execution context
     * @return array{
     *     estimated_duration: float,
     *     estimated_memory: int,
     *     estimated_cost?: float,
     *     confidence_level: float,
     *     factors: array<string>
     * } Execution estimates and confidence
     */
    public function estimateExecution(array $context): array;
    
    /**
     * Serializes the node to array for storage
     *
     * Converts the node instance including configuration, state, and metadata
     * to an array suitable for JSON serialization and database storage.
     *
     * @return array{
     *     id: string,
     *     type: string,
     *     label: string,
     *     description: string,
     *     config: array<string, mixed>,
     *     metadata: array<string, mixed>,
     *     version: string,
     *     created_at?: float,
     *     updated_at?: float
     * } Serialized node data
     */
    public function toArray(): array;
    
    /**
     * Creates node instance from serialized array data
     *
     * Factory method that creates and configures a node instance from
     * previously serialized data. Handles version compatibility and
     * configuration migration.
     *
     * @param array<string, mixed> $data Serialized node data
     * @return static Configured node instance
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exceptions\WorkflowException If deserialization fails
     */
    public static function fromArray(array $data): self;
    
    /**
     * Clones the node with optional configuration changes
     *
     * Creates a deep copy of the node with optional configuration overrides.
     * Used for workflow templating and dynamic node generation.
     *
     * @param array<string, mixed> $configOverrides Optional configuration overrides
     * @return static Cloned node instance
     */
    public function clone(array $configOverrides = []): self;
    
    /**
     * Validates node compatibility with workflow context
     *
     * Checks if the node is compatible with the given workflow environment,
     * including required services, permissions, and runtime requirements.
     *
     * @param array<string, mixed> $workflowContext Workflow environment context
     * @return array{
     *     compatible: bool,
     *     requirements_met: array<string, bool>,
     *     missing_requirements: array<string>,
     *     warnings: array<string>
     * } Compatibility check results
     */
    public function checkCompatibility(array $workflowContext): array;
    
    /**
     * Returns node documentation and help information
     *
     * Provides comprehensive documentation including configuration examples,
     * troubleshooting guides, and best practices for administrative interfaces.
     *
     * @return array{
     *     usage_guide: array<string, mixed>,
     *     configuration_examples: array<string, mixed>,
     *     troubleshooting: array<string, mixed>,
     *     best_practices: array<string>,
     *     related_nodes: array<string>
     * } Node documentation and help information
     */
    public static function getDocumentation(): array;
    
    /**
     * Returns node version and compatibility information
     *
     * Provides version information, compatibility matrix, and migration
     * guides for version management and upgrade planning.
     *
     * @return array{
     *     version: string,
     *     api_version: string,
     *     min_engine_version: string,
     *     compatible_versions: array<string>,
     *     deprecation_info?: array<string, mixed>,
     *     migration_guide?: array<string, mixed>
     * } Version and compatibility information
     */
    public static function getVersionInfo(): array;
}
