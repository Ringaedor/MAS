<?php
/**
 * MAS Module Model for OpenCart 4.x
 * Handles database operations, permissions management, provider configurations, and system statistics.
 * Follows OpenCart enterprise conventions with comprehensive functionality.
 */

namespace Opencart\Admin\Model\Extension\Mas\Module;

class Mas extends \Opencart\System\Engine\Model {
    
    /**
     * MAS permission routes for user group management
     * @var array<string>
     */
    private array $permissions = [
        'extension/mas/dashboard',
        'extension/mas/segments',
        'extension/mas/workflows',
        'extension/mas/messaging',
        'extension/mas/consent',
        'extension/mas/reports',
        'extension/mas/audit',
        'extension/mas/module/mas'
    ];
    
    /**
     * Install MAS module - create database tables and initialize system
     *
     * @return void
     */
    public function install(): void {
        // Create audit log table for security and compliance tracking
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_audit_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `action` varchar(255) NOT NULL,
                `context` text,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `user_id` (`user_id`),
                KEY `action` (`action`),
                KEY `date_added` (`date_added`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create customer segments table for advanced segmentation
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_segment` (
                `segment_id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text,
                `type` enum('static','dynamic','ai') NOT NULL DEFAULT 'dynamic',
                `filters` text,
                `logic` enum('AND','OR') NOT NULL DEFAULT 'AND',
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `customer_count` int(11) DEFAULT 0,
                `last_materialization` datetime DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`segment_id`),
                KEY `type` (`type`),
                KEY `status` (`status`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create segment materialization log for performance tracking
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_segment_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `segment_id` int(11) NOT NULL,
                `status` enum('success','failed','running','cancelled') NOT NULL,
                `customers_count` int(11) DEFAULT 0,
                `started_at` datetime NOT NULL,
                `completed_at` datetime DEFAULT NULL,
                `duration_seconds` int(11) DEFAULT NULL,
                `memory_used` bigint(20) DEFAULT NULL,
                `cpu_time` decimal(10,3) DEFAULT NULL,
                `error_message` text,
                `created_by` int(11) DEFAULT NULL,
                PRIMARY KEY (`log_id`),
                KEY `segment_id` (`segment_id`),
                KEY `status` (`status`),
                KEY `started_at` (`started_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create segment customers materialized table for fast queries
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_segment_customer` (
                `segment_id` int(11) NOT NULL,
                `customer_id` int(11) NOT NULL,
                `added_at` datetime NOT NULL,
                PRIMARY KEY (`segment_id`, `customer_id`),
                KEY `customer_id` (`customer_id`),
                KEY `added_at` (`added_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create AI suggestions table for intelligent recommendations
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_ai_suggestion` (
                `suggestion_id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text,
                `filters` text,
                `confidence` decimal(3,2) DEFAULT 0.00,
                `goal` varchar(100) DEFAULT NULL,
                `business_type` varchar(100) DEFAULT NULL,
                `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
                `created_by` int(11) DEFAULT NULL,
                `accepted_at` datetime DEFAULT NULL,
                `rejected_at` datetime DEFAULT NULL,
                `created_segment_id` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`suggestion_id`),
                KEY `status` (`status`),
                KEY `created_by` (`created_by`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create workflow automation table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_workflow` (
                `workflow_id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text,
                `definition` longtext,
                `status` enum('active','inactive','draft','error') NOT NULL DEFAULT 'draft',
                `version` int(11) NOT NULL DEFAULT 1,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`workflow_id`),
                KEY `status` (`status`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create workflow execution tracking table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_workflow_execution` (
                `execution_id` int(11) NOT NULL AUTO_INCREMENT,
                `workflow_id` int(11) NOT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `status` enum('running','completed','failed','cancelled') NOT NULL,
                `current_step` varchar(100) DEFAULT NULL,
                `context` text,
                `started_at` datetime NOT NULL,
                `completed_at` datetime DEFAULT NULL,
                `error_message` text,
                PRIMARY KEY (`execution_id`),
                KEY `workflow_id` (`workflow_id`),
                KEY `customer_id` (`customer_id`),
                KEY `status` (`status`),
                KEY `started_at` (`started_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create message queue table for multi-channel messaging
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_message_queue` (
                `queue_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` enum('email','sms','push') NOT NULL,
                `provider` varchar(100) NOT NULL,
                `recipient` varchar(255) NOT NULL,
                `subject` varchar(500) DEFAULT NULL,
                `content` text NOT NULL,
                `metadata` text,
                `status` enum('pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
                `attempts` int(11) NOT NULL DEFAULT 0,
                `max_attempts` int(11) NOT NULL DEFAULT 3,
                `scheduled_at` datetime DEFAULT NULL,
                `sent_at` datetime DEFAULT NULL,
                `error_message` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`queue_id`),
                KEY `type` (`type`),
                KEY `provider` (`provider`),
                KEY `status` (`status`),
                KEY `scheduled_at` (`scheduled_at`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create GDPR consent definitions table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_consent_definition` (
                `definition_id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(100) NOT NULL,
                `name` varchar(255) NOT NULL,
                `description` text,
                `version` varchar(20) NOT NULL DEFAULT '1.0',
                `required` tinyint(1) NOT NULL DEFAULT 0,
                `language_id` int(11) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`definition_id`),
                UNIQUE KEY `code_language` (`code`, `language_id`),
                KEY `required` (`required`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create consent tracking log for compliance
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_consent_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `customer_id` int(11) DEFAULT NULL,
                `definition_id` int(11) NOT NULL,
                `action` enum('accept','revoke') NOT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `metadata` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `customer_id` (`customer_id`),
                KEY `definition_id` (`definition_id`),
                KEY `action` (`action`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create scheduled jobs table for automation
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mas_scheduled_job` (
                `job_id` int(11) NOT NULL AUTO_INCREMENT,
                `segment_id` int(11) NOT NULL,
                `schedule_type` enum('hourly','daily','weekly','monthly','cron') NOT NULL,
                `schedule_value` varchar(100) NOT NULL,
                `status` enum('active','inactive','paused','error') NOT NULL DEFAULT 'active',
                `next_run` datetime NOT NULL,
                `last_run` datetime DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`job_id`),
                KEY `segment_id` (`segment_id`),
                KEY `status` (`status`),
                KEY `next_run` (`next_run`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Uninstall MAS module - remove database tables (optional)
     * WARNING: This will remove all MAS data permanently
     *
     * @return void
     */
    public function uninstall(): void {
        $tables = [
            'mas_scheduled_job',
            'mas_consent_log',
            'mas_consent_definition',
            'mas_message_queue',
            'mas_workflow_execution',
            'mas_workflow',
            'mas_ai_suggestion',
            'mas_segment_customer',
            'mas_segment_log',
            'mas_segment',
            'mas_audit_log'
        ];
        
        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
        }
    }
    
    /**
     * Get total number of active segments
     *
     * @return int Number of segments
     */
    public function getTotalSegments(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_segment` WHERE `status` = 1");
        return (int)$query->row['total'];
    }
    
    /**
     * Get total number of active workflows
     *
     * @return int Number of workflows
     */
    public function getTotalWorkflows(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_workflow` WHERE `status` = 'active'");
        return (int)$query->row['total'];
    }
    
    /**
     * Get total number of messages in queue
     *
     * @return int Number of queued messages
     */
    public function getTotalQueuedMessages(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_message_queue` WHERE `status` IN ('pending','processing')");
        return (int)$query->row['total'];
    }
    
    /**
     * Get total number of messages sent today
     *
     * @return int Number of messages sent today
     */
    public function getTotalMessagesSentToday(): int {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_message_queue` WHERE `status` = 'sent' AND DATE(`sent_at`) = CURDATE()");
        return (int)$query->row['total'];
    }
    
    /**
     * Get provider configuration by type and name
     *
     * @param string $type Provider type
     * @param string $name Provider name
     * @return array|null Provider configuration or null if not found
     */
    public function getProviderConfig(string $type, string $name): ?array {
        $query = $this->db->query("
            SELECT `value` FROM `" . DB_PREFIX . "setting`
            WHERE `key` = 'module_mas_providers_" . $this->db->escape($type) . "_" . $this->db->escape($name) . "'
            AND `store_id` = 0
        ");
        
        if ($query->num_rows > 0) {
            return json_decode($query->row['value'], true);
        }
        
        return null;
    }
    
    /**
     * Get all provider configurations grouped by type
     *
     * @return array<string, array> Provider configurations
     */
    public function getAllProviderConfigs(): array {
        $query = $this->db->query("
            SELECT `key`, `value` FROM `" . DB_PREFIX . "setting`
            WHERE `key` LIKE 'module_mas_providers_%'
            AND `store_id` = 0
        ");
        
        $configs = [];
        foreach ($query->rows as $row) {
            $key_parts = explode('_', $row['key']);
            if (count($key_parts) >= 5) {
                $type = $key_parts[3];
                $name = $key_parts[4];
                
                if (!isset($configs[$type])) {
                    $configs[$type] = [];
                }
                
                $configs[$type][$name] = json_decode($row['value'], true);
            }
        }
        
        return $configs;
    }
    
    /**
     * Check if MAS is properly configured with at least one active provider
     *
     * @return bool True if configured
     */
    public function isConfigured(): bool {
        $query = $this->db->query("
            SELECT COUNT(*) as total FROM `" . DB_PREFIX . "setting`
            WHERE `key` LIKE 'module_mas_providers_%'
            AND `store_id` = 0
        ");
        
        return $query->row['total'] > 0;
    }
    
    /**
     * Get comprehensive system statistics for dashboard
     *
     * @return array<string, mixed> System statistics
     */
    public function getSystemStats(): array {
        $stats = [
            'segments_total' => 0,
            'segments_active' => 0,
            'workflows_total' => 0,
            'workflows_active' => 0,
            'messages_queued' => 0,
            'messages_sent_today' => 0,
            'providers_configured' => 0,
            'providers_active' => 0,
            'consent_definitions' => 0,
            'audit_logs_count' => 0
        ];
        
        // Get segments statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_segment`");
        $stats['segments_total'] = (int)$query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_segment` WHERE `status` = 1");
        $stats['segments_active'] = (int)$query->row['total'];
        
        // Get workflows statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_workflow`");
        $stats['workflows_total'] = (int)$query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_workflow` WHERE `status` = 'active'");
        $stats['workflows_active'] = (int)$query->row['total'];
        
        // Get messages statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_message_queue` WHERE `status` IN ('pending','processing')");
        $stats['messages_queued'] = (int)$query->row['total'];
        
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_message_queue` WHERE `status` = 'sent' AND DATE(`sent_at`) = CURDATE()");
        $stats['messages_sent_today'] = (int)$query->row['total'];
        
        // Get providers statistics
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE 'module_mas_providers_%' AND `store_id` = 0");
        $stats['providers_configured'] = (int)$query->row['total'];
        
        // Count active providers
        $providers = $this->getAllProviderConfigs();
        $active_count = 0;
        foreach ($providers as $type => $type_providers) {
            foreach ($type_providers as $provider) {
                if (isset($provider['enabled']) && $provider['enabled']) {
                    $active_count++;
                }
            }
        }
        $stats['providers_active'] = $active_count;
        
        // Get consent definitions count
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_consent_definition`");
        $stats['consent_definitions'] = (int)$query->row['total'];
        
        // Get audit logs count (last 30 days)
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "mas_audit_log` WHERE DATE(`date_added`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stats['audit_logs_count'] = (int)$query->row['total'];
        
        return $stats;
    }
    
    /**
     * Get recent activity from audit logs
     *
     * @param int $limit Number of records to return
     * @return array<array> Recent activities
     */
    public function getRecentActivity(int $limit = 10): array {
        $query = $this->db->query("
            SELECT al.*, CONCAT(u.firstname, ' ', u.lastname) as user_name
            FROM `" . DB_PREFIX . "mas_audit_log` al
            LEFT JOIN `" . DB_PREFIX . "user` u ON al.user_id = u.user_id
            ORDER BY al.date_added DESC
            LIMIT " . (int)$limit . "
        ");
        
        return $query->rows;
    }
    
    /**
     * Add MAS permissions to a user group (only if not already existing)
     *
     * @param int $user_group_id User group ID
     * @return void
     */
    public function setPermissions(int $user_group_id): void {
        $user_group_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user_group` WHERE `user_group_id` = '" . (int)$user_group_id . "'");
        
        if ($user_group_query->num_rows) {
            $data = $user_group_query->row['permission'] ? json_decode($user_group_query->row['permission'], true) : [];
            
            foreach (['access', 'modify'] as $type) {
                if (!isset($data[$type]) || !is_array($data[$type])) {
                    $data[$type] = [];
                }
                foreach ($this->permissions as $route) {
                    if (!in_array($route, $data[$type], true)) {
                        $data[$type][] = $route;
                    }
                }
                // Remove duplicates and reindex
                $data[$type] = array_values(array_unique($data[$type]));
            }
            
            $this->db->query("UPDATE `" . DB_PREFIX . "user_group` SET `permission` = '" . $this->db->escape(json_encode($data)) . "' WHERE `user_group_id` = '" . (int)$user_group_id . "'");
        }
    }
    
    /**
     * Remove all MAS permissions from a user group (clean and robust)
     *
     * @param int $user_group_id User group ID
     * @return void
     */
    public function removePermissions(int $user_group_id): void {
        $user_group_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user_group` WHERE `user_group_id` = '" . (int)$user_group_id . "'");
        
        if ($user_group_query->num_rows) {
            $data = $user_group_query->row['permission'] ? json_decode($user_group_query->row['permission'], true) : [];
            
            foreach (['access', 'modify'] as $type) {
                if (!isset($data[$type]) || !is_array($data[$type])) {
                    $data[$type] = [];
                }
                // Remove all MAS permission routes
                $data[$type] = array_values(array_diff($data[$type], $this->permissions));
            }
            
            $this->db->query("UPDATE `" . DB_PREFIX . "user_group` SET `permission` = '" . $this->db->escape(json_encode($data)) . "' WHERE `user_group_id` = '" . (int)$user_group_id . "'");
        }
    }
    
    /**
     * Log audit event for security and compliance
     *
     * @param string $action Action performed
     * @param array $context Additional context data
     * @param int|null $user_id User ID (defaults to current user)
     * @return void
     */
    public function logAuditEvent(string $action, array $context = [], ?int $user_id = null): void {
        if ($user_id === null && isset($this->user)) {
            $user_id = $this->user->getId();
        }
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "mas_audit_log` SET
            `user_id` = '" . (int)$user_id . "',
            `action` = '" . $this->db->escape($action) . "',
            `context` = '" . $this->db->escape(json_encode($context)) . "',
            `ip_address` = '" . $this->db->escape($ip_address) . "',
            `user_agent` = '" . $this->db->escape($user_agent) . "',
            `date_added` = NOW()
        ");
    }
    
    /**
     * Clean up old audit logs to maintain performance
     *
     * @param int $days_to_keep Number of days to keep (default: 90)
     * @return int Number of records deleted
     */
    public function cleanupAuditLogs(int $days_to_keep = 90): int {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "mas_audit_log`
            WHERE `date_added` < DATE_SUB(NOW(), INTERVAL " . (int)$days_to_keep . " DAY)
        ");
        
        return $this->db->countAffected();
    }
    
    /**
     * Get provider configuration status summary
     *
     * @return array<string, array> Provider status by type
     */
    public function getProviderStatus(): array {
        $providers = $this->getAllProviderConfigs();
        $status = [];
        
        foreach ($providers as $type => $type_providers) {
            $status[$type] = [
                'total' => count($type_providers),
                'active' => 0,
                'inactive' => 0
            ];
            
            foreach ($type_providers as $provider) {
                if (isset($provider['enabled']) && $provider['enabled']) {
                    $status[$type]['active']++;
                } else {
                    $status[$type]['inactive']++;
                }
            }
        }
        
        return $status;
    }
}
