<?php
/**
 * MAS Module Language File - English (UK)
 * extension/mas/admin/language/en-gb/module/mas.php
 *
 * Complete language strings for MAS Marketing Automation Suite module
 * Enterprise-level translations with comprehensive coverage
 */

// Module identification and basic info
$_['heading_title'] = 'MAS - Marketing Automation Suite';
$_['text_extension'] = 'Extensions';
$_['text_module'] = 'Modules';

// Navigation and UI elements
$_['text_configuration'] = 'Configuration';
$_['text_general'] = 'General';
$_['text_providers'] = 'Providers';
$_['text_performance'] = 'Performance';
$_['text_security'] = 'Security';
$_['text_system_status'] = 'System Status';

// Basic settings section
$_['text_basic_settings'] = 'Basic Settings';
$_['text_status'] = 'Status';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_admin_email'] = 'Administrator Email';
$_['text_timezone'] = 'Default Timezone';
$_['text_language'] = 'Default Language';

// Cache settings section
$_['text_cache_settings'] = 'Cache Settings';
$_['text_enable_cache'] = 'Enable Caching';
$_['text_cache_ttl'] = 'Cache TTL (Time to Live)';
$_['text_seconds'] = 'seconds';
$_['text_debug_mode'] = 'Debug Mode';
$_['text_flush_cache'] = 'Flush Cache';

// Provider management
$_['text_available_providers'] = 'Available Providers';
$_['text_configured_providers'] = 'Configured Providers';
$_['text_configure'] = 'Configure';
$_['text_configure_provider'] = 'Configure Provider';
$_['text_provider_name'] = 'Provider Name';
$_['text_provider'] = 'Provider';
$_['text_configured'] = 'Configured';
$_['text_active'] = 'Active';
$_['text_refresh'] = 'Refresh';
$_['text_no_providers_found'] = 'No Providers Found';
$_['text_no_providers_help'] = 'Install MAS provider extensions to enable third-party integrations.';
$_['text_no_providers_configured'] = 'No Providers Configured';
$_['text_configure_providers_help'] = 'Select providers from the left panel to configure them.';
$_['text_updated'] = 'Updated';

// Provider configuration modal
$_['text_test_configuration'] = 'Test Configuration';
$_['text_test_connection'] = 'Test Connection';
$_['text_edit_configuration'] = 'Edit Configuration';
$_['text_delete_configuration'] = 'Delete Configuration';
$_['text_testing'] = 'Testing...';
$_['text_test_passed'] = 'Test Passed';
$_['text_test_failed'] = 'Test Failed';
$_['text_connection_error'] = 'Connection Error';
$_['text_saving'] = 'Saving...';
$_['text_save_error'] = 'Save Error';
$_['text_confirm_delete'] = 'Are you sure you want to delete this provider configuration?';

// Performance settings
$_['text_execution_settings'] = 'Execution Settings';
$_['text_batch_size'] = 'Batch Size';
$_['text_records'] = 'records';
$_['text_max_execution_time'] = 'Max Execution Time';
$_['text_rate_limiting'] = 'Rate Limiting';
$_['text_enable_rate_limiting'] = 'Enable Rate Limiting';
$_['text_max_requests_hour'] = 'Max Requests per Hour';
$_['text_hour'] = 'hour';

// Security settings
$_['text_api_settings'] = 'API Settings';
$_['text_enable_api'] = 'Enable REST API';
$_['text_api_token'] = 'API Token';
$_['text_webhook_secret'] = 'Webhook Secret';
$_['text_cors_settings'] = 'CORS Settings';
$_['text_enable_cors'] = 'Enable CORS';
$_['text_allowed_domains'] = 'Allowed Domains';
$_['text_confirm_regenerate_token'] = 'Regenerating the API token will invalidate all existing API connections. Continue?';
$_['text_confirm_regenerate_secret'] = 'Regenerating the webhook secret will invalidate all existing webhook endpoints. Continue?';

// System status
$_['text_system_overview'] = 'System Overview';
$_['text_mas_status'] = 'MAS Status';
$_['text_cache'] = 'Cache';
$_['text_memory_usage'] = 'Memory Usage';
$_['text_system_tests'] = 'System Tests';
$_['text_run_system_tests'] = 'Run System Tests';
$_['text_running_tests'] = 'Running Tests...';
$_['text_testing_system'] = 'Testing system components...';
$_['text_test_results'] = 'Test Results';
$_['text_test_error'] = 'Error running tests';
$_['text_flushing'] = 'Flushing...';

// Quick actions
$_['text_quick_actions'] = 'Quick Actions';
$_['text_open_dashboard'] = 'Open Dashboard';
$_['text_manage_segments'] = 'Manage Segments';
$_['text_manage_workflows'] = 'Manage Workflows';

// Button labels
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';
$_['button_close'] = 'Close';
$_['button_test'] = 'Test';
$_['button_configure'] = 'Configure';
$_['button_edit'] = 'Edit';
$_['button_delete'] = 'Delete';

