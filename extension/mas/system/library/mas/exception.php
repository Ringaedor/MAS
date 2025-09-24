<?php
/**
 * MAS - Marketing Automation Suite
 * Base Exception Class - Enterprise Exception Foundation
 *
 * Provides the foundation exception class for all MAS exceptions with enhanced
 * context management, error categorization, audit integration, and enterprise-grade
 * debugging capabilities. This is the core exception class that all other MAS
 * exceptions extend from.
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
 * Base MAS Exception Class
 *
 * Enhanced exception handling with context support, categorization, audit integration,
 * and enterprise-grade error tracking capabilities. Provides foundation for all
 * MAS-specific exceptions with standardized error handling patterns.
 *
 * Key Features:
 * - Rich context data support with automatic enrichment
 * - Error categorization and severity levels for proper prioritization
 * - Audit logging integration with structured data export
 * - Notification and alert trigger support
 * - Enterprise debugging capabilities with unique tracking IDs
 * - Component and operation tracking for precise error location
 * - Fluent interface for easy exception configuration
 * - Factory methods for simplified exception creation
 *
 * Usage Examples:
 *
 * Basic exception:
 * throw new Exception('Something went wrong');
 *
 * Exception with context:
 * throw new Exception('Database error', 500, ['table' => 'customers', 'query' => $sql]);
 *
 * Configured exception:
 * throw Exception::create('Provider failed', 'provider', 'high', ['provider' => 'sendgrid'])
 *     ->setComponent('email')
 *     ->setOperation('send_campaign')
 *     ->setShouldNotify(true);
 */
class Exception extends \Exception
{
    /**
     * @var array Additional context data for debugging and audit purposes
     */
    protected array $context = [];
    
    /**
     * @var string Error category for grouping and filtering exceptions
     */
    protected string $category = 'general';
    
    /**
     * @var string Severity level for error prioritization and handling
     */
    protected string $severity = 'medium';
    
    /**
     * @var bool Whether this exception should be logged to audit system
     */
    protected bool $shouldLog = true;
    
    /**
     * @var bool Whether this exception should trigger notifications
     */
    protected bool $shouldNotify = false;
    
    /**
     * @var bool Whether this exception should trigger immediate alerts
     */
    protected bool $shouldAlert = false;
    
    /**
     * @var string|null Component that originated this exception
     */
    protected ?string $component = null;
    
    /**
     * @var string|null Operation that was being performed when exception occurred
     */
    protected ?string $operation = null;
    
    /**
     * @var array Available severity levels in order of priority
     */
    public const SEVERITY_LEVELS = ['low', 'medium', 'high', 'critical'];
    
    /**
     * @var array Available error categories for classification
     */
    public const CATEGORIES = [
        'general', 'config', 'provider', 'workflow', 'segment',
        'ai', 'campaign', 'consent', 'validation', 'security',
        'performance', 'database', 'api', 'integration'
    ];
    
    /**
     * Constructor - Enhanced exception initialization
     *
     * Creates a new MAS exception with optional context data and automatic
     * runtime information enrichment. The constructor automatically adds
     * PHP version, memory usage, and timestamp to the context.
     *
     * @param string $message Exception message describing the error
     * @param int $code Numeric exception code for programmatic handling
     * @param array $context Additional context data for debugging
     * @param \Throwable|null $previous Previous exception in the chain
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        array $context = [],
        ?\Throwable $previous = null
        ) {
            parent::__construct($message, $code, $previous);
            $this->context = $context;
            $this->enrichContext();
    }
    
    /**
     * Gets the complete exception context data
     *
     * Returns all context data including user-provided context and
     * automatically enriched runtime information.
     *
     * @return array Complete context data array
     */
    public function getContext(): array
    {
        return $this->context;
    }
    
