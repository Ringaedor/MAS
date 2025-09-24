<?php
namespace Opencart\Admin\Controller\Extension\Mas;

class Dashboard extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/mas/dashboard');
        
        if (!$this->user->hasPermission('access', 'extension/mas/dashboard')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
            return;
        }
        
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('../extension/mas/admin/view/stylesheet/mas/mas.css');
        $this->document->addStyle('../extension/mas/admin/view/stylesheet/mas/dashboard.css');
       
        $data = [];
        
        // Breadcrumbs - OpenCart standard
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mas/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        
        // Messages
        $data['error_warning'] = $this->session->data['error'] ?? '';
        $data['success'] = $this->session->data['success'] ?? '';
        
        // URLs - OpenCart standard with user_token
        $token = 'user_token=' . $this->session->data['user_token'];
        $data['home'] = $this->url->link('common/dashboard', $token);
        $data['refresh_url'] = $this->url->link('extension/mas/dashboard', $token);
        
        // Navigation URLs
        $data['segments'] = $this->url->link('extension/mas/segments', $token);
        $data['workflows'] = $this->url->link('extension/mas/workflows', $token);
        $data['messages'] = $this->url->link('extension/mas/messages', $token);
        $data['reports'] = $this->url->link('extension/mas/reports', $token);
        $data['privacy'] = $this->url->link('extension/mas/privacy', $token);
        $data['audit'] = $this->url->link('extension/mas/audit', $token);
        $data['settings'] = $this->url->link('extension/mas/settings', $token);
        
        // KPI Cards Data
        $data['kpis'] = $this->getKPICards();
        
        // Section Cards Data
        $data['sections'] = $this->getSectionCards();
        
        // Quick Actions
        $data['quick_actions'] = $this->getQuickActions();
        
        // System Health
        $data['health'] = $this->getSystemHealth();
        $data['usage'] = $this->getResourceUsage();
        
        // Recent Activity
        $data['recent_activity'] = $this->getRecentActivity();
        
        $data['user_token'] = $this->session->data['user_token'];
        
        // Standard OpenCart footer controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Clear session messages
        unset($this->session->data['error'], $this->session->data['success']);
        
        $this->response->setOutput($this->load->view('extension/mas/dashboard', $data));
    }
    
    /**
     * AJAX: Refresh system health
     */
    public function refreshHealth(): void {
        $json = [];
        
        if (!$this->user->hasPermission('access', 'extension/mas/dashboard')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $json['success'] = true;
            $json['health'] = $this->getSystemHealth();
            $json['usage'] = $this->getResourceUsage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * AJAX: Run maintenance
     */
    public function runMaintenance(): void {
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mas/dashboard')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $mas = $this->getMasInstance();
            if ($mas && $mas->maintenanceManager) {
                $result = $mas->maintenanceManager->runScheduledMaintenance();
                if ($result && $result['success']) {
                    // Log maintenance
                    if ($mas->auditLogger) {
                        $mas->auditLogger->logUserAction('maintenance_executed', [
                            'tasks_completed' => $result['tasks_completed'] ?? 0,
                            'execution_time' => $result['execution_time'] ?? 0,
                            'user_id' => $this->user->getId()
                        ]);
                    }
                    $json['success'] = true;
                    $json['message'] = $this->language->get('text_maintenance_completed');
                } else {
                    $json['error'] = $this->language->get('error_maintenance_failed');
                }
            } else {
                $json['error'] = $this->language->get('error_mas_unavailable');
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get MAS library instance with fail-safe
     */
    private function getMasInstance(): ?\Opencart\System\Library\Mas\Mas {
        static $mas = null;
        static $initialized = false;
        
        if (!$initialized) {
            $initialized = true;
            $mas = $this->registry->get('mas');
        }
        
        return $mas;
    }
    
    /**
     * Build KPI cards data array
     */
    private function getKPICards(): array {
        $mas = $this->getMasInstance();
        
        // Default empty state
        $defaults = [
            ['label' => $this->language->get('text_revenue'), 'value' => '--', 'change' => null, 'icon' => 'dollar-sign', 'color' => 'primary'],
            ['label' => $this->language->get('text_orders'), 'value' => '--', 'change' => null, 'icon' => 'shopping-cart', 'color' => 'info'],
            ['label' => $this->language->get('text_aov'), 'value' => '--', 'change' => null, 'icon' => 'chart-line', 'color' => 'success'],
            ['label' => $this->language->get('text_conversion_rate'), 'value' => '--', 'change' => null, 'icon' => 'percentage', 'color' => 'warning'],
            ['label' => $this->language->get('text_active_segments'), 'value' => '--', 'change' => null, 'icon' => 'users', 'color' => 'secondary'],
            ['label' => $this->language->get('text_active_campaigns'), 'value' => '--', 'change' => null, 'icon' => 'paper-plane', 'color' => 'dark']
        ];
        
        if (!$mas || !$mas->analyticsManager) {
            return $defaults;
        }
        
        // Get real KPI data
        $kpiData = $mas->analyticsManager->getKPISummary([
            'from' => date('Y-m-d', strtotime('-30 days')),
            'to' => date('Y-m-d')
        ]);
        
        return [
            [
                'label' => $this->language->get('text_revenue'),
                'value' => $this->currency->format($kpiData['revenue'] ?? 0, $this->config->get('config_currency')),
                'change' => $kpiData['revenue_change'] ?? null,
                'icon' => 'dollar-sign',
                'color' => 'primary'
            ],
            [
                'label' => $this->language->get('text_orders'),
                'value' => number_format($kpiData['orders'] ?? 0),
                'change' => $kpiData['orders_change'] ?? null,
                'icon' => 'shopping-cart',
                'color' => 'info'
            ],
            [
                'label' => $this->language->get('text_aov'),
                'value' => $this->currency->format($kpiData['aov'] ?? 0, $this->config->get('config_currency')),
                'change' => $kpiData['aov_change'] ?? null,
                'icon' => 'chart-line',
                'color' => 'success'
            ],
            [
                'label' => $this->language->get('text_conversion_rate'),
                'value' => number_format($kpiData['conversion_rate'] ?? 0, 2) . '%',
                'change' => $kpiData['conversion_change'] ?? null,
                'icon' => 'percentage',
                'color' => 'warning'
            ],
            [
                'label' => $this->language->get('text_active_segments'),
                'value' => number_format($this->getActiveSegmentsCount()),
                'change' => $this->getSegmentsGrowthRate(),
                'icon' => 'users',
                'color' => 'secondary'
            ],
            [
                'label' => $this->language->get('text_active_campaigns'),
                'value' => number_format($this->getActiveCampaignsCount()),
                'change' => $this->getCampaignsGrowthRate(),
                'icon' => 'paper-plane',
                'color' => 'dark'
            ]
        ];
    }
    
    /**
     * Build section cards data array
     */
    private function getSectionCards(): array {
        $token = 'user_token=' . $this->session->data['user_token'];
        
        return [
            [
                'title' => $this->language->get('text_segments'),
                'description' => $this->language->get('text_segments_description'),
                'url' => $this->url->link('extension/mas/segments', $token),
                'count' => $this->getSegmentsCount(),
                'last_update' => $this->getSegmentsLastUpdate(),
                'icon' => 'users',
                'color' => 'primary',
                'features' => [
                    ['icon' => 'magic', 'color' => 'info', 'text' => $this->language->get('text_ai_powered_builder')],
                    ['icon' => 'chart-bar', 'color' => 'success', 'text' => $this->language->get('text_realtime_analytics')],
                    ['icon' => 'filter', 'color' => 'warning', 'text' => $this->language->get('text_advanced_filters')]
                ]
            ],
            [
                'title' => $this->language->get('text_workflows'),
                'description' => $this->language->get('text_workflows_description'),
                'url' => $this->url->link('extension/mas/workflows', $token),
                'count' => $this->getWorkflowsCount(),
                'last_update' => $this->getWorkflowsLastUpdate(),
                'icon' => 'project-diagram',
                'color' => 'success',
                'features' => [
                    ['icon' => 'play', 'color' => 'success', 'text' => $this->language->get('text_automated_triggers')],
                    ['icon' => 'code-branch', 'color' => 'info', 'text' => $this->language->get('text_conditional_logic')],
                    ['icon' => 'clock', 'color' => 'warning', 'text' => $this->language->get('text_scheduled_actions')]
                ]
            ],
            [
                'title' => $this->language->get('text_messages'),
                'description' => $this->language->get('text_messages_description'),
                'url' => $this->url->link('extension/mas/messages', $token),
                'count' => $this->getMessagesQueueCount(),
                'last_update' => $this->getMessagesLastUpdate(),
                'icon' => 'envelope',
                'color' => 'info',
                'features' => [
                    ['icon' => 'at', 'color' => 'primary', 'text' => $this->language->get('text_email_campaigns')],
                    ['icon' => 'sms', 'color' => 'success', 'text' => $this->language->get('text_sms_marketing')],
                    ['icon' => 'bell', 'color' => 'warning', 'text' => $this->language->get('text_push_notifications')]
                ]
            ],
            [
                'title' => $this->language->get('text_reports'),
                'description' => $this->language->get('text_reports_description'),
                'url' => $this->url->link('extension/mas/reports', $token),
                'count' => $this->getReportsCount(),
                'last_update' => $this->getReportsLastUpdate(),
                'icon' => 'chart-pie',
                'color' => 'warning',
                'features' => [
                    ['icon' => 'chart-line', 'color' => 'success', 'text' => $this->language->get('text_performance_metrics')],
                    ['icon' => 'download', 'color' => 'info', 'text' => $this->language->get('text_export_reports')],
                    ['icon' => 'calendar', 'color' => 'primary', 'text' => $this->language->get('text_scheduled_reports')]
                ]
            ],
            [
                'title' => $this->language->get('text_privacy'),
                'description' => $this->language->get('text_privacy_description'),
                'url' => $this->url->link('extension/mas/privacy', $token),
                'count' => $this->getConsentTypesCount(),
                'last_update' => $this->getPrivacyLastUpdate(),
                'icon' => 'shield-alt',
                'color' => 'secondary',
                'features' => [
                    ['icon' => 'check-circle', 'color' => 'success', 'text' => $this->language->get('text_consent_management')],
                    ['icon' => 'file-export', 'color' => 'info', 'text' => $this->language->get('text_gdpr_export')],
                    ['icon' => 'history', 'color' => 'warning', 'text' => $this->language->get('text_audit_trail')]
                ]
            ],
            [
                'title' => $this->language->get('text_security'),
                'description' => $this->language->get('text_security_description'),
                'url' => $this->url->link('extension/mas/audit', $token),
                'count' => $this->getAuditLogsCount(),
                'last_update' => $this->getAuditLastUpdate(),
                'icon' => 'user-shield',
                'color' => 'danger',
                'features' => [
                    ['icon' => 'eye', 'color' => 'info', 'text' => $this->language->get('text_activity_monitoring')],
                    ['icon' => 'key', 'color' => 'warning', 'text' => $this->language->get('text_access_control')],
                    ['icon' => 'archive', 'color' => 'secondary', 'text' => $this->language->get('text_log_retention')]
                ]
            ]
        ];
    }
    
    /**
     * Build quick actions array
     */
    private function getQuickActions(): array {
        $token = 'user_token=' . $this->session->data['user_token'];
        
        return [
            ['text' => $this->language->get('button_create_segment'), 'url' => $this->url->link('extension/mas/segments', $token), 'icon' => 'users', 'color' => 'primary'],
            ['text' => $this->language->get('button_create_workflow'), 'url' => $this->url->link('extension/mas/workflows', $token), 'icon' => 'project-diagram', 'color' => 'success'],
            ['text' => $this->language->get('button_send_message'), 'url' => $this->url->link('extension/mas/messages', $token), 'icon' => 'envelope', 'color' => 'info'],
            ['text' => $this->language->get('button_generate_report'), 'url' => $this->url->link('extension/mas/reports', $token), 'icon' => 'chart-pie', 'color' => 'warning'],
            ['text' => $this->language->get('button_run_maintenance'), 'url' => 'javascript:runMaintenance()', 'icon' => 'tools', 'color' => 'secondary'],
            ['text' => $this->language->get('button_system_settings'), 'url' => $this->url->link('extension/mas/settings', $token), 'icon' => 'cog', 'color' => 'dark']
        ];
    }
    
    /**
     * Get system health components
     */
    private function getSystemHealth(): array {
        $mas = $this->getMasInstance();
        
        if (!$mas || !$mas->healthChecker) {
            return [
                ['name' => $this->language->get('text_mas_core'), 'status' => 'error', 'status_text' => $this->language->get('text_error')],
                ['name' => $this->language->get('text_database'), 'status' => 'error', 'status_text' => $this->language->get('text_error')],
                ['name' => $this->language->get('text_ai_gateway'), 'status' => 'disabled', 'status_text' => $this->language->get('text_disabled')],
                ['name' => $this->language->get('text_message_queue'), 'status' => 'error', 'status_text' => $this->language->get('text_error')]
            ];
        }
        
        $health = $mas->healthChecker->performHealthCheck();
        
        return [
            [
                'name' => $this->language->get('text_mas_core'),
                'status' => $health['mas_core'] ? 'healthy' : 'error',
                'status_text' => $health['mas_core'] ? $this->language->get('text_healthy') : $this->language->get('text_error')
            ],
            [
                'name' => $this->language->get('text_database'),
                'status' => $health['database'] ? 'healthy' : 'error',
                'status_text' => $health['database'] ? $this->language->get('text_healthy') : $this->language->get('text_error')
            ],
            [
                'name' => $this->language->get('text_ai_gateway'),
                'status' => $health['ai_gateway'] === null ? 'disabled' : ($health['ai_gateway'] ? 'healthy' : 'error'),
                'status_text' => $health['ai_gateway'] === null ? $this->language->get('text_disabled') : ($health['ai_gateway'] ? $this->language->get('text_healthy') : $this->language->get('text_error'))
            ],
            [
                'name' => $this->language->get('text_message_queue'),
                'status' => $health['message_queue'] ? 'healthy' : 'delayed',
                'status_text' => $health['message_queue'] ? $this->language->get('text_healthy') : $this->language->get('text_delayed')
            ]
        ];
    }
    
    /**
     * Get resource usage data
     */
    private function getResourceUsage(): array {
        $mas = $this->getMasInstance();
        
        if (!$mas || !$mas->healthChecker) {
            return ['memory' => 0, 'cpu' => 0];
        }
        
        $health = $mas->healthChecker->performHealthCheck();
        
        return [
            'memory' => min(100, max(0, (int)($health['memory_usage'] ?? 0))),
            'cpu' => min(100, max(0, (int)($health['cpu_usage'] ?? 0)))
        ];
    }
    
    /**
     * Get recent activity feed
     */
    private function getRecentActivity(): array {
        $mas = $this->getMasInstance();
        
        if (!$mas || !$mas->auditLogger) {
            return [];
        }
        
        $activities = $mas->auditLogger->getRecentActivities(10);
        $formatted = [];
        
        foreach ($activities as $activity) {
            $formatted[] = [
                'description' => $this->formatActivityDescription($activity),
                'user' => $activity['user_name'] ?? $this->language->get('text_system'),
                'timestamp' => date('M d, Y H:i', strtotime($activity['created_at'])),
                'icon' => $this->getActivityIcon($activity['action']),
                'color' => $this->getActivityColor($activity['action']),
                'url' => $this->getActivityUrl($activity)
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Format activity description with language support
     */
    private function formatActivityDescription(array $activity): string {
        $action = $activity['action'];
        $details = $activity['details'] ?? [];
        
        switch ($action) {
            case 'segment_created':
                return sprintf($this->language->get('text_activity_segment_created'), $details['segment_name'] ?? $this->language->get('text_unknown'));
            case 'segment_updated':
                return sprintf($this->language->get('text_activity_segment_updated'), $details['segment_name'] ?? $this->language->get('text_unknown'));
            case 'segment_deleted':
                return sprintf($this->language->get('text_activity_segment_deleted'), $details['segment_name'] ?? $this->language->get('text_unknown'));
            case 'workflow_activated':
                return sprintf($this->language->get('text_activity_workflow_activated'), $details['workflow_name'] ?? $this->language->get('text_unknown'));
            case 'campaign_sent':
                return sprintf($this->language->get('text_activity_campaign_sent'), $details['recipients_count'] ?? 0);
            case 'report_generated':
                return sprintf($this->language->get('text_activity_report_generated'), $details['report_name'] ?? $this->language->get('text_unknown'));
            default:
                return ucfirst(str_replace('_', ' ', $action));
        }
    }
    
    /**
     * Get activity icon mapping
     */
    private function getActivityIcon(string $action): string {
        $icons = [
            'segment_created' => 'plus',
            'segment_updated' => 'edit',
            'segment_deleted' => 'trash',
            'segment_materialized' => 'play',
            'workflow_activated' => 'project-diagram',
            'workflow_deactivated' => 'pause',
            'campaign_sent' => 'paper-plane',
            'report_generated' => 'chart-bar',
            'user_login' => 'sign-in-alt',
            'settings_updated' => 'cog'
        ];
        
        return $icons[$action] ?? 'info-circle';
    }
    
    /**
     * Get activity color mapping
     */
    private function getActivityColor(string $action): string {
        $colors = [
            'segment_created' => 'success',
            'segment_updated' => 'info',
            'segment_deleted' => 'danger',
            'segment_materialized' => 'primary',
            'workflow_activated' => 'success',
            'workflow_deactivated' => 'warning',
            'campaign_sent' => 'info',
            'report_generated' => 'primary',
            'user_login' => 'secondary',
            'settings_updated' => 'warning'
        ];
        
        return $colors[$action] ?? 'secondary';
    }
    
    /**
     * Get activity URL with proper routing
     */
    private function getActivityUrl(array $activity): ?string {
        $action = $activity['action'];
        $details = $activity['details'] ?? [];
        $token = 'user_token=' . $this->session->data['user_token'];
        
        switch ($action) {
            case 'segment_created':
            case 'segment_updated':
                if (isset($details['segment_id'])) {
                    return $this->url->link('extension/mas/segments', $token . '&segment_id=' . $details['segment_id']);
                }
                return $this->url->link('extension/mas/segments', $token);
            case 'workflow_activated':
            case 'workflow_deactivated':
                if (isset($details['workflow_id'])) {
                    return $this->url->link('extension/mas/workflows', $token . '&workflow_id=' . $details['workflow_id']);
                }
                return $this->url->link('extension/mas/workflows', $token);
            case 'campaign_sent':
                return $this->url->link('extension/mas/messages', $token);
            case 'report_generated':
                return $this->url->link('extension/mas/reports', $token);
            default:
                return null;
        }
    }
    
    // Count methods with fail-safe patterns
    private function getSegmentsCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->segmentManager) ? $mas->segmentManager->getTotalSegmentsCount() : 0;
    }
    
    private function getActiveSegmentsCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->segmentManager) ? $mas->segmentManager->getActiveSegmentsCount() : 0;
    }
    
    private function getSegmentsLastUpdate(): string {
        $mas = $this->getMasInstance();
        $lastUpdate = ($mas && $mas->segmentManager) ? $mas->segmentManager->getLastUpdateTime() : null;
        return $lastUpdate ? date('M d, H:i', strtotime($lastUpdate)) : date('M d, H:i');
    }
    
    private function getSegmentsGrowthRate(): ?float {
        $mas = $this->getMasInstance();
        return ($mas && $mas->analyticsManager) ? $mas->analyticsManager->getSegmentsGrowthRate() : null;
    }
    
    private function getWorkflowsCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->workflowManager) ? $mas->workflowManager->getTotalWorkflowsCount() : 0;
    }
    
    private function getWorkflowsLastUpdate(): string {
        $mas = $this->getMasInstance();
        $lastUpdate = ($mas && $mas->workflowManager) ? $mas->workflowManager->getLastUpdateTime() : null;
        return $lastUpdate ? date('M d, H:i', strtotime($lastUpdate)) : date('M d, H:i');
    }
    
    private function getMessagesQueueCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->messageManager) ? $mas->messageManager->getQueuedMessagesCount() : 0;
    }
    
    private function getMessagesLastUpdate(): string {
        $mas = $this->getMasInstance();
        $lastUpdate = ($mas && $mas->messageManager) ? $mas->messageManager->getLastUpdateTime() : null;
        return $lastUpdate ? date('M d, H:i', strtotime($lastUpdate)) : date('M d, H:i');
    }
    
    private function getActiveCampaignsCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->campaignManager) ? $mas->campaignManager->getActiveCampaignsCount() : 0;
    }
    
    private function getCampaignsGrowthRate(): ?float {
        $mas = $this->getMasInstance();
        return ($mas && $mas->analyticsManager) ? $mas->analyticsManager->getCampaignsGrowthRate() : null;
    }
    
    private function getReportsCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->reportManager) ? $mas->reportManager->getTotalReportsCount() : 0;
    }
    
    private function getReportsLastUpdate(): string {
        $mas = $this->getMasInstance();
        $lastUpdate = ($mas && $mas->reportManager) ? $mas->reportManager->getLastUpdateTime() : null;
        return $lastUpdate ? date('M d, H:i', strtotime($lastUpdate)) : date('M d, H:i');
    }
    
    private function getConsentTypesCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->privacyManager) ? $mas->privacyManager->getConsentTypesCount() : 0;
    }
    
    private function getPrivacyLastUpdate(): string {
        $mas = $this->getMasInstance();
        $lastUpdate = ($mas && $mas->privacyManager) ? $mas->privacyManager->getLastUpdateTime() : null;
        return $lastUpdate ? date('M d, H:i', strtotime($lastUpdate)) : date('M d, H:i');
    }
    
    private function getAuditLogsCount(): int {
        $mas = $this->getMasInstance();
        return ($mas && $mas->auditLogger) ? $mas->auditLogger->getTotalLogsCount() : 0;
    }
    
    private function getAuditLastUpdate(): string {
        $mas = $this->getMasInstance();
        $lastUpdate = ($mas && $mas->auditLogger) ? $mas->auditLogger->getLastAuditTime() : null;
        return $lastUpdate ? date('M d, H:i', strtotime($lastUpdate)) : date('M d, H:i');
    }
}