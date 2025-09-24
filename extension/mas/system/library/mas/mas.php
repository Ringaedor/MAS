<?php
/**
 * MAS - Marketing Automation Suite
 * Enterprise Bootstrap & Service Locator for OpenCart 4.x
 *
 * Entry point for the MAS library: initializes the dependency injection
 * container, registers core services and managers, and establishes a unified,
 * fail-safe access point for all MAS functionality, ensuring robust integration
 * in the OpenCart ecosystem.
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
 * Main MAS bootstrapper for the Marketing Automation Suite.
 *
 * Handles dependency injection, manager/service registration, auto-loading,
 * and robust error isolation for all MAS features in OpenCart 4.x.
 */
class Mas
{
    /**
     * @var \Opencart\System\Engine\Registry
     */
    protected $registry;
    
    /**
     * @var \Opencart\System\Engine\Loader
     */
    protected $loader;
    
    /**
     * @var \Opencart\System\Engine\Config
     */
    protected $config;
    
    /**
     * @var \Opencart\System\Engine\Log
     */
    protected $log;
    
    /**
     * @var \Opencart\System\Library\Cache
     */
    protected $cache;
    
    /**
     * @var ServiceContainer
     */
    protected $container;
    
    /**
     * Initializes MAS library with OpenCart core services.
     *
     * @param \Opencart\System\Engine\Registry $registry
     */
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->loader   = $registry->get('load');
        $this->config   = $registry->get('config');
        $this->log      = $registry->get('log');
        $this->cache    = $registry->get('cache');
        
        $this->boot();
    }
    
    /**
     * Boots MAS: loads config, initializes DI container, registers core services.
     */
    protected function boot(): void
    {
        // Load MAS configuration file (always present in custom extension path)
        $this->loader->config('mas/config');
        
        // Initialize the enterprise-grade DI container
        $this->container = new ServiceContainer();
        
        // Register OpenCart base services
        $this->container->set('registry',   $this->registry);
        $this->container->set('loader',     $this->loader);
        $this->container->set('config',     $this->config);
        $this->container->set('log',        $this->log);
        $this->container->set('cache',      $this->cache);
        
        // Register all core MAS managers and services
        $this->registerProviderManager();
        $this->registerWorkflowManager();
        $this->registerSegmentManager();
        $this->registerAIGateway();
        $this->registerEventDispatcher();
        $this->registerConsentManager();
        $this->registerDashboardService();
        $this->registerAuditLogger();
    }
    
    /**
     * Registers ProviderManager as a closure (lazy loaded DI).
     */
    protected function registerProviderManager(): void
    {
        $this->container->set('mas.provider_manager', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Provider\ProviderManager($c);
        });
    }
    
    /**
     * Registers WorkflowManager as a closure.
     */
    protected function registerWorkflowManager(): void
    {
        $this->container->set('mas.workflow_manager', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Workflow\WorkflowManager($c);
        });
    }
    
    /**
     * Registers SegmentManager as a closure.
     */
    protected function registerSegmentManager(): void
    {
        $this->container->set('mas.segment_manager', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Segmentation\SegmentManager($c);
        });
    }
    
    /**
     * Registers AIGateway as a closure.
     */
    protected function registerAIGateway(): void
    {
        $this->container->set('mas.ai_gateway', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Service\Ai\AIGateway($c);
        });
    }
    
    /**
     * Registers EventDispatcher as a closure.
     */
    protected function registerEventDispatcher(): void
    {
        $this->container->set('mas.event_dispatcher', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Event\EventDispatcher($c);
        });
    }
    
    /**
     * Registers ConsentManager as a closure.
     */
    protected function registerConsentManager(): void
    {
        $this->container->set('mas.consent_manager', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Consent\ConsentManager($c);
        });
    }
    
    /**
     * Registers DashboardService as a closure.
     */
    protected function registerDashboardService(): void
    {
        $this->container->set('mas.dashboard_service', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Reporting\DashboardService($c);
        });
    }
    
    /**
     * Registers AuditLogger as a closure.
     */
    protected function registerAuditLogger(): void
    {
        $this->container->set('mas.audit_logger', function ($c) {
            return new \Opencart\Extension\Mas\System\Library\Mas\Audit\AuditLogger($c);
        });
    }
    
    /**
     * Returns the service container instance.
     *
     * @return ServiceContainer
     */
    public function getContainer(): ServiceContainer
    {
        return $this->container;
    }
    
    /**
     * Magic method for direct access to container services (fail-safe).
     *
     * @param string $key Service identifier
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->container->get($key);
    }
}
