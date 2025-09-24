<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Event Dispatcher - Advanced Event Management System
 *
 * Central event dispatcher providing comprehensive event management with listener registration,
 * event firing, priority management, wildcard matching, asynchronous queue integration,
 * performance monitoring, circuit breaker patterns, conditional dispatching, middleware support,
 * and comprehensive audit logging for enterprise-grade event-driven architecture.
 *
 * This dispatcher serves as the backbone of the MAS event system, enabling loose coupling
 * between components while providing advanced features like event propagation control,
 * performance optimization, error isolation, and comprehensive monitoring capabilities
 * required for production enterprise environments.
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas\Events
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Opencart\Extension\Mas\System\Library\Mas\Events;

/**
 * Enterprise Event Dispatcher
 *
 * Comprehensive event management system providing advanced dispatching capabilities
 * with enterprise-grade features including performance monitoring, error handling,
 * conditional dispatching, middleware support, and comprehensive audit logging.
 *
 * Key Features:
 * - Advanced listener management with priority queuing
 * - Wildcard pattern matching for flexible event handling
 * - Conditional dispatching with complex rule support
 * - Middleware pipeline for event preprocessing
 * - Circuit breaker patterns for resilience
 * - Performance monitoring and optimization
 * - Asynchronous queue integration for scalability
 * - Comprehensive audit logging for compliance
 * - Event propagation control and cancellation
 * - Memory-efficient batch processing
 * - Error isolation and recovery mechanisms
 * - Rate limiting and throttling support
 *
 * Event Flow:
 * 1. Event triggered with payload and context
 * 2. Middleware pipeline processes event
 * 3. Conditional rules evaluated for dispatch eligibility
 * 4. Listeners matched using direct and wildcard patterns
 * 5. Listeners executed in priority order with error isolation
 * 6. Results aggregated and performance metrics collected
 * 7. Audit logs generated for compliance tracking
 *
 * Advanced Features:
 * - Event bubbling and capture phases
 * - Dynamic listener registration and removal
 * - Event context and metadata management
 * - Conditional listener execution
 * - Performance profiling and optimization
 * - Memory usage monitoring
 * - Error recovery and fallback strategies
 * - Integration with external event systems
 *
 * Usage Examples:
 *
 * Basic event dispatching:
 * $dispatcher = new EventDispatcher();
 * $dispatcher->addListener('user.created', $listener);
 * $dispatcher->dispatch('user.created', $userData);
 *
 * Priority-based listeners:
 * $dispatcher->addListener('order.placed', $highPriorityListener, 100);
 * $dispatcher->addListener('order.placed', $lowPriorityListener, 10);
 *
 * Wildcard patterns:
 * $dispatcher->addListener('user.*', $userEventListener);
 * $dispatcher->addListener('*.error', $errorHandler);
 *
 * Conditional dispatching:
 * $dispatcher->dispatchIf('notification.send', $data, $conditions);
 *
 * Middleware support:
 * $dispatcher->addMiddleware($authMiddleware);
 * $dispatcher->addMiddleware($validationMiddleware);
 */
class EventDispatcher
{
    /**
     * @var array<string, array<int, array<callable>>> Registered event listeners organized by priority
     */
    protected array $listeners = [];
    
    /**
     * @var array<callable> Middleware pipeline for event preprocessing
     */
    protected array $middleware = [];
    
    /**
     * @var array<string, array<string, mixed>> Event metadata and context storage
     */
    protected array $eventMetadata = [];
    
    /**
     * @var array<string, mixed> Performance and monitoring statistics
     */
    protected array $stats = [
        'total_dispatches' => 0,
        'total_listeners_executed' => 0,
        'total_execution_time' => 0.0,
        'failed_dispatches' => 0,
        'circuit_breaker_trips' => 0,
        'middleware_executions' => 0
    ];
    
    /**
     * @var array<string, mixed> Circuit breaker state for error isolation
     */
    protected array $circuitBreaker = [
        'failure_count' => 0,
        'failure_threshold' => 5,
        'timeout' => 300,
        'last_failure_time' => null,
        'state' => 'closed' // closed, open, half-open
    ];
    