// Help text and tooltips
$_['help_status'] = 'Enable or disable the entire MAS Marketing Automation Suite.';
$_['help_admin_email'] = 'Email address for system notifications and alerts.';
$_['help_timezone'] = 'Default timezone for scheduled campaigns and automation rules.';
$_['help_language'] = 'Default language for system messages and customer communications.';
$_['help_cache'] = 'Enable caching to improve performance. Disable only for debugging.';
$_['help_cache_ttl'] = 'How long cached data remains valid (300-86400 seconds).';
$_['help_debug'] = 'Enable verbose logging for troubleshooting. Disable in production.';
$_['help_batch_size'] = 'Number of records processed in each batch operation (10-1000).';
$_['help_max_execution_time'] = 'Maximum time for long-running operations (60-3600 seconds).';
$_['help_rate_limiting'] = 'Prevent API abuse by limiting request rates.';
$_['help_max_requests'] = 'Maximum API requests allowed per hour (100-10000).';
$_['help_api'] = 'Enable REST API access for external applications.';
$_['help_api_token'] = 'Secure token for API authentication. Keep confidential.';
$_['help_webhook_secret'] = 'Secret key for webhook signature verification.';
$_['help_cors'] = 'Allow cross-origin requests from specified domains.';
$_['help_cors_domains'] = 'Enter one domain per line (e.g., https://example.com).';
$_['help_provider_name'] = 'Unique name to identify this provider configuration.';

// Success messages
$_['text_success'] = 'Configuration saved successfully!';
$_['text_provider_saved'] = 'Provider configuration saved successfully!';
$_['text_provider_deleted'] = 'Provider configuration deleted successfully!';
$_['text_cache_flushed'] = 'Cache flushed successfully!';
$_['text_test_success'] = 'Connection test successful!';
$_['text_system_test_passed'] = 'All system tests passed!';
$_['text_system_test_failed'] = 'Some system tests failed. Check the details below.';

// Error messages
$_['error_permission'] = 'Warning: You do not have permission to modify MAS configuration!';
$_['error_email'] = 'Administrator email must be a valid email address!';
$_['error_numeric'] = 'Field must be a valid number!';
$_['error_range'] = 'Value must be between %d and %d!';
$_['error_required'] = 'This field is required!';
$_['error_invalid_provider'] = 'Invalid provider configuration!';
$_['error_provider_exists'] = 'A provider with this name already exists!';
$_['error_connection_failed'] = 'Provider connection test failed!';
$_['error_save_failed'] = 'Failed to save configuration!';
$_['error_delete_failed'] = 'Failed to delete configuration!';

// Provider types
$_['text_email_providers'] = 'Email Providers';
$_['text_sms_providers'] = 'SMS Providers';
$_['text_push_providers'] = 'Push Notification Providers';
$_['text_ai_providers'] = 'AI/ML Providers';
$_['text_payment_providers'] = 'Payment Providers';

// Additional status indicators
$_['text_working'] = 'Working';
$_['text_error'] = 'Error';
$_['text_unknown'] = 'Unknown';
$_['text_pending'] = 'Pending';
$_['text_processing'] = 'Processing';
$_['text_completed'] = 'Completed';
$_['text_failed'] = 'Failed';

// Advanced settings
$_['text_advanced_settings'] = 'Advanced Settings';
$_['text_logging_level'] = 'Logging Level';
$_['text_log_level_error'] = 'Error Only';
$_['text_log_level_warning'] = 'Warning & Error';
$_['text_log_level_info'] = 'Info, Warning & Error';
$_['text_log_level_debug'] = 'All Messages';
$_['text_maintenance_mode'] = 'Maintenance Mode';
$_['text_backup_settings'] = 'Backup Settings';
$_['text_export_config'] = 'Export Configuration';
$_['text_import_config'] = 'Import Configuration';

// Module menu integration
$_['text_menu_mas'] = 'Marketing Automation';

// Validation messages
$_['text_validation_required'] = 'This field is required';
$_['text_validation_email'] = 'Please enter a valid email address';
$_['text_validation_url'] = 'Please enter a valid URL';
$_['text_validation_number'] = 'Please enter a valid number';
$_['text_validation_min'] = 'Value must be at least %d';
$_['text_validation_max'] = 'Value must be no more than %d';

// Installation and setup
$_['text_installation'] = 'Installation';
$_['text_setup_wizard'] = 'Setup Wizard';
$_['text_initial_setup'] = 'Initial Setup';
$_['text_database_setup'] = 'Database Setup';
$_['text_permissions_setup'] = 'Permissions Setup';
$_['text_setup_complete'] = 'Setup Complete';
$_['text_welcome_message'] = 'Welcome to MAS Marketing Automation Suite!';
$_['text_getting_started'] = 'Getting Started';

// Documentation links
$_['text_documentation'] = 'Documentation';
$_['text_api_documentation'] = 'API Documentation';
$_['text_user_guide'] = 'User Guide';
$_['text_developer_guide'] = 'Developer Guide';
$_['text_support'] = 'Support';
$_['text_changelog'] = 'Changelog';

// Version and licensing
$_['text_version'] = 'Version';
$_['text_license'] = 'License';
$_['text_copyright'] = 'Copyright';
$_['text_developed_by'] = 'Developed by';

