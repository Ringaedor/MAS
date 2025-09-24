<?php
namespace Opencart\Admin\Model\Extension\Mas;

/**
 * MAS Segments Model - Enterprise Level
 *
 * Advanced data access layer for customer segmentation with support for:
 * - Complex filtering and materialization
 * - AI-powered suggestions and analytics
 * - Real-time preview and optimization
 * - Comprehensive audit logging and dependency tracking
 *
 * @package    MAS Marketing Automation Suite
 * @subpackage Admin Models
 * @version    1.0.0
 * @author     MAS Development Team
 * @copyright  2025 Marketing Automation Suite
 */
class Segments extends \Opencart\System\Engine\Model {
    
    /**
     * Get segments with advanced filtering and pagination
     *
     * @param array $data Filtering and pagination parameters
     * @return array List of segments
     */
    public function getSegments(array $data = []): array {
        $sql = "SELECT s.*,
                       COUNT(DISTINCT sc.customer_id) as customer_count,
                       MAX(ml.completed_at) as last_materialization
                FROM `" . DB_PREFIX . "mas_segment` s
                LEFT JOIN `" . DB_PREFIX . "mas_segment_customer` sc ON (s.segment_id = sc.segment_id)
                LEFT JOIN `" . DB_PREFIX . "mas_materialization_log` ml ON (s.segment_id = ml.segment_id AND ml.status = 'success')
                WHERE 1 = 1";
        
        $params = [];
        
        // Apply filters
        if (!empty($data['name'])) {
            $sql .= " AND s.name LIKE ?";
            $params[] = '%' . $this->db->escape($data['name']) . '%';
        }
        
        if (!empty($data['type'])) {
            $sql .= " AND s.type = ?";
            $params[] = $data['type'];
        }
        
        if (isset($data['status']) && $data['status'] !== '') {
            $sql .= " AND s.status = ?";
            $params[] = (int)$data['status'];
        }
        
        if (!empty($data['date_from'])) {
            $sql .= " AND DATE(s.created_at) >= ?";
            $params[] = $data['date_from'];
        }
        
        if (!empty($data['date_to'])) {
            $sql .= " AND DATE(s.created_at) <= ?";
            $params[] = $data['date_to'];
        }
        
        // Group by for aggregate functions
        $sql .= " GROUP BY s.segment_id";
        
        // Sorting
        $sort_columns = ['name', 'type', 'status', 'created_at', 'updated_at', 'customer_count'];
        $sort = in_array($data['sort'] ?? 'name', $sort_columns) ? $data['sort'] : 'name';
        $order = strtoupper($data['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        
        $sql .= " ORDER BY " . $sort . " " . $order;
        
        // Pagination
        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int)($data['start'] ?? 0));
            $limit = max(1, min(100, (int)($data['limit'] ?? 20)));
            
            $sql .= " LIMIT " . $start . ", " . $limit;
        }
        
        $query = $this->db->query($sql, $params);
        
