<?php
/**
 * File: admin/controller/startup/mas.php  
 * MAS startup controller for OpenCart 4.x admin backend.
 * Registers MAS library and services in registry following OpenCart conventions.
 */

namespace Opencart\Admin\Controller\Startup;

class Mas extends \Opencart\System\Engine\Controller {

    /**
     * Entry point executed automatically by OpenCart during admin startup.
     */
    public function index(): void {
        if (!$this->config->get('module_mas_status')) {
            return;
        }
        
        // MAS è già caricato dal system/startup.php, registra solo nel registry
        if (!$this->registry->has('mas') && class_exists('\Opencart\System\Library\Mas\Mas')) {
            $mas = new \Opencart\System\Library\Mas\Mas($this->registry);
            $this->registry->set('mas', $mas);
            
            // Register all MAS services
            $this->registerMasServices();
        }
    }

    /**
     * Registers all MAS services in OpenCart registry for direct access.
     * Service locator pattern that avoids repeated container calls.
     */
    private function registerMasServices(): void {
        $mas = $this->registry->get('mas');
        if (!$mas) {
            return;
        }

        try {
            $container = $mas->getContainer();

            // Map MAS services to OpenCart registry keys
            $services = [
                'mas_dashboardservice'  => 'mas.dashboardservice',
                'mas_segmentmanager'    => 'mas.segmentmanager', 
                'mas_workflowmanager'   => 'mas.workflowmanager',
                'mas_auditlogger'       => 'mas.auditlogger',
                'mas_consentmanager'    => 'mas.consentmanager',
                'mas_providermanager'   => 'mas.providermanager',
                'mas_aigateway'         => 'mas.aigateway',
                'mas_eventdispatcher'   => 'mas.eventdispatcher'
            ];

            // Lazy registration of services (only if available in container)
            foreach ($services as $registryKey => $containerId) {
                if (!$this->registry->has($registryKey) && $container->has($containerId)) {
                    $this->registry->set($registryKey, $container->get($containerId));
                }
            }

        } catch (\Throwable $e) {
            $this->log->write('[MAS] Service registration failed: ' . $e->getMessage());
        }
    }
}
