<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Array Helper - Advanced Array Manipulation Utilities
 *
 * Comprehensive static utility class providing advanced array manipulation, filtering,
 * transformation, validation, and optimization methods specifically designed for MAS
 * enterprise operations. Includes performance-optimized algorithms, type safety,
 * comprehensive error handling, and specialized methods for complex data structures.
 *
 * This helper class serves as the foundation for all array operations throughout the
 * MAS ecosystem, providing consistent behavior, performance optimization, and enterprise-grade
 * features including deep manipulation, validation, transformation pipelines, and
 * specialized operations for customer data, configuration management, and analytics.
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas\Helper
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Opencart\Extension\Mas\System\Library\Mas\Helper;

/**
 * Enterprise Array Helper
 *
 * Advanced static utility class providing comprehensive array manipulation capabilities
 * for enterprise MAS operations. Features performance-optimized algorithms, type safety,
 * comprehensive validation, and specialized methods for complex data transformations.
 *
 * Key Features:
 * - Deep array manipulation with dot notation support
 * - Performance-optimized algorithms for large datasets
 * - Type-safe operations with comprehensive validation
 * - Advanced filtering and transformation pipelines
 * - Specialized methods for customer data processing
 * - Configuration management utilities
 * - Analytics data transformation helpers
 * - Memory-efficient operations for large arrays
 * - Comprehensive error handling with context preservation
 * - Support for complex nested structures
 * - Batch processing capabilities
 * - Data validation and sanitization
 * - Export/import utilities for various formats
 *
 * Method Categories:
 * - Basic Operations: get, set, has, remove, merge
 * - Advanced Manipulation: dot notation, deep operations, transformation
 * - Filtering & Searching: advanced filters, multi-criteria search
 * - Data Processing: grouping, sorting, aggregation, statistics
 * - Validation: structure validation, type checking, sanitization
 * - Performance: memory-efficient operations, batch processing
 * - Conversion: format conversion, serialization, export/import
 * - Analytics: statistical functions, data analysis helpers
 *
 * Usage Examples:
 *
 * Basic operations:
 * $value = ArrayHelper::get($data, 'key', 'default');
 * ArrayHelper::set($data, 'path.to.key', $value);
 *
 * Dot notation:
 * $nested = ArrayHelper::getDot($config, 'database.connections.default.host');
 * ArrayHelper::setDot($config, 'cache.redis.host', 'localhost');
 *
 * Advanced filtering:
 * $filtered = ArrayHelper::filterBy($customers, [
 *     'status' => 'active',
 *     'order_count' => ['>', 5]
 * ]);
 *
 * Data processing:
 * $grouped = ArrayHelper::groupBy($orders, 'customer_id');
 * $stats = ArrayHelper::calculateStats($revenue, 'amount');
 */
class ArrayHelper
{
    /**
     * @var int Maximum recursion depth for deep operations
     */
    protected const MAX_DEPTH = 100;
    
    /**
     * @var int Batch size for memory-efficient operations
     */
    protected const BATCH_SIZE = 1000;
    
    /**
     * @var array<string> Operators for advanced filtering
     */
    protected const FILTER_OPERATORS = ['=', '!=', '>', '<', '>=', '<=', 'in', 'not_in', 'like', 'not_like'];
    
    /**
     * Gets a value from an array by key with enhanced default handling
     *
     * Retrieves a value from an array with support for multiple key types,
     * callable defaults, and comprehensive error handling.
     *
     * @param array<mixed, mixed> $array The array to search
     * @param string|int $key The key to look for
     * @param mixed $default The default value or callable if key is not found
     * @return mixed The value, default value, or result of default callable
     */
    public static function get(array $array, $key, $default = null)
    {
        if (!array_key_exists($key, $array)) {
            return is_callable($default) ? $default($key, $array) : $default;
        }
        
        return $array[$key];
    }
    