        return $query->rows;
    }
    
    /**
     * Get total number of segments matching filters
     *
     * @param array $data Filter parameters
     * @return int Total count
     */
    public function getTotalSegments(array $data = []): int {
        $sql = "SELECT COUNT(DISTINCT s.segment_id) as total
                FROM `" . DB_PREFIX . "mas_segment` s
                WHERE 1 = 1";
        
        $params = [];
        
        // Apply same filters as getSegments
        if (!empty($data['name'])) {
            $sql .= " AND s.name LIKE ?";
            $params[] = '%' . $this->db->escape($data['name']) . '%';
        }
        
        if (!empty($data['type'])) {
            $sql .= " AND s.type = ?";
            $params[] = $data['type'];
        }
        
        if (isset($data['status']) && $data['status'] !== '') {
            $sql .= " AND s.status = ?";
            $params[] = (int)$data['status'];
        }
        
        if (!empty($data['date_from'])) {
            $sql .= " AND DATE(s.created_at) >= ?";
            $params[] = $data['date_from'];
        }
        
        if (!empty($data['date_to'])) {
            $sql .= " AND DATE(s.created_at) <= ?";
            $params[] = $data['date_to'];
        }
        
        $query = $this->db->query($sql, $params);
        
        return (int)$query->row['total'];
    }
    
    /**
     * Get a single segment by ID
     *
     * @param int $segment_id Segment ID
     * @return array|null Segment data or null if not found
     */
    public function getSegment(int $segment_id): ?array {
        $sql = "SELECT s.*,
                       COUNT(DISTINCT sc.customer_id) as customer_count,
                       u.firstname, u.lastname
                FROM `" . DB_PREFIX . "mas_segment` s
                LEFT JOIN `" . DB_PREFIX . "mas_segment_customer` sc ON (s.segment_id = sc.segment_id)
                LEFT JOIN `" . DB_PREFIX . "user` u ON (s.created_by = u.user_id)
                WHERE s.segment_id = ?
                GROUP BY s.segment_id";
        
        $query = $this->db->query($sql, [$segment_id]);
        
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Add a new segment
     *
     * @param array $data Segment data
     * @return int New segment ID
     */
    public function addSegment(array $data): int {
        // Validate required fields
        $validation_result = $this->validateSegmentData($data);
        if (!$validation_result['valid']) {
            return 0;
        }
        
        $sql = "INSERT INTO `" . DB_PREFIX . "mas_segment`
                (name, description, type, filters, logic, status, refresh_interval, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['name'],
            $data['description'] ?? '',
            $data['type'] ?? 'dynamic',
            is_array($data['filters']) ? json_encode($data['filters']) : $data['filters'],
            $data['logic'] ?? 'AND',
            (int)($data['status'] ?? 1),
            (int)($data['refresh_interval'] ?? 0),
            (int)($data['created_by'] ?? 0)
        ];
        
        $this->db->query($sql, $params);
        
        $segment_id = $this->db->getLastId();
        
        if ($segment_id) {
            // Create initial analytics record
            $this->createSegmentAnalytics($segment_id);
            
            // If static segment with customer IDs, add them
            if (isset($data['customer_ids']) && is_array($data['customer_ids'])) {
                $this->addSegmentCustomers($segment_id, $data['customer_ids']);
            }
        }
        
        return $segment_id;
    }
    
    /**
     * Update an existing segment
     *
     * @param int $segment_id Segment ID
     * @param array $data Updated segment data
     * @return bool Success status
     */
    public function editSegment(int $segment_id, array $data): bool {
        // Validate data
        $validation_result = $this->validateSegmentData($data, $segment_id);
        if (!$validation_result['valid']) {
            return false;
        }
        
        $sql = "UPDATE `" . DB_PREFIX . "mas_segment`
                SET name = ?,
                    description = ?,
                    type = ?,
                    filters = ?,
                    logic = ?,
                    status = ?,
                    refresh_interval = ?,
                    updated_at = NOW(),
                    version = version + 1
                WHERE segment_id = ?";
        
        $params = [
            $data['name'],
            $data['description'] ?? '',
            $data['type'] ?? 'dynamic',
            is_array($data['filters']) ? json_encode($data['filters']) : $data['filters'],
            $data['logic'] ?? 'AND',
            (int)($data['status'] ?? 1),
            (int)($data['refresh_interval'] ?? 0),
            $segment_id
        ];
        
        $this->db->query($sql, $params);
        
        $affected = $this->db->countAffected();
        
        // If filters changed and it's a dynamic segment, clear materialized data
        if ($affected > 0 && isset($data['filters'])) {
            $this->clearSegmentMaterialization($segment_id);
        }
        
        return $affected > 0;
    }
    
    /**
     * Delete a segment and all related data
     *
     * @param int $segment_id Segment ID
     * @return bool Success status
     */
    public function deleteSegment(int $segment_id): bool {
        // Start transaction
        $this->db->query("START TRANSACTION");
        
        $success = true;
        
        // Delete related data
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_customer` WHERE segment_id = ?", [$segment_id]);
        if ($this->db->error) {
            $success = false;
        }
        
        if ($success) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_analytics` WHERE segment_id = ?", [$segment_id]);
            if ($this->db->error) {
                $success = false;
            }
        }
        
        if ($success) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_materialization_log` WHERE segment_id = ?", [$segment_id]);
            if ($this->db->error) {
                $success = false;
            }
        }
        
        if ($success) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_export` WHERE segment_id = ?", [$segment_id]);
            if ($this->db->error) {
                $success = false;
            }
        }
        
        // Delete the segment
        if ($success) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment` WHERE segment_id = ?", [$segment_id]);
            if (!$this->db->countAffected()) {
                $success = false;
            }
        }
        
        if ($success) {
            $this->db->query("COMMIT");
        } else {
            $this->db->query("ROLLBACK");
        }
        
        return $success;
    }
    
    /**
     * Update segment status (enable/disable)
     *
     * @param int $segment_id Segment ID
     * @param int $status New status (0 or 1)
     * @return bool Success status
     */
    public function updateSegmentStatus(int $segment_id, int $status): bool {
        $sql = "UPDATE `" . DB_PREFIX . "mas_segment`
                SET status = ?, updated_at = NOW()
                WHERE segment_id = ?";
        
        $this->db->query($sql, [$status ? 1 : 0, $segment_id]);
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Get materialization logs with filtering
     *
     * @param array $data Filter and pagination parameters
     * @return array List of logs
     */
    public function getMaterializationLogs(array $data = []): array {
        $sql = "SELECT ml.*, s.name as segment_name
                FROM `" . DB_PREFIX . "mas_materialization_log` ml
                LEFT JOIN `" . DB_PREFIX . "mas_segment` s ON (ml.segment_id = s.segment_id)
                WHERE 1 = 1";
        
        $params = [];
        
        if (!empty($data['segment_id'])) {
            $sql .= " AND ml.segment_id = ?";
            $params[] = (int)$data['segment_id'];
        }
        
        if (!empty($data['status'])) {
            $sql .= " AND ml.status = ?";
            $params[] = $data['status'];
        }
        
        if (!empty($data['date_from'])) {
            $sql .= " AND DATE(ml.started_at) >= ?";
            $params[] = $data['date_from'];
        }
        
        if (!empty($data['date_to'])) {
            $sql .= " AND DATE(ml.started_at) <= ?";
            $params[] = $data['date_to'];
        }
        
        $sql .= " ORDER BY ml.started_at DESC";
        
        // Pagination
        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int)($data['start'] ?? 0));
            $limit = max(1, min(100, (int)($data['limit'] ?? 20)));
            
            $sql .= " LIMIT " . $start . ", " . $limit;
        }
        
        $query = $this->db->query($sql, $params);
        
        return $query->rows;
    }
    
    /**
     * Get total materialization logs count
     *
     * @param array $data Filter parameters
     * @return int Total count
     */
    public function getTotalMaterializationLogs(array $data = []): int {
        $sql = "SELECT COUNT(*) as total
                FROM `" . DB_PREFIX . "mas_materialization_log` ml
                WHERE 1 = 1";
        
        $params = [];
        
        if (!empty($data['segment_id'])) {
            $sql .= " AND ml.segment_id = ?";
            $params[] = (int)$data['segment_id'];
        }
        
        if (!empty($data['status'])) {
            $sql .= " AND ml.status = ?";
            $params[] = $data['status'];
        }
        
        if (!empty($data['date_from'])) {
            $sql .= " AND DATE(ml.started_at) >= ?";
            $params[] = $data['date_from'];
        }
        
        if (!empty($data['date_to'])) {
            $sql .= " AND DATE(ml.started_at) <= ?";
            $params[] = $data['date_to'];
        }
        
        $query = $this->db->query($sql, $params);
        
        return (int)$query->row['total'];
    }
    
    /**
     * Get AI suggestions with filtering
     *
     * @param array $data Filter parameters
     * @return array List of AI suggestions
     */
    public function getAISuggestions(array $data = []): array {
        $sql = "SELECT ai.*, u.firstname, u.lastname,
                       s.name as accepted_segment_name
                FROM `" . DB_PREFIX . "mas_ai_suggestion` ai
                LEFT JOIN `" . DB_PREFIX . "user` u ON (ai.created_by = u.user_id)
                LEFT JOIN `" . DB_PREFIX . "mas_segment` s ON (ai.accepted_segment_id = s.segment_id)
                WHERE 1 = 1";
        
        $params = [];
        
        if (!empty($data['status'])) {
            $sql .= " AND ai.status = ?";
            $params[] = $data['status'];
        }
        
        if (!empty($data['goal'])) {
            $sql .= " AND ai.goal = ?";
            $params[] = $data['goal'];
        }
        
        if (!empty($data['business_type'])) {
            $sql .= " AND ai.business_type = ?";
            $params[] = $data['business_type'];
        }
        
        $sql .= " ORDER BY ai.created_at DESC";
        
        // Pagination
        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int)($data['start'] ?? 0));
            $limit = max(1, min(50, (int)($data['limit'] ?? 10)));
            
            $sql .= " LIMIT " . $start . ", " . $limit;
        }
        
        $query = $this->db->query($sql, $params);
        
        // Decode JSON filters for each suggestion
        $suggestions = [];
        foreach ($query->rows as $row) {
            $decoded_filters = json_decode($row['filters'], true);
            $row['filters'] = is_array($decoded_filters) ? $decoded_filters : [];
            $suggestions[] = $row;
        }
        
        return $suggestions;
    }
    
    /**
     * Save AI suggestion
     *
     * @param array $data Suggestion data
     * @return int New suggestion ID
     */
    public function saveAISuggestion(array $data): int {
        $sql = "INSERT INTO `" . DB_PREFIX . "mas_ai_suggestion`
                (name, description, filters, confidence, goal, business_type, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['name'],
            $data['description'] ?? '',
            is_array($data['filters']) ? json_encode($data['filters']) : $data['filters'],
            (float)($data['confidence'] ?? 0.0),
            $data['goal'] ?? '',
            $data['business_type'] ?? '',
            (int)($data['created_by'] ?? 0)
        ];
        
        $this->db->query($sql, $params);
        
        return $this->db->getLastId();
    }
    
    /**
     * Get specific AI suggestion
     *
     * @param int $suggestion_id Suggestion ID
     * @return array|null Suggestion data or null
     */
    public function getAISuggestion(int $suggestion_id): ?array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_ai_suggestion` WHERE suggestion_id = ?";
        
        $query = $this->db->query($sql, [$suggestion_id]);
        
        if ($query->num_rows) {
            $suggestion = $query->row;
            $decoded_filters = json_decode($suggestion['filters'], true);
            $suggestion['filters'] = is_array($decoded_filters) ? $decoded_filters : [];
            return $suggestion;
        }
        
        return null;
    }
    
    /**
     * Mark AI suggestion as accepted
     *
     * @param int $suggestion_id Suggestion ID
     * @param int $segment_id Created segment ID
     * @return bool Success status
     */
    public function markSuggestionAccepted(int $suggestion_id, int $segment_id): bool {
        $sql = "UPDATE `" . DB_PREFIX . "mas_ai_suggestion`
                SET status = 'accepted',
                    accepted_segment_id = ?,
                    accepted_at = NOW()
                WHERE suggestion_id = ?";
        
        $this->db->query($sql, [$segment_id, $suggestion_id]);
        
        return $this->db->countAffected() > 0;
    }
    
    /**
     * Get segment dependencies (workflows, campaigns using this segment)
     *
     * @param int $segment_id Segment ID
     * @return array List of dependencies
     */
    public function getSegmentDependencies(int $segment_id): array {
        $dependencies = [];
        
        // Check workflows - access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $workflowManager = $mas->get('workflowmanager');
            if ($workflowManager && method_exists($workflowManager, 'getSegmentDependencies')) {
                $workflow_deps = $workflowManager->getSegmentDependencies($segment_id);
                foreach ($workflow_deps as $dep) {
                    $dependencies[] = [
                        'type' => 'workflow',
                        'id' => $dep['id'],
                        'name' => $dep['name'],
                        'status' => $dep['status']
                    ];
                }
            }
        }
        
        // Check campaigns (if campaigns table exists)
        $table_exists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "mas_campaign'");
        if ($table_exists->num_rows) {
            $sql = "SELECT c.campaign_id, c.name, c.status
                    FROM `" . DB_PREFIX . "mas_campaign` c
                    WHERE c.target_segment_id = ? AND c.status IN ('active', 'scheduled')";
            
            $query = $this->db->query($sql, [$segment_id]);
            
            foreach ($query->rows as $row) {
                $dependencies[] = [
                    'type' => 'campaign',
                    'id' => $row['campaign_id'],
                    'name' => $row['name'],
                    'status' => $row['status']
                ];
            }
        }
        
        return $dependencies;
    }
    
    /**
     * Get total number of customers in all segments
     *
     * @return int Total segmented customers
     */
    public function getTotalSegmentedCustomers(): int {
        $sql = "SELECT COUNT(DISTINCT customer_id) as total
                FROM `" . DB_PREFIX . "mas_segment_customer` sc
                JOIN `" . DB_PREFIX . "mas_segment` s ON (sc.segment_id = s.segment_id)
                WHERE s.status = 1";
        
        $query = $this->db->query($sql);
        
        return (int)$query->row['total'];
    }
    
    /**
     * Get segment analytics data
     *
     * @param int $segment_id Segment ID
     * @param string $period Period (day, week, month)
     * @param int $days Number of days back
     * @return array Analytics data
     */
    public function getSegmentAnalytics(int $segment_id, string $period = 'day', int $days = 30): array {
        $date_format = match($period) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };
        
        $sql = "SELECT DATE_FORMAT(date, ?) as period,
                       AVG(customer_count) as avg_customers,
                       AVG(growth_rate) as avg_growth,
                       AVG(engagement_score) as avg_engagement
                FROM `" . DB_PREFIX . "mas_segment_analytics`
                WHERE segment_id = ? AND date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE_FORMAT(date, ?)
                ORDER BY period";
        
        $params = [$date_format, $segment_id, $days, $date_format];
        
        $query = $this->db->query($sql, $params);
        
        return $query->rows;
    }
    
    /**
     * Export segment data to CSV format
     *
     * @param int $segment_id Segment ID
     * @param array $fields Fields to export
     * @return array Export data
     */
    public function exportSegmentData(int $segment_id, array $fields = []): array {
        // Default fields if none specified
        if (empty($fields)) {
            $fields = ['customer_id', 'firstname', 'lastname', 'email', 'telephone', 'date_added'];
        }
        
        // Sanitize field names
        $safe_fields = [];
        foreach ($fields as $field) {
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
                $safe_fields[] = 'c.' . $field;
            }
        }
        
        if (empty($safe_fields)) {
            return [];
        }
        
        $field_list = implode(', ', $safe_fields);
        
        $sql = "SELECT " . $field_list . "
                FROM `" . DB_PREFIX . "mas_segment_customer` sc
                JOIN `" . DB_PREFIX . "customer` c ON (sc.customer_id = c.customer_id)
                WHERE sc.segment_id = ?
                ORDER BY c.customer_id";
        
        $query = $this->db->query($sql, [$segment_id]);
        
        // Log export activity
        if ($query->num_rows) {
            $this->logSegmentExport($segment_id, $query->num_rows);
        }
        
        return $query->rows;
    }
    
    /**
     * Get segment templates for quick creation
     *
     * @return array List of segment templates
     */
    public function getSegmentTemplates(): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_segment_template`
                WHERE status = 1
                ORDER BY sort_order, name";
        
        $query = $this->db->query($sql);
        
        $templates = [];
        foreach ($query->rows as $row) {
            $decoded_filters = json_decode($row['filters'], true);
            $row['filters'] = is_array($decoded_filters) ? $decoded_filters : [];
            $templates[] = $row;
        }
        
        return $templates;
    }
    
    /**
     * Get RFM data for a customer
     *
     * @param int $customer_id Customer ID
     * @return array|null RFM scores or null
     */
    public function getCustomerRFM(int $customer_id): ?array {
        // Access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $rfmAnalyzer = $mas->get('rfmanalyzer');
            if ($rfmAnalyzer && method_exists($rfmAnalyzer, 'getCustomerRFM')) {
                return $rfmAnalyzer->getCustomerRFM($customer_id);
            }
        }
        
        // Fallback to direct database query
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_customer_rfm` WHERE customer_id = ?";
        $query = $this->db->query($sql, [$customer_id]);
        
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Get predictive data for a customer
     *
     * @param int $customer_id Customer ID
     * @return array|null Prediction data or null
     */
    public function getCustomerPredictions(int $customer_id): ?array {
        // Access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $predictor = $mas->get('predictor');
            if ($predictor && method_exists($predictor, 'getCustomerPredictions')) {
                return $predictor->getCustomerPredictions($customer_id);
            }
        }
        
        // Fallback to direct database query
        $sql = "SELECT * FROM `" . DB_PREFIX . "mas_customer_predictions` WHERE customer_id = ?";
        $query = $this->db->query($sql, [$customer_id]);
        
        return $query->num_rows ? $query->row : null;
    }
    
    /**
     * Get segment performance summary
     *
     * @param array $segment_ids List of segment IDs
     * @return array Performance data
     */
    public function getSegmentPerformanceSummary(array $segment_ids = []): array {
        $sql = "SELECT s.segment_id, s.name, s.type,
                       COUNT(sc.customer_id) as customer_count,
                       AVG(sa.engagement_score) as avg_engagement,
                       MAX(sa.date) as last_updated
                FROM `" . DB_PREFIX . "mas_segment` s
                LEFT JOIN `" . DB_PREFIX . "mas_segment_customer` sc ON (s.segment_id = sc.segment_id)
                LEFT JOIN `" . DB_PREFIX . "mas_segment_analytics` sa ON (s.segment_id = sa.segment_id)
                WHERE s.status = 1";
        
        $params = [];
        
        if (!empty($segment_ids)) {
            $placeholders = implode(',', array_fill(0, count($segment_ids), '?'));
            $sql .= " AND s.segment_id IN ($placeholders)";
            $params = $segment_ids;
        }
        
        $sql .= " GROUP BY s.segment_id ORDER BY customer_count DESC";
        
        $query = $this->db->query($sql, $params);
        
        return $query->rows;
    }
    
    /**
     * Duplicate a segment
     *
     * @param int $segment_id Segment ID to duplicate
     * @return int New segment ID
     */
    public function duplicateSegment(int $segment_id): int {
        $original = $this->getSegment($segment_id);
        if (!$original) {
            return 0;
        }
        
        $data = [
            'name' => $original['name'] . ' (Copy)',
            'description' => $original['description'],
            'type' => $original['type'],
            'filters' => $original['filters'],
            'logic' => $original['logic'],
            'status' => 0, // Create disabled copy
            'refresh_interval' => $original['refresh_interval'],
            'created_by' => isset($this->user) ? $this->user->getId() : 0
        ];
        
        return $this->addSegment($data);
    }
    
    // === PRIVATE HELPER METHODS ===
    
    /**
     * Validate segment data before save/update
     *
     * @param array $data Segment data
     * @param int|null $segment_id Segment ID for updates
     * @return array Validation result
     */
    private function validateSegmentData(array $data, ?int $segment_id = null): array {
        $errors = [];
        
        // Required fields
        if (empty($data['name'])) {
            $errors[] = 'Segment name is required';
        }
        
        if (strlen($data['name']) > 255) {
            $errors[] = 'Segment name too long (max 255 characters)';
        }
        
        // Check for duplicate names
        if (!empty($data['name'])) {
            $sql = "SELECT segment_id FROM `" . DB_PREFIX . "mas_segment` WHERE name = ?";
            $params = [$data['name']];
            
            if ($segment_id) {
                $sql .= " AND segment_id != ?";
                $params[] = $segment_id;
            }
            
            $query = $this->db->query($sql, $params);
            if ($query->num_rows) {
                $errors[] = 'Segment name already exists';
            }
        }
        
        // Validate type
        $valid_types = ['static', 'dynamic', 'ai'];
        if (isset($data['type']) && !in_array($data['type'], $valid_types)) {
            $errors[] = 'Invalid segment type';
        }
        
        // Validate logic
        $valid_logic = ['AND', 'OR'];
        if (isset($data['logic']) && !in_array($data['logic'], $valid_logic)) {
            $errors[] = 'Invalid filter logic';
        }
        
        // Validate refresh interval
        if (isset($data['refresh_interval']) && ($data['refresh_interval'] < 0 || $data['refresh_interval'] > 168)) {
            $errors[] = 'Refresh interval must be between 0 and 168 hours';
        }
        
        // Validate filters for dynamic segments
        if (($data['type'] ?? 'dynamic') === 'dynamic') {
            $filters = is_string($data['filters'] ?? '') ? json_decode($data['filters'], true) : ($data['filters'] ?? []);
            
            if (!is_array($filters)) {
                $errors[] = 'Invalid filters format';
            } elseif (empty($filters)) {
                $errors[] = 'At least one filter is required for dynamic segments';
            } elseif (count($filters) > 20) {
                $errors[] = 'Maximum 20 filters allowed per segment';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Create initial analytics record for new segment
     *
     * @param int $segment_id Segment ID
     */
    private function createSegmentAnalytics(int $segment_id): void {
        $sql = "INSERT INTO `" . DB_PREFIX . "mas_segment_analytics`
                (segment_id, date, customer_count, growth_rate, engagement_score)
                VALUES (?, CURDATE(), 0, 0, 0)";
        
        $this->db->query($sql, [$segment_id]);
    }
    
    /**
     * Add customers to a static segment
     *
     * @param int $segment_id Segment ID
     * @param array $customer_ids List of customer IDs
     */
    private function addSegmentCustomers(int $segment_id, array $customer_ids): void {
        if (empty($customer_ids)) {
            return;
        }
        
        $values = [];
        $params = [];
        
        foreach ($customer_ids as $customer_id) {
            $values[] = "(?, ?, NOW())";
            $params[] = $segment_id;
            $params[] = (int)$customer_id;
        }
        
        $sql = "INSERT IGNORE INTO `" . DB_PREFIX . "mas_segment_customer`
                (segment_id, customer_id, added_at) VALUES " . implode(', ', $values);
        
        $this->db->query($sql, $params);
    }
    
    /**
     * Clear materialized data for a segment
     *
     * @param int $segment_id Segment ID
     */
    private function clearSegmentMaterialization(int $segment_id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mas_segment_customer` WHERE segment_id = ?", [$segment_id]);
    }
    
    /**
     * Log segment export activity
     *
     * @param int $segment_id Segment ID
     * @param int $customer_count Number of customers exported
     */
    private function logSegmentExport(int $segment_id, int $customer_count): void {
        $user_id = 0;
        if (isset($this->user) && method_exists($this->user, 'getId')) {
            $user_id = $this->user->getId();
        }
        
        $sql = "INSERT INTO `" . DB_PREFIX . "mas_segment_export`
                (segment_id, customer_count, exported_at, exported_by)
                VALUES (?, ?, NOW(), ?)";
        
        $params = [$segment_id, $customer_count, $user_id];
        
        $this->db->query($sql, $params);
    }
}
