<?php
/**
 * MAS Module Controller for OpenCart 4.x
 * extension/mas/admin/controller/module/mas.php
 *
 * Complete controller with provider discovery, configuration management, and system operations
 * Enterprise-level functionality with comprehensive error handling and validation
 */

namespace Opencart\Admin\Controller\Extension\Mas\Module;

class Mas extends \Opencart\System\Engine\Controller {
    
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
     * Main configuration page
     *
     * @return void
     */
    public function index(): void {
        $this->load->language('extension/mas/module/mas');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Load model
        $this->load->model('extension/mas/module/mas');
        
        // Get configuration data
        $data['heading_title'] = $this->language->get('heading_title');
        
        // Breadcrumbs
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/mas/module/mas', 'user_token=' . $this->session->data['user_token'])
        ];
        
        // URLs
        $data['save'] = $this->url->link('extension/mas/module/mas.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');
        
        // User token
        $data['user_token'] = $this->session->data['user_token'];
        
        // Load current configuration
        $this->load->model('setting/setting');
        
        $data['module_mas_status'] = $this->config->get('module_mas_status');
        $data['module_mas_admin_email'] = $this->config->get('module_mas_admin_email') ?: $this->config->get('config_email');
        $data['module_mas_timezone'] = $this->config->get('module_mas_timezone') ?: 'UTC';
        $data['module_mas_language'] = $this->config->get('module_mas_language') ?: $this->config->get('config_language');
        
        // Cache settings
        $data['module_mas_cache_enabled'] = $this->config->get('module_mas_cache_enabled');
        $data['module_mas_cache_ttl'] = $this->config->get('module_mas_cache_ttl') ?: 3600;
        $data['module_mas_debug'] = $this->config->get('module_mas_debug');
        
        // Performance settings
        $data['module_mas_batch_size'] = $this->config->get('module_mas_batch_size') ?: 100;
        $data['module_mas_max_execution_time'] = $this->config->get('module_mas_max_execution_time') ?: 300;
        $data['module_mas_rate_limit_enabled'] = $this->config->get('module_mas_rate_limit_enabled');
        $data['module_mas_rate_limit_requests'] = $this->config->get('module_mas_rate_limit_requests') ?: 1000;
        
        // Security settings
        $data['module_mas_api_enabled'] = $this->config->get('module_mas_api_enabled');
        $data['module_mas_api_token'] = $this->config->get('module_mas_api_token') ?: $this->generateRandomToken(64);
        $data['module_mas_webhook_secret'] = $this->config->get('module_mas_webhook_secret') ?: $this->generateRandomToken(48);
        $data['module_mas_cors_enabled'] = $this->config->get('module_mas_cors_enabled');
        $data['module_mas_cors_domains'] = $this->config->get('module_mas_cors_domains') ?: '';
        
        // Discover available providers
        $data['discovered_providers'] = $this->discoverProviders();
        
        // Get configured providers
        $data['provider_configurations'] = $this->getProviderConfigurations();
        
        // System status
        $data['system_status'] = $this->getSystemStatus();
        
        // Available timezones
        $data['timezones'] = $this->getTimezones();
        
        // Available languages
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        
        // Load language strings
        foreach ($this->language->all() as $key => $value) {
            $data[$key] = $value;
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/mas/module/mas', $data));
    }
    
    /**
     * Save configuration
     *
     * @return void
     */
    public function save(): void {
        $this->load->language('extension/mas/module/mas');
        
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        }
        