// Dashboard shortcuts
$_['text_dashboard_shortcuts'] = 'Dashboard Shortcuts';
$_['text_create_segment'] = 'Create Segment';
$_['text_create_workflow'] = 'Create Workflow';
$_['text_send_campaign'] = 'Send Campaign';
$_['text_view_reports'] = 'View Reports';

// System requirements
$_['text_system_requirements'] = 'System Requirements';
$_['text_php_version'] = 'PHP Version';
$_['text_mysql_version'] = 'MySQL Version';
$_['text_required_extensions'] = 'Required Extensions';
$_['text_recommended_settings'] = 'Recommended Settings';

// Feature toggles
$_['text_feature_toggles'] = 'Feature Toggles';
$_['text_enable_ai_features'] = 'Enable AI Features';
$_['text_enable_advanced_segmentation'] = 'Enable Advanced Segmentation';
$_['text_enable_workflow_builder'] = 'Enable Workflow Builder';
$_['text_enable_multi_channel'] = 'Enable Multi-channel Messaging';
$_['text_enable_analytics'] = 'Enable Advanced Analytics';

// Integration settings
$_['text_integration_settings'] = 'Integration Settings';
$_['text_webhook_endpoints'] = 'Webhook Endpoints';
$_['text_api_endpoints'] = 'API Endpoints';
$_['text_external_services'] = 'External Services';
$_['text_third_party_tools'] = 'Third-party Tools';

// GDPR and compliance
$_['text_gdpr_compliance'] = 'GDPR Compliance';
$_['text_data_retention'] = 'Data Retention';
$_['text_consent_management'] = 'Consent Management';
$_['text_privacy_settings'] = 'Privacy Settings';
$_['text_audit_logging'] = 'Audit Logging';

// Performance monitoring
$_['text_performance_monitoring'] = 'Performance Monitoring';
$_['text_system_metrics'] = 'System Metrics';
$_['text_resource_usage'] = 'Resource Usage';
$_['text_queue_status'] = 'Queue Status';
$_['text_error_rates'] = 'Error Rates';

// Backup and recovery
$_['text_backup_restore'] = 'Backup & Restore';
$_['text_create_backup'] = 'Create Backup';
$_['text_restore_backup'] = 'Restore Backup';
$_['text_automatic_backups'] = 'Automatic Backups';
$_['text_backup_schedule'] = 'Backup Schedule';

// Multi-store support
$_['text_multi_store'] = 'Multi-store';
$_['text_store_settings'] = 'Store Settings';
$_['text_global_settings'] = 'Global Settings';
$_['text_store_specific'] = 'Store Specific';
$_['text_inherit_global'] = 'Inherit Global';

// Localization
$_['text_localization'] = 'Localization';
$_['text_date_format'] = 'Date Format';
$_['text_time_format'] = 'Time Format';
$_['text_currency_format'] = 'Currency Format';
$_['text_number_format'] = 'Number Format';
$_['text_regional_settings'] = 'Regional Settings';

// Additional provider management strings
$_['text_provider_capabilities'] = 'Capabilities';
$_['text_provider_version'] = 'Version';
$_['text_provider_author'] = 'Author';
$_['text_provider_description'] = 'Description';
$_['text_provider_requirements'] = 'Requirements';
$_['text_provider_documentation'] = 'Documentation';

// Form field types for dynamic provider configuration
$_['text_field_text'] = 'Text';
$_['text_field_email'] = 'Email';
$_['text_field_password'] = 'Password';
$_['text_field_textarea'] = 'Textarea';
$_['text_field_select'] = 'Select';
$_['text_field_checkbox'] = 'Checkbox';
$_['text_field_radio'] = 'Radio';
$_['text_field_file'] = 'File';
$_['text_field_url'] = 'URL';
$_['text_field_number'] = 'Number';

// Provider test results
$_['text_test_connection_success'] = 'Connection test successful';
$_['text_test_authentication_success'] = 'Authentication successful';
$_['text_test_send_success'] = 'Test message sent successfully';
$_['text_test_connection_failed'] = 'Connection test failed';
$_['text_test_authentication_failed'] = 'Authentication failed';
$_['text_test_send_failed'] = 'Test message failed to send';

// System test components
$_['text_test_database'] = 'Database';
$_['text_test_cache'] = 'Cache';
$_['text_test_file_permissions'] = 'File Permissions';
$_['text_test_php_extensions'] = 'PHP Extensions';
$_['text_test_memory_limit'] = 'Memory Limit';
$_['text_test_curl'] = 'cURL';
$_['text_test_openssl'] = 'OpenSSL';
$_['text_test_json'] = 'JSON';
$_['text_test_mbstring'] = 'Mbstring';

// Miscellaneous
$_['text_loading'] = 'Loading...';
$_['text_please_wait'] = 'Please wait...';
$_['text_none'] = 'None';
$_['text_all'] = 'All';
$_['text_default'] = 'Default';
$_['text_custom'] = 'Custom';
$_['text_optional'] = 'Optional';
$_['text_required_field'] = 'Required field';
$_['text_invalid_value'] = 'Invalid value';
$_['text_out_of_range'] = 'Value out of range';