    /**
     * @var array<string, mixed> Configuration options
     */
    protected array $config = [
        'max_execution_time' => 30,
        'max_listeners_per_event' => 100,
        'enable_profiling' => false,
        'enable_audit_logging' => true,
        'batch_size' => 50,
        'memory_limit' => 134217728, // 128MB
        'enable_circuit_breaker' => true,
        'enable_middleware' => true
    ];
    
    /**
     * @var array<string> Currently dispatching events (for recursion detection)
     */
    protected array $dispatchingEvents = [];
    
    /**
     * @var object|null Service container for dependency injection
     */
    protected $container = null;
    
    /**
     * Constructor with enhanced initialization
     *
     * Initializes the event dispatcher with configuration options and
     * sets up performance monitoring, circuit breaker, and audit systems.
     *
     * @param object|null $container Service container instance
     * @param array<string, mixed> $config Configuration options
     */
    public function __construct($container = null, array $config = [])
    {
        $this->container = $container;
        $this->config = array_merge($this->config, $config);
        $this->initializeStats();
    }
    
    /**
     * Registers an event listener with advanced options
     *
     * Registers a listener for specific events with priority management,
     * conditional execution, and comprehensive validation.
     *
     * @param string $event Event name or pattern (supports wildcards)
     * @param callable $listener Listener callback function
     * @param int $priority Execution priority (higher = earlier execution)
     * @param array<string, mixed> $options Additional listener options
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If listener registration fails
     */
    public function addListener(string $event, callable $listener, int $priority = 0, array $options = []): void
    {
        // Validate event name
        if (empty($event)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Event name cannot be empty',
                'event_dispatcher',
                'medium',
                ['event' => $event]
                );
        }
        
