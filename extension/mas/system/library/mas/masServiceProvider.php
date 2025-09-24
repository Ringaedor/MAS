<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Service Provider - Advanced Service Registration System
 *
 * Registra e inizializza tutti i servizi MAS avanzati all'interno del ServiceContainer
 * e del Registry OpenCart come singleton enterprise-ready con configurazione centralizzata,
 * per garantire robustezza, performance, estendibilità e integrazione tra livelli applicativi.
 *
 * Features:
 * - Configuration merging with environment overrides
 * - Enterprise service container integration
 * - Comprehensive service registration with proper namespacing
 * - OpenCart Registry shortcuts for controller access
 * - Error handling and service validation
 * - Performance optimization with lazy loading
 * - Service lifecycle management
 * - Dependency resolution and injection
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
 * Enterprise MAS Service Provider
 *
 * Comprehensive service registration system that handles all MAS service initialization,
 * configuration merging, dependency injection, and OpenCart integration. Provides
 * enterprise-grade service management with proper error handling, performance
 * optimization, and extensibility features for production environments.
 *
 * Service Categories:
 * - Core Infrastructure (Event System, Configuration, Logging)
 * - Gateway Services (AI, Messaging, Payment Processing)
 * - Business Logic (Workflow, Segmentation, Campaign Management)
 * - Analytics & Reporting (Dashboard, Export, Analytics)
 * - Compliance & Security (Audit, Consent, Security)
 * - Integration Services (API, Webhooks, External Services)
 *
 * Usage:
 * MasServiceProvider::register($registry);
 *
 * The provider automatically:
 * - Loads and merges configuration from multiple sources
 * - Initializes the enterprise service container
 * - Registers all services with proper dependency injection
 * - Creates OpenCart Registry shortcuts for easy access
 * - Handles service lifecycle and performance optimization
 */
class MasServiceProvider
{
    /**
     * @var array<string> List of core services that must be registered
     */
    protected static array $coreServices = [
        'event_dispatcher', 'event_queue', 'ai_gateway', 'message_gateway',
        'payment_gateway', 'audit_logger', 'dashboard_service', 'csv_exporter',
        'workflow_manager', 'segment_manager', 'campaign_manager', 'consent_manager',
        'provider_manager', 'template_engine', 'analytics_manager'
    ];
    
    /**
     * @var array<string> Services that should have Registry shortcuts
     */
    protected static array $registryShortcuts = [
        'event_dispatcher', 'ai_gateway', 'message_gateway', 'payment_gateway',
        'audit_logger', 'workflow_manager', 'segment_manager', 'consent_manager'
    ];
    
    /**
     * @var bool Service registration status flag
     */
    protected static bool $registered = false;
    
    /**
     * Registra tutti i servizi MAS nel ServiceContainer enterprise e nel Registry OpenCart
     *
     * Performs comprehensive service registration including configuration loading,
     * service container initialization, dependency injection setup, and OpenCart
     * integration with proper error handling and performance optimization.
     *
     * @param \Opencart\System\Engine\Registry $registry Registry di OpenCart
     * @throws Exception If service registration fails or configuration is invalid
     */
    public static function register($registry): void
    {
        // Prevent double registration
        if (self::$registered) {
            return;
        }
        
        $startTime = microtime(true);
        
        // 1. Advanced Configuration Loading with Multiple Sources
        $cfg = self::loadConfiguration($registry);
        
        // 2. Initialize Enterprise Service Container
        $container = self::initializeServiceContainer($registry, $cfg);
        
        // 3. Register Core Infrastructure Services
        self::registerCoreInfrastructure($container, $registry, $cfg);
        
        // 4. Register Gateway Services
        self::registerGatewayServices($container, $registry, $cfg);
        
        // 5. Register Business Logic Services
        self::registerBusinessServices($container, $registry, $cfg);
        
        // 6. Register Analytics & Reporting Services
        self::registerAnalyticsServices($container, $registry, $cfg);
        
        // 7. Register Compliance & Security Services
        self::registerComplianceServices($container, $registry, $cfg);
        
        // 8. Register Integration Services
        self::registerIntegrationServices($container, $registry, $cfg);
        
        // 9. Validate Service Registration
        self::validateServiceRegistration($container);
        
        // 10. Create OpenCart Registry Shortcuts
        self::createRegistryShortcuts($container, $registry);
        
        // Mark as registered and log performance
        self::$registered = true;
        $duration = microtime(true) - $startTime;
        
        if (isset($container) && $container->has('mas.audit_logger')) {
            $container->get('mas.audit_logger')->logSystem(
                'service_provider_registration_complete',
                [
                    'duration' => $duration,
                    'services_registered' => count(self::$coreServices),
                    'memory_usage' => memory_get_usage(true)
                ],
                'info'
                );
        }
    }
    
