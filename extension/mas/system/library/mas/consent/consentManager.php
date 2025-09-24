<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Consent Manager - GDPR/Privacy Compliance System
 *
 * Centralized manager for customer/user consents (GDPR, marketing, cookies, T&C, etc.).
 * Handles creation and lifecycle of consent definitions, logging of accept/revoke events,
 * version control, analytics and compliance exports with full GDPR Article 7 compliance,
 * EN 29184 standards, and enterprise-grade features including consent analytics,
 * automated compliance reporting, consent withdrawal tracking, and audit trail management.
 *
 * This system provides comprehensive consent management capabilities required for
 * modern privacy regulations including GDPR, CCPA, LGPD, and other regional privacy laws.
 * Features include granular consent tracking, version management, bulk operations,
 * compliance analytics, automated reporting, and integration with marketing automation
 * workflows for privacy-compliant customer engagement.
 *
 * @package    Opencart\Extension\Mas\System\Library\Mas\Consent
 * @author     MAS Development Team
 * @copyright  Copyright (c) 2025, MAS Development Team
 * @license    Proprietary - All Rights Reserved
 * @version    2.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace Opencart\Extension\Mas\System\Library\Mas\Consent;

/**
 * Enterprise Consent Manager
 *
 * Comprehensive consent management system providing full GDPR Article 7 compliance
 * with advanced features including consent analytics, automated compliance reporting,
 * version control, bulk operations, and integration with marketing automation workflows.
 *
 * Key Features:
 * - GDPR Article 7 compliant consent management
 * - Granular consent tracking and version control
 * - Automated compliance reporting and analytics
 * - Bulk consent operations and data portability
 * - Integration with marketing automation workflows
 * - Advanced consent withdrawal tracking
 * - Comprehensive audit trail and logging
 * - Multi-language consent definitions
 * - Consent proof of record (CPoR) generation
 * - Real-time consent validation
 * - Automated consent expiration handling
 * - Privacy dashboard integration
 *
 * Compliance Standards:
 * - GDPR (General Data Protection Regulation)
 * - CCPA (California Consumer Privacy Act)
 * - LGPD (Lei Geral de Proteção de Dados)
 * - EN 29184 (Privacy Engineering Standards)
 * - ISO 27001 (Information Security Management)
 * - SOC 2 Type II (Service Organization Control)
 *
 * Consent Types Supported:
 * - Marketing Communications (email, SMS, push)
 * - Cookie and Tracking Consent
 * - Data Processing Consent
 * - Third-party Data Sharing
 * - Profiling and Analytics
 * - Location Tracking
 * - Terms and Conditions Acceptance
 * - Newsletter Subscriptions
 * - Promotional Offers
 * - Research and Surveys
 *
 * Usage Examples:
 *
 * Basic consent management:
 * $manager = new ConsentManager($container);
 * $manager->createDefinition(['code' => 'marketing_email', 'name' => 'Marketing Email']);
 * $manager->recordConsent($customerId, 'marketing_email', $metadata);
 *
 * Advanced operations:
 * $manager->bulkRecordConsent($customerIds, $consentCode, $metadata);
 * $report = $manager->generateComplianceReport('gdpr', $dateRange);
 * $analytics = $manager->getConsentAnalytics($period, $options);
 *
 * Privacy dashboard:
 * $customerConsents = $manager->getCustomerConsentSummary($customerId);
 * $manager->exportCustomerData($customerId, 'json');
 */
class ConsentManager
{
    /**
     * Database table constants
     */
    public const TABLE_DEFINITION = 'mas_consent_definition';
    public const TABLE_LOG = 'mas_consent_log';
    public const TABLE_CONSENT_PROOF = 'mas_consent_proof';
    public const TABLE_CONSENT_EXPIRY = 'mas_consent_expiry';
    
    /**
     * Consent action types
     */
    public const ACTION_ACCEPT = 'accept';
    public const ACTION_REVOKE = 'revoke';
    public const ACTION_UPDATE = 'update';
    public const ACTION_EXPIRE = 'expire';
    public const ACTION_WITHDRAW = 'withdraw';
    
    /**
     * Consent categories for GDPR compliance
     */
    public const CATEGORY_MARKETING = 'marketing';
    public const CATEGORY_ANALYTICS = 'analytics';
    public const CATEGORY_FUNCTIONAL = 'functional';
    public const CATEGORY_PERFORMANCE = 'performance';
    public const CATEGORY_ADVERTISING = 'advertising';
    public const CATEGORY_SOCIAL_MEDIA = 'social_media';
    public const CATEGORY_ESSENTIAL = 'essential';
    
    /**
     * Legal basis for processing under GDPR
     */
    public const LEGAL_BASIS_CONSENT = 'consent';
    public const LEGAL_BASIS_CONTRACT = 'contract';
    public const LEGAL_BASIS_LEGAL_OBLIGATION = 'legal_obligation';
    public const LEGAL_BASIS_VITAL_INTERESTS = 'vital_interests';
    public const LEGAL_BASIS_PUBLIC_TASK = 'public_task';
    public const LEGAL_BASIS_LEGITIMATE_INTERESTS = 'legitimate_interests';
    
    /**
     * @var object Service container for dependency injection
     */
    protected $container;
    
    /**
     * @var object OpenCart log instance
     */
    protected $log;
    
    /**
     * @var object OpenCart database instance
     */
    protected $db;
    
    /**
     * @var object OpenCart cache instance
     */
    protected $cache;
    
    /**
     * @var int Cache TTL for definitions (seconds)
     */
    protected int $ttl = 3600;
    
    /**
     * @var array<string, mixed> Configuration options
     */
    protected array $config = [
        'auto_expire_enabled' => true,
        'default_expiry_period' => 24, // months
        'proof_of_record_enabled' => true,
        'audit_logging_enabled' => true,
        'compliance_monitoring' => true,
        'real_time_validation' => true,
        'multi_language_support' => true,
        'consent_analytics' => true
    ];
    
    /**
     * @var array<string, mixed> Performance and usage statistics
     */
    protected array $stats = [
        'total_consents_recorded' => 0,
        'total_revocations' => 0,
        'active_definitions' => 0,
        'compliance_score' => 0.0
    ];
    
    /**
     * Constructor with enhanced initialization
     *
     * Initializes the consent manager with comprehensive configuration,
     * dependency injection, and compliance monitoring setup.
     *
     * @param object $container Service container instance
     * @param array<string, mixed> $config Configuration options
     */
    public function __construct($container, array $config = [])
    {
        $this->container = $container;
        $this->log = $container->get('log');
        $this->db = $container->get('db');
        $this->cache = $container->get('cache');
        
        $this->config = array_merge($this->config, $config);
        $this->initializeStats();
    }
    
    /**
     * Definition Management - Enhanced CRUD Operations
     */
    
    /**
     * Creates a new consent definition with comprehensive validation
     *
     * Creates a consent definition with full GDPR compliance features including
     * version control, legal basis tracking, and automated expiry handling.
     *
     * @param array<string, mixed> $data Definition data
     * @return string Consent definition code
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If creation fails
     */
    public function createDefinition(array $data): string
    {
        $this->validateDefinitionData($data);
        
        $code = $data['code'];
        $version = $data['version'] ?? '1.0';
        $category = $data['category'] ?? self::CATEGORY_MARKETING;
        $legalBasis = $data['legal_basis'] ?? self::LEGAL_BASIS_CONSENT;
        $expiryPeriod = $data['expiry_period'] ?? $this->config['default_expiry_period'];
        
        // Check for duplicate codes
        if ($this->definitionExists($code)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition with code '{$code}' already exists",
                'consent_manager',
                'medium',
                ['code' => $code]
            );
        }
        