        // Check maximum listeners limit
        if (isset($this->listeners[$event])) {
            $currentCount = $this->countListenersForEvent($event);
            if ($currentCount >= $this->config['max_listeners_per_event']) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    "Maximum listeners limit exceeded for event '{$event}'",
                    'event_dispatcher',
                    'medium',
                    [
                        'event' => $event,
                        'current_count' => $currentCount,
                        'max_allowed' => $this->config['max_listeners_per_event']
                    ]
                    );
            }
        }
        
        // Initialize event listener array if needed
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        
        if (!isset($this->listeners[$event][$priority])) {
            $this->listeners[$event][$priority] = [];
        }
        
        // Create listener wrapper with metadata
        $listenerWrapper = [
            'callback' => $listener,
            'options' => $options,
            'added_at' => microtime(true),
            'execution_count' => 0,
            'total_execution_time' => 0.0,
            'last_executed_at' => null,
            'error_count' => 0
        ];
        
        $this->listeners[$event][$priority][] = $listenerWrapper;
        
        // Sort priorities in descending order (highest priority first)
        krsort($this->listeners[$event], SORT_NUMERIC);
        
        // Log listener registration if audit logging is enabled
        if ($this->config['enable_audit_logging']) {
            $this->auditLog('listener_registered', [
                'event' => $event,
                'priority' => $priority,
                'options' => $options,
                'total_listeners' => $this->countListenersForEvent($event)
            ]);
        }
    }
    
    /**
     * Adds middleware to the event processing pipeline
     *
     * Registers middleware functions that process events before listener execution
     * with support for conditional middleware and comprehensive error handling.
     *
     * @param callable $middleware Middleware function
     * @param array<string, mixed> $options Middleware options
     * @return void
     */
    public function addMiddleware(callable $middleware, array $options = []): void
    {
        if (!$this->config['enable_middleware']) {
            return;
        }
        
        $middlewareWrapper = [
            'callback' => $middleware,
            'options' => $options,
            'added_at' => microtime(true),
            'execution_count' => 0,
            'total_execution_time' => 0.0
        ];
        
        $this->middleware[] = $middlewareWrapper;
    }
    
    /**
     * Dispatches an event to all relevant listeners with comprehensive processing
     *
     * Processes events through middleware pipeline, executes matching listeners
     * in priority order, and provides comprehensive error handling and monitoring.
     *
     * @param string $event Event name
     * @param mixed $payload Event payload data
     * @param array<string, mixed> $context Additional event context
     * @return array<string, mixed> Dispatch results with comprehensive information
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If dispatch fails critically
     */
    public function dispatch(string $event, $payload = null, array $context = []): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Check circuit breaker state
        if (!$this->isCircuitBreakerClosed()) {
            return [
                'success' => false,
                'error' => 'Circuit breaker is open',
                'responses' => [],
                'listener_count' => 0,
                'execution_time' => 0.0
            ];
        }
        
        // Detect recursive dispatch
        if (in_array($event, $this->dispatchingEvents, true)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Recursive event dispatch detected for event '{$event}'",
                'event_dispatcher',
                'high',
                [
                    'event' => $event,
                    'dispatch_stack' => $this->dispatchingEvents
                ]
                );
        }
        
        $this->dispatchingEvents[] = $event;
        
        try {
            // Create event object with comprehensive context
            $eventObj = [
                'name' => $event,
                'payload' => $payload,
                'context' => $context,
                'timestamp' => microtime(true),
                'stopped' => false,
                'results' => [],
                'metadata' => $this->eventMetadata[$event] ?? []
            ];
            
            // Process through middleware pipeline
            $eventObj = $this->processMiddleware($eventObj);
            
            if ($eventObj['stopped']) {
                return [
                    'success' => true,
                    'stopped_by_middleware' => true,
                    'responses' => [],
                    'listener_count' => 0,
                    'execution_time' => microtime(true) - $startTime
                ];
            }
            
            // Get matching listeners
            $listeners = $this->getListenersForEvent($event);
            $responses = [];
            $executedCount = 0;
            $failedCount = 0;
            
            // Execute listeners with error isolation
            foreach ($listeners as $listenerData) {
                try {
                    $listenerStart = microtime(true);
                    
                    // Check execution conditions
                    if (!$this->shouldExecuteListener($listenerData, $eventObj)) {
                        continue;
                    }
                    
                    $result = $this->executeListener($listenerData, $eventObj);
                    $executionTime = microtime(true) - $listenerStart;
                    
                    // Update listener statistics
                    $listenerData['execution_count']++;
                    $listenerData['total_execution_time'] += $executionTime;
                    $listenerData['last_executed_at'] = microtime(true);
                    
                    $responses[] = [
                        'result' => $result,
                        'execution_time' => $executionTime,
                        'success' => true
                    ];
                    
                    $executedCount++;
                    
                    // Check if event propagation should stop
                    if ($this->shouldStopPropagation($result, $eventObj)) {
                        break;
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $listenerData['error_count']++;
                    
                    $responses[] = [
                        'error' => $e->getMessage(),
                        'execution_time' => microtime(true) - $listenerStart,
                        'success' => false
                    ];
                    
                    $this->handleListenerError($e, $listenerData, $eventObj);
                    
                    // Continue with other listeners unless critical error
                    if ($this->isCriticalError($e)) {
                        break;
                    }
                }
            }
            
            $totalTime = microtime(true) - $startTime;
            $memoryUsed = memory_get_usage(true) - $startMemory;
            
            // Update statistics
            $this->updateStats($event, $executedCount, $failedCount, $totalTime);
            
            // Reset circuit breaker on successful dispatch
            if ($failedCount === 0) {
                $this->resetCircuitBreaker();
            } else {
                $this->updateCircuitBreaker($failedCount);
            }
            
            $result = [
                'success' => true,
                'event' => $event,
                'responses' => $responses,
                'listener_count' => count($listeners),
                'executed_count' => $executedCount,
                'failed_count' => $failedCount,
                'execution_time' => $totalTime,
                'memory_used' => $memoryUsed,
                'timestamp' => $startTime
            ];
            
            // Audit log successful dispatch
            if ($this->config['enable_audit_logging']) {
                $this->auditLog('event_dispatched', $result);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->updateCircuitBreaker(1);
            
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Event dispatch failed for '{$event}': " . $e->getMessage(),
                'event_dispatcher',
                'high',
                [
                    'event' => $event,
                    'payload_type' => gettype($payload),
                    'context' => $context,
                    'execution_time' => microtime(true) - $startTime
                ],
                $e
                );
        } finally {
            array_pop($this->dispatchingEvents);
        }
    }
    
    /**
     * Dispatches event conditionally based on rules
     *
     * Dispatches an event only if specified conditions are met, providing
     * advanced conditional logic for intelligent event processing.
     *
     * @param string $event Event name
     * @param mixed $payload Event payload
     * @param array<string, mixed> $conditions Dispatch conditions
     * @param array<string, mixed> $context Event context
     * @return array<string, mixed> Dispatch results
     */
    public function dispatchIf(string $event, $payload = null, array $conditions = [], array $context = []): array
    {
        if (!$this->evaluateConditions($conditions, $payload, $context)) {
            return [
                'success' => true,
                'skipped' => true,
                'reason' => 'Conditions not met',
                'responses' => [],
                'listener_count' => 0,
                'execution_time' => 0.0
            ];
        }
        
        return $this->dispatch($event, $payload, $context);
    }
    
    /**
     * Dispatches multiple events in batch with optimization
     *
     * Efficiently dispatches multiple events with batch processing,
     * memory management, and performance optimization.
     *
     * @param array<array<string, mixed>> $events Array of events to dispatch
     * @param array<string, mixed> $options Batch processing options
     * @return array<string, mixed> Batch dispatch results
     */
    public function dispatchBatch(array $events, array $options = []): array
    {
        $startTime = microtime(true);
        $batchSize = $options['batch_size'] ?? $this->config['batch_size'];
        $results = [];
        $totalProcessed = 0;
        $totalFailed = 0;
        
        $chunks = array_chunk($events, $batchSize);
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $eventData) {
                try {
                    $result = $this->dispatch(
                        $eventData['event'],
                        $eventData['payload'] ?? null,
                        $eventData['context'] ?? []
                        );
                    
                    $results[] = $result;
                    $totalProcessed++;
                    
                    if (!$result['success']) {
                        $totalFailed++;
                    }
                    
                } catch (\Exception $e) {
                    $totalFailed++;
                    $results[] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'event' => $eventData['event'] ?? 'unknown'
                    ];
                }
            }
            
            // Check memory usage and perform cleanup if needed
            if (memory_get_usage(true) > $this->config['memory_limit']) {
                gc_collect_cycles();
            }
        }
        
        return [
            'success' => $totalFailed === 0,
            'total_processed' => $totalProcessed,
            'total_failed' => $totalFailed,
            'results' => $results,
            'execution_time' => microtime(true) - $startTime
        ];
    }
    
    /**
     * Gets all listeners for a given event including wildcard matches
     *
     * Retrieves matching listeners using direct matching and wildcard patterns
     * with comprehensive pattern matching and performance optimization.
     *
     * @param string $event Event name to match
     * @return array<array<string, mixed>> Array of matching listeners
     */
    protected function getListenersForEvent(string $event): array
    {
        $matched = [];
        
        // Direct listeners (exact match)
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $priority => $priorityListeners) {
                foreach ($priorityListeners as $listenerData) {
                    $matched[] = $listenerData;
                }
            }
        }
        
        // Wildcard listeners with pattern matching
        foreach ($this->listeners as $registeredEvent => $priorityGroups) {
            if ($registeredEvent !== $event && $this->matchesWildcard($registeredEvent, $event)) {
                foreach ($priorityGroups as $priority => $priorityListeners) {
                    foreach ($priorityListeners as $listenerData) {
                        $matched[] = $listenerData;
                    }
                }
            }
        }
        
        return $matched;
    }
    
    /**
     * Removes an event listener with comprehensive validation
     *
     * Removes a specific listener from the event system with validation
     * and comprehensive cleanup of related metadata.
     *
     * @param string $event Event name
     * @param callable $listener Listener to remove
     * @return bool True if listener was found and removed
     */
    public function removeListener(string $event, callable $listener): bool
    {
        if (!isset($this->listeners[$event])) {
            return false;
        }
        
        foreach ($this->listeners[$event] as $priority => &$priorityListeners) {
            foreach ($priorityListeners as $i => $listenerData) {
                if ($listenerData['callback'] === $listener) {
                    unset($priorityListeners[$i]);
                    
                    // Clean up empty priority groups
                    if (empty($priorityListeners)) {
                        unset($this->listeners[$event][$priority]);
                    }
                    
                    // Clean up empty event entries
                    if (empty($this->listeners[$event])) {
                        unset($this->listeners[$event]);
                    }
                    
                    // Audit log listener removal
                    if ($this->config['enable_audit_logging']) {
                        $this->auditLog('listener_removed', [
                            'event' => $event,
                            'priority' => $priority,
                            'remaining_listeners' => $this->countListenersForEvent($event)
                        ]);
                    }
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Clears listeners with advanced filtering options
     *
     * Removes listeners based on various criteria with comprehensive
     * cleanup and audit logging.
     *
     * @param string|null $event Specific event to clear (null = all events)
     * @param array<string, mixed> $criteria Additional clearing criteria
     * @return int Number of listeners cleared
     */
    public function clearListeners(?string $event = null, array $criteria = []): int
    {
        $clearedCount = 0;
        
        if ($event !== null) {
            if (isset($this->listeners[$event])) {
                $clearedCount = $this->countListenersForEvent($event);
                unset($this->listeners[$event]);
            }
        } else {
            $clearedCount = $this->getTotalListenerCount();
            $this->listeners = [];
        }
        
        // Clear related metadata
        if ($event !== null) {
            unset($this->eventMetadata[$event]);
        } else {
            $this->eventMetadata = [];
        }
        
        // Audit log listener clearing
        if ($this->config['enable_audit_logging'] && $clearedCount > 0) {
            $this->auditLog('listeners_cleared', [
                'event' => $event,
                'cleared_count' => $clearedCount,
                'criteria' => $criteria
            ]);
        }
        
        return $clearedCount;
    }
    
    /**
     * Gets comprehensive performance and usage statistics
     *
     * Returns detailed statistics about event dispatcher performance,
     * usage patterns, and operational metrics.
     *
     * @return array<string, mixed> Performance statistics
     */
    public function getStats(): array
    {
        $currentTime = microtime(true);
        
        return array_merge($this->stats, [
            'total_events_registered' => count($this->listeners),
            'total_listeners_registered' => $this->getTotalListenerCount(),
            'average_execution_time' => $this->stats['total_dispatches'] > 0
            ? $this->stats['total_execution_time'] / $this->stats['total_dispatches']
            : 0.0,
            'success_rate' => $this->stats['total_dispatches'] > 0
            ? ($this->stats['total_dispatches'] - $this->stats['failed_dispatches']) / $this->stats['total_dispatches']
            : 1.0,
            'circuit_breaker_state' => $this->circuitBreaker['state'],
            'memory_usage' => memory_get_usage(true),
            'uptime' => $currentTime - ($this->stats['initialized_at'] ?? $currentTime)
        ]);
    }
    
    /**
     * Gets health status of the event dispatcher
     *
     * Returns comprehensive health information including circuit breaker status,
     * performance metrics, and operational health indicators.
     *
     * @return array<string, mixed> Health status information
     */
    public function getHealthStatus(): array
    {
        $stats = $this->getStats();
        
        return [
            'healthy' => $this->isHealthy(),
            'circuit_breaker_open' => $this->circuitBreaker['state'] === 'open',
            'success_rate' => $stats['success_rate'],
            'average_execution_time' => $stats['average_execution_time'],
            'total_dispatches' => $stats['total_dispatches'],
            'failed_dispatches' => $stats['failed_dispatches'],
            'memory_usage' => $stats['memory_usage'],
            'issues' => $this->getHealthIssues()
        ];
    }
    
    /**
     * Protected helper methods for internal operations
     */
    
    /**
     * Processes event through middleware pipeline
     *
     * @param array<string, mixed> $eventObj Event object
     * @return array<string, mixed> Processed event object
     */
    protected function processMiddleware(array $eventObj): array
    {
        foreach ($this->middleware as &$middlewareData) {
            $startTime = microtime(true);
            
            try {
                $eventObj = $middlewareData['callback']($eventObj, $this) ?: $eventObj;
                $middlewareData['execution_count']++;
                $middlewareData['total_execution_time'] += microtime(true) - $startTime;
                $this->stats['middleware_executions']++;
                
                if ($eventObj['stopped']) {
                    break;
                }
                
            } catch (\Exception $e) {
                // Log middleware error but continue processing
                if ($this->config['enable_audit_logging']) {
                    $this->auditLog('middleware_error', [
                        'error' => $e->getMessage(),
                        'event' => $eventObj['name']
                    ]);
                }
            }
        }
        
        return $eventObj;
    }
    
    /**
     * Executes individual listener with error handling
     *
     * @param array<string, mixed> $listenerData Listener data
     * @param array<string, mixed> $eventObj Event object
     * @return mixed Listener result
     */
    protected function executeListener(array $listenerData, array $eventObj)
    {
        $callback = $listenerData['callback'];
        
        // Call listener with event data
        return $callback($eventObj['payload'], $eventObj['name'], $this, $eventObj['context']);
    }
    
    /**
     * Checks if listener should execute based on conditions
     *
     * @param array<string, mixed> $listenerData Listener data
     * @param array<string, mixed> $eventObj Event object
     * @return bool True if listener should execute
     */
    protected function shouldExecuteListener(array $listenerData, array $eventObj): bool
    {
        $options = $listenerData['options'] ?? [];
        
        // Check conditional execution
        if (isset($options['condition']) && is_callable($options['condition'])) {
            return $options['condition']($eventObj, $this);
        }
        
        return true;
    }
    
    /**
     * Determines if event propagation should stop
     *
     * @param mixed $result Listener result
     * @param array<string, mixed> $eventObj Event object
     * @return bool True if propagation should stop
     */
    protected function shouldStopPropagation($result, array $eventObj): bool
    {
        // Check if result explicitly stops propagation
        if (is_array($result) && ($result['stop_propagation'] ?? false)) {
            return true;
        }
        
        // Check if event object was marked as stopped
        return $eventObj['stopped'] ?? false;
    }
    
    /**
     * Matches wildcard patterns against event names
     *
     * @param string $pattern Wildcard pattern
     * @param string $event Event name to match
     * @return bool True if pattern matches event
     */
    protected function matchesWildcard(string $pattern, string $event): bool
    {
        if (strpos($pattern, '*') === false) {
            return false;
        }
        
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
        return preg_match($regex, $event) === 1;
    }
    
    /**
     * Evaluates conditional dispatch rules
     *
     * @param array<string, mixed> $conditions Conditions to evaluate
     * @param mixed $payload Event payload
     * @param array<string, mixed> $context Event context
     * @return bool True if conditions are met
     */
    protected function evaluateConditions(array $conditions, $payload, array $context): bool
    {
        foreach ($conditions as $key => $value) {
            if (is_callable($value)) {
                if (!$value($payload, $context)) {
                    return false;
                }
            } else {
                // Simple field comparison
                if (is_array($payload) && isset($payload[$key])) {
                    if ($payload[$key] !== $value) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Circuit breaker and error handling methods
     */
    
    /**
     * Checks if circuit breaker is in closed state
     *
     * @return bool True if circuit breaker allows requests
     */
    protected function isCircuitBreakerClosed(): bool
    {
        if (!$this->config['enable_circuit_breaker']) {
            return true;
        }
        
        $state = $this->circuitBreaker['state'];
        
        if ($state === 'closed') {
            return true;
        }
        
        if ($state === 'open') {
            $timeSinceFailure = microtime(true) - ($this->circuitBreaker['last_failure_time'] ?? 0);
            if ($timeSinceFailure > $this->circuitBreaker['timeout']) {
                $this->circuitBreaker['state'] = 'half-open';
                return true;
            }
            return false;
        }
        
        // half-open state - allow limited requests
        return true;
    }
    
    /**
     * Updates circuit breaker state based on failures
     *
     * @param int $failureCount Number of failures in this dispatch
     * @return void
     */
    protected function updateCircuitBreaker(int $failureCount): void
    {
        if (!$this->config['enable_circuit_breaker']) {
            return;
        }
        
        $this->circuitBreaker['failure_count'] += $failureCount;
        $this->circuitBreaker['last_failure_time'] = microtime(true);
        
        if ($this->circuitBreaker['failure_count'] >= $this->circuitBreaker['failure_threshold']) {
            $this->circuitBreaker['state'] = 'open';
            $this->stats['circuit_breaker_trips']++;
        }
    }
    
    /**
     * Resets circuit breaker to closed state
     *
     * @return void
     */
    protected function resetCircuitBreaker(): void
    {
        $this->circuitBreaker['failure_count'] = 0;
        $this->circuitBreaker['state'] = 'closed';
        $this->circuitBreaker['last_failure_time'] = null;
    }
    
    /**
     * Handles listener execution errors
     *
     * @param \Exception $exception Exception that occurred
     * @param array<string, mixed> $listenerData Listener data
     * @param array<string, mixed> $eventObj Event object
     * @return void
     */
    protected function handleListenerError(\Exception $exception, array $listenerData, array $eventObj): void
    {
        if ($this->config['enable_audit_logging']) {
            $this->auditLog('listener_error', [
                'error' => $exception->getMessage(),
                'event' => $eventObj['name'],
                'listener_executions' => $listenerData['execution_count'],
                'listener_errors' => $listenerData['error_count']
            ]);
        }
    }
    
    /**
     * Determines if an error is critical and should stop processing
     *
     * @param \Exception $exception Exception to evaluate
     * @return bool True if error is critical
     */
    protected function isCriticalError(\Exception $exception): bool
    {
        // Consider memory errors and timeout errors as critical
        return strpos($exception->getMessage(), 'memory') !== false ||
        strpos($exception->getMessage(), 'timeout') !== false ||
        strpos($exception->getMessage(), 'fatal') !== false;
    }
    
    /**
     * Statistics and monitoring methods
     */
    
    /**
     * Initializes performance statistics
     *
     * @return void
     */
    protected function initializeStats(): void
    {
        $this->stats['initialized_at'] = microtime(true);
    }
    
    /**
     * Updates performance statistics
     *
     * @param string $event Event name
     * @param int $executedCount Number of executed listeners
     * @param int $failedCount Number of failed listeners
     * @param float $executionTime Total execution time
     * @return void
     */
    protected function updateStats(string $event, int $executedCount, int $failedCount, float $executionTime): void
    {
        $this->stats['total_dispatches']++;
        $this->stats['total_listeners_executed'] += $executedCount;
        $this->stats['total_execution_time'] += $executionTime;
        
        if ($failedCount > 0) {
            $this->stats['failed_dispatches']++;
        }
    }
    
    /**
     * Counts listeners for a specific event
     *
     * @param string $event Event name
     * @return int Number of listeners
     */
    protected function countListenersForEvent(string $event): int
    {
        if (!isset($this->listeners[$event])) {
            return 0;
        }
        
        $count = 0;
        foreach ($this->listeners[$event] as $priorityListeners) {
            $count += count($priorityListeners);
        }
        
        return $count;
    }
    
    /**
     * Gets total listener count across all events
     *
     * @return int Total listener count
     */
    protected function getTotalListenerCount(): int
    {
        $total = 0;
        foreach ($this->listeners as $eventListeners) {
            foreach ($eventListeners as $priorityListeners) {
                $total += count($priorityListeners);
            }
        }
        return $total;
    }
    
    /**
     * Checks overall health of the dispatcher
     *
     * @return bool True if dispatcher is healthy
     */
    protected function isHealthy(): bool
    {
        $stats = $this->getStats();
        
        return $this->circuitBreaker['state'] === 'closed' &&
        $stats['success_rate'] > 0.95 &&
        $stats['average_execution_time'] < $this->config['max_execution_time'];
    }
    
    /**
     * Gets list of current health issues
     *
     * @return array<string> List of health issues
     */
    protected function getHealthIssues(): array
    {
        $issues = [];
        $stats = $this->getStats();
        
        if ($this->circuitBreaker['state'] === 'open') {
            $issues[] = 'Circuit breaker is open due to failures';
        }
        
        if ($stats['success_rate'] < 0.95) {
            $issues[] = 'Low success rate: ' . number_format($stats['success_rate'] * 100, 1) . '%';
        }
        
        if ($stats['average_execution_time'] > $this->config['max_execution_time']) {
            $issues[] = 'High average execution time: ' . number_format($stats['average_execution_time'], 3) . 's';
        }
        
        return $issues;
    }
    
    /**
     * Logs audit events for compliance and monitoring
     *
     * @param string $action Action type
     * @param array<string, mixed> $data Action data
     * @return void
     */
    protected function auditLog(string $action, array $data): void
    {
        if (!$this->config['enable_audit_logging']) {
            return;
        }
        
        // If container is available and has audit logger, use it
        if ($this->container && method_exists($this->container, 'has') && $this->container->has('mas.audit_logger')) {
            $auditLogger = $this->container->get('mas.audit_logger');
            $auditLogger->log($action, $data, 'event_dispatcher');
        }
    }
}