    /**
     * Sets the complete exception context data
     *
     * Replaces the entire context array with the provided data.
     * This will override any existing context including enriched data.
     *
     * @param array $context New context data array
     * @return self Returns self for method chaining
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
    
    /**
     * Adds a single context entry
     *
     * Adds or updates a single key-value pair in the context data.
     * This method is useful for adding specific debugging information.
     *
     * @param string $key Context key identifier
     * @param mixed $value Context value data
     * @return self Returns self for method chaining
     */
    public function addContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }
    
    /**
     * Merges additional context entries
     *
     * Merges the provided context array with the existing context,
     * preserving existing keys unless explicitly overridden.
     *
     * @param array $context Context entries to merge
     * @return self Returns self for method chaining
     */
    public function mergeContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }
    
    /**
     * Gets the exception category
     *
     * Returns the current category classification for this exception.
     * Categories are used for filtering and routing exceptions.
     *
     * @return string Current category name
     */
    public function getCategory(): string
    {
        return $this->category;
    }
    
    /**
     * Sets the exception category with validation
     *
     * Sets the exception category after validating it against the
     * list of allowed categories. Invalid categories will throw
     * an InvalidArgumentException.
     *
     * @param string $category Category name to set
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If category is not valid
     */
    public function setCategory(string $category): self
    {
        if (!in_array($category, self::CATEGORIES, true)) {
            throw new \InvalidArgumentException("Invalid exception category: {$category}");
        }
        $this->category = $category;
        return $this;
    }
    
    /**
     * Gets the exception severity level
     *
     * Returns the current severity level used for prioritization
     * and handling decisions.
     *
     * @return string Current severity level
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }
    
    /**
     * Sets the exception severity with validation
     *
     * Sets the severity level after validating it against the
     * list of allowed severity levels. Invalid levels will throw
     * an InvalidArgumentException.
     *
     * @param string $severity Severity level to set
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If severity level is not valid
     */
    public function setSeverity(string $severity): self
    {
        if (!in_array($severity, self::SEVERITY_LEVELS, true)) {
            throw new \InvalidArgumentException("Invalid exception severity: {$severity}");
        }
        $this->severity = $severity;
        return $this;
    }
    
    /**
     * Gets the originating component
     *
     * Returns the component name that originated this exception,
     * useful for debugging and error routing.
     *
     * @return string|null Component name or null if not set
     */
    public function getComponent(): ?string
    {
        return $this->component;
    }
    
    /**
     * Sets the originating component
     *
     * Sets the component name that originated this exception.
     * This helps in debugging and error routing.
     *
     * @param string|null $component Component name or null to clear
     * @return self Returns self for method chaining
     */
    public function setComponent(?string $component): self
    {
        $this->component = $component;
        return $this;
    }
    
    /**
     * Gets the operation being performed
     *
     * Returns the operation name that was being performed when
     * this exception occurred.
     *
     * @return string|null Operation name or null if not set
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }
    
    /**
     * Sets the operation context
     *
     * Sets the operation name that was being performed when
     * this exception occurred. This provides specific context
     * about what the system was doing.
     *
     * @param string|null $operation Operation name or null to clear
     * @return self Returns self for method chaining
     */
    public function setOperation(?string $operation): self
    {
        $this->operation = $operation;
        return $this;
    }
    
    /**
     * Checks if exception should be logged
     *
     * Returns whether this exception should be written to the
     * audit logging system.
     *
     * @return bool True if should be logged, false otherwise
     */
    public function shouldLog(): bool
    {
        return $this->shouldLog;
    }
    
    /**
     * Sets the logging flag
     *
     * Controls whether this exception should be written to the
     * audit logging system.
     *
     * @param bool $shouldLog True to enable logging, false to disable
     * @return self Returns self for method chaining
     */
    public function setShouldLog(bool $shouldLog): self
    {
        $this->shouldLog = $shouldLog;
        return $this;
    }
    
    /**
     * Checks if exception should trigger notifications
     *
     * Returns whether this exception should trigger notification
     * messages to administrators or relevant parties.
     *
     * @return bool True if should notify, false otherwise
     */
    public function shouldNotify(): bool
    {
        return $this->shouldNotify;
    }
    
    /**
     * Sets the notification flag
     *
     * Controls whether this exception should trigger notification
     * messages to administrators or relevant parties.
     *
     * @param bool $shouldNotify True to enable notifications, false to disable
     * @return self Returns self for method chaining
     */
    public function setShouldNotify(bool $shouldNotify): self
    {
        $this->shouldNotify = $shouldNotify;
        return $this;
    }
    
    /**
     * Checks if exception should trigger alerts
     *
     * Returns whether this exception should trigger immediate
     * alert notifications for critical issues.
     *
     * @return bool True if should alert, false otherwise
     */
    public function shouldAlert(): bool
    {
        return $this->shouldAlert;
    }
    
    /**
     * Sets the alert flag
     *
     * Controls whether this exception should trigger immediate
     * alert notifications for critical issues.
     *
     * @param bool $shouldAlert True to enable alerts, false to disable
     * @return self Returns self for method chaining
     */
    public function setShouldAlert(bool $shouldAlert): self
    {
        $this->shouldAlert = $shouldAlert;
        return $this;
    }
    
    /**
     * Converts exception to structured array for logging and audit
     *
     * Creates a comprehensive array representation of the exception
     * suitable for JSON serialization, logging, and audit trails.
     * Includes all exception data, context, and metadata.
     *
     * @return array Complete exception data structure
     */
    public function toArray(): array
    {
        return [
            'exception_id' => $this->generateExceptionId(),
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'category' => $this->getCategory(),
            'severity' => $this->getSeverity(),
            'component' => $this->getComponent(),
            'operation' => $this->getOperation(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->getContext(),
            'flags' => [
                'should_log' => $this->shouldLog(),
                'should_notify' => $this->shouldNotify(),
                'should_alert' => $this->shouldAlert()
            ],
            'trace' => $this->getTrace(),
            'trace_string' => $this->getTraceAsString(),
            'previous' => $this->getPrevious() ? [
                'message' => $this->getPrevious()->getMessage(),
                'code' => $this->getPrevious()->getCode(),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine()
            ] : null,
        ];
    }
    
    /**
     * Creates formatted error message with context and debug info
     *
     * Generates a human-readable, formatted error message that includes
     * the base message plus optional context data and stack trace information.
     * This method is particularly useful for logging and debugging.
     *
     * @param bool $includeContext Whether to include context data in output
     * @param bool $includeTrace Whether to include stack trace in output
     * @return string Formatted exception message
     */
    public function getFormattedMessage(bool $includeContext = true, bool $includeTrace = false): string
    {
        $parts = [];
        
        // Base message with metadata tags
        $parts[] = sprintf(
            '[%s][%s] %s',
            strtoupper($this->getCategory()),
            strtoupper($this->getSeverity()),
            $this->getMessage()
            );
        
        // Component and operation information
        if ($this->component || $this->operation) {
            $componentInfo = [];
            if ($this->component) {
                $componentInfo[] = "Component: {$this->component}";
            }
            if ($this->operation) {
                $componentInfo[] = "Operation: {$this->operation}";
            }
            $parts[] = implode(', ', $componentInfo);
        }
        
        // File location information
        $parts[] = sprintf('File: %s:%d', basename($this->getFile()), $this->getLine());
        
        // Context data as JSON
        if ($includeContext && !empty($this->context)) {
            $parts[] = 'Context: ' . json_encode($this->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        
        // Previous exception information
        if ($this->getPrevious()) {
            $parts[] = 'Previous: ' . $this->getPrevious()->getMessage();
        }
        
        // Full stack trace
        if ($includeTrace) {
            $parts[] = 'Trace: ' . $this->getTraceAsString();
        }
        
        return implode("\n", $parts);
    }
    
    /**
     * Gets concise exception summary for logging
     *
     * Creates a brief, one-line summary of the exception suitable
     * for log entries and quick debugging. Includes category,
     * severity, message, and location.
     *
     * @return string Concise exception summary
     */
    public function getSummary(): string
    {
        return sprintf(
            '%s/%s: %s in %s:%d',
            $this->getCategory(),
            $this->getSeverity(),
            $this->getMessage(),
            basename($this->getFile()),
            $this->getLine()
            );
    }
    
    /**
     * Checks if this is a critical exception requiring immediate attention
     *
     * Determines if this exception represents a critical error that
     * requires immediate attention, either due to severity level
     * or alert flag being set.
     *
     * @return bool True if critical, false otherwise
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical' || $this->shouldAlert;
    }
    
    /**
     * Checks if this exception affects user experience
     *
     * Determines if this exception is likely to directly impact
     * the user experience, typically validation, configuration,
     * or provider errors.
     *
     * @return bool True if user-facing, false otherwise
     */
    public function isUserFacing(): bool
    {
        return in_array($this->category, ['validation', 'config', 'provider'], true);
    }
    
    /**
     * Factory method to create exception with full context
     *
     * Convenient factory method to create a fully configured exception
     * with category, severity, and context in a single call. This method
     * provides a fluent way to create exceptions with all necessary metadata.
     *
     * @param string $message Exception message
     * @param string $category Error category (default: 'general')
     * @param string $severity Severity level (default: 'medium')
     * @param array $context Context data (default: empty array)
     * @param int $code Exception code (default: 0)
     * @param \Throwable|null $previous Previous exception (default: null)
     * @return static New configured exception instance
     */
    public static function create(
        string $message,
        string $category = 'general',
        string $severity = 'medium',
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
        ): self {
            return (new static($message, $code, $context, $previous))
            ->setCategory($category)
            ->setSeverity($severity);
    }
    
    /**
     * Enriches context with automatic runtime information
     *
     * Automatically adds runtime information to the context if not
     * already present, including PHP version, memory usage, and
     * precise timestamp. This provides valuable debugging information.
     *
     * @return void
     */
    private function enrichContext(): void
    {
        if (!isset($this->context['php_version'])) {
            $this->context['php_version'] = PHP_VERSION;
        }
        
        if (!isset($this->context['memory_usage'])) {
            $this->context['memory_usage'] = memory_get_usage(true);
        }
        
        if (!isset($this->context['timestamp'])) {
            $this->context['timestamp'] = microtime(true);
        }
        
        if (!isset($this->context['request_id'])) {
            $this->context['request_id'] = $_SERVER['REQUEST_ID'] ?? uniqid('req_', true);
        }
    }
    
    /**
     * Generates unique exception identifier for tracking
     *
     * Creates a unique identifier for this exception instance that
     * can be used for tracking, correlation, and debugging across
     * logs and systems.
     *
     * @return string Unique exception identifier
     */
    private function generateExceptionId(): string
    {
        return 'mas_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 8);
    }
}
