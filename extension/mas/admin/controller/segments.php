<?php
namespace Opencart\Admin\Controller\Extension\Mas;

/**
 * MAS Segments Controller - Enterprise Level
 *
 * Advanced customer segmentation controller with AI-powered suggestions,
 * drag-and-drop builder, real-time materialization and comprehensive analytics.
 * Follows OpenCart 4.x enterprise conventions and MAS architecture patterns.
 *
 * @package    MAS Marketing Automation Suite
 * @subpackage Admin Controllers
 * @version    1.0.0
 * @author     MAS Development Team
 * @copyright  2025 Marketing Automation Suite
 */
class Segments extends \Opencart\System\Engine\Controller {
    
    /**
     * Error handling array for validation and user feedback
     * @var array
     */
    private array $error = [];
    
    /**
     * Main segments management page
     * Handles the primary interface with tabs, filters, and segment listing
     */
    public function index(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        // Set page title and breadcrumbs
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_mas'),
            'href' => $this->url->link('extension/mas/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'])
        ];
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/dashboard', 'user_token=' . $this->session->data['user_token']));
        }
        
        // Handle bulk actions
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['bulk_action'])) {
            $this->processBulkAction();
        }
        
        // Get filter parameters
        $filters = $this->getFilterParams();
        
        // Fetch segments with filters
        $segments = $this->model_extension_mas_segments->getSegments($filters);
        $total_segments = $this->model_extension_mas_segments->getTotalSegments($filters);
        
        // Prepare segments data for display
        $data['segments'] = [];
        foreach ($segments as $segment) {
            $data['segments'][] = [
                'segment_id' => $segment['segment_id'],
                'name' => $segment['name'],
                'description' => $segment['description'] ?? '',
                'type' => $this->getSegmentTypeLabel($segment['type']),
                'type_class' => $this->getSegmentTypeClass($segment['type']),
                'status' => $segment['status'],
                'status_label' => $this->getStatusLabel($segment['status']),
                'status_class' => $this->getStatusClass($segment['status']),
                'customer_count' => number_format($segment['customer_count'] ?? 0),
                'last_materialization' => $segment['last_materialization'] ?
                date($this->language->get('date_format_short'), strtotime($segment['last_materialization'])) :
                $this->language->get('text_never'),
                'created_at' => date($this->language->get('date_format_short'), strtotime($segment['created_at'])),
                'updated_at' => date($this->language->get('date_format_short'), strtotime($segment['updated_at'])),
                'edit' => $this->url->link('extension/mas/segments.form', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment['segment_id']),
                'delete' => $this->url->link('extension/mas/segments.delete', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment['segment_id']),
                'logs' => $this->url->link('extension/mas/segments.logs', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment['segment_id'])
            ];
        }
        
        // Build pagination URL
        $url = '';
        if (!empty($filters['name'])) {
            $url .= '&filter_name=' . urlencode($filters['name']);
        }
        if (!empty($filters['type'])) {
            $url .= '&filter_type=' . $filters['type'];
        }
        if (!empty($filters['status']) && $filters['status'] !== '') {
            $url .= '&filter_status=' . $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $url .= '&filter_date_from=' . $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $url .= '&filter_date_to=' . $filters['date_to'];
        }
        
        // OpenCart 4.x pagination
        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $total_segments,
            'page'  => $filters['page'],
            'limit' => $this->config->get('config_pagination_admin') ?: 20,
            'url'   => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
        ]);
        
        // URLs and links
        $data['add'] = $this->url->link('extension/mas/segments.form', 'user_token=' . $this->session->data['user_token']);
        $data['builder'] = $this->url->link('extension/mas/segments.builder', 'user_token=' . $this->session->data['user_token']);
        $data['ai_insights'] = $this->url->link('extension/mas/segments.ai', 'user_token=' . $this->session->data['user_token']);
        
        // AJAX endpoints
        $data['ajax_materialize'] = $this->url->link('extension/mas/segments.materialize', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_toggle_status'] = $this->url->link('extension/mas/segments.toggle_status', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_duplicate'] = $this->url->link('extension/mas/segments.duplicate', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_export'] = $this->url->link('extension/mas/segments.export', 'user_token=' . $this->session->data['user_token']);
        
        // Filter data
        $data['filter_name'] = $filters['name'];
        $data['filter_type'] = $filters['type'];
        $data['filter_status'] = $filters['status'];
        $data['filter_date_from'] = $filters['date_from'];
        $data['filter_date_to'] = $filters['date_to'];
        
        // Segment type options
        $data['segment_types'] = [
            '' => $this->language->get('text_all_types'),
            'static' => $this->language->get('text_static'),
            'dynamic' => $this->language->get('text_dynamic'),
            'ai' => $this->language->get('text_ai_generated')
        ];
        
        // Status options
        $data['statuses'] = [
            '' => $this->language->get('text_all_statuses'),
            '1' => $this->language->get('text_enabled'),
            '0' => $this->language->get('text_disabled')
        ];
        
        // Statistics
        $data['total_segments'] = $total_segments;
        $data['total_customers'] = $this->model_extension_mas_segments->getTotalSegmentedCustomers();
        
        // Success/Error messages
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }
        
        // Set common template data
        $this->setCommonData($data);
        
        // Load the main segments template
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/segments_list', $data));
    }
    
    /**
     * Detailed view of a single segment
     * Shows comprehensive information, customer list, materialization history, and statistics
     */
    public function view(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        $segment_id = isset($this->request->get['segment_id']) ? (int)$this->request->get['segment_id'] : 0;
        
        if (!$segment_id) {
            $this->session->data['error'] = $this->language->get('error_segment_id_required');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $segment = $this->model_extension_mas_segments->getSegment($segment_id);
        
        if (!$segment) {
            $this->session->data['error'] = $this->language->get('error_segment_not_found');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $this->document->setTitle($segment['name'] . ' - ' . $this->language->get('text_segment_details'));
        
        $data = [];
        
        // Segment basic information
        $data['segment'] = [
            'segment_id' => $segment['segment_id'],
            'name' => $segment['name'],
            'description' => $segment['description'],
            'type' => $this->getSegmentTypeLabel($segment['type']),
            'type_class' => $this->getSegmentTypeClass($segment['type']),
            'status' => $segment['status'],
            'status_label' => $this->getStatusLabel($segment['status']),
            'status_class' => $this->getStatusClass($segment['status']),
            'filters' => json_decode($segment['filters'] ?? '[]', true),
            'logic' => $segment['logic'] ?? 'AND',
            'created_at' => date($this->language->get('datetime_format'), strtotime($segment['created_at'])),
            'updated_at' => date($this->language->get('datetime_format'), strtotime($segment['updated_at'])),
            'created_by' => $segment['created_by_name'] ?? 'System',
            'customer_count' => $segment['customer_count'] ?? 0
        ];
        
        // Get segment customers with pagination
        $customer_page = isset($this->request->get['customer_page']) ? (int)$this->request->get['customer_page'] : 1;
        $customer_limit = 50;
        
        $customer_filters = [
            'segment_id' => $segment_id,
            'start' => ($customer_page - 1) * $customer_limit,
            'limit' => $customer_limit
        ];
        
        $customers = $this->model_extension_mas_segments->getSegmentCustomers($customer_filters);
        $total_customers = $this->model_extension_mas_segments->getTotalSegmentCustomers($segment_id);
        
        $data['customers'] = [];
        foreach ($customers as $customer) {
            $data['customers'][] = [
                'customer_id' => $customer['customer_id'],
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'email' => $customer['email'],
                'telephone' => $customer['telephone'] ?? '',
                'customer_group' => $customer['customer_group'] ?? '',
                'status' => $customer['status'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($customer['date_added'])),
                'total_orders' => $customer['total_orders'] ?? 0,
                'total_spent' => $this->currency->format($customer['total_spent'] ?? 0, $this->config->get('config_currency')),
                'view_customer' => $this->url->link('customer/customer.form', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $customer['customer_id'])
            ];
        }
        
        // Customer pagination
        $data['customer_pagination'] = $this->load->controller('common/pagination', [
            'total' => $total_customers,
            'page'  => $customer_page,
            'limit' => $customer_limit,
            'url'   => $this->url->link('extension/mas/segments.view', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment_id . '&customer_page={page}')
        ]);
        
        // Materialization history
        $history_filters = [
            'segment_id' => $segment_id,
            'start' => 0,
            'limit' => 10
        ];
        
        $materialization_history = $this->model_extension_mas_segments->getMaterializationLogs($history_filters);
        $data['materialization_history'] = [];
        
        foreach ($materialization_history as $log) {
            $data['materialization_history'][] = [
                'log_id' => $log['log_id'],
                'status' => $log['status'],
                'status_label' => $this->getMaterializationStatusLabel($log['status']),
                'status_class' => $this->getMaterializationStatusClass($log['status']),
                'customers_count' => number_format($log['customers_count'] ?? 0),
                'started_at' => date($this->language->get('datetime_format'), strtotime($log['started_at'])),
                'completed_at' => $log['completed_at'] ? date($this->language->get('datetime_format'), strtotime($log['completed_at'])) : '',
                'duration' => $this->calculateDuration($log['started_at'], $log['completed_at']),
                'error_message' => $log['error_message'] ?? ''
            ];
        }
        
        // Segment statistics
        $data['statistics'] = $this->model_extension_mas_segments->getSegmentStatistics($segment_id);
        
        // Performance data for chart
        $data['performance_data'] = $this->model_extension_mas_segments->getSegmentPerformanceData($segment_id, 30); // Last 30 days
        
        // Set breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_mas'),
            'href' => $this->url->link('extension/mas/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_segments'),
            'href' => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $segment['name'],
            'href' => ''
        ];
        
        // URLs and actions
        $data['edit'] = $this->url->link('extension/mas/segments.builder', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment_id);
        $data['logs'] = $this->url->link('extension/mas/segments.logs', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment_id);
        $data['back'] = $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']);
        $data['duplicate'] = $this->url->link('extension/mas/segments.duplicate', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment_id);
        
        // AJAX endpoints
        $data['ajax_materialize'] = $this->url->link('extension/mas/segments.materialize', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_export_customers'] = $this->url->link('extension/mas/segments.export_customers', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_refresh_stats'] = $this->url->link('extension/mas/segments.refresh_stats', 'user_token=' . $this->session->data['user_token']);
        
        $this->setCommonData($data);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/segments_view', $data));
    }
    
    /**
     * AJAX endpoint for exporting segment customers
     */
    public function export_customers(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $segment_id = (int)($this->request->post['segment_id'] ?? 0);
        $format = $this->request->post['format'] ?? 'csv';
        
        if (!$segment_id) {
            $this->response->setOutput(json_encode(['error' => 'Segment ID required']));
            return;
        }
        
        $customers = $this->model_extension_mas_segments->getSegmentCustomers(['segment_id' => $segment_id, 'limit' => 999999]);
        
        if ($format === 'csv') {
            $filename = 'segment_' . $segment_id . '_customers_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, ['Customer ID', 'First Name', 'Last Name', 'Email', 'Telephone', 'Customer Group', 'Status', 'Date Added', 'Total Orders', 'Total Spent']);
            
            foreach ($customers as $customer) {
                fputcsv($output, [
                    $customer['customer_id'],
                    $customer['firstname'],
                    $customer['lastname'],
                    $customer['email'],
                    $customer['telephone'] ?? '',
                    $customer['customer_group'] ?? '',
                    $customer['status'] ? 'Enabled' : 'Disabled',
                    $customer['date_added'],
                    $customer['total_orders'] ?? 0,
                    $customer['total_spent'] ?? 0
                ]);
            }
            
            fclose($output);
            exit();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'customers' => $customers
        ]));
    }
    
    /**
     * AJAX endpoint for refreshing segment statistics
     */
    public function refresh_stats(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $segment_id = (int)($this->request->post['segment_id'] ?? 0);
        
        if (!$segment_id) {
            $this->response->setOutput(json_encode(['error' => 'Segment ID required']));
            return;
        }
        
        // Refresh statistics
        $statistics = $this->model_extension_mas_segments->refreshSegmentStatistics($segment_id);
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'statistics' => $statistics
        ]));
    }
    
    /**
     * Segment builder interface
     * Advanced drag-and-drop builder with AI assistance and real-time preview
     */
    public function builder(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        $this->document->setTitle($this->language->get('text_segment_builder'));
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $segment_id = isset($this->request->get['segment_id']) ? (int)$this->request->get['segment_id'] : 0;
        
        // Initialize builder data
        $data = $this->initializeBuilderData($segment_id);
        
        // Set breadcrumbs
        $data['breadcrumbs'] = $this->getBuilderBreadcrumbs();
        
        // Available filters by category
        $data['filter_categories'] = [
            'demographic' => [
                'name' => $this->language->get('text_demographic'),
                'filters' => $this->getDemographicFilters()
            ],
            'behavioral' => [
                'name' => $this->language->get('text_behavioral'),
                'filters' => $this->getBehavioralFilters()
            ],
            'rfm' => [
                'name' => $this->language->get('text_rfm'),
                'filters' => $this->getRFMFilters()
            ],
            'predictive' => [
                'name' => $this->language->get('text_predictive'),
                'filters' => $this->getPredictiveFilters()
            ]
        ];
        
        // AJAX endpoints for builder
        $data['ajax_preview'] = $this->url->link('extension/mas/segments.preview', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_save'] = $this->url->link('extension/mas/segments.save', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_validate'] = $this->url->link('extension/mas/segments.validate', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_ai_suggest'] = $this->url->link('extension/mas/segments.ai_suggest', 'user_token=' . $this->session->data['user_token']);
        
        $this->setCommonData($data);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/segments_builder', $data));
    }
    
    /**
     * AI-powered insights and suggestions interface
     * Intelligent segment recommendations and analytics
     */
    public function ai(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        $this->document->setTitle($this->language->get('text_ai_insights'));
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $data = [];
        
        // Set breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_mas'),
            'href' => $this->url->link('extension/mas/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_segments'),
            'href' => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_ai_insights'),
            'href' => ''
        ];
        
        // Get recent AI suggestions
        $data['ai_suggestions'] = $this->model_extension_mas_segments->getAISuggestions();
        
        // Business goals for AI context
        $data['business_goals'] = [
            'reduce_churn' => $this->language->get('text_predict_churn'),
            'increase_engagement' => $this->language->get('text_find_opportunities'),
            'optimize_marketing' => $this->language->get('text_optimize'),
            'analyze_performance' => $this->language->get('text_analyze_performance')
        ];
        
        // Business types for context
        $data['business_types'] = [
            'ecommerce' => $this->language->get('text_ecommerce'),
            'saas' => $this->language->get('text_saas'),
            'retail' => $this->language->get('text_retail'),
            'services' => $this->language->get('text_services')
        ];
        
        // AJAX endpoints - CORRETTI
        $data['ajax_generate_insights'] = $this->url->link('extension/mas/segments.generate_insights', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_accept_suggestion'] = $this->url->link('extension/mas/segments.accept_suggestion', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_preview_suggestion'] = $this->url->link('extension/mas/segments.preview_suggestion', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_reject_suggestion'] = $this->url->link('extension/mas/segments.reject_suggestion', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_get_history'] = $this->url->link('extension/mas/segments.ai_history', 'user_token=' . $this->session->data['user_token']);
        
        // URLs and links - CORRETTI
        $data['add'] = $this->url->link('extension/mas/segments.form', 'user_token=' . $this->session->data['user_token']);
        $data['builder'] = $this->url->link('extension/mas/segments.builder', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']);
        
        // Success/Error messages
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }
        
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }
        
        $this->setCommonData($data);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/segments_ai', $data));
    }
    
    /**
     * Enhanced materialization logs viewer with advanced filtering
     */
    public function logs(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        $this->document->setTitle($this->language->get('text_materialization_logs'));
    
        $segment_id = isset($this->request->get['segment_id']) ? (int)$this->request->get['segment_id'] : 0;
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        
        // Enhanced filter parameters
        $filters = [
            'segment_id' => $segment_id,
            'status' => $this->request->get['filter_status'] ?? '',
            'date_from' => $this->request->get['filter_date_from'] ?? '',
            'date_to' => $this->request->get['filter_date_to'] ?? '',
            'duration_min' => $this->request->get['filter_duration_min'] ?? '',
            'duration_max' => $this->request->get['filter_duration_max'] ?? '',
            'customer_count_min' => $this->request->get['filter_customer_count_min'] ?? '',
            'customer_count_max' => $this->request->get['filter_customer_count_max'] ?? '',
            'error_only' => $this->request->get['filter_error_only'] ?? false,
            'user_id' => $this->request->get['filter_user_id'] ?? '',
            'start' => ($page - 1) * 20,
            'limit' => 20,
            'sort' => $this->request->get['sort'] ?? 'started_at',
            'order' => $this->request->get['order'] ?? 'DESC'
        ];
    
        // Fetch enhanced logs
        $logs = $this->model_extension_mas_segments->getEnhancedMaterializationLogs($filters);
        $total_logs = $this->model_extension_mas_segments->getTotalMaterializationLogs($filters);
    
        // Log statistics for dashboard
        $log_stats = $this->model_extension_mas_segments->getLogStatistics($filters);
        
        $data['log_statistics'] = $log_stats;
        $data['logs'] = [];
        
        foreach ($logs as $log) {
            $data['logs'][] = [
                'log_id' => $log['log_id'],
                'segment_name' => $log['segment_name'],
                'segment_id' => $log['segment_id'],
                'status' => $log['status'],
                'status_label' => $this->getMaterializationStatusLabel($log['status']),
                'status_class' => $this->getMaterializationStatusClass($log['status']),
                'customers_count' => number_format($log['customers_count'] ?? 0),
                'started_at' => date($this->language->get('datetime_format'), strtotime($log['started_at'])),
                'completed_at' => $log['completed_at'] ? date($this->language->get('datetime_format'), strtotime($log['completed_at'])) : '',
                'duration' => $this->calculateDuration($log['started_at'], $log['completed_at']),
                'duration_seconds' => $log['duration_seconds'] ?? 0,
                'memory_used' => $this->formatBytes($log['memory_used'] ?? 0),
                'cpu_time' => $log['cpu_time'] ?? 0,
                'error_message' => $log['error_message'] ?? '',
                'user_name' => $log['user_firstname'] . ' ' . $log['user_lastname'],
                'retry' => $this->url->link('extension/mas/segments.retry_materialization', 'user_token=' . $this->session->data['user_token'] . '&log_id=' . $log['log_id']),
                'view_segment' => $this->url->link('extension/mas/segments.view', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $log['segment_id'])
            ];
        }
    
        // Build enhanced pagination URL
        $url = '';
        foreach (['segment_id', 'filter_status', 'filter_date_from', 'filter_date_to', 'filter_duration_min', 'filter_duration_max', 'filter_customer_count_min', 'filter_customer_count_max', 'filter_error_only', 'filter_user_id', 'sort', 'order'] as $param) {
            if (!empty($filters[str_replace('filter_', '', $param)])) {
                $url .= '&' . $param . '=' . urlencode($filters[str_replace('filter_', '', $param)]);
            }
        }
    
        // Enhanced pagination
        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $total_logs,
            'page'  => $page,
            'limit' => 20,
            'url'   => $this->url->link('extension/mas/segments.logs', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
        ]);
    
        // Enhanced filter data
        $data['filter_status'] = $filters['status'];
        $data['filter_date_from'] = $filters['date_from'];
        $data['filter_date_to'] = $filters['date_to'];
        $data['filter_duration_min'] = $filters['duration_min'];
        $data['filter_duration_max'] = $filters['duration_max'];
        $data['filter_customer_count_min'] = $filters['customer_count_min'];
        $data['filter_customer_count_max'] = $filters['customer_count_max'];
        $data['filter_error_only'] = $filters['error_only'];
        $data['filter_user_id'] = $filters['user_id'];
    
        // User options for filter
        $data['user_options'] = $this->model_extension_mas_segments->getMaterializationUsers();
        
        // Enhanced status options
        $data['status_options'] = [
            '' => $this->language->get('text_all_statuses'),
            'success' => $this->language->get('text_success'),
            'failed' => $this->language->get('text_failed'),
            'running' => $this->language->get('text_running'),
            'cancelled' => $this->language->get('text_cancelled')
        ];
    
        // Additional AJAX endpoints
        $data['ajax_export_logs'] = $this->url->link('extension/mas/segments.export_logs', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_clear_logs'] = $this->url->link('extension/mas/segments.clear_logs', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_retry_materialization'] = $this->url->link('extension/mas/segments.retry_materialization', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_cancel_materialization'] = $this->url->link('extension/mas/segments.cancel_materialization', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_log_details'] = $this->url->link('extension/mas/segments.log_details', 'user_token=' . $this->session->data['user_token']);
    
        $this->setCommonData($data);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
    
        $this->response->setOutput($this->load->view('extension/mas/segments_logs', $data));
    }
    
    /**
     * AJAX endpoint for real-time segment preview
     * Returns estimated customer count and sample data
     */
    public function preview(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        // Verify AJAX request
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $filters = $this->request->post['filters'] ?? [];
        $logic = $this->request->post['logic'] ?? 'AND';
        
        // Access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $segmentManager = $mas->get('segmentmanager');
            if ($segmentManager && method_exists($segmentManager, 'previewSegment')) {
                $preview_data = $segmentManager->previewSegment($filters, $logic);
                
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode([
                    'success' => true,
                    'data' => [
                        'estimated_count' => $preview_data['count'] ?? 0,
                        'sample_customers' => $preview_data['sample'] ?? [],
                        'estimated_time' => $preview_data['estimated_time'] ?? '0s',
                        'complexity' => $preview_data['complexity'] ?? 'low'
                    ]
                ]));
                return;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Segment manager not available'
        ]));
    }
    
    /**
     * AJAX endpoint for segment materialization
     * Triggers asynchronous segment materialization process
     */
    public function materialize(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('modify', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $segment_id = (int)($this->request->post['segment_id'] ?? 0);
        
        if (!$segment_id) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_segment_id_required')]));
            return;
        }
        
        // Access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $segmentManager = $mas->get('segmentmanager');
            if ($segmentManager && method_exists($segmentManager, 'materializeSegment')) {
                $result = $segmentManager->materializeSegment($segment_id);
                
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode([
                    'success' => true,
                    'message' => $this->language->get('text_materialization_started'),
                    'job_id' => $result['job_id'] ?? null
                ]));
                return;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Segment manager not available'
        ]));
    }
    
    /**
     * AJAX endpoint for AI-powered segment suggestions
     * Generates intelligent segment recommendations based on business goals
     */
    public function generate_insights(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $goal = $this->request->post['goal'] ?? '';
        $context = $this->request->post['context'] ?? '';
        $business_type = $this->request->post['business_type'] ?? 'ecommerce';
        
        if (empty($goal)) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_goal_required')]));
            return;
        }
        
        // Access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $aiGateway = $mas->get('aigateway');
            if ($aiGateway && method_exists($aiGateway, 'generateSegmentSuggestions')) {
                $suggestions = $aiGateway->generateSegmentSuggestions($goal, $context, $business_type);
                
                // Store suggestions in database for future reference
                if (is_array($suggestions)) {
                    foreach ($suggestions as $suggestion) {
                        $this->model_extension_mas_segments->saveAISuggestion([
                            'name' => $suggestion['name'] ?? '',
                            'description' => $suggestion['description'] ?? '',
                            'filters' => json_encode($suggestion['filters'] ?? []),
                            'confidence' => $suggestion['confidence'] ?? 0.0,
                            'goal' => $goal,
                            'business_type' => $business_type,
                            'created_by' => $this->user->getId()
                        ]);
                    }
                }
                
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode([
                    'success' => true,
                    'suggestions' => $suggestions ?? []
                ]));
                return;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'AI Gateway not available'
        ]));
    }
    
    /**
     * AJAX endpoint to accept AI suggestion and create segment
     */
    public function accept_suggestion(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('modify', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $suggestion_id = (int)($this->request->post['suggestion_id'] ?? 0);
        
        if (!$suggestion_id) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_suggestion_id_required')]));
            return;
        }
        
        $suggestion = $this->model_extension_mas_segments->getAISuggestion($suggestion_id);
        
        if (!$suggestion) {
            $this->response->setOutput(json_encode(['error' => 'Suggestion not found']));
            return;
        }
        
        // Create segment from suggestion
        $segment_data = [
            'name' => $suggestion['name'],
            'description' => $suggestion['description'],
            'type' => 'ai',
            'filters' => $suggestion['filters'],
            'status' => 1,
            'created_by' => $this->user->getId()
        ];
        
        $segment_id = $this->model_extension_mas_segments->addSegment($segment_data);
        
        if ($segment_id) {
            // Mark suggestion as accepted
            $this->model_extension_mas_segments->markSuggestionAccepted($suggestion_id, $segment_id);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => $this->language->get('text_suggestion_accepted'),
                'segment_id' => $segment_id,
                'redirect' => $this->url->link('extension/mas/segments.form', 'user_token=' . $this->session->data['user_token'] . '&segment_id=' . $segment_id)
            ]));
        } else {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Failed to create segment from suggestion'
            ]));
        }
    }
    
    /**
     * AJAX endpoint for AI suggestion preview
     */
    public function preview_suggestion(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $suggestion_id = (int)($this->request->post['suggestion_id'] ?? 0);
        
        if (!$suggestion_id) {
            $this->response->setOutput(json_encode(['error' => 'Suggestion ID required']));
            return;
        }
        
        $suggestion = $this->model_extension_mas_segments->getAISuggestion($suggestion_id);
        
        if (!$suggestion) {
            $this->response->setOutput(json_encode(['error' => 'Suggestion not found']));
            return;
        }
        
        // Mock preview data - replace with actual preview logic
        $preview_data = [
            'estimated_count' => rand(500, 5000),
            'complexity' => 'Medium',
            'estimated_time' => '2-5 minutes'
        ];
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'suggestion' => $suggestion,
            'preview' => $preview_data
        ]));
    }
    
    /**
     * AJAX endpoint for rejecting AI suggestion
     */
    public function reject_suggestion(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('modify', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $suggestion_id = (int)($this->request->post['suggestion_id'] ?? 0);
        
        if (!$suggestion_id) {
            $this->response->setOutput(json_encode(['error' => 'Suggestion ID required']));
            return;
        }
        
        // Update suggestion status to rejected
        $sql = "UPDATE `" . DB_PREFIX . "mas_ai_suggestion`
                SET status = 'rejected', rejected_at = NOW()
                WHERE suggestion_id = ?";
        
        $this->db->query($sql, [$suggestion_id]);
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'message' => 'Suggestion rejected'
        ]));
    }
    
    /**
     * AJAX endpoint for AI history
     */
    public function ai_history(): void {
        $this->load->language('extension/mas/segments');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'GET') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        // Mock history data - replace with actual history logic
        $history = [
            [
                'action' => 'AI Suggestion Generated',
                'description' => 'Generated 3 new segment suggestions for e-commerce optimization',
                'created_at' => date('d/m/Y H:i')
            ],
            [
                'action' => 'Suggestion Accepted',
                'description' => 'Accepted suggestion "High-Value Customers" and created segment',
                'created_at' => date('d/m/Y H:i', strtotime('-2 hours'))
            ]
        ];
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'history' => $history
        ]));
    }
    
    /**
     * Delete segment with confirmation and dependency check
     */
    public function delete(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        
        // Permission check
        if (!$this->user->hasPermission('modify', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $segment_id = (int)($this->request->get['segment_id'] ?? 0);
        
        if (!$segment_id) {
            $this->session->data['error'] = $this->language->get('error_segment_id_required');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        // Check for dependencies (workflows, campaigns using this segment)
        $dependencies = $this->model_extension_mas_segments->getSegmentDependencies($segment_id);
        
        if (!empty($dependencies)) {
            $this->session->data['error'] = $this->language->get('error_segment_has_dependencies');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        // Delete segment
        $result = $this->model_extension_mas_segments->deleteSegment($segment_id);
        
        if ($result) {
            $this->session->data['success'] = $this->language->get('text_segment_deleted');
        } else {
            $this->session->data['error'] = $this->language->get('error_delete_failed');
        }
        
        $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
    }
    
    // === PRIVATE HELPER METHODS ===
    
    /**
     * Get filter parameters from request
     */
    private function getFilterParams(): array {
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $limit = $this->config->get('config_pagination_admin') ?: 20;
        
        return [
            'name' => $this->request->get['filter_name'] ?? '',
            'type' => $this->request->get['filter_type'] ?? '',
            'status' => $this->request->get['filter_status'] ?? '',
            'date_from' => $this->request->get['filter_date_from'] ?? '',
            'date_to' => $this->request->get['filter_date_to'] ?? '',
            'page' => $page,
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
            'sort' => $this->request->get['sort'] ?? 'name',
            'order' => $this->request->get['order'] ?? 'ASC'
        ];
    }
    
    /**
     * Process bulk actions on multiple segments
     */
    private function processBulkAction(): void {
        $action = $this->request->post['bulk_action'] ?? '';
        $segment_ids = $this->request->post['selected'] ?? [];
        
        if (empty($segment_ids) || empty($action)) {
            return;
        }
        
        foreach ($segment_ids as $segment_id) {
            switch ($action) {
                case 'enable':
                    $this->model_extension_mas_segments->updateSegmentStatus($segment_id, 1);
                    break;
                case 'disable':
                    $this->model_extension_mas_segments->updateSegmentStatus($segment_id, 0);
                    break;
                case 'materialize':
                    $this->triggerMaterialization($segment_id);
                    break;
                case 'delete':
                    if (!$this->model_extension_mas_segments->getSegmentDependencies($segment_id)) {
                        $this->model_extension_mas_segments->deleteSegment($segment_id);
                    }
                    break;
            }
        }
        
        $this->session->data['success'] = 'Bulk action completed successfully';
    }
    
    /**
     * Set common template data used across all segment views
     */
    private function setCommonData(array &$data): void {
        $data['user_token'] = $this->session->data['user_token'];
        $data['back'] = $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']);
        
        // Add CSS and JS for segments
        $this->document->addStyle('view/stylesheet/extension/mas/segments.css');
        $this->document->addScript('view/javascript/extension/mas/segments.js');
    }
    
    /**
     * Get segment type label for display
     */
    private function getSegmentTypeLabel(string $type): string {
        $labels = [
            'static' => $this->language->get('text_static'),
            'dynamic' => $this->language->get('text_dynamic'),
            'ai' => $this->language->get('text_ai_generated')
        ];
        
        return $labels[$type] ?? ucfirst($type);
    }
    
    /**
     * Get CSS class for segment type
     */
    private function getSegmentTypeClass(string $type): string {
        $classes = [
            'static' => 'badge-secondary',
            'dynamic' => 'badge-primary',
            'ai' => 'badge-success'
        ];
        
        return $classes[$type] ?? 'badge-light';
    }
    
    /**
     * Get status label for display
     */
    private function getStatusLabel(int $status): string {
        return $status ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
    }
    
    /**
     * Get CSS class for status
     */
    private function getStatusClass(int $status): string {
        return $status ? 'badge-success' : 'badge-secondary';
    }
    
    /**
     * Initialize builder data for existing or new segment
     */
    private function initializeBuilderData(int $segment_id): array {
        $data = [];
        
        if ($segment_id) {
            $segment = $this->model_extension_mas_segments->getSegment($segment_id);
            if ($segment) {
                $data['segment'] = $segment;
                $data['segment']['filters'] = json_decode($segment['filters'] ?? '[]', true);
            }
        } else {
            $data['segment'] = [
                'segment_id' => 0,
                'name' => '',
                'description' => '',
                'type' => 'dynamic',
                'filters' => [],
                'logic' => 'AND',
                'status' => 1
            ];
        }
        
        return $data;
    }
    
    /**
     * Get demographic filters for builder
     */
    private function getDemographicFilters(): array {
        return [
            ['id' => 'customer_group', 'name' => 'Customer Group', 'type' => 'select'],
            ['id' => 'age_range', 'name' => 'Age Range', 'type' => 'range'],
            ['id' => 'gender', 'name' => 'Gender', 'type' => 'select'],
            ['id' => 'country', 'name' => 'Country', 'type' => 'select'],
            ['id' => 'city', 'name' => 'City', 'type' => 'text'],
            ['id' => 'registration_date', 'name' => 'Registration Date', 'type' => 'date_range']
        ];
    }
    
    /**
     * Get behavioral filters for builder
     */
    private function getBehavioralFilters(): array {
        return [
            ['id' => 'last_login', 'name' => 'Last Login', 'type' => 'date_range'],
            ['id' => 'total_orders', 'name' => 'Total Orders', 'type' => 'number_range'],
            ['id' => 'page_views', 'name' => 'Page Views', 'type' => 'number_range'],
            ['id' => 'cart_abandonment', 'name' => 'Cart Abandonment', 'type' => 'boolean'],
            ['id' => 'product_category', 'name' => 'Purchased Category', 'type' => 'multiselect'],
            ['id' => 'email_engagement', 'name' => 'Email Engagement', 'type' => 'select']
        ];
    }
    
    /**
     * Get RFM filters for builder
     */
    private function getRFMFilters(): array {
        return [
            ['id' => 'rfm_recency', 'name' => 'Recency Score', 'type' => 'number_range'],
            ['id' => 'rfm_frequency', 'name' => 'Frequency Score', 'type' => 'number_range'],
            ['id' => 'rfm_monetary', 'name' => 'Monetary Score', 'type' => 'number_range'],
            ['id' => 'rfm_segment', 'name' => 'RFM Segment', 'type' => 'select']
        ];
    }
    
    /**
     * Get predictive filters for builder
     */
    private function getPredictiveFilters(): array {
        return [
            ['id' => 'churn_probability', 'name' => 'Churn Probability', 'type' => 'range'],
            ['id' => 'ltv_prediction', 'name' => 'Predicted LTV', 'type' => 'number_range'],
            ['id' => 'next_purchase_likelihood', 'name' => 'Next Purchase Likelihood', 'type' => 'range']
        ];
    }
    
    /**
     * Calculate duration between two timestamps
     */
    private function calculateDuration(?string $start, ?string $end): string {
        if (!$start) return '';
        if (!$end) return $this->language->get('text_running');
        
        $duration = strtotime($end) - strtotime($start);
        
        if ($duration < 60) {
            return $duration . 's';
        } elseif ($duration < 3600) {
            return round($duration / 60, 1) . 'm';
        } else {
            return round($duration / 3600, 1) . 'h';
        }
    }
    
    /**
     * Get materialization status label
     */
    private function getMaterializationStatusLabel(string $status): string {
        $labels = [
            'success' => $this->language->get('text_success'),
            'failed' => $this->language->get('text_failed'),
            'running' => $this->language->get('text_running'),
            'cancelled' => $this->language->get('text_cancelled')
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
    
    /**
     * Get materialization status CSS class
     */
    private function getMaterializationStatusClass(string $status): string {
        $classes = [
            'success' => 'badge-success',
            'failed' => 'badge-danger',
            'running' => 'badge-primary',
            'cancelled' => 'badge-warning'
        ];
        
        return $classes[$status] ?? 'badge-secondary';
    }
    
    /**
     * Get breadcrumbs for builder page
     */
    private function getBuilderBreadcrumbs(): array {
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
            ],
            [
                'text' => $this->language->get('text_mas'),
                'href' => $this->url->link('extension/mas/dashboard', 'user_token=' . $this->session->data['user_token'])
            ],
            [
                'text' => $this->language->get('text_segments'),
                'href' => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'])
            ],
            [
                'text' => $this->language->get('text_segment_builder'),
                'href' => ''
            ]
        ];
    }
    
    /**
     * Trigger segment materialization
     */
    private function triggerMaterialization(int $segment_id): bool {
        // Access MAS library from registry
        $mas = $this->registry->get('mas');
        if ($mas && method_exists($mas, 'get')) {
            $segmentManager = $mas->get('segmentmanager');
            if ($segmentManager && method_exists($segmentManager, 'materializeSegment')) {
                $segmentManager->materializeSegment($segment_id);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Helper method to format bytes
     */
    private function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Scheduler management interface
     */
    public function scheduler(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        $this->load->model('extension/mas/scheduler');
        
        $this->document->setTitle($this->language->get('text_scheduler'));
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $data = [];
        
        // Get scheduled jobs
        $scheduled_jobs = $this->model_extension_mas_scheduler->getScheduledJobs();
        
        $data['scheduled_jobs'] = [];
        foreach ($scheduled_jobs as $job) {
            $data['scheduled_jobs'][] = [
                'job_id' => $job['job_id'],
                'segment_name' => $job['segment_name'],
                'segment_id' => $job['segment_id'],
                'schedule_type' => $job['schedule_type'],
                'schedule_value' => $job['schedule_value'],
                'next_run' => $job['next_run'] ? date($this->language->get('datetime_format'), strtotime($job['next_run'])) : '',
                'last_run' => $job['last_run'] ? date($this->language->get('datetime_format'), strtotime($job['last_run'])) : '',
                'status' => $job['status'],
                'status_label' => $this->getSchedulerStatusLabel($job['status']),
                'status_class' => $this->getSchedulerStatusClass($job['status']),
                'edit' => $this->url->link('extension/mas/segments.edit_schedule', 'user_token=' . $this->session->data['user_token'] . '&job_id=' . $job['job_id']),
                'delete' => $this->url->link('extension/mas/segments.delete_schedule', 'user_token=' . $this->session->data['user_token'] . '&job_id=' . $job['job_id'])
            ];
        }
        
        // Available segments for scheduling
        $segments = $this->model_extension_mas_segments->getSegments(['status' => 1]);
        $data['available_segments'] = [];
        foreach ($segments as $segment) {
            $data['available_segments'][] = [
                'segment_id' => $segment['segment_id'],
                'name' => $segment['name'],
                'type' => $segment['type']
            ];
        }
        
        // Schedule types
        $data['schedule_types'] = [
            'hourly' => $this->language->get('text_hourly'),
            'daily' => $this->language->get('text_daily'),
            'weekly' => $this->language->get('text_weekly'),
            'monthly' => $this->language->get('text_monthly'),
            'cron' => $this->language->get('text_custom_cron')
        ];
        
        // Set breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_segments'),
            'href' => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_scheduler'),
            'href' => ''
        ];
        
        // AJAX endpoints
        $data['ajax_add_schedule'] = $this->url->link('extension/mas/segments.add_schedule', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_update_schedule'] = $this->url->link('extension/mas/segments.update_schedule', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_delete_schedule'] = $this->url->link('extension/mas/segments.delete_schedule', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_run_now'] = $this->url->link('extension/mas/segments.run_schedule_now', 'user_token=' . $this->session->data['user_token']);
        
        $this->setCommonData($data);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/segments_scheduler', $data));
    }
    
    /**
     * AJAX endpoint to add schedule
     */
    public function add_schedule(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/scheduler');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('modify', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $segment_id = (int)($this->request->post['segment_id'] ?? 0);
        $schedule_type = $this->request->post['schedule_type'] ?? '';
        $schedule_value = $this->request->post['schedule_value'] ?? '';
        
        if (!$segment_id || !$schedule_type) {
            $this->response->setOutput(json_encode(['error' => 'Missing required fields']));
            return;
        }
        
        // Validate schedule
        if (!$this->validateSchedule($schedule_type, $schedule_value)) {
            $this->response->setOutput(json_encode(['error' => 'Invalid schedule configuration']));
            return;
        }
        
        $job_data = [
            'segment_id' => $segment_id,
            'schedule_type' => $schedule_type,
            'schedule_value' => $schedule_value,
            'status' => 'active',
            'created_by' => $this->user->getId(),
            'next_run' => $this->calculateNextRun($schedule_type, $schedule_value)
        ];
        
        $job_id = $this->model_extension_mas_scheduler->addScheduledJob($job_data);
        
        if ($job_id) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => $this->language->get('text_schedule_added'),
                'job_id' => $job_id
            ]));
        } else {
            $this->response->setOutput(json_encode(['error' => 'Failed to add schedule']));
        }
    }
    
    /**
     * AJAX endpoint to run schedule now
     */
    public function run_schedule_now(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/scheduler');
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode(['error' => 'Invalid request method']));
            return;
        }
        
        // Permission check
        if (!$this->user->hasPermission('modify', 'extension/mas/segments')) {
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));
            return;
        }
        
        $job_id = (int)($this->request->post['job_id'] ?? 0);
        
        if (!$job_id) {
            $this->response->setOutput(json_encode(['error' => 'Job ID required']));
            return;
        }
        
        $job = $this->model_extension_mas_scheduler->getScheduledJob($job_id);
        
        if (!$job) {
            $this->response->setOutput(json_encode(['error' => 'Job not found']));
            return;
        }
        
        // Trigger materialization
        $result = $this->triggerMaterialization($job['segment_id']);
        
        if ($result) {
            // Update last run time
            $this->model_extension_mas_scheduler->updateJobLastRun($job_id);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => $this->language->get('text_schedule_executed')
            ]));
        } else {
            $this->response->setOutput(json_encode(['error' => 'Failed to execute schedule']));
        }
    }
    
    /**
     * Validate schedule configuration
     */
    private function validateSchedule(string $type, string $value): bool {
        switch ($type) {
            case 'hourly':
                return is_numeric($value) && $value >= 1 && $value <= 23;
            case 'daily':
                return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value);
            case 'weekly':
                return in_array($value, ['0', '1', '2', '3', '4', '5', '6']);
            case 'monthly':
                return is_numeric($value) && $value >= 1 && $value <= 31;
            case 'cron':
                return $this->validateCronExpression($value);
        }
        return false;
    }
    
    /**
     * Validate cron expression
     */
    private function validateCronExpression(string $expression): bool {
        $parts = explode(' ', $expression);
        if (count($parts) !== 5) return false;
        
        // Basic cron validation - can be enhanced
        foreach ($parts as $part) {
            if (!preg_match('/^(\*|[0-9\-,\/]+)$/', $part)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Calculate next run time based on schedule
     */
    private function calculateNextRun(string $type, string $value): string {
        $now = new DateTime();
        
        switch ($type) {
            case 'hourly':
                $now->add(new DateInterval('PT' . $value . 'H'));
                break;
            case 'daily':
                $time = explode(':', $value);
                $now->setTime((int)$time[0], (int)$time[1], 0);
                if ($now <= new DateTime()) {
                    $now->add(new DateInterval('P1D'));
                }
                break;
            case 'weekly':
                $now->modify('next ' . ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][$value]);
                break;
            case 'monthly':
                $now->setDate($now->format('Y'), $now->format('n'), (int)$value);
                if ($now <= new DateTime()) {
                    $now->add(new DateInterval('P1M'));
                }
                break;
            case 'cron':
                // For cron expressions, you'd need a cron parser library
                // This is a simplified version
                $now->add(new DateInterval('PT1H')); // Default to 1 hour
                break;
        }
        
        return $now->format('Y-m-d H:i:s');
    }
    
    /**
     * Get scheduler status label
     */
    private function getSchedulerStatusLabel(string $status): string {
        $labels = [
            'active' => $this->language->get('text_active'),
            'inactive' => $this->language->get('text_inactive'),
            'paused' => $this->language->get('text_paused'),
            'error' => $this->language->get('text_error')
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
    
    /**
     * Get scheduler status CSS class
     */
    private function getSchedulerStatusClass(string $status): string {
        $classes = [
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'paused' => 'badge-warning',
            'error' => 'badge-danger'
        ];
        
        return $classes[$status] ?? 'badge-light';
    }
    
    /**
     * Advanced analytics dashboard
     */
    public function analytics(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/segments');
        $this->load->model('extension/mas/analytics');
        
        $this->document->setTitle($this->language->get('text_analytics'));
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $data = [];
        
        // Get analytics filters
        $date_range = $this->request->get['date_range'] ?? '30';
        $segment_ids = $this->request->get['segment_ids'] ?? [];
        
        if (!is_array($segment_ids)) {
            $segment_ids = explode(',', $segment_ids);
        }
        
        // Overall analytics
        $data['overview'] = $this->model_extension_mas_analytics->getOverviewAnalytics($date_range);
        
        // Segment comparison
        if (!empty($segment_ids)) {
            $data['segment_comparison'] = $this->model_extension_mas_analytics->getSegmentComparison($segment_ids, $date_range);
        }
        
        // Performance trends
        $data['performance_trends'] = $this->model_extension_mas_analytics->getPerformanceTrends($date_range);
        
        // Top performing segments
        $data['top_segments'] = $this->model_extension_mas_analytics->getTopPerformingSegments(10);
        
        // Materialization efficiency
        $data['materialization_efficiency'] = $this->model_extension_mas_analytics->getMaterializationEfficiency($date_range);
        
        // Available segments for comparison
        $segments = $this->model_extension_mas_segments->getSegments(['status' => 1]);
        $data['available_segments'] = [];
        foreach ($segments as $segment) {
            $data['available_segments'][] = [
                'segment_id' => $segment['segment_id'],
                'name' => $segment['name'],
                'type' => $segment['type'],
                'customer_count' => $segment['customer_count'] ?? 0
            ];
        }
        
        // Date range options
        $data['date_ranges'] = [
            '7' => $this->language->get('text_last_7_days'),
            '30' => $this->language->get('text_last_30_days'),
            '90' => $this->language->get('text_last_90_days'),
            '365' => $this->language->get('text_last_year')
        ];
        
        $data['selected_date_range'] = $date_range;
        $data['selected_segments'] = $segment_ids;
        
        // Set breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_segments'),
            'href' => $this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_analytics'),
            'href' => ''
        ];
        
        // AJAX endpoints
        $data['ajax_export_analytics'] = $this->url->link('extension/mas/segments.export_analytics', 'user_token=' . $this->session->data['user_token']);
        $data['ajax_refresh_analytics'] = $this->url->link('extension/mas/segments.refresh_analytics', 'user_token=' . $this->session->data['user_token']);
        
        $this->setCommonData($data);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/segments_analytics', $data));
    }
    
    /**
     * Export analytics data
     */
    public function export_analytics(): void {
        $this->load->language('extension/mas/segments');
        $this->load->model('extension/mas/analytics');
        
        // Permission check
        if (!$this->user->hasPermission('access', 'extension/mas/segments')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/mas/segments', 'user_token=' . $this->session->data['user_token']));
        }
        
        $date_range = $this->request->get['date_range'] ?? '30';
        $format = $this->request->get['format'] ?? 'csv';
        
        $analytics_data = $this->model_extension_mas_analytics->getFullAnalyticsExport($date_range);
        
        if ($format === 'csv') {
            $filename = 'segments_analytics_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Export segments data
            fputcsv($output, ['Segment Name', 'Type', 'Status', 'Customer Count', 'Conversion Rate', 'Total Revenue', 'Avg Order Value', 'Last Materialization']);
            
            foreach ($analytics_data['segments'] as $segment) {
                fputcsv($output, [
                    $segment['name'],
                    $segment['type'],
                    $segment['status'] ? 'Active' : 'Inactive',
                    $segment['customer_count'],
                    $segment['conversion_rate'] . '%',
                    $segment['total_revenue'],
                    $segment['avg_order_value'],
                    $segment['last_materialization']
                ]);
            }
            
            fclose($output);
            exit();
        }
        
        // JSON export
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="segments_analytics_' . date('Y-m-d') . '.json"');
        echo json_encode($analytics_data, JSON_PRETTY_PRINT);
        exit();
    }
}