    /**
     * Loads and merges configuration from multiple sources
     *
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @return array<string, mixed> Merged configuration array
     * @throws Exception If configuration loading fails
     */
    protected static function loadConfiguration($registry): array
    {
        // Load base configuration file
        $fileConfig = DIR_SYSTEM . 'library/mas/config.php';
        $fileCfg = [];
        
        if (file_exists($fileConfig)) {
            $fileCfg = include $fileConfig;
            if (!is_array($fileCfg)) {
                throw Exception::create(
                    'MAS configuration file must return an array',
                    'config',
                    'critical',
                    ['config_file' => $fileConfig]
                    );
            }
        } else {
            throw Exception::create(
                'MAS configuration file not found',
                'config',
                'critical',
                ['expected_path' => $fileConfig]
                );
        }
        
        // Load store-specific overrides
        $storeCfg = $registry->get('config')->get('mas_config') ?? [];
        
        // Load environment-specific overrides
        $envCfg = self::loadEnvironmentConfiguration();
        
        // Merge configurations with priority: env > store > file
        $cfg = array_replace_recursive($fileCfg, $storeCfg, $envCfg);
        
        // Validate configuration structure
        self::validateConfiguration($cfg);
        
        return $cfg;
    }
    
    /**
     * Loads environment-specific configuration overrides
     *
     * @return array<string, mixed> Environment configuration
     */
    protected static function loadEnvironmentConfiguration(): array
    {
        $envCfg = [];
        
        // Parse environment variables for MAS configuration
        foreach ($_ENV as $key => $value) {
            if (strpos($key, 'MAS_') === 0) {
                // Convert MAS_AI_GATEWAY_DEFAULT_PROVIDER to ai_gateway.default_provider
                $configPath = strtolower(substr($key, 4)); // Remove MAS_ prefix
                $configPath = str_replace('_', '.', $configPath);
                
                // Set nested configuration value
                self::setNestedValue($envCfg, $configPath, $value);
            }
        }
        
        return $envCfg;
    }
    
    /**
     * Sets a nested configuration value using dot notation
     *
     * @param array $array Target array
     * @param string $path Dot notation path
     * @param mixed $value Value to set
     */
    protected static function setNestedValue(array &$array, string $path, $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;
        
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        
        $current = $value;
    }
    
    /**
     * Validates configuration structure and required sections
     *
     * @param array<string, mixed> $cfg Configuration array
     * @throws Exception If configuration is invalid
     */
    protected static function validateConfiguration(array $cfg): void
    {
        $requiredSections = [
            'ai_gateway', 'message_gateway', 'payment_gateway',
            'event_dispatcher', 'audit_logger', 'dashboard_service'
        ];
        
        foreach ($requiredSections as $section) {
            if (!isset($cfg[$section]) || !is_array($cfg[$section])) {
                throw Exception::create(
                    "Required configuration section '{$section}' is missing or invalid",
                    'config',
                    'critical',
                    ['section' => $section, 'config_keys' => array_keys($cfg)]
                );
            }
        }
    }
    
    /**
     * Initializes the enterprise service container
     *
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     * @return ServiceContainer Initialized container
     */
    protected static function initializeServiceContainer($registry, array $cfg): ServiceContainer
    {
        $debugMode = $cfg['debug']['enabled'] ?? false;
        $container = new ServiceContainer($debugMode);
        
        // Register OpenCart core services
        $container->singleton('registry', $registry);
        $container->singleton('loader', $registry->get('load'));
        $container->singleton('config', $registry->get('config'));
        $container->singleton('log', $registry->get('log'));
        $container->singleton('cache', $registry->get('cache'));
        $container->singleton('db', $registry->get('db'));
        $container->singleton('session', $registry->get('session'));
        
        // Register MAS configuration
        $container->singleton('mas.config', $cfg);
        
        return $container;
    }
    