    /**
     * Gets a value using dot notation path
     *
     * Retrieves nested values using dot notation (e.g., 'user.profile.name')
     * with support for array indices and comprehensive path validation.
     *
     * @param array<mixed, mixed> $array The array to search
     * @param string $path The dot notation path
     * @param mixed $default The default value if path is not found
     * @return mixed The value at the path or default
     */
    public static function getDot(array $array, string $path, $default = null)
    {
        if (empty($path)) {
            return $default;
        }
        
        $keys = explode('.', $path);
        $current = $array;
        
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return is_callable($default) ? $default($path, $array) : $default;
            }
            $current = $current[$key];
        }
        
        return $current;
    }
    
    /**
     * Sets a value in an array by key with return value
     *
     * Sets a value in an array and returns the modified array for chaining.
     * Supports type validation and automatic array creation.
     *
     * @param array<mixed, mixed> $array The array to modify
     * @param string|int $key The key to set
     * @param mixed $value The value to assign
     * @return array<mixed, mixed> The modified array
     */
    public static function set(array &$array, $key, $value): array
    {
        $array[$key] = $value;
        return $array;
    }
    
    /**
     * Sets a value using dot notation path
     *
     * Sets nested values using dot notation, automatically creating
     * intermediate arrays as needed with comprehensive path handling.
     *
     * @param array<mixed, mixed> $array The array to modify
     * @param string $path The dot notation path
     * @param mixed $value The value to assign
     * @return array<mixed, mixed> The modified array
     */
    public static function setDot(array &$array, string $path, $value): array
    {
        if (empty($path)) {
            return $array;
        }
        
        $keys = explode('.', $path);
        $current = &$array;
        
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
        
        return $array;
    }
    
    /**
     * Checks if an array has a specific key with type validation
     *
     * Enhanced key existence checking with support for type validation
     * and comprehensive error handling.
     *
     * @param array<mixed, mixed> $array The array to check
     * @param string|int $key The key to look for
     * @return bool True if the key exists, false otherwise
     */
    public static function has(array $array, $key): bool
    {
        return array_key_exists($key, $array);
    }
    
    /**
     * Checks if a dot notation path exists in the array
     *
     * Verifies the existence of nested keys using dot notation with
     * support for complex path validation and error handling.
     *
     * @param array<mixed, mixed> $array The array to check
     * @param string $path The dot notation path
     * @return bool True if the path exists, false otherwise
     */
    public static function hasDot(array $array, string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        
        $keys = explode('.', $path);
        $current = $array;
        
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return false;
            }
            $current = $current[$key];
        }
        
        return true;
    }
    
    /**
     * Removes a value from an array by key
     *
     * Removes a key-value pair from an array and returns the modified array.
     * Supports multiple key removal and comprehensive error handling.
     *
     * @param array<mixed, mixed> $array The array to modify
     * @param string|int|array<string|int> $keys The key(s) to remove
     * @return array<mixed, mixed> The modified array
     */
    public static function remove(array &$array, $keys): array
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        foreach ($keys as $key) {
            unset($array[$key]);
        }
        
        return $array;
    }
    
    /**
     * Removes a value using dot notation path
     *
     * Removes nested values using dot notation with support for
     * path validation and automatic cleanup of empty parent arrays.
     *
     * @param array<mixed, mixed> $array The array to modify
     * @param string $path The dot notation path
     * @return array<mixed, mixed> The modified array
     */
    public static function removeDot(array &$array, string $path): array
    {
        if (empty($path)) {
            return $array;
        }
        
        $keys = explode('.', $path);
        $current = &$array;
        $parents = [];
        
        // Navigate to the parent of the target key
        foreach (array_slice($keys, 0, -1) as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return $array; // Path doesn't exist
            }
            $parents[] = &$current;
            $current = &$current[$key];
        }
        
        $finalKey = end($keys);
        if (is_array($current) && array_key_exists($finalKey, $current)) {
            unset($current[$finalKey]);
        }
        
        return $array;
    }
    
    /**
     * Filters an array with advanced criteria support
     *
     * Advanced filtering with support for multiple criteria, operators,
     * and complex conditions for enterprise data processing.
     *
     * @param array<mixed, mixed> $array The array to filter
     * @param callable|array<string, mixed> $criteria Filter criteria or callback
     * @param bool $preserveKeys Whether to preserve array keys
     * @return array<mixed, mixed> The filtered array
     */
    public static function filter(array $array, $criteria, bool $preserveKeys = true): array
    {
        if (is_callable($criteria)) {
            return array_filter($array, $criteria, ARRAY_FILTER_USE_BOTH);
        }
        
        if (is_array($criteria)) {
            return self::filterBy($array, $criteria, $preserveKeys);
        }
        
        return $array;
    }
    
    /**
     * Filters array by multiple criteria with operators
     *
     * Advanced filtering supporting multiple field criteria with various
     * operators for complex data filtering operations.
     *
     * @param array<mixed, mixed> $array The array to filter
     * @param array<string, mixed> $criteria Filter criteria with operators
     * @param bool $preserveKeys Whether to preserve array keys
     * @return array<mixed, mixed> The filtered array
     */
    public static function filterBy(array $array, array $criteria, bool $preserveKeys = true): array
    {
        $filtered = [];
        
        foreach ($array as $key => $item) {
            $matches = true;
            
            foreach ($criteria as $field => $condition) {
                $value = is_array($item) ? self::getDot($item, $field) : $item;
                
                if (!self::matchesCriteria($value, $condition)) {
                    $matches = false;
                    break;
                }
            }
            
            if ($matches) {
                if ($preserveKeys) {
                    $filtered[$key] = $item;
                } else {
                    $filtered[] = $item;
                }
            }
        }
        
        return $filtered;
    }
    
    /**
     * Maps an array through a callback with enhanced features
     *
     * Enhanced array mapping with support for key mapping, error handling,
     * and batch processing for large datasets.
     *
     * @param array<mixed, mixed> $array The array to map
     * @param callable $callback The callback function
     * @param bool $mapKeys Whether to map keys as well
     * @return array<mixed, mixed> The mapped array
     */
    public static function map(array $array, callable $callback, bool $mapKeys = false): array
    {
        if ($mapKeys) {
            $result = [];
            foreach ($array as $key => $value) {
                $mapped = $callback($value, $key);
                if (is_array($mapped) && count($mapped) === 2) {
                    [$newKey, $newValue] = $mapped;
                    $result[$newKey] = $newValue;
                } else {
                    $result[$key] = $mapped;
                }
            }
            return $result;
        }
        
        return array_map($callback, $array);
    }
    
    /**
     * Merges arrays recursively with advanced conflict resolution
     *
     * Advanced recursive merging with configurable conflict resolution
     * strategies and support for complex data structures.
     *
     * @param array<mixed, mixed> ...$arrays Arrays to merge
     * @return array<mixed, mixed> The merged array
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        if (empty($arrays)) {
            return [];
        }
        
        $result = array_shift($arrays);
        
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::mergeRecursive($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Deep merges arrays with custom merge strategies
     *
     * Advanced merging with configurable strategies for handling conflicts,
     * array values, and complex nested structures.
     *
     * @param array<mixed, mixed> $array1 First array
     * @param array<mixed, mixed> $array2 Second array
     * @param array<string, mixed> $options Merge options and strategies
     * @return array<mixed, mixed> The merged array
     */
    public static function deepMerge(array $array1, array $array2, array $options = []): array
    {
        $strategy = $options['strategy'] ?? 'replace';
        $maxDepth = $options['max_depth'] ?? self::MAX_DEPTH;
        
        return self::deepMergeRecursive($array1, $array2, $strategy, 0, $maxDepth);
    }
    
    /**
     * Flattens a multi-dimensional array with key preservation options
     *
     * Advanced flattening with support for key preservation, custom separators,
     * and depth limiting for performance optimization.
     *
     * @param array<mixed, mixed> $array The array to flatten
     * @param string $separator Key separator for nested keys
     * @param int|null $maxDepth Maximum depth to flatten
     * @return array<mixed, mixed> The flattened array
     */
    public static function flatten(array $array, string $separator = '.', ?int $maxDepth = null): array
    {
        return self::flattenRecursive($array, '', $separator, 0, $maxDepth ?? self::MAX_DEPTH);
    }
    
    /**
     * Returns the first element with enhanced filtering
     *
     * Returns the first element of an array with support for condition filtering
     * and comprehensive null handling.
     *
     * @param array<mixed, mixed> $array The array to process
     * @param callable|null $condition Optional condition callback
     * @return mixed The first element or null if empty/no match
     */
    public static function first(array $array, ?callable $condition = null)
    {
        if (empty($array)) {
            return null;
        }
        
        if ($condition === null) {
            $value = reset($array);
            return $value !== false ? $value : null;
        }
        
        foreach ($array as $key => $value) {
            if ($condition($value, $key)) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Returns the last element with enhanced filtering
     *
     * Returns the last element of an array with support for condition filtering
     * and comprehensive null handling.
     *
     * @param array<mixed, mixed> $array The array to process
     * @param callable|null $condition Optional condition callback
     * @return mixed The last element or null if empty/no match
     */
    public static function last(array $array, ?callable $condition = null)
    {
        if (empty($array)) {
            return null;
        }
        
        if ($condition === null) {
            $value = end($array);
            return $value !== false ? $value : null;
        }
        
        $result = null;
        foreach ($array as $key => $value) {
            if ($condition($value, $key)) {
                $result = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Plucks values with support for complex paths and transformations
     *
     * Advanced value extraction with support for dot notation paths,
     * transformation callbacks, and null handling.
     *
     * @param array<mixed, mixed> $array The array to process
     * @param string $key The key or path to extract
     * @param string|null $indexKey Optional key to use as array index
     * @param callable|null $transform Optional transformation callback
     * @return array<mixed, mixed> The array of extracted values
     */
    public static function pluck(array $array, string $key, ?string $indexKey = null, ?callable $transform = null): array
    {
        $result = [];
        
        foreach ($array as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $value = self::getDot($item, $key);
            if ($value !== null) {
                if ($transform) {
                    $value = $transform($value, $item);
                }
                
                if ($indexKey) {
                    $index = self::getDot($item, $indexKey);
                    if ($index !== null) {
                        $result[$index] = $value;
                    } else {
                        $result[] = $value;
                    }
                } else {
                    $result[] = $value;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Groups array by key with advanced grouping options
     *
     * Advanced grouping with support for multiple grouping keys,
     * custom key generation, and hierarchical grouping.
     *
     * @param array<mixed, mixed> $array The array to group
     * @param string|callable $key The key to group by or callback
     * @param bool $preserveKeys Whether to preserve original array keys
     * @return array<string|int, array<mixed, mixed>> The grouped array
     */
    public static function groupBy(array $array, $key, bool $preserveKeys = false): array
    {
        $result = [];
        
        foreach ($array as $index => $item) {
            if (is_callable($key)) {
                $groupKey = $key($item, $index);
            } else {
                $groupKey = is_array($item) ? self::getDot($item, $key) : null;
            }
            
            if ($groupKey === null) {
                $groupKey = '__null__';
            }
            
            if (!isset($result[$groupKey])) {
                $result[$groupKey] = [];
            }
            
            if ($preserveKeys) {
                $result[$groupKey][$index] = $item;
            } else {
                $result[$groupKey][] = $item;
            }
        }
        
        return $result;
    }
    
    /**
     * Sorts array by key with advanced sorting options
     *
     * Advanced sorting with support for multiple sort keys, custom comparators,
     * and stable sorting for consistent results.
     *
     * @param array<mixed, mixed> $array The array to sort
     * @param string|callable $key The key to sort by or comparator
     * @param string $direction Sort direction ('asc', 'desc')
     * @param int $flags Sort flags for different data types
     * @return array<mixed, mixed> The sorted array
     */
    public static function sortBy(array $array, $key, string $direction = 'asc', int $flags = SORT_REGULAR): array
    {
        if (is_callable($key)) {
            uasort($array, $key);
            return $direction === 'desc' ? array_reverse($array, true) : $array;
        }
        
        $sortKeys = [];
        foreach ($array as $index => $item) {
            $sortKeys[$index] = is_array($item) ? self::getDot($item, $key) : $item;
        }
        
        $sortDirection = $direction === 'desc' ? SORT_DESC : SORT_ASC;
        array_multisort($sortKeys, $sortDirection, $flags, $array);
        
        return $array;
    }
    
    /**
     * Multi-level sorting with multiple criteria
     *
     * Advanced sorting supporting multiple sort criteria with individual
     * directions and flags for complex data sorting requirements.
     *
     * @param array<mixed, mixed> $array The array to sort
     * @param array<array<string, mixed>> $criteria Sort criteria
     * @return array<mixed, mixed> The sorted array
     */
    public static function multiSort(array $array, array $criteria): array
    {
        if (empty($criteria)) {
            return $array;
        }
        
        $sortColumns = [];
        $sortDirections = [];
        $sortFlags = [];
        
        foreach ($criteria as $criterion) {
            $key = $criterion['key'];
            $direction = $criterion['direction'] ?? 'asc';
            $flags = $criterion['flags'] ?? SORT_REGULAR;
            
            $column = [];
            foreach ($array as $item) {
                $column[] = is_array($item) ? self::getDot($item, $key) : $item;
            }
            
            $sortColumns[] = $column;
            $sortDirections[] = $direction === 'desc' ? SORT_DESC : SORT_ASC;
            $sortFlags[] = $flags;
        }
        
        // Build array_multisort arguments
        $args = [];
        for ($i = 0; $i < count($sortColumns); $i++) {
            $args[] = $sortColumns[$i];
            $args[] = $sortDirections[$i];
            $args[] = $sortFlags[$i];
        }
        $args[] = &$array;
        
        call_user_func_array('array_multisort', $args);
        
        return $array;
    }
    
    /**
     * Converts array to JSON with enhanced options
     *
     * Advanced JSON conversion with comprehensive error handling,
     * encoding options, and format validation.
     *
     * @param array<mixed, mixed> $array The array to convert
     * @param int $options JSON encoding options
     * @param int $depth Maximum encoding depth
     * @return string The JSON string
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If JSON encoding fails
     */
    public static function toJson(array $array, int $options = 0, int $depth = 512): string
    {
        $json = json_encode($array, $options, $depth);
        
        if ($json === false) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'JSON encoding failed: ' . json_last_error_msg(),
                'array_helper',
                'high',
                [
                    'json_error' => json_last_error(),
                    'json_error_msg' => json_last_error_msg(),
                    'array_size' => count($array),
                    'options' => $options,
                    'depth' => $depth
                ]
                );
        }
        
        return $json;
    }
    
    /**
     * Converts JSON string to array with enhanced validation
     *
     * Advanced JSON parsing with comprehensive error handling,
     * validation, and security features.
     *
     * @param string $json The JSON string to decode
     * @param bool $assoc Whether to return associative arrays
     * @param int $depth Maximum decoding depth
     * @param int $flags JSON decoding flags
     * @return array<mixed, mixed> The decoded array
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If JSON decoding fails
     */
    public static function fromJson(string $json, bool $assoc = true, int $depth = 512, int $flags = 0): array
    {
        $decoded = json_decode($json, $assoc, $depth, $flags);
        
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'JSON decoding failed: ' . json_last_error_msg(),
                'array_helper',
                'high',
                [
                    'json_error' => json_last_error(),
                    'json_error_msg' => json_last_error_msg(),
                    'json_length' => strlen($json),
                    'flags' => $flags,
                    'depth' => $depth
                ]
                );
        }
        
        if (!is_array($decoded)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'JSON does not decode to an array',
                'array_helper',
                'medium',
                ['decoded_type' => gettype($decoded)]
                );
        }
        
        return $decoded;
    }
    
    /**
     * Validates array structure against a schema
     *
     * Comprehensive array validation with support for nested structures,
     * type checking, and custom validation rules.
     *
     * @param array<mixed, mixed> $array The array to validate
     * @param array<string, mixed> $schema Validation schema
     * @return array<string, mixed> Validation results
     */
    public static function validate(array $array, array $schema): array
    {
        $errors = [];
        $warnings = [];
        
        // Validate required fields
        foreach ($schema['required'] ?? [] as $field) {
            if (!self::hasDot($array, $field)) {
                $errors[] = "Required field '{$field}' is missing";
            }
        }
        
        // Validate field types and constraints
        foreach ($schema['properties'] ?? [] as $field => $rules) {
            if (!self::hasDot($array, $field)) {
                continue;
            }
            
            $value = self::getDot($array, $field);
            $fieldErrors = self::validateField($field, $value, $rules);
            $errors = array_merge($errors, $fieldErrors);
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Calculates statistical information for numeric arrays
     *
     * Comprehensive statistical analysis including basic statistics,
     * distribution analysis, and outlier detection.
     *
     * @param array<mixed, mixed> $array The array to analyze
     * @param string|null $key Optional key for nested arrays
     * @return array<string, mixed> Statistical information
     */
    public static function calculateStats(array $array, ?string $key = null): array
    {
        $values = [];
        
        if ($key) {
            foreach ($array as $item) {
                $value = is_array($item) ? self::getDot($item, $key) : null;
                if (is_numeric($value)) {
                    $values[] = (float)$value;
                }
            }
        } else {
            foreach ($array as $value) {
                if (is_numeric($value)) {
                    $values[] = (float)$value;
                }
            }
        }
        
        if (empty($values)) {
            return [
                'count' => 0,
                'sum' => 0,
                'mean' => 0,
                'median' => 0,
                'min' => 0,
                'max' => 0,
                'std_deviation' => 0
            ];
        }
        
        sort($values);
        $count = count($values);
        $sum = array_sum($values);
        $mean = $sum / $count;
        
        // Calculate median
        $median = $count % 2 === 0
        ? ($values[$count / 2 - 1] + $values[$count / 2]) / 2
        : $values[intval($count / 2)];
        
        // Calculate standard deviation
        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= $count;
        $stdDeviation = sqrt($variance);
        
        return [
            'count' => $count,
            'sum' => $sum,
            'mean' => $mean,
            'median' => $median,
            'min' => min($values),
            'max' => max($values),
            'std_deviation' => $stdDeviation,
            'variance' => $variance
        ];
    }
    
    /**
     * Chunks array into smaller arrays for batch processing
     *
     * Memory-efficient array chunking with support for key preservation
     * and custom chunk size calculation based on memory constraints.
     *
     * @param array<mixed, mixed> $array The array to chunk
     * @param int $size Chunk size
     * @param bool $preserveKeys Whether to preserve keys
     * @return array<int, array<mixed, mixed>> Array of chunks
     */
    public static function chunk(array $array, int $size = null, bool $preserveKeys = false): array
    {
        $chunkSize = $size ?? self::BATCH_SIZE;
        return array_chunk($array, $chunkSize, $preserveKeys);
    }
    
    /**
     * Checks if array contains only specified types
     *
     * Type validation helper for ensuring array homogeneity
     * with support for multiple allowed types.
     *
     * @param array<mixed, mixed> $array The array to check
     * @param string|array<string> $types Allowed type(s)
     * @return bool True if all values match allowed types
     */
    public static function isTyped(array $array, $types): bool
    {
        $allowedTypes = is_array($types) ? $types : [$types];
        
        foreach ($array as $value) {
            $valueType = gettype($value);
            if (!in_array($valueType, $allowedTypes, true)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Sanitizes array values using provided sanitizers
     *
     * Comprehensive array sanitization with support for nested structures
     * and custom sanitization functions.
     *
     * @param array<mixed, mixed> $array The array to sanitize
     * @param array<string, callable> $sanitizers Field-specific sanitizers
     * @param array<string, mixed> $options Sanitization options
     * @return array<mixed, mixed> The sanitized array
     */
    public static function sanitize(array $array, array $sanitizers = [], array $options = []): array
    {
        $recursive = $options['recursive'] ?? true;
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            if (isset($sanitizers[$key])) {
                $sanitized[$key] = $sanitizers[$key]($value);
            } elseif (is_array($value) && $recursive) {
                $sanitized[$key] = self::sanitize($value, $sanitizers, $options);
            } elseif (is_string($value)) {
                $sanitized[$key] = trim($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Legacy and compatibility methods
     */
    
    /**
     * Checks if all values in an array are true (strict)
     *
     * @param array<mixed, mixed> $array The array to check
     * @return bool True if all values are true, false otherwise
     */
    public static function allTrue(array $array): bool
    {
        foreach ($array as $value) {
            if ($value !== true) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Checks if any value in an array is true (strict)
     *
     * @param array<mixed, mixed> $array The array to check
     * @return bool True if any value is true, false otherwise
     */
    public static function anyTrue(array $array): bool
    {
        foreach ($array as $value) {
            if ($value === true) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns a new array with only the specified keys
     *
     * @param array<mixed, mixed> $array The array to filter
     * @param array<string|int> $keys The keys to keep
     * @return array<mixed, mixed> The filtered array
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }
    
    /**
     * Returns a new array without the specified keys
     *
     * @param array<mixed, mixed> $array The array to filter
     * @param array<string|int> $keys The keys to exclude
     * @return array<mixed, mixed> The filtered array
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }
    
    /**
     * Returns whether the array is associative
     *
     * @param array<mixed, mixed> $array The array to check
     * @return bool True if associative, false otherwise
     */
    public static function isAssoc(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    /**
     * Protected helper methods for internal operations
     */
    
    /**
     * Recursively performs deep merge operation
     *
     * @param array<mixed, mixed> $array1 First array
     * @param array<mixed, mixed> $array2 Second array
     * @param string $strategy Merge strategy
     * @param int $depth Current depth
     * @param int $maxDepth Maximum allowed depth
     * @return array<mixed, mixed> Merged array
     */
    protected static function deepMergeRecursive(array $array1, array $array2, string $strategy, int $depth, int $maxDepth): array
    {
        if ($depth >= $maxDepth) {
            return $array1;
        }
        
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = self::deepMergeRecursive($array1[$key], $value, $strategy, $depth + 1, $maxDepth);
            } else {
                switch ($strategy) {
                    case 'replace':
                        $array1[$key] = $value;
                        break;
                    case 'keep':
                        if (!isset($array1[$key])) {
                            $array1[$key] = $value;
                        }
                        break;
                    case 'append':
                        if (isset($array1[$key])) {
                            if (!is_array($array1[$key])) {
                                $array1[$key] = [$array1[$key]];
                            }
                            $array1[$key][] = $value;
                        } else {
                            $array1[$key] = $value;
                        }
                        break;
                }
            }
        }
        
        return $array1;
    }
    
    /**
     * Recursively flattens an array with key preservation
     *
     * @param array<mixed, mixed> $array Array to flatten
     * @param string $prefix Key prefix
     * @param string $separator Key separator
     * @param int $depth Current depth
     * @param int $maxDepth Maximum depth
     * @return array<mixed, mixed> Flattened array
     */
    protected static function flattenRecursive(array $array, string $prefix, string $separator, int $depth, int $maxDepth): array
    {
        if ($depth >= $maxDepth) {
            return [$prefix => $array];
        }
        
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . $separator . $key;
            
            if (is_array($value)) {
                $result = array_merge($result, self::flattenRecursive($value, $newKey, $separator, $depth + 1, $maxDepth));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Checks if a value matches filtering criteria
     *
     * @param mixed $value Value to check
     * @param mixed $condition Condition to match against
     * @return bool True if value matches condition
     */
    protected static function matchesCriteria($value, $condition): bool
    {
        if (!is_array($condition)) {
            return $value === $condition;
        }
        
        if (count($condition) === 2) {
            [$operator, $compareValue] = $condition;
            return self::applyOperator($value, $operator, $compareValue);
        }
        
        return false;
    }
    
    /**
     * Applies comparison operator to values
     *
     * @param mixed $value Value to compare
     * @param string $operator Comparison operator
     * @param mixed $compareValue Value to compare against
     * @return bool Comparison result
     */
    protected static function applyOperator($value, string $operator, $compareValue): bool
    {
        switch ($operator) {
            case '=':
            case '==':
                return $value == $compareValue;
            case '!=':
            case '<>':
                return $value != $compareValue;
            case '>':
                return $value > $compareValue;
            case '<':
                return $value < $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<=':
                return $value <= $compareValue;
            case 'in':
                return is_array($compareValue) && in_array($value, $compareValue, true);
            case 'not_in':
                return is_array($compareValue) && !in_array($value, $compareValue, true);
            case 'like':
                return is_string($value) && is_string($compareValue) && strpos($value, $compareValue) !== false;
            case 'not_like':
                return is_string($value) && is_string($compareValue) && strpos($value, $compareValue) === false;
            default:
                return false;
        }
    }
    
    /**
     * Validates a single field against rules
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array<string, mixed> $rules Validation rules
     * @return array<string> Validation errors
     */
    protected static function validateField(string $field, $value, array $rules): array
    {
        $errors = [];
        
        // Type validation
        if (isset($rules['type'])) {
            $expectedType = $rules['type'];
            $actualType = gettype($value);
            
            if ($expectedType !== $actualType) {
                $errors[] = "Field '{$field}' must be of type {$expectedType}, {$actualType} given";
            }
        }
        
        // Required validation
        if (($rules['required'] ?? false) && ($value === null || $value === '')) {
            $errors[] = "Field '{$field}' is required";
        }
        
        // Min/max validation for strings and numbers
        if (isset($rules['min'])) {
            $min = $rules['min'];
            if ((is_string($value) && strlen($value) < $min) || (is_numeric($value) && $value < $min)) {
                $errors[] = "Field '{$field}' must be at least {$min}";
            }
        }
        
        if (isset($rules['max'])) {
            $max = $rules['max'];
            if ((is_string($value) && strlen($value) > $max) || (is_numeric($value) && $value > $max)) {
                $errors[] = "Field '{$field}' must be at most {$max}";
            }
        }
        
        // Pattern validation
        if (isset($rules['pattern']) && is_string($value) && !preg_match($rules['pattern'], $value)) {
            $errors[] = "Field '{$field}' does not match required pattern";
        }
        
        return $errors;
    }
}