        if (!$json) {
            $this->load->model('setting/setting');
            
            // Sanitize and validate input
            $settings = [];
            
            // Basic settings
            $settings['module_mas_status'] = isset($this->request->post['module_mas_status']) ? (int)$this->request->post['module_mas_status'] : 0;
            $settings['module_mas_admin_email'] = filter_var($this->request->post['module_mas_admin_email'] ?? '', FILTER_VALIDATE_EMAIL) ?: $this->config->get('config_email');
            $settings['module_mas_timezone'] = $this->request->post['module_mas_timezone'] ?? 'UTC';
            $settings['module_mas_language'] = $this->request->post['module_mas_language'] ?? $this->config->get('config_language');
            
            // Cache settings
            $settings['module_mas_cache_enabled'] = isset($this->request->post['module_mas_cache_enabled']) ? 1 : 0;
            $settings['module_mas_cache_ttl'] = max(300, min(86400, (int)($this->request->post['module_mas_cache_ttl'] ?? 3600)));
            $settings['module_mas_debug'] = isset($this->request->post['module_mas_debug']) ? 1 : 0;
            
            // Performance settings
            $settings['module_mas_batch_size'] = max(10, min(1000, (int)($this->request->post['module_mas_batch_size'] ?? 100)));
            $settings['module_mas_max_execution_time'] = max(60, min(3600, (int)($this->request->post['module_mas_max_execution_time'] ?? 300)));
            $settings['module_mas_rate_limit_enabled'] = isset($this->request->post['module_mas_rate_limit_enabled']) ? 1 : 0;
            $settings['module_mas_rate_limit_requests'] = max(100, min(10000, (int)($this->request->post['module_mas_rate_limit_requests'] ?? 1000)));
            
            // Security settings
            $settings['module_mas_api_enabled'] = isset($this->request->post['module_mas_api_enabled']) ? 1 : 0;
            $settings['module_mas_api_token'] = $this->request->post['module_mas_api_token'] ?? $this->generateRandomToken(64);
            $settings['module_mas_webhook_secret'] = $this->request->post['module_mas_webhook_secret'] ?? $this->generateRandomToken(48);
            $settings['module_mas_cors_enabled'] = isset($this->request->post['module_mas_cors_enabled']) ? 1 : 0;
            $settings['module_mas_cors_domains'] = $this->request->post['module_mas_cors_domains'] ?? '';
            
            // Save settings
            $this->model_setting_setting->editSetting('module_mas', $settings);
            
            // Install/uninstall based on status
            $this->load->model('extension/mas/module/mas');
            
            if ($settings['module_mas_status']) {
                $this->model_extension_mas_module_mas->install();
                
                // Add permissions to current user group
                if ($this->user->getGroupId()) {
                    $this->model_extension_mas_module_mas->setPermissions($this->user->getGroupId());
                }
            }
            
            $json['success'] = $this->language->get('text_success');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Discover available providers in the MAS extension directory
     *
     * @return array<string, array> Discovered providers grouped by type
     */
    private function discoverProviders(): array {
        $providers = [];
        
        // Provider directory path
        $provider_path = DIR_EXTENSION . 'mas/system/library/mas/providers/';
        
        if (!is_dir($provider_path)) {
            return $providers;
        }
        
        // Scan provider types (email, sms, push, ai, etc.)
        $provider_types = array_filter(glob($provider_path . '*'), 'is_dir');
        
        foreach ($provider_types as $type_dir) {
            $type = basename($type_dir);
            
            // Skip hidden directories and files
            if (substr($type, 0, 1) === '.') {
                continue;
            }
            
            $providers[$type] = [];
            
            // Scan for provider files in each type directory
            $provider_files = glob($type_dir . '/*.php');
            
            foreach ($provider_files as $provider_file) {
                $provider_name = basename($provider_file, '.php');
                
                // Skip abstract classes and interfaces
                if (in_array($provider_name, ['Abstract', 'Interface', 'Base'])) {
                    continue;
                }
                
                // Include provider file to get metadata
                if (is_readable($provider_file)) {
                    require_once $provider_file;
                    
                    $class_name = 'Opencart\\Extension\\Mas\\System\\Library\\Mas\\Providers\\' . ucfirst($type) . '\\' . ucfirst($provider_name);
                    
                    // Check if class exists and has required methods
                    if (class_exists($class_name)) {
                        $reflection = new \ReflectionClass($class_name);
                        
                        // Skip abstract classes
                        if ($reflection->isAbstract()) {
                            continue;
                        }
                        
                        // Get provider metadata
                        $provider_info = $this->getProviderMetadata($class_name, $reflection);
                        
                        if ($provider_info) {
                            $provider_info['class'] = $class_name;
                            $provider_info['file'] = $provider_file;
                            $provider_info['type'] = $type;
                            
                            $providers[$type][] = $provider_info;
                        }
                    }
                }
            }
        }
        
        return $providers;
    }
    
    /**
     * Extract metadata from provider class
     *
     * @param string $class_name Provider class name
     * @param \ReflectionClass $reflection Class reflection
     * @return array|null Provider metadata
     */
    private function getProviderMetadata(string $class_name, \ReflectionClass $reflection): ?array {
        // Check if class has required methods
        $required_methods = ['getName', 'getDescription', 'getVersion'];
        
        foreach ($required_methods as $method) {
            if (!$reflection->hasMethod($method)) {
                return null;
            }
        }
        
        // Create temporary instance to get metadata (if constructor allows)
        $constructor = $reflection->getConstructor();
        $instance = null;
        
        if (!$constructor || $constructor->getNumberOfRequiredParameters() === 0) {
            if ($reflection->isInstantiable()) {
                $instance = $reflection->newInstance();
            }
        }
        
        $metadata = [];
        
        if ($instance) {
            $metadata['name'] = method_exists($instance, 'getName') ? $instance->getName() : basename($reflection->getShortName());
            $metadata['description'] = method_exists($instance, 'getDescription') ? $instance->getDescription() : '';
            $metadata['version'] = method_exists($instance, 'getVersion') ? $instance->getVersion() : '1.0.0';
            $metadata['capabilities'] = method_exists($instance, 'getCapabilities') ? $instance->getCapabilities() : [];
            $metadata['setup_schema'] = method_exists($instance, 'getSetupSchema') ? $instance->getSetupSchema() : null;
        } else {
            // Fallback to class constants or static methods
            $metadata['name'] = defined($class_name . '::NAME') ? constant($class_name . '::NAME') : $reflection->getShortName();
            $metadata['description'] = defined($class_name . '::DESCRIPTION') ? constant($class_name . '::DESCRIPTION') : '';
            $metadata['version'] = defined($class_name . '::VERSION') ? constant($class_name . '::VERSION') : '1.0.0';
            $metadata['capabilities'] = defined($class_name . '::CAPABILITIES') ? constant($class_name . '::CAPABILITIES') : [];
            $metadata['setup_schema'] = null;
        }
        
        // Additional metadata from docblock
        $doc_comment = $reflection->getDocComment();
        if ($doc_comment) {
            // Extract additional info from docblock if needed
            if (preg_match('/@author\s+(.+)/i', $doc_comment, $matches)) {
                $metadata['author'] = trim($matches[1]);
            }
            
            if (preg_match('/@license\s+(.+)/i', $doc_comment, $matches)) {
                $metadata['license'] = trim($matches[1]);
            }
        }
        
        return $metadata;
    }
    
    /**
     * Get configured provider configurations
     *
     * @return array<string, array> Configured providers
     */
    private function getProviderConfigurations(): array {
        $configurations = [];
        
        // Get all MAS provider settings from database
        $this->load->model('setting/setting');
        
        $query = $this->db->query("
            SELECT `key`, `value` FROM `" . DB_PREFIX . "setting`
            WHERE `key` LIKE 'module_mas_providers_%'
            AND `store_id` = 0
            ORDER BY `key`
        ");
        
        foreach ($query->rows as $row) {
            $key_parts = explode('_', $row['key']);
            
            // Expected format: module_mas_providers_TYPE_NAME
            if (count($key_parts) >= 5) {
                $type = $key_parts[3];
                $name = $key_parts[4];
                
                if (!isset($configurations[$type])) {
                    $configurations[$type] = [];
                }
                
                $config_data = json_decode($row['value'], true);
                if ($config_data) {
                    $configurations[$type][$name] = $config_data;
                }
            }
        }
        
        return $configurations;
    }
    
    /**
     * Get system status information
     *
     * @return array<string, mixed> System status
     */
    private function getSystemStatus(): array {
        $status = [];
        
        // MAS enabled status
        $status['mas_enabled'] = (bool)$this->config->get('module_mas_status');
        
        // Provider counts
        $provider_configs = $this->getProviderConfigurations();
        $status['providers_configured'] = 0;
        $status['providers_active'] = 0;
        
        foreach ($provider_configs as $type => $providers) {
            $status['providers_configured'] += count($providers);
            
            foreach ($providers as $provider) {
                if (isset($provider['enabled']) && $provider['enabled']) {
                    $status['providers_active']++;
                }
            }
        }
        
        // Cache status
        if (function_exists('opcache_get_status')) {
            $opcache_status = opcache_get_status();
            $status['cache_status'] = $opcache_status ? 'working' : 'disabled';
        } else {
            $status['cache_status'] = 'unavailable';
        }
        
        // Memory usage
        $status['memory_usage'] = memory_get_usage(true);
        $status['memory_peak'] = memory_get_peak_usage(true);
        
        // Disk space
        $status['disk_free'] = disk_free_space(DIR_SYSTEM);
        $status['disk_total'] = disk_total_space(DIR_SYSTEM);
        
        return $status;
    }
    
    /**
     * Get available timezones
     *
     * @return array<string> Timezone list
     */
    private function getTimezones(): array {
        $timezones = [];
        
        $timezone_identifiers = \DateTimeZone::listIdentifiers();
        
        foreach ($timezone_identifiers as $timezone) {
            $timezones[] = $timezone;
        }
        
        return $timezones;
    }
    
    /**
     * Generate random token
     *
     * @param int $length Token length
     * @return string Generated token
     */
    private function generateRandomToken(int $length = 32): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $token = '';
        
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $token;
    }
    
    /**
     * Test provider connection
     *
     * @return void
     */
    public function test_provider(): void {
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $provider_class = $this->request->post['provider_class'] ?? '';
            $config = json_decode($this->request->post['config'] ?? '{}', true);
            
            if (!$provider_class || !class_exists($provider_class)) {
                $json['error'] = 'Invalid provider class';
            } else {
                $provider = new $provider_class($config);
                
                if (method_exists($provider, 'testConnection')) {
                    $result = $provider->testConnection();
                    if ($result) {
                        $json['success'] = true;
                        $json['message'] = 'Connection test successful';
                    } else {
                        $json['error'] = 'Connection test failed';
                    }
                } else {
                    $json['success'] = true;
                    $json['message'] = 'Provider configured successfully';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Save provider configuration
     *
     * @return void
     */
    public function save_provider(): void {
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $provider_type = $this->request->post['provider_type'] ?? '';
            $provider_name = $this->request->post['provider_name'] ?? '';
            $config = json_decode($this->request->post['config'] ?? '{}', true);
            $enabled = isset($this->request->post['enabled']) ? (bool)$this->request->post['enabled'] : false;
            
            if (!$provider_type || !$provider_name) {
                $json['error'] = 'Invalid provider type or name';
            } else {
                $setting_key = 'module_mas_providers_' . $provider_type . '_' . $provider_name;
                
                $provider_config = [
                    'enabled' => $enabled,
                    'config' => $config,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->load->model('setting/setting');
                $this->model_setting_setting->editSettingValue('module_mas', $setting_key, $provider_config);
                
                $json['success'] = true;
                $json['message'] = 'Provider configuration saved successfully';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Delete provider configuration
     *
     * @return void
     */
    public function delete_provider(): void {
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $provider_type = $this->request->post['provider_type'] ?? '';
            $provider_name = $this->request->post['provider_name'] ?? '';
            
            if (!$provider_type || !$provider_name) {
                $json['error'] = 'Invalid provider type or name';
            } else {
                $setting_key = 'module_mas_providers_' . $provider_type . '_' . $provider_name;
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $this->db->escape($setting_key) . "' AND `store_id` = 0");
                
                $json['success'] = true;
                $json['message'] = 'Provider configuration deleted successfully';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Run system tests
     *
     * @return void
     */
    public function test_system(): void {
        $json = [];
        $json['results'] = [];
        
        if (!$this->user->hasPermission('access', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Test database connection
            $this->db->query("SELECT 1");
            $json['results']['database'] = ['success' => true, 'message' => 'Working'];
            
            // Test PHP extensions
            $required_extensions = ['json', 'curl', 'mbstring'];
            foreach ($required_extensions as $ext) {
                $json['results']['php_' . $ext] = [
                    'success' => extension_loaded($ext),
                    'message' => extension_loaded($ext) ? 'Available' : 'Missing'
                ];
            }
            
            // Test file permissions
            $test_dirs = [DIR_CACHE, DIR_LOGS];
            foreach ($test_dirs as $dir) {
                $writable = is_writable($dir);
                $json['results']['permissions_' . basename($dir)] = [
                    'success' => $writable,
                    'message' => $writable ? 'Writable' : 'Not writable'
                ];
            }
            
            $json['success'] = true;
            $json['message'] = 'System tests completed';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Flush cache
     *
     * @return void
     */
    public function flush_cache(): void {
        $json = [];
        
        if (!$this->user->hasPermission('modify', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Clear OpenCart cache
            $files = glob(DIR_CACHE . '*');
            
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            
            // Clear OPcache if available
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            $json['success'] = true;
            $json['message'] = 'Cache flushed successfully';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Refresh system status
     *
     * @return void
     */
    public function refresh_status(): void {
        $json = [];
        
        if (!$this->user->hasPermission('access', 'extension/mas/module/mas')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $json['success'] = true;
            $json['system_status'] = $this->getSystemStatus();
            $json['message'] = 'Status refreshed successfully';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