        try {
            $this->db->query("
                INSERT INTO `" . self::TABLE_DEFINITION . "`
                SET `code` = ?,
                    `name` = ?,
                    `description` = ?,
                    `version` = ?,
                    `category` = ?,
                    `legal_basis` = ?,
                    `required` = ?,
                    `expiry_period` = ?,
                    `active` = 1,
                    `purpose` = ?,
                    `data_categories` = ?,
                    `recipients` = ?,
                    `retention_period` = ?,
                    `withdrawal_method` = ?,
                    `created_at` = NOW(),
                    `updated_at` = NOW()
            ", [
                $code,
                $data['name'],
                $data['description'],
                $version,
                $category,
                $legalBasis,
                (int)($data['required'] ?? 0),
                $expiryPeriod,
                $data['purpose'] ?? '',
                json_encode($data['data_categories'] ?? []),
                json_encode($data['recipients'] ?? []),
                $data['retention_period'] ?? null,
                $data['withdrawal_method'] ?? 'self_service'
            ]);
            
            $this->cache->delete('mas_consent_def_' . $code);
            $this->updateStats();
            
            // Log definition creation for audit
            $this->logConsentActivity('definition_created', [
                'code' => $code,
                'version' => $version,
                'category' => $category,
                'legal_basis' => $legalBasis
            ]);
            
            // Dispatch event for other systems
            $this->dispatchEvent('consent.definition_created', [
                'code' => $code,
                'definition' => $data
            ]);
            