    /**
     * Registers core infrastructure services
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     */
    protected static function registerCoreInfrastructure(ServiceContainer $container, $registry, array $cfg): void
    {
        // Event Dispatcher with enhanced capabilities
        $container->singleton('mas.event_dispatcher', function($c) use ($cfg) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Events\EventDispatcher(
                $c,
                $cfg['event_dispatcher'] ?? []
                );
        });
            
            // Event Queue for asynchronous processing
            $container->singleton('mas.event_queue', function($c) use ($cfg) {
                return new \Opencart\Extension\Mas\System\Library\Mas\Events\EventQueue(
                    $c,
                    $cfg['event_queue'] ?? []
                    );
            });
                
                // Audit Logger for compliance and monitoring
                $container->singleton('mas.audit_logger', function($c) use ($cfg) {
                    return new \Opencart\Extension\Mas\System\Library\Mas\Audit\AuditLogger(
                        $c,
                        $cfg['audit_logger'] ?? []
                        );
                });
    }
    
    /**
     * Registers gateway services for external integrations
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     */
    protected static function registerGatewayServices(ServiceContainer $container, $registry, array $cfg): void
    {
        // AI Gateway for artificial intelligence services
        $container->singleton('mas.ai_gateway', function($c) use ($cfg) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Services\Ai\AiGateway(
                $c,
                $cfg['ai_gateway'] ?? []
                );
        });
            
            // Message Gateway for multi-channel messaging
            $container->singleton('mas.message_gateway', function($c) use ($cfg) {
                return new \Opencart\Extension\Mas\System\Library\Mas\Services\Message\MessageGateway(
                    $c,
                    $cfg['message_gateway'] ?? []
                    );
            });
                
                // Payment Gateway for financial transactions
                $container->singleton('mas.payment_gateway', function($c) use ($cfg) {
                    return new \Opencart\Extension\Mas\System\Library\Mas\Services\Payment\PaymentGateway(
                        $c,
                        $cfg['payment_gateway'] ?? []
                        );
                });
    }
    
    /**
     * Registers business logic services
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     */
    protected static function registerBusinessServices(ServiceContainer $container, $registry, array $cfg): void
    {
        // Workflow Manager for business process automation
        $container->singleton('mas.workflow_manager', function($c) use ($cfg) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Workflow\WorkflowManager(
                $c,
                $cfg['workflow_manager'] ?? []
                );
        });
            
            // Segment Manager for customer segmentation
            $container->singleton('mas.segment_manager', function($c) use ($cfg) {
                return new \Opencart\Extension\Mas\System\Library\Mas\Segmentation\SegmentManager(
                    $c,
                    $cfg['segment_manager'] ?? []
                    );
            });
                
                // Campaign Manager for marketing campaigns
                $container->singleton('mas.campaign_manager', function($c) use ($cfg) {
                    return new \Opencart\Extension\Mas\System\Library\Mas\Campaign\CampaignManager(
                        $c,
                        $cfg['campaign_manager'] ?? []
                        );
                });
                    
                    // Provider Manager for service provider management
                    $container->singleton('mas.provider_manager', function($c) use ($cfg) {
                        return new \Opencart\Extension\Mas\System\Library\Mas\Provider\ProviderManager(
                            $c,
                            $cfg['provider_manager'] ?? []
                            );
                    });
    }
    
    /**
     * Registers analytics and reporting services
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     */
    protected static function registerAnalyticsServices(ServiceContainer $container, $registry, array $cfg): void
    {
        // Dashboard Service for KPI and analytics
        $container->singleton('mas.dashboard_service', function($c) use ($cfg) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Reporting\DashboardService(
                $c,
                $cfg['dashboard_service'] ?? []
                );
        });
            
            // CSV Exporter for data export functionality
            $container->singleton('mas.csv_exporter', function($c) use ($cfg) {
                return new \Opencart\Extension\Mas\System\Library\Mas\Reporting\CsvExporter(
                    $c,
                    $cfg['csv_exporter'] ?? []
                    );
            });
                
                // Analytics Manager for advanced analytics
                $container->singleton('mas.analytics_manager', function($c) use ($cfg) {
                    return new \Opencart\Extension\Mas\System\Library\Mas\Analytics\AnalyticsManager(
                        $c,
                        $cfg['analytics_manager'] ?? []
                        );
                });
    }
    
    /**
     * Registers compliance and security services
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     */
    protected static function registerComplianceServices(ServiceContainer $container, $registry, array $cfg): void
    {
        // Consent Manager for GDPR compliance
        $container->singleton('mas.consent_manager', function($c) use ($cfg) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Consent\ConsentManager(
                $c,
                $cfg['consent_manager'] ?? []
                );
        });
            
            // Security Manager for system security
            $container->singleton('mas.security_manager', function($c) use ($cfg) {
                return new \Opencart\Extension\Mas\System\Library\Mas\Security\SecurityManager(
                    $c,
                    $cfg['security'] ?? []
                    );
            });
    }
    
    /**
     * Registers integration services for external connectivity
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     * @param array<string, mixed> $cfg Configuration array
     */
    protected static function registerIntegrationServices(ServiceContainer $container, $registry, array $cfg): void
    {
        // Template Engine for dynamic content generation
        $container->singleton('mas.template_engine', function($c) use ($cfg) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Template\TemplateEngine(
                $c,
                $cfg['template_engine'] ?? []
                );
        });
            
            // Webhook Manager for webhook handling
            $container->singleton('mas.webhook_manager', function($c) use ($cfg) {
                return new \Opencart\Extension\Mas\System\Library\Mas\Integration\WebhookManager(
                    $c,
                    $cfg['webhooks'] ?? []
                    );
            });
                
                // API Manager for REST API functionality
                $container->singleton('mas.api_manager', function($c) use ($cfg) {
                    return new \Opencart\Extension\Mas\System\Library\Mas\Api\ApiManager(
                        $c,
                        $cfg['api'] ?? []
                        );
                });
    }
    
    /**
     * Validates that all core services were registered successfully
     *
     * @param ServiceContainer $container Service container to validate
     * @throws Exception If required services are missing
     */
    protected static function validateServiceRegistration(ServiceContainer $container): void
    {
        $missingServices = [];
        
        foreach (self::$coreServices as $service) {
            $serviceId = 'mas.' . $service;
            if (!$container->has($serviceId)) {
                $missingServices[] = $serviceId;
            }
        }
        
        if (!empty($missingServices)) {
            throw Exception::create(
                'Required MAS services failed to register',
                'config',
                'critical',
                [
                    'missing_services' => $missingServices,
                    'registered_services' => $container->getServiceIds()
                ]
                );
        }
    }
    
    /**
     * Creates OpenCart Registry shortcuts for commonly used services
     *
     * @param ServiceContainer $container Service container
     * @param \Opencart\System\Engine\Registry $registry OpenCart registry
     */
    protected static function createRegistryShortcuts(ServiceContainer $container, $registry): void
    {
        foreach (self::$registryShortcuts as $service) {
            $serviceId = 'mas.' . $service;
            $registryKey = 'mas_' . $service;
            
            if ($container->has($serviceId)) {
                $registry->set($registryKey, $container->get($serviceId));
            }
        }
        
        // Special shortcuts for frequently accessed services
        $registry->set('mas', $container);
        $registry->set('mas_container', $container);
    }
    
    /**
     * Gets service registration status
     *
     * @return bool True if services are registered, false otherwise
     */
    public static function isRegistered(): bool
    {
        return self::$registered;
    }
    
    /**
     * Gets list of core services
     *
     * @return array<string> Core service identifiers
     */
    public static function getCoreServices(): array
    {
        return self::$coreServices;
    }
    
    /**
     * Gets list of registry shortcuts
     *
     * @return array<string> Registry shortcut identifiers
     */
    public static function getRegistryShortcuts(): array
    {
        return self::$registryShortcuts;
    }
    
    /**
     * Resets registration status (useful for testing)
     */
    public static function reset(): void
    {
        self::$registered = false;
    }
}