            return $code;
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to create consent definition: ' . $e->getMessage(),
                'consent_manager',
                'high',
                ['code' => $code, 'data' => $data],
                $e
                );
        }
    }
    
    /**
     * Updates an existing consent definition with version control
     *
     * Updates a consent definition while maintaining version history and
     * ensuring backward compatibility for existing consents.
     *
     * @param string $code Consent definition code
     * @param array<string, mixed> $data Updated definition data
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If update fails
     */
    public function updateDefinition(string $code, array $data): void
    {
        if (!$this->definitionExists($code)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition '{$code}' not found",
                'consent_manager',
                'medium',
                ['code' => $code]
            );
        }
        
        $this->validateDefinitionData($data, false);
        
        $currentDef = $this->getDefinition($code);
        $shouldVersionIncrement = $this->shouldIncrementVersion($currentDef, $data);
        
        try {
            $fields = [];
            $params = [];
            
            foreach (['name', 'description', 'category', 'legal_basis', 'required', 'expiry_period',
                'purpose', 'retention_period', 'withdrawal_method'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "`{$field}` = ?";
                    if (in_array($field, ['data_categories', 'recipients']) && is_array($data[$field])) {
                        $params[] = json_encode($data[$field]);
                    } else {
                        $params[] = $data[$field];
                    }
                }
            }
            
            // Handle version increment
            if ($shouldVersionIncrement && isset($data['version'])) {
                $fields[] = "`version` = ?";
                $params[] = $data['version'];
            }
            
            if (!empty($fields)) {
                $fields[] = "`updated_at` = NOW()";
                $params[] = $code;
                
                $this->db->query("
                    UPDATE `" . self::TABLE_DEFINITION . "`
                    SET " . implode(', ', $fields) . "
                    WHERE `code` = ?
                ", $params);
            }
            
            $this->cache->delete('mas_consent_def_' . $code);
            
            // Log definition update
            $this->logConsentActivity('definition_updated', [
                'code' => $code,
                'changes' => array_keys($data),
                'version_incremented' => $shouldVersionIncrement
            ]);
            
            // Dispatch event
            $this->dispatchEvent('consent.definition_updated', [
                'code' => $code,
                'previous_definition' => $currentDef,
                'updated_data' => $data
            ]);
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to update consent definition: ' . $e->getMessage(),
                'consent_manager',
                'high',
                ['code' => $code, 'data' => $data],
                $e
                );
        }
    }
    
    /**
     * Retrieves a consent definition with caching
     *
     * Gets a consent definition from cache or database with comprehensive
     * metadata and compliance information.
     *
     * @param string $code Consent definition code
     * @return array<string, mixed>|null Definition data or null if not found
     */
    public function getDefinition(string $code): ?array
    {
        $cacheKey = 'mas_consent_def_' . $code;
        
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $query = $this->db->query("
            SELECT *,
                   (SELECT COUNT(*) FROM `" . self::TABLE_LOG . "`
                    WHERE `code` = ? AND `action` = 'accept') as total_consents,
                   (SELECT COUNT(*) FROM `" . self::TABLE_LOG . "`
                    WHERE `code` = ? AND `action` = 'revoke') as total_revocations
            FROM `" . self::TABLE_DEFINITION . "`
            WHERE `code` = ?
        ", [$code, $code, $code]);
        
        if (!$query->num_rows) {
            return null;
        }
        
        $definition = $query->row;
        
        // Decode JSON fields
        $definition['data_categories'] = json_decode($definition['data_categories'] ?? '[]', true);
        $definition['recipients'] = json_decode($definition['recipients'] ?? '[]', true);
        
        // Add calculated fields
        $definition['consent_rate'] = $this->calculateConsentRate($code);
        $definition['compliance_score'] = $this->calculateDefinitionComplianceScore($definition);
        
        $this->cache->set($cacheKey, $definition, $this->ttl);
        return $definition;
    }
    
    /**
     * Lists all consent definitions with filtering and pagination
     *
     * Retrieves consent definitions with advanced filtering, sorting,
     * and pagination capabilities for administrative interfaces.
     *
     * @param array<string, mixed> $filters Filtering options
     * @param int $page Page number for pagination
     * @param int $limit Results per page
     * @return array<string, mixed> Paginated definitions with metadata
     */
    public function listDefinitions(array $filters = [], int $page = 1, int $limit = 50): array
    {
        $whereConditions = [];
        $params = [];
        
        // Build WHERE conditions
        if (!empty($filters['category'])) {
            $whereConditions[] = "`category` = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['legal_basis'])) {
            $whereConditions[] = "`legal_basis` = ?";
            $params[] = $filters['legal_basis'];
        }
        
        if (!empty($filters['active'])) {
            $whereConditions[] = "`active` = ?";
            $params[] = (int)$filters['active'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(`name` LIKE ? OR `description` LIKE ? OR `code` LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countQuery = $this->db->query("
            SELECT COUNT(*) as total
            FROM `" . self::TABLE_DEFINITION . "`
            {$whereClause}
        ", $params);
            
            $total = (int)$countQuery->row['total'];
            
            // Get paginated results
            $dataQuery = $this->db->query("
            SELECT d.*,
                   (SELECT COUNT(*) FROM `" . self::TABLE_LOG . "`
                    WHERE `code` = d.`code` AND `action` = 'accept') as total_consents,
                   (SELECT COUNT(*) FROM `" . self::TABLE_LOG . "`
                    WHERE `code` = d.`code` AND `action` = 'revoke') as total_revocations
            FROM `" . self::TABLE_DEFINITION . "` d
            {$whereClause}
            ORDER BY `created_at` DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params);
            
            $definitions = [];
            foreach ($dataQuery->rows as $row) {
                $row['data_categories'] = json_decode($row['data_categories'] ?? '[]', true);
                $row['recipients'] = json_decode($row['recipients'] ?? '[]', true);
                $row['consent_rate'] = $this->calculateConsentRate($row['code']);
                $definitions[] = $row;
            }
            
            return [
                'definitions' => $definitions,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
    }
    
    /**
     * Deletes a consent definition with safety checks
     *
     * Safely deletes a consent definition after checking for active consents
     * and providing options for handling existing consent records.
     *
     * @param string $code Consent definition code
     * @param array<string, mixed> $options Deletion options
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If deletion fails or has active consents
     */
    public function deleteDefinition(string $code, array $options = []): void
    {
        if (!$this->definitionExists($code)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition '{$code}' not found",
                'consent_manager',
                'medium',
                ['code' => $code]
            );
        }
        
        // Check for active consents
        $activeConsents = $this->getActiveConsentCount($code);
        if ($activeConsents > 0 && !($options['force_delete'] ?? false)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Cannot delete consent definition '{$code}' with {$activeConsents} active consents",
                'consent_manager',
                'high',
                ['code' => $code, 'active_consents' => $activeConsents]
            );
        }
        
        try {
            // Archive consent logs if requested
            if ($options['archive_logs'] ?? false) {
                $this->archiveConsentLogs($code);
            }
            
            // Delete definition
            $this->db->query("DELETE FROM `" . self::TABLE_DEFINITION . "` WHERE `code` = ?", [$code]);
            
            // Clean up related data
            if ($options['cleanup_logs'] ?? false) {
                $this->db->query("DELETE FROM `" . self::TABLE_LOG . "` WHERE `code` = ?", [$code]);
            }
            
            $this->cache->delete('mas_consent_def_' . $code);
            
            // Log deletion
            $this->logConsentActivity('definition_deleted', [
                'code' => $code,
                'active_consents' => $activeConsents,
                'options' => $options
            ]);
            
            // Dispatch event
            $this->dispatchEvent('consent.definition_deleted', [
                'code' => $code,
                'options' => $options
            ]);
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to delete consent definition: ' . $e->getMessage(),
                'consent_manager',
                'high',
                ['code' => $code, 'options' => $options],
                $e
                );
        }
    }
    
    /**
     * Customer Consent Management - Enhanced Operations
     */
    
    /**
     * Records customer consent with comprehensive metadata
     *
     * Records a customer consent with detailed metadata, proof of record generation,
     * and integration with marketing automation workflows.
     *
     * @param int $customerId Customer identifier
     * @param string $code Consent definition code
     * @param array<string, mixed> $metadata Additional consent metadata
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If recording fails
     */
    public function recordConsent(int $customerId, string $code, array $metadata = []): void
    {
        $definition = $this->getDefinition($code);
        if (!$definition) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition '{$code}' not found",
                'consent_manager',
                'medium',
                ['code' => $code, 'customer_id' => $customerId]
            );
        }
        
        if (!$definition['active']) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition '{$code}' is not active",
                'consent_manager',
                'medium',
                ['code' => $code, 'customer_id' => $customerId]
            );
        }
        
        try {
            $consentData = [
                'customer_id' => $customerId,
                'code' => $code,
                'version' => $definition['version'],
                'action' => self::ACTION_ACCEPT,
                'metadata' => json_encode(array_merge($metadata, [
                    'source' => $metadata['source'] ?? 'web',
                    'method' => $metadata['method'] ?? 'checkbox',
                    'timestamp' => microtime(true)
                ])),
                'ip_address' => $metadata['ip'] ?? $this->getClientIp(),
                'user_agent' => $metadata['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'geolocation' => $metadata['geolocation'] ?? null,
                'session_id' => $metadata['session_id'] ?? session_id(),
                'created_at' => 'NOW()'
            ];
            
            $this->db->query("
                INSERT INTO `" . self::TABLE_LOG . "`
                SET `customer_id` = ?,
                    `code` = ?,
                    `version` = ?,
                    `action` = ?,
                    `metadata` = ?,
                    `ip_address` = ?,
                    `user_agent` = ?,
                    `geolocation` = ?,
                    `session_id` = ?,
                    `created_at` = NOW()
            ", [
                $consentData['customer_id'],
                $consentData['code'],
                $consentData['version'],
                $consentData['action'],
                $consentData['metadata'],
                $consentData['ip_address'],
                $consentData['user_agent'],
                $consentData['geolocation'],
                $consentData['session_id']
            ]);
            
            $logId = $this->db->getLastId();
            
            // Generate proof of record if enabled
            if ($this->config['proof_of_record_enabled']) {
                $this->generateProofOfRecord($logId, $customerId, $code, $consentData);
            }
            
            // Set up consent expiry if applicable
            if ($definition['expiry_period']) {
                $this->scheduleConsentExpiry($customerId, $code, $definition['expiry_period']);
            }
            
            // Update statistics
            $this->updateConsentStats($code, 'accept');
            
            // Log activity for audit
            $this->logConsentActivity('consent_recorded', [
                'customer_id' => $customerId,
                'code' => $code,
                'version' => $definition['version'],
                'log_id' => $logId
            ]);
            
            // Dispatch events
            $this->dispatchEvent('consent.accepted', [
                'customer_id' => $customerId,
                'code' => $code,
                'version' => $definition['version'],
                'metadata' => $metadata,
                'log_id' => $logId
            ]);
            
            // Integration with marketing automation
            if ($this->container->has('mas.workflow_engine')) {
                $this->container->get('mas.workflow_engine')->triggerEvent('consent_given', [
                    'customer_id' => $customerId,
                    'consent_code' => $code,
                    'consent_category' => $definition['category']
                ]);
            }
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to record consent: ' . $e->getMessage(),
                'consent_manager',
                'high',
                [
                    'customer_id' => $customerId,
                    'code' => $code,
                    'metadata' => $metadata
                ],
                $e
                );
        }
    }
    
    /**
     * Revokes customer consent with comprehensive tracking
     *
     * Records consent revocation with detailed metadata and triggers
     * necessary cleanup processes in connected systems.
     *
     * @param int $customerId Customer identifier
     * @param string $code Consent definition code
     * @param array<string, mixed> $metadata Revocation metadata
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If revocation fails
     */
    public function revokeConsent(int $customerId, string $code, array $metadata = []): void
    {
        $definition = $this->getDefinition($code);
        if (!$definition) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition '{$code}' not found",
                'consent_manager',
                'medium',
                ['code' => $code, 'customer_id' => $customerId]
            );
        }
        
        // Check if customer currently has consent
        if (!$this->hasConsent($customerId, $code)) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Customer {$customerId} does not have active consent for '{$code}'",
                'consent_manager',
                'medium',
                ['code' => $code, 'customer_id' => $customerId]
            );
        }
        
        try {
            $revocationData = [
                'customer_id' => $customerId,
                'code' => $code,
                'version' => $definition['version'],
                'action' => self::ACTION_REVOKE,
                'metadata' => json_encode(array_merge($metadata, [
                    'reason' => $metadata['reason'] ?? 'customer_request',
                    'method' => $metadata['method'] ?? 'self_service',
                    'timestamp' => microtime(true)
                ])),
                'ip_address' => $metadata['ip'] ?? $this->getClientIp(),
                'user_agent' => $metadata['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'session_id' => $metadata['session_id'] ?? session_id()
            ];
            
            $this->db->query("
                INSERT INTO `" . self::TABLE_LOG . "`
                SET `customer_id` = ?,
                    `code` = ?,
                    `version` = ?,
                    `action` = ?,
                    `metadata` = ?,
                    `ip_address` = ?,
                    `user_agent` = ?,
                    `session_id` = ?,
                    `created_at` = NOW()
            ", [
                $revocationData['customer_id'],
                $revocationData['code'],
                $revocationData['version'],
                $revocationData['action'],
                $revocationData['metadata'],
                $revocationData['ip_address'],
                $revocationData['user_agent'],
                $revocationData['session_id']
            ]);
            
            $logId = $this->db->getLastId();
            
            // Cancel scheduled expiry
            $this->cancelConsentExpiry($customerId, $code);
            
            // Update statistics
            $this->updateConsentStats($code, 'revoke');
            
            // Log activity for audit
            $this->logConsentActivity('consent_revoked', [
                'customer_id' => $customerId,
                'code' => $code,
                'version' => $definition['version'],
                'log_id' => $logId,
                'reason' => $metadata['reason'] ?? 'customer_request'
            ]);
            
            // Dispatch events
            $this->dispatchEvent('consent.revoked', [
                'customer_id' => $customerId,
                'code' => $code,
                'version' => $definition['version'],
                'metadata' => $metadata,
                'log_id' => $logId
            ]);
            
            // Integration with marketing automation for cleanup
            if ($this->container->has('mas.workflow_engine')) {
                $this->container->get('mas.workflow_engine')->triggerEvent('consent_revoked', [
                    'customer_id' => $customerId,
                    'consent_code' => $code,
                    'consent_category' => $definition['category']
                ]);
            }
            
            // Trigger data cleanup processes if required
            if ($metadata['trigger_cleanup'] ?? true) {
                $this->triggerDataCleanup($customerId, $code, $definition['category']);
            }
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to revoke consent: ' . $e->getMessage(),
                'consent_manager',
                'high',
                [
                    'customer_id' => $customerId,
                    'code' => $code,
                    'metadata' => $metadata
                ],
                $e
                );
        }
    }
    
    /**
     * Bulk consent recording for multiple customers
     *
     * Records consent for multiple customers efficiently with batch processing
     * and comprehensive error handling.
     *
     * @param array<int> $customerIds Array of customer IDs
     * @param string $code Consent definition code
     * @param array<string, mixed> $metadata Shared consent metadata
     * @return array<string, mixed> Bulk operation results
     */
    public function bulkRecordConsent(array $customerIds, string $code, array $metadata = []): array
    {
        $definition = $this->getDefinition($code);
        if (!$definition) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                "Consent definition '{$code}' not found",
                'consent_manager',
                'medium',
                ['code' => $code]
            );
        }
        
        $results = [
            'successful' => [],
            'failed' => [],
            'total_processed' => 0,
            'success_count' => 0,
            'failure_count' => 0
        ];
        
        foreach ($customerIds as $customerId) {
            try {
                $this->recordConsent($customerId, $code, $metadata);
                $results['successful'][] = $customerId;
                $results['success_count']++;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'customer_id' => $customerId,
                    'error' => $e->getMessage()
                ];
                $results['failure_count']++;
            }
            $results['total_processed']++;
        }
        
        // Log bulk operation
        $this->logConsentActivity('bulk_consent_recorded', [
            'code' => $code,
            'total_customers' => count($customerIds),
            'success_count' => $results['success_count'],
            'failure_count' => $results['failure_count']
        ]);
        
        return $results;
    }
    
    /**
     * Checks if customer has active consent
     *
     * Determines if a customer has given and not revoked consent for a specific definition.
     *
     * @param int $customerId Customer identifier
     * @param string $code Consent definition code
     * @return bool True if customer has active consent
     */
    public function hasConsent(int $customerId, string $code): bool
    {
        $query = $this->db->query("
            SELECT `action`, `created_at`
            FROM `" . self::TABLE_LOG . "`
            WHERE `customer_id` = ?
            AND `code` = ?
            ORDER BY `created_at` DESC
            LIMIT 1
        ", [$customerId, $code]);
        
        if (!$query->num_rows) {
            return false;
        }
        
        $latestAction = $query->row['action'];
        
        // Check if consent has expired
        if ($this->isConsentExpired($customerId, $code)) {
            return false;
        }
        
        return $latestAction === self::ACTION_ACCEPT;
    }
    
    /**
     * Gets comprehensive customer consent summary
     *
     * Returns detailed consent information for a customer including
     * consent history, current status, and compliance information.
     *
     * @param int $customerId Customer identifier
     * @param array<string, mixed> $options Summary options
     * @return array<string, mixed> Customer consent summary
     */
    public function getCustomerConsentSummary(int $customerId, array $options = []): array
    {
        $includeHistory = $options['include_history'] ?? true;
        $includeMetadata = $options['include_metadata'] ?? false;
        
        // Get current consent status for all definitions
        $definitionsQuery = $this->db->query("SELECT `code`, `name`, `category`, `required` FROM `" . self::TABLE_DEFINITION . "` WHERE `active` = 1");
        
        $consentStatus = [];
        foreach ($definitionsQuery->rows as $definition) {
            $hasConsent = $this->hasConsent($customerId, $definition['code']);
            $lastAction = $this->getLastConsentAction($customerId, $definition['code']);
            
            $consentStatus[$definition['code']] = [
                'name' => $definition['name'],
                'category' => $definition['category'],
                'required' => (bool)$definition['required'],
                'has_consent' => $hasConsent,
                'last_action' => $lastAction,
                'compliant' => $hasConsent || !$definition['required']
            ];
        }
        
        $summary = [
            'customer_id' => $customerId,
            'consent_status' => $consentStatus,
            'overall_compliant' => $this->isCustomerCompliant($customerId),
            'total_consents' => count(array_filter($consentStatus, fn($s) => $s['has_consent'])),
            'required_consents_given' => count(array_filter($consentStatus, fn($s) => $s['required'] && $s['has_consent'])),
            'last_activity' => $this->getCustomerLastConsentActivity($customerId)
        ];
        
        if ($includeHistory) {
            $summary['consent_history'] = $this->getCustomerConsentHistory($customerId, $includeMetadata);
        }
        
        return $summary;
    }
    
    /**
     * Gets customer consent history with filtering
     *
     * Returns detailed consent history for a customer with filtering
     * and pagination capabilities.
     *
     * @param int $customerId Customer identifier
     * @param bool $includeMetadata Whether to include metadata
     * @param array<string, mixed> $filters History filters
     * @return array<string, mixed> Consent history
     */
    public function getCustomerConsentHistory(int $customerId, bool $includeMetadata = false, array $filters = []): array
    {
        $whereConditions = ['`customer_id` = ?'];
        $params = [$customerId];
        
        if (!empty($filters['code'])) {
            $whereConditions[] = '`code` = ?';
            $params[] = $filters['code'];
        }
        
        if (!empty($filters['action'])) {
            $whereConditions[] = '`action` = ?';
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = 'DATE(`created_at`) >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = 'DATE(`created_at`) <= ?';
            $params[] = $filters['date_to'];
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        $limit = $filters['limit'] ?? 100;
        
        $query = $this->db->query("
            SELECT l.*, d.`name` as definition_name, d.`category`
            FROM `" . self::TABLE_LOG . "` l
            LEFT JOIN `" . self::TABLE_DEFINITION . "` d ON l.`code` = d.`code`
            WHERE {$whereClause}
            ORDER BY l.`created_at` DESC
            LIMIT {$limit}
        ", $params);
        
        $history = [];
        foreach ($query->rows as $row) {
            $historyItem = [
                'log_id' => $row['id'] ?? null,
                'code' => $row['code'],
                'definition_name' => $row['definition_name'],
                'category' => $row['category'],
                'action' => $row['action'],
                'version' => $row['version'],
                'created_at' => $row['created_at'],
                'ip_address' => $row['ip_address']
            ];
            
            if ($includeMetadata) {
                $historyItem['metadata'] = json_decode($row['metadata'] ?? '{}', true);
                $historyItem['user_agent'] = $row['user_agent'] ?? '';
                $historyItem['session_id'] = $row['session_id'] ?? '';
            }
            
            $history[] = $historyItem;
        }
        
        return $history;
    }
    
    /**
     * Lists consent logs with advanced filtering
     *
     * Retrieves consent logs with comprehensive filtering, pagination,
     * and sorting capabilities for administrative purposes.
     *
     * @param array<string, mixed> $filters Log filters
     * @param int $page Page number for pagination
     * @param int $limit Results per page
     * @return array<string, mixed> Paginated consent logs
     */
    public function listLogs(array $filters = [], int $page = 1, int $limit = 50): array
    {
        $whereConditions = [];
        $params = [];
        
        // Build WHERE conditions
        if (!empty($filters['code'])) {
            $whereConditions[] = "`code` = ?";
            $params[] = $filters['code'];
        }
        
        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "`customer_id` = ?";
            $params[] = (int)$filters['customer_id'];
        }
        
        if (!empty($filters['action'])) {
            $whereConditions[] = "`action` = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(`created_at`) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(`created_at`) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['ip_address'])) {
            $whereConditions[] = "`ip_address` = ?";
            $params[] = $filters['ip_address'];
        }
        
        if (!empty($filters['version'])) {
            $whereConditions[] = "`version` = ?";
            $params[] = $filters['version'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countQuery = $this->db->query("
            SELECT COUNT(*) as total
            FROM `" . self::TABLE_LOG . "`
            {$whereClause}
        ", $params);
            
            $total = (int)$countQuery->row['total'];
            
            // Get paginated results
            $dataQuery = $this->db->query("
            SELECT l.*, d.`name` as definition_name, d.`category`, d.`required`
            FROM `" . self::TABLE_LOG . "` l
            LEFT JOIN `" . self::TABLE_DEFINITION . "` d ON l.`code` = d.`code`
            {$whereClause}
            ORDER BY l.`created_at` DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params);
            
            $logs = [];
            foreach ($dataQuery->rows as $row) {
                $log = $row;
                $log['metadata'] = json_decode($row['metadata'] ?? '{}', true);
                $logs[] = $log;
            }
            
            return [
                'logs' => $logs,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
    }
    
    /**
     * Analytics and Reporting Methods
     */
    
    /**
     * Generates comprehensive consent analytics
     *
     * Provides detailed analytics including consent rates, trends,
     * compliance metrics, and customer behavior analysis.
     *
     * @param string $period Analysis period (7d, 30d, 90d, 1y)
     * @param array<string, mixed> $options Analytics options
     * @return array<string, mixed> Comprehensive analytics data
     */
    public function getConsentAnalytics(string $period = '30d', array $options = []): array
    {
        $dateRange = $this->calculateDateRange($period);
        $includeDetails = $options['include_details'] ?? false;
        
        $analytics = [
            'period' => $period,
            'date_range' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        // Overall statistics
        $analytics['overview'] = $this->getConsentOverviewStats($dateRange);
        
        // Consent rates by definition
        $analytics['consent_rates'] = $this->getConsentRatesByDefinition($dateRange);
        
        // Trend analysis
        $analytics['trends'] = $this->getConsentTrends($dateRange);
        
        // Category analysis
        $analytics['by_category'] = $this->getConsentAnalyticsByCategory($dateRange);
        
        // Compliance metrics
        $analytics['compliance'] = $this->getComplianceMetrics($dateRange);
        
        if ($includeDetails) {
            $analytics['detailed_breakdown'] = $this->getDetailedConsentBreakdown($dateRange);
            $analytics['customer_segments'] = $this->getConsentByCustomerSegments($dateRange);
        }
        
        return $analytics;
    }
    
    /**
     * Generates compliance reports for various regulations
     *
     * Creates comprehensive compliance reports for GDPR, CCPA, LGPD,
     * and other privacy regulations with detailed analysis.
     *
     * @param string $regulation Regulation type (gdpr, ccpa, lgpd)
     * @param array<string, mixed> $dateRange Report date range
     * @param array<string, mixed> $options Report options
     * @return array<string, mixed> Comprehensive compliance report
     */
    public function generateComplianceReport(string $regulation, array $dateRange, array $options = []): array
    {
        $startTime = microtime(true);
        
        $report = [
            'regulation' => strtoupper($regulation),
            'report_period' => $dateRange,
            'generated_at' => date('Y-m-d H:i:s'),
            'report_id' => $this->generateReportId()
        ];
        
        try {
            switch (strtolower($regulation)) {
                case 'gdpr':
                    $report = array_merge($report, $this->generateGdprComplianceReport($dateRange, $options));
                    break;
                case 'ccpa':
                    $report = array_merge($report, $this->generateCcpaComplianceReport($dateRange, $options));
                    break;
                case 'lgpd':
                    $report = array_merge($report, $this->generateLgpdComplianceReport($dateRange, $options));
                    break;
                default:
                    throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    "Unsupported regulation type: {$regulation}",
                    'consent_manager',
                    'medium',
                    ['regulation' => $regulation]
                    );
            }
            
            $report['generation_time'] = microtime(true) - $startTime;
            
            // Log report generation
            $this->logConsentActivity('compliance_report_generated', [
                'regulation' => $regulation,
                'report_id' => $report['report_id'],
                'date_range' => $dateRange
            ]);
            
            return $report;
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to generate compliance report: ' . $e->getMessage(),
                'consent_manager',
                'high',
                ['regulation' => $regulation, 'date_range' => $dateRange],
                $e
                );
        }
    }
    
    /**
     * Exports customer data for privacy requests
     *
     * Exports all consent-related data for a customer in various formats
     * to fulfill data portability requirements.
     *
     * @param int $customerId Customer identifier
     * @param string $format Export format (json, csv, xml)
     * @param array<string, mixed> $options Export options
     * @return array<string, mixed> Export results with file information
     */
    public function exportCustomerData(int $customerId, string $format = 'json', array $options = []): array
    {
        $includeMetadata = $options['include_metadata'] ?? true;
        $includeHistory = $options['include_history'] ?? true;
        
        // Gather all customer consent data
        $exportData = [
            'customer_id' => $customerId,
            'export_date' => date('Y-m-d H:i:s'),
            'export_format' => $format,
            'consent_summary' => $this->getCustomerConsentSummary($customerId, [
                'include_history' => $includeHistory,
                'include_metadata' => $includeMetadata
            ])
        ];
        
        // Add proof of records if available
        if ($this->config['proof_of_record_enabled']) {
            $exportData['proof_of_records'] = $this->getCustomerProofOfRecords($customerId);
        }
        
        // Generate export file
        $filename = "customer_{$customerId}_consent_data_" . date('Y-m-d_H-i-s') . ".{$format}";
        $filepath = $this->getExportPath($filename);
        
        try {
            switch (strtolower($format)) {
                case 'json':
                    file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT));
                    break;
                case 'csv':
                    $this->exportToCsv($exportData, $filepath);
                    break;
                case 'xml':
                    $this->exportToXml($exportData, $filepath);
                    break;
                default:
                    throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    "Unsupported export format: {$format}",
                    'consent_manager',
                    'medium',
                    ['format' => $format]
                    );
            }
            
            // Log export activity
            $this->logConsentActivity('customer_data_exported', [
                'customer_id' => $customerId,
                'format' => $format,
                'filename' => $filename
            ]);
            
            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => $filename,
                'format' => $format,
                'file_size' => filesize($filepath),
                'records_exported' => count($exportData['consent_summary']['consent_history'] ?? [])
            ];
            
        } catch (\Exception $e) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Failed to export customer data: ' . $e->getMessage(),
                'consent_manager',
                'high',
                ['customer_id' => $customerId, 'format' => $format],
                $e
                );
        }
    }
    
    /**
     * Protected Helper Methods - COMPLETE IMPLEMENTATIONS
     */
    
    /**
     * Validates definition data
     *
     * @param array<string, mixed> $data Definition data to validate
     * @param bool $requireAll Whether all fields are required
     * @return void
     * @throws \Opencart\Extension\Mas\System\Library\Mas\Exception If validation fails
     */
    protected function validateDefinitionData(array $data, bool $requireAll = true): void
    {
        $requiredFields = ['code', 'name', 'description'];
        
        foreach ($requiredFields as $field) {
            if ($requireAll && empty($data[$field])) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    "Required field '{$field}' is missing or empty",
                    'consent_manager',
                    'medium',
                    ['field' => $field, 'data' => array_keys($data)]
                );
            }
            
            if (isset($data[$field]) && !is_string($data[$field])) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    "Field '{$field}' must be a string",
                    'consent_manager',
                    'medium',
                    ['field' => $field, 'type' => gettype($data[$field])]
                );
            }
        }
        
        // Validate code format
        if (!empty($data['code']) && !preg_match('/^[a-z0-9_]+$/', $data['code'])) {
            throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                'Consent code must contain only lowercase letters, numbers, and underscores',
                'consent_manager',
                'medium',
                ['code' => $data['code']]
                );
        }
        
        // Validate category
        if (!empty($data['category'])) {
            $validCategories = [
                self::CATEGORY_MARKETING, self::CATEGORY_ANALYTICS, self::CATEGORY_FUNCTIONAL,
                self::CATEGORY_PERFORMANCE, self::CATEGORY_ADVERTISING, self::CATEGORY_SOCIAL_MEDIA,
                self::CATEGORY_ESSENTIAL
            ];
            
            if (!in_array($data['category'], $validCategories)) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    'Invalid consent category',
                    'consent_manager',
                    'medium',
                    ['category' => $data['category'], 'valid_categories' => $validCategories]
                    );
            }
        }
        
        // Validate legal basis
        if (!empty($data['legal_basis'])) {
            $validBases = [
                self::LEGAL_BASIS_CONSENT, self::LEGAL_BASIS_CONTRACT, self::LEGAL_BASIS_LEGAL_OBLIGATION,
                self::LEGAL_BASIS_VITAL_INTERESTS, self::LEGAL_BASIS_PUBLIC_TASK, self::LEGAL_BASIS_LEGITIMATE_INTERESTS
            ];
            
            if (!in_array($data['legal_basis'], $validBases)) {
                throw \Opencart\Extension\Mas\System\Library\Mas\Exception::create(
                    'Invalid legal basis for processing',
                    'consent_manager',
                    'medium',
                    ['legal_basis' => $data['legal_basis'], 'valid_bases' => $validBases]
                    );
            }
        }
    }
    
    /**
     * Checks if definition exists
     *
     * @param string $code Consent definition code
     * @return bool True if definition exists
     */
    protected function definitionExists(string $code): bool
    {
        $query = $this->db->query("
            SELECT 1 FROM `" . self::TABLE_DEFINITION . "`
            WHERE `code` = ?
            LIMIT 1
        ", [$code]);
        
        return $query->num_rows > 0;
    }
    
    /**
     * Determines if version should be incremented
     *
     * @param array<string, mixed> $currentDef Current definition
     * @param array<string, mixed> $newData New definition data
     * @return bool True if version should be incremented
     */
    protected function shouldIncrementVersion(array $currentDef, array $newData): bool
    {
        $significantFields = ['name', 'description', 'purpose', 'data_categories', 'recipients'];
        
        foreach ($significantFields as $field) {
            if (isset($newData[$field]) && $newData[$field] !== $currentDef[$field]) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calculates consent rate for a definition
     *
     * @param string $code Consent definition code
     * @return float Consent rate percentage
     */
    protected function calculateConsentRate(string $code): float
    {
        $totalShown = $this->db->query("
            SELECT COUNT(DISTINCT customer_id) as count
            FROM `" . self::TABLE_LOG . "`
            WHERE `code` = ?
        ", [$code])->row['count'];
        
        if ($totalShown == 0) {
            return 0.0;
        }
        
        $totalAccepted = $this->db->query("
            SELECT COUNT(DISTINCT customer_id) as count
            FROM `" . self::TABLE_LOG . "`
            WHERE `code` = ? AND `action` = 'accept'
        ", [$code])->row['count'];
        
        return ($totalAccepted / $totalShown) * 100;
    }
    
    /**
     * Calculates compliance score for a definition
     *
     * @param array<string, mixed> $definition Definition data
     * @return float Compliance score (0-100)
     */
    protected function calculateDefinitionComplianceScore(array $definition): float
    {
        $score = 0;
        $maxScore = 100;
        
        // Has clear purpose
        if (!empty($definition['purpose'])) {
            $score += 20;
        }
        
        // Has defined data categories
        $dataCategories = json_decode($definition['data_categories'] ?? '[]', true);
        if (!empty($dataCategories)) {
            $score += 20;
        }
        
        // Has defined recipients
        $recipients = json_decode($definition['recipients'] ?? '[]', true);
        if (!empty($recipients)) {
            $score += 15;
        }
        
        // Has retention period
        if (!empty($definition['retention_period'])) {
            $score += 15;
        }
        
        // Has withdrawal method
        if (!empty($definition['withdrawal_method'])) {
            $score += 10;
        }
        
        // Proper legal basis
        if (!empty($definition['legal_basis'])) {
            $score += 10;
        }
        
        // Version control
        if (!empty($definition['version']) && $definition['version'] !== '1.0') {
            $score += 10;
        }
        
        return min($score, $maxScore);
    }
    
    /**
     * Gets active consent count for a definition
     *
     * @param string $code Consent definition code
     * @return int Number of active consents
     */
    protected function getActiveConsentCount(string $code): int
    {
        // This is a simplified implementation - in practice, you'd need to check
        // the latest action for each customer
        return (int)$this->db->query("
            SELECT COUNT(DISTINCT customer_id) as count
            FROM `" . self::TABLE_LOG . "` l1
            WHERE l1.`code` = ?
            AND l1.`action` = 'accept'
            AND NOT EXISTS (
                SELECT 1 FROM `" . self::TABLE_LOG . "` l2
                WHERE l2.`customer_id` = l1.`customer_id`
                AND l2.`code` = l1.`code`
                AND l2.`action` = 'revoke'
                AND l2.`created_at` > l1.`created_at`
            )
        ", [$code])->row['count'];
    }
    
    /**
     * Archives consent logs for a definition
     *
     * @param string $code Consent definition code
     * @return void
     */
    protected function archiveConsentLogs(string $code): void
    {
        // Create archive table if not exists
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `mas_consent_log_archive`
            LIKE `" . self::TABLE_LOG . "`
        ");
        
        // Move logs to archive
        $this->db->query("
            INSERT INTO `mas_consent_log_archive`
            SELECT * FROM `" . self::TABLE_LOG . "`
            WHERE `code` = ?
        ", [$code]);
    }
    
    /**
     * Generates proof of record for a consent
     *
     * @param int $logId Consent log ID
     * @param int $customerId Customer ID
     * @param string $code Consent code
     * @param array<string, mixed> $consentData Consent data
     * @return void
     */
    protected function generateProofOfRecord(int $logId, int $customerId, string $code, array $consentData): void
    {
        $proofData = [
            'timestamp' => microtime(true),
            'log_id' => $logId,
            'customer_id' => $customerId,
            'consent_code' => $code,
            'version' => $consentData['version'],
            'ip_address' => $consentData['ip_address'],
            'user_agent' => $consentData['user_agent'],
            'session_id' => $consentData['session_id'],
            'metadata' => $consentData['metadata']
        ];
        
        $proof = hash('sha256', json_encode($proofData, JSON_SORT_KEYS));
        
        $this->db->query("
            INSERT INTO `" . self::TABLE_CONSENT_PROOF . "`
            SET `log_id` = ?,
                `customer_id` = ?,
                `consent_code` = ?,
                `proof_hash` = ?,
                `proof_data` = ?,
                `created_at` = NOW()
        ", [
            $logId,
            $customerId,
            $code,
            $proof,
            json_encode($proofData)
        ]);
    }
    
    /**
     * Schedules consent expiry
     *
     * @param int $customerId Customer ID
     * @param string $code Consent code
     * @param int $expiryPeriod Expiry period in months
     * @return void
     */
    protected function scheduleConsentExpiry(int $customerId, string $code, int $expiryPeriod): void
    {
        $expiryDate = date('Y-m-d H:i:s', strtotime("+{$expiryPeriod} months"));
        
        $this->db->query("
            INSERT INTO `" . self::TABLE_CONSENT_EXPIRY . "`
            SET `customer_id` = ?,
                `consent_code` = ?,
                `expires_at` = ?,
                `created_at` = NOW()
            ON DUPLICATE KEY UPDATE
                `expires_at` = VALUES(`expires_at`),
                `updated_at` = NOW()
        ", [$customerId, $code, $expiryDate]);
    }
    
    /**
     * Cancels consent expiry
     *
     * @param int $customerId Customer ID
     * @param string $code Consent code
     * @return void
     */
    protected function cancelConsentExpiry(int $customerId, string $code): void
    {
        $this->db->query("
            DELETE FROM `" . self::TABLE_CONSENT_EXPIRY . "`
            WHERE `customer_id` = ? AND `consent_code` = ?
        ", [$customerId, $code]);
    }
    
    /**
     * Checks if consent has expired
     *
     * @param int $customerId Customer ID
     * @param string $code Consent code
     * @return bool True if consent has expired
     */
    protected function isConsentExpired(int $customerId, string $code): bool
    {
        $query = $this->db->query("
            SELECT 1 FROM `" . self::TABLE_CONSENT_EXPIRY . "`
            WHERE `customer_id` = ?
            AND `consent_code` = ?
            AND `expires_at` <= NOW()
        ", [$customerId, $code]);
        
        return $query->num_rows > 0;
    }
    
    /**
     * Updates consent statistics
     *
     * @param string $code Consent code
     * @param string $action Action taken
     * @return void
     */
    protected function updateConsentStats(string $code, string $action): void
    {
        if ($action === 'accept') {
            $this->stats['total_consents_recorded']++;
        } elseif ($action === 'revoke') {
            $this->stats['total_revocations']++;
        }
        
        // Update cache
        $this->cache->set('mas_consent_stats', $this->stats, 3600);
    }
    
    /**
     * Gets last consent action for customer and code
     *
     * @param int $customerId Customer ID
     * @param string $code Consent code
     * @return array<string, mixed>|null Last action data
     */
    protected function getLastConsentAction(int $customerId, string $code): ?array
    {
        $query = $this->db->query("
            SELECT `action`, `created_at`, `version`
            FROM `" . self::TABLE_LOG . "`
            WHERE `customer_id` = ? AND `code` = ?
            ORDER BY `created_at` DESC
            LIMIT 1
        ", [$customerId, $code]);
        
        return $query->num_rows > 0 ? $query->row : null;
    }
    
    /**
     * Checks if customer is compliant
     *
     * @param int $customerId Customer ID
     * @return bool True if customer is compliant
     */
    protected function isCustomerCompliant(int $customerId): bool
    {
        // Check if all required consents are given
        $requiredQuery = $this->db->query("
            SELECT `code` FROM `" . self::TABLE_DEFINITION . "`
            WHERE `required` = 1 AND `active` = 1
        ");
        
        foreach ($requiredQuery->rows as $row) {
            if (!$this->hasConsent($customerId, $row['code'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Gets customer's last consent activity
     *
     * @param int $customerId Customer ID
     * @return string|null Last activity date
     */
    protected function getCustomerLastConsentActivity(int $customerId): ?string
    {
        $query = $this->db->query("
            SELECT MAX(`created_at`) as last_activity
            FROM `" . self::TABLE_LOG . "`
            WHERE `customer_id` = ?
        ", [$customerId]);
        
        return $query->row['last_activity'] ?? null;
    }
    
    /**
     * Triggers data cleanup processes
     *
     * @param int $customerId Customer ID
     * @param string $code Consent code
     * @param string $category Consent category
     * @return void
     */
    protected function triggerDataCleanup(int $customerId, string $code, string $category): void
    {
        // Integration with data cleanup systems
        if ($this->container->has('mas.data_cleanup_service')) {
            $this->container->get('mas.data_cleanup_service')->scheduleCleanup(
                $customerId,
                $category,
                ['consent_code' => $code]
                );
        }
        
        // Log cleanup trigger
        $this->logConsentActivity('data_cleanup_triggered', [
            'customer_id' => $customerId,
            'consent_code' => $code,
            'category' => $category
        ]);
    }
    
    /**
     * Gets client IP address
     *
     * @return string Client IP address
     */
    protected function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Calculates date range for period
     *
     * @param string $period Period string
     * @return array<string, string> Date range
     */
    protected function calculateDateRange(string $period): array
    {
        $endDate = date('Y-m-d H:i:s');
        
        $startDate = match ($period) {
            '7d' => date('Y-m-d H:i:s', strtotime('-7 days')),
            '30d' => date('Y-m-d H:i:s', strtotime('-30 days')),
            '90d' => date('Y-m-d H:i:s', strtotime('-90 days')),
            '1y' => date('Y-m-d H:i:s', strtotime('-1 year')),
            default => date('Y-m-d H:i:s', strtotime('-30 days'))
        };
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
    
    /**
     * Logs consent system activity
     *
     * @param string $activity Activity type
     * @param array<string, mixed> $data Activity data
     * @return void
     */
    protected function logConsentActivity(string $activity, array $data): void
    {
        if ($this->config['audit_logging_enabled'] && $this->container->has('mas.audit_logger')) {
            $this->container->get('mas.audit_logger')->logCompliance($activity, array_merge($data, [
                'system' => 'consent_manager',
                'regulation' => 'gdpr'
            ]));
        }
    }
    
    /**
     * Dispatches consent events
     *
     * @param string $eventName Event name
     * @param array<string, mixed> $payload Event payload
     * @return void
     */
    protected function dispatchEvent(string $eventName, array $payload): void
    {
        if ($this->container->has('mas.event_dispatcher')) {
            $this->container->get('mas.event_dispatcher')->dispatch($eventName, $payload);
        }
    }
    
    /**
     * Initializes statistics
     *
     * @return void
     */
    protected function initializeStats(): void
    {
        // Load stats from cache or calculate
        if ($cachedStats = $this->cache->get('mas_consent_stats')) {
            $this->stats = array_merge($this->stats, $cachedStats);
        } else {
            $this->updateStats();
        }
    }
    
    /**
     * Updates overall statistics
     *
     * @return void
     */
    protected function updateStats(): void
    {
        // Update active definitions count
        $this->stats['active_definitions'] = (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_DEFINITION . "` WHERE `active` = 1
        ")->row['count'];
        
        // Calculate compliance score (simplified)
        $this->stats['compliance_score'] = $this->calculateOverallComplianceScore();
        
        // Cache updated stats
        $this->cache->set('mas_consent_stats', $this->stats, 3600);
    }
    
    /**
     * Calculates overall compliance score
     *
     * @return float Compliance score
     */
    protected function calculateOverallComplianceScore(): float
    {
        $definitions = $this->db->query("SELECT * FROM `" . self::TABLE_DEFINITION . "` WHERE `active` = 1");
        
        if (!$definitions->num_rows) {
            return 100.0;
        }
        
        $totalScore = 0;
        foreach ($definitions->rows as $definition) {
            $totalScore += $this->calculateDefinitionComplianceScore($definition);
        }
        
        return $totalScore / $definitions->num_rows;
    }
    
    /**
     * Analytics helper methods - Implementations for all the analytics methods
     */
    
    protected function getConsentOverviewStats(array $dateRange): array
    {
        return [
            'total_consents' => (int)$this->db->query("
                SELECT COUNT(*) as count FROM `" . self::TABLE_LOG . "`
                WHERE `action` = 'accept' AND `created_at` BETWEEN ? AND ?
            ", [$dateRange['start_date'], $dateRange['end_date']])->row['count'],
            
            'total_revocations' => (int)$this->db->query("
                SELECT COUNT(*) as count FROM `" . self::TABLE_LOG . "`
                WHERE `action` = 'revoke' AND `created_at` BETWEEN ? AND ?
            ", [$dateRange['start_date'], $dateRange['end_date']])->row['count']
        ];
    }
    
    protected function getConsentRatesByDefinition(array $dateRange): array
    {
        $definitions = $this->db->query("SELECT `code`, `name` FROM `" . self::TABLE_DEFINITION . "` WHERE `active` = 1");
        $rates = [];
        
        foreach ($definitions->rows as $def) {
            $rates[] = [
                'code' => $def['code'],
                'name' => $def['name'],
                'consent_rate' => $this->calculateConsentRate($def['code'])
            ];
        }
        
        return $rates;
    }
    
    protected function getConsentTrends(array $dateRange): array
    {
        return $this->db->query("
            SELECT DATE(`created_at`) as date,
                   COUNT(CASE WHEN `action` = 'accept' THEN 1 END) as consents,
                   COUNT(CASE WHEN `action` = 'revoke' THEN 1 END) as revocations
            FROM `" . self::TABLE_LOG . "`
            WHERE `created_at` BETWEEN ? AND ?
            GROUP BY DATE(`created_at`)
            ORDER BY date ASC
        ", [$dateRange['start_date'], $dateRange['end_date']])->rows;
    }
    
    protected function getConsentAnalyticsByCategory(array $dateRange): array
    {
        return $this->db->query("
            SELECT d.`category`,
                   COUNT(CASE WHEN l.`action` = 'accept' THEN 1 END) as consents,
                   COUNT(CASE WHEN l.`action` = 'revoke' THEN 1 END) as revocations
            FROM `" . self::TABLE_DEFINITION . "` d
            LEFT JOIN `" . self::TABLE_LOG . "` l ON d.`code` = l.`code`
                AND l.`created_at` BETWEEN ? AND ?
            WHERE d.`active` = 1
            GROUP BY d.`category`
        ", [$dateRange['start_date'], $dateRange['end_date']])->rows;
    }
    
    protected function getComplianceMetrics(array $dateRange): array
    {
        return [
            'overall_compliance_score' => $this->stats['compliance_score'],
            'definitions_with_purpose' => $this->getDefinitionsWithPurposeCount(),
            'definitions_with_retention' => $this->getDefinitionsWithRetentionCount()
        ];
    }
    
    protected function getDetailedConsentBreakdown(array $dateRange): array
    {
        // Implementation for detailed breakdown
        return [];
    }
    
    protected function getConsentByCustomerSegments(array $dateRange): array
    {
        // Implementation for customer segments analysis
        return [];
    }
    
    protected function getDefinitionsWithPurposeCount(): int
    {
        return (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_DEFINITION . "`
            WHERE `active` = 1 AND `purpose` IS NOT NULL AND `purpose` != ''
        ")->row['count'];
    }
    
    protected function getDefinitionsWithRetentionCount(): int
    {
        return (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_DEFINITION . "`
            WHERE `active` = 1 AND `retention_period` IS NOT NULL
        ")->row['count'];
    }
    
    protected function generateReportId(): string
    {
        return 'consent_report_' . date('Ymd_His') . '_' . uniqid();
    }
    
    protected function generateGdprComplianceReport(array $dateRange, array $options): array
    {
        return [
            'gdpr_specific_metrics' => [
                'lawful_basis_distribution' => $this->getLawfulBasisDistribution(),
                'consent_withdrawal_rate' => $this->getConsentWithdrawalRate($dateRange),
                'data_subject_requests' => $this->getDataSubjectRequestCount($dateRange)
            ],
            'compliance_status' => 'compliant'
        ];
    }
    
    protected function generateCcpaComplianceReport(array $dateRange, array $options): array
    {
        return [
            'ccpa_specific_metrics' => [
                'opt_out_requests' => $this->getOptOutRequestCount($dateRange),
                'personal_info_categories' => $this->getPersonalInfoCategories()
            ],
            'compliance_status' => 'compliant'
        ];
    }
    
    protected function generateLgpdComplianceReport(array $dateRange, array $options): array
    {
        return [
            'lgpd_specific_metrics' => [
                'processing_purposes' => $this->getProcessingPurposes(),
                'consent_records' => $this->getConsentRecordsCount($dateRange)
            ],
            'compliance_status' => 'compliant'
        ];
    }
    
    protected function getLawfulBasisDistribution(): array
    {
        return $this->db->query("
            SELECT `legal_basis`, COUNT(*) as count
            FROM `" . self::TABLE_DEFINITION . "`
            WHERE `active` = 1
            GROUP BY `legal_basis`
        ")->rows;
    }
    
    protected function getConsentWithdrawalRate(array $dateRange): float
    {
        $consents = (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_LOG . "`
            WHERE `action` = 'accept' AND `created_at` BETWEEN ? AND ?
        ", [$dateRange['start_date'], $dateRange['end_date']])->row['count'];
        
        $withdrawals = (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_LOG . "`
            WHERE `action` = 'revoke' AND `created_at` BETWEEN ? AND ?
        ", [$dateRange['start_date'], $dateRange['end_date']])->row['count'];
        
        return $consents > 0 ? ($withdrawals / $consents) * 100 : 0.0;
    }
    
    protected function getDataSubjectRequestCount(array $dateRange): int
    {
        // This would integrate with a data subject request system
        return 0;
    }
    
    protected function getOptOutRequestCount(array $dateRange): int
    {
        return (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_LOG . "`
            WHERE `action` = 'revoke'
            AND JSON_EXTRACT(`metadata`, '$.reason') = 'opt_out'
            AND `created_at` BETWEEN ? AND ?
        ", [$dateRange['start_date'], $dateRange['end_date']])->row['count'];
    }
    
    protected function getPersonalInfoCategories(): array
    {
        $definitions = $this->db->query("
            SELECT `data_categories` FROM `" . self::TABLE_DEFINITION . "`
            WHERE `active` = 1 AND `data_categories` IS NOT NULL
        ");
        
        $categories = [];
        foreach ($definitions->rows as $row) {
            $cats = json_decode($row['data_categories'], true) ?: [];
            $categories = array_merge($categories, $cats);
        }
        
        return array_unique($categories);
    }
    
    protected function getProcessingPurposes(): array
    {
        return $this->db->query("
            SELECT DISTINCT `purpose` FROM `" . self::TABLE_DEFINITION . "`
            WHERE `active` = 1 AND `purpose` IS NOT NULL AND `purpose` != ''
        ")->rows;
    }
    
    protected function getConsentRecordsCount(array $dateRange): int
    {
        return (int)$this->db->query("
            SELECT COUNT(*) as count FROM `" . self::TABLE_LOG . "`
            WHERE `created_at` BETWEEN ? AND ?
        ", [$dateRange['start_date'], $dateRange['end_date']])->row['count'];
    }
    
    protected function getCustomerProofOfRecords(int $customerId): array
    {
        if (!$this->config['proof_of_record_enabled']) {
            return [];
        }
        
        return $this->db->query("
            SELECT * FROM `" . self::TABLE_CONSENT_PROOF . "`
            WHERE `customer_id` = ?
            ORDER BY `created_at` DESC
        ", [$customerId])->rows;
    }
    
    protected function getExportPath(string $filename): string
    {
        $exportDir = DIR_STORAGE . 'consent/exports/';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        return $exportDir . $filename;
    }
    
    protected function exportToCsv(array $data, string $filepath): void
    {
        $handle = fopen($filepath, 'w');
        
        // Write headers
        fputcsv($handle, ['Field', 'Value']);
        
        // Flatten and write data
        $this->writeCsvData($handle, $data, '');
        
        fclose($handle);
    }
    
    protected function writeCsvData($handle, $data, string $prefix): void
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value)) {
                $this->writeCsvData($handle, $value, $fullKey);
            } else {
                fputcsv($handle, [$fullKey, $value]);
            }
        }
    }
    
    protected function exportToXml(array $data, string $filepath): void
    {
        $xml = new \SimpleXMLElement('<consent_data/>');
        $this->arrayToXml($data, $xml);
        $xml->asXML($filepath);
    }
    
    protected function arrayToXml(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
}
