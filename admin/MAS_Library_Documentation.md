# DOCUMENTAZIONE TECNICA LIBRERIA MAS
## Marketing Automation Suite per OpenCart 4.x

### Panoramica Generale
La libreria MAS (Marketing Automation Suite) è un sistema completo di automazione marketing integrato in OpenCart 4.x. La libreria utilizza un'architettura basata su dependency injection container, fornendo servizi modulari per AI, workflow, segmentazione, eventi, audit e reporting.

---

## SYSTEM/LIBRARY/MAS/MAS.PHP

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Classe centrale di bootstrap per la libreria MAS. Carica la configurazione, inizializza il container di dependency injection e registra tutti i servizi principali della suite.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| registry | Registry | Istanza del registry di OpenCart, iniettata dal costruttore |
| loader | Loader | Loader di OpenCart, prelevato dal registry |
| config | Config | Oggetto di configurazione di OpenCart, prelevato dal registry |
| log | Log | Logger di OpenCart, prelevato dal registry |
| cache | Cache | Cache di OpenCart, prelevata dal registry |
| container | ServiceContainer | Container di dependency injection per i servizi MAS |

### Metodi

**__construct(Registry $registry)**  
Input:
- $registry (Opencart\System\Engine\Registry): Registry di OpenCart

Tipo di ritorno: void

Descrizione:  
Costruttore della classe. Inietta le dipendenze principali di OpenCart e avvia il metodo di bootstrap interno.

**boot(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Esegue il boot della libreria. Carica la configurazione MAS tramite il loader di OpenCart, inizializza il container di dependency injection, registra i servizi di base OpenCart e tutti i principali manager e gateway della suite.

**registerProviderManager(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.provider_manager come closure che restituisce una nuova istanza di ProviderManager.

**registerWorkflowManager(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.workflow_manager come closure che restituisce una nuova istanza di WorkflowManager.

**registerSegmentManager(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.segment_manager come closure che restituisce una nuova istanza di SegmentManager.

**registerAIGateway(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.ai_gateway come closure che restituisce una nuova istanza di AIGateway.

**registerEventDispatcher(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.event_dispatcher come closure che restituisce una nuova istanza di EventDispatcher.

**registerConsentManager(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.consent_manager come closure che restituisce una nuova istanza di ConsentManager.

**registerDashboardService(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.dashboard_service come closure che restituisce una nuova istanza di DashboardService.

**registerAuditLogger(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Registra nel container il servizio mas.audit_logger come closure che restituisce una nuova istanza di AuditLogger.

**getContainer(): ServiceContainer**  
Input: Nessuno  
Tipo di ritorno: ServiceContainer

Descrizione:  
Restituisce l'istanza del container di dependency injection, consentendo l'accesso diretto ai servizi registrati.

**__get(string $key): mixed**  
Input:
- $key (string): Nome del servizio richiesto

Tipo di ritorno: mixed

Descrizione:  
Consente l'accesso diretto ai servizi MAS tramite sintassi di proprietà. Se il servizio richiesto non esiste, viene lanciata un'eccezione.

---

## SYSTEM/LIBRARY/MAS/SERVICECONTAINER.PHP

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Container di dependency injection per la gestione dei servizi MAS. Supporta registrazione, risoluzione e gestione del ciclo di vita dei servizi con pattern singleton e factory.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| services | array<string, mixed> | Servizi registrati e loro definizioni |
| instances | array<string, mixed> | Cache delle istanze singleton |
| singletons | array<string, bool> | Flag di singleton per i servizi |
| aliases | array<string, string> | Alias dei servizi |
| tags | array<string, array> | Tag per raggruppamento servizi |

### Metodi

**set(string $id, $definition, bool $singleton = true, array $tags = []): void**  
Input:
- $id (string): Identificatore del servizio
- $definition (mixed): Definizione del servizio (callable, object, class name)
- $singleton (bool): Se trattare come singleton (default: true)
- $tags (array): Tag opzionali per raggruppamento

Tipo di ritorno: void

Descrizione:  
Registra un servizio nel container. Solleva MasException se l'ID del servizio è già registrato.

**get(string $id): mixed**  
Input:
- $id (string): Identificatore del servizio

Tipo di ritorno: mixed

Descrizione:  
Recupera un servizio dal container. Gestisce automaticamente alias e cache delle istanze singleton.

**has(string $id): bool**  
Input:
- $id (string): Identificatore del servizio

Tipo di ritorno: bool

Descrizione:  
Verifica se un servizio è registrato nel container.

**alias(string $alias, string $id): void**  
Input:
- $alias (string): Nome dell'alias
- $id (string): ID del servizio originale

Tipo di ritorno: void

Descrizione:  
Registra un alias per un servizio esistente.

**factory(string $id, callable $factory, array $tags = []): void**  
Input:
- $id (string): Identificatore del servizio
- $factory (callable): Funzione factory
- $tags (array): Tag opzionali

Tipo di ritorno: void

Descrizione:  
Registra un servizio factory che crea sempre nuove istanze.

**singleton(string $id, $definition, array $tags = []): void**  
Input:
- $id (string): Identificatore del servizio
- $definition (mixed): Definizione del servizio
- $tags (array): Tag opzionali

Tipo di ritorno: void

Descrizione:  
Registra un servizio singleton.

**getByTag(string $tag): array<string, mixed>**  
Input:
- $tag (string): Nome del tag

Tipo di ritorno: array<string, mixed>

Descrizione:  
Ottiene tutti i servizi che corrispondono al tag specificato.

**extend(string $id, callable $extender): void**  
Input:
- $id (string): Identificatore del servizio
- $extender (callable): Funzione per estendere il servizio

Tipo di ritorno: void

Descrizione:  
Estende la definizione di un servizio esistente.

**remove(string $id): void**  
Input:
- $id (string): Identificatore del servizio

Tipo di ritorno: void

Descrizione:  
Rimuove un servizio dal container.

**clear(): void**  
Input: Nessuno  
Tipo di ritorno: void

Descrizione:  
Cancella tutti i servizi dal container.

**getServiceIds(): array<string>**  
Input: Nessuno  
Tipo di ritorno: array<string>

Descrizione:  
Ottiene tutti gli ID dei servizi registrati.

**getAliases(): array<string, string>**  
Input: Nessuno  
Tipo di ritorno: array<string, string>

Descrizione:  
Ottiene tutti gli alias registrati mappati agli ID dei servizi.

**resolve(string $id): mixed**  
Input:
- $id (string): Identificatore del servizio

Tipo di ritorno: mixed

Descrizione:  
Risolve una definizione di servizio in un'istanza (metodo protetto).

**resolveDefinition($definition): mixed**  
Input:
- $definition (mixed): Definizione del servizio

Tipo di ritorno: mixed

Descrizione:  
Risolve una definizione di servizio (metodo protetto).

**__get(string $id): mixed**  
Input:
- $id (string): Identificatore del servizio

Tipo di ritorno: mixed

Descrizione:  
Metodo magico per accedere ai servizi come proprietà.

**__isset(string $id): bool**  
Input:
- $id (string): Identificatore del servizio

Tipo di ritorno: bool

Descrizione:  
Metodo magico per verificare se un servizio esiste.

---

## SYSTEM/LIBRARY/MAS/EXCEPTION.PHP

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Classe base per tutte le eccezioni MAS con supporto per contesto avanzato, categorizzazione errori e gestione specializzata per diversi componenti del sistema.

### Classe Exception (Base)

#### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| context | array | Dati di contesto aggiuntivi per l'eccezione |
| category | string | Categoria dell'errore per raggruppamento |
| shouldLog | bool | Se questa eccezione deve essere loggata |
| shouldNotify | bool | Se questa eccezione deve triggerare notifiche |

#### Metodi

**__construct(string $message = '', int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $code (int): Codice dell'eccezione
- $context (array): Dati di contesto aggiuntivi
- $previous (?Throwable): Eccezione precedente

Tipo di ritorno: void

Descrizione:  
Costruttore dell'eccezione base con supporto per contesto esteso.

**getContext(): array**  
Input: Nessuno  
Tipo di ritorno: array

Descrizione:  
Ottiene il contesto dell'eccezione.

**setContext(array $context): self**  
Input:
- $context (array): Dati di contesto

Tipo di ritorno: self

Descrizione:  
Imposta il contesto dell'eccezione.

**addContext(string $key, $value): self**  
Input:
- $key (string): Chiave del contesto
- $value (mixed): Valore del contesto

Tipo di ritorno: self

Descrizione:  
Aggiunge dati di contesto all'eccezione.

**getCategory(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Ottiene la categoria dell'eccezione.

**setCategory(string $category): self**  
Input:
- $category (string): Nome della categoria

Tipo di ritorno: self

Descrizione:  
Imposta la categoria dell'eccezione.

**shouldLog(): bool**  
Input: Nessuno  
Tipo di ritorno: bool

Descrizione:  
Verifica se questa eccezione deve essere loggata.

**setShouldLog(bool $shouldLog): self**  
Input:
- $shouldLog (bool): Flag di logging

Tipo di ritorno: self

Descrizione:  
Imposta se questa eccezione deve essere loggata.

**shouldNotify(): bool**  
Input: Nessuno  
Tipo di ritorno: bool

Descrizione:  
Verifica se questa eccezione deve triggerare notifiche.

**setShouldNotify(bool $shouldNotify): self**  
Input:
- $shouldNotify (bool): Flag di notifica

Tipo di ritorno: self

Descrizione:  
Imposta se questa eccezione deve triggerare notifiche.

**toArray(): array**  
Input: Nessuno  
Tipo di ritorno: array

Descrizione:  
Converte l'eccezione in un array per logging/debugging.

**getFormattedMessage(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Crea un messaggio di errore formattato con contesto.

### Classe ProviderException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi ai provider.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| providerType | string | Tipo di provider che ha causato l'errore |
| providerName | string | Nome del provider che ha causato l'errore |

#### Metodi Aggiuntivi

**__construct(string $message, string $providerType = '', string $providerName = '', int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $providerType (string): Tipo di provider
- $providerName (string): Nome del provider
- $code (int): Codice dell'eccezione
- $context (array): Contesto aggiuntivo
- $previous (?Throwable): Eccezione precedente

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori dei provider.

**getProviderType(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Ottiene il tipo di provider.

**getProviderName(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Ottiene il nome del provider.

### Classe WorkflowException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi ai workflow.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| workflowId | int | ID del workflow che ha causato l'errore |

#### Metodi Aggiuntivi

**__construct(string $message, int $workflowId = 0, int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $workflowId (int): ID del workflow
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori dei workflow.

**getWorkflowId(): int**  
Input: Nessuno  
Tipo di ritorno: int

Descrizione:  
Ottiene l'ID del workflow.

### Classe SegmentException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi alla segmentazione.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| segmentId | int | ID del segmento che ha causato l'errore |

#### Metodi Aggiuntivi

**__construct(string $message, int $segmentId = 0, int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $segmentId (int): ID del segmento
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori di segmentazione.

**getSegmentId(): int**  
Input: Nessuno  
Tipo di ritorno: int

Descrizione:  
Ottiene l'ID del segmento.

### Classe AIException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi ai servizi AI.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| serviceName | string | Nome del servizio AI che ha causato l'errore |

#### Metodi Aggiuntivi

**__construct(string $message, string $serviceName = '', int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $serviceName (string): Nome del servizio AI
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori AI.

**getServiceName(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Ottiene il nome del servizio AI.

### Classe CampaignException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi alle campagne.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| campaignId | int | ID della campagna che ha causato l'errore |

#### Metodi Aggiuntivi

**__construct(string $message, int $campaignId = 0, int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $campaignId (int): ID della campagna
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori di campagna.

**getCampaignId(): int**  
Input: Nessuno  
Tipo di ritorno: int

Descrizione:  
Ottiene l'ID della campagna.

### Classe ConsentException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi alla gestione del consenso.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| customerId | int | ID del cliente relativo all'errore di consenso |
| channel | string | Canale relativo all'errore di consenso |

#### Metodi Aggiuntivi

**__construct(string $message, int $customerId = 0, string $channel = '', int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $customerId (int): ID del cliente
- $channel (string): Nome del canale
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori di consenso.

**getCustomerId(): int**  
Input: Nessuno  
Tipo di ritorno: int

Descrizione:  
Ottiene l'ID del cliente.

**getChannel(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Ottiene il canale.

### Classe ConfigException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori relativi alla configurazione.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| configKey | string | Chiave di configurazione che ha causato l'errore |

#### Metodi Aggiuntivi

**__construct(string $message, string $configKey = '', int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $configKey (string): Chiave di configurazione
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori di configurazione.

**getConfigKey(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Ottiene la chiave di configurazione.

### Classe ValidationException

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Eccezione per errori di validazione.

#### Proprietà Aggiuntive

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| errors | array | Errori di validazione |

#### Metodi Aggiuntivi

**__construct(string $message, array $errors = [], int $code = 0, array $context = [], ?Throwable $previous = null)**  
Input:
- $message (string): Messaggio dell'eccezione
- $errors (array): Errori di validazione
- Altri parametri come nella classe base

Tipo di ritorno: void

Descrizione:  
Costruttore specializzato per errori di validazione.

**getErrors(): array**  
Input: Nessuno  
Tipo di ritorno: array

Descrizione:  
Ottiene gli errori di validazione.

**addError(string $field, string $message): self**  
Input:
- $field (string): Nome del campo
- $message (string): Messaggio di errore

Tipo di ritorno: self

Descrizione:  
Aggiunge un errore di validazione.

**hasErrors(): bool**  
Input: Nessuno  
Tipo di ritorno: bool

Descrizione:  
Verifica se esistono errori di validazione.

---

## SYSTEM/LIBRARY/MAS/CONFIG.PHP

**Descrizione:**  
File di configurazione che restituisce un array associativo contenente tutte le impostazioni per i componenti della libreria MAS. Include configurazioni per gateway, eventi, audit, reporting, segmentazione, AI, workflow, provider, sicurezza e performance.

### Sezioni di Configurazione

#### AI Gateway
- **default_provider**: Provider AI predefinito ('openai')
- **enable_cache**: Abilitazione cache (true)
- **cache_ttl**: TTL cache in secondi (3600)
- **max_retries**: Numero massimo di tentativi (3)
- **circuit_breaker_threshold**: Soglia circuit breaker (5)
- **fallback_order**: Ordine di fallback per diversi tipi di operazioni AI

#### Message Gateway
- **default_provider**: Provider messaggi predefinito ('sendgrid')
- **default_batch_size**: Dimensione batch predefinita (100)
- **fallback_order**: Ordine di fallback per email, SMS, push, WhatsApp, Slack

#### Payment Gateway
- **default_provider**: Provider pagamenti predefinito ('stripe')
- **rate_limit_window**: Finestra rate limiting (60 secondi)
- **rate_limit_max**: Massimo richieste per finestra (100)

#### Event System
- **event_dispatcher.enabled**: Abilitazione dispatcher eventi (true)
- **event_queue.batch_size**: Dimensione batch coda eventi (100)
- **event_queue.max_attempts**: Tentativi massimi (5)

#### Audit & Compliance
- **audit_logger.enabled**: Abilitazione logging audit (true)
- **audit_logger.retention_days**: Giorni di retention (2555)
- **audit_logger.alert_events**: Eventi che triggerano alert
- **consent_manager.cache_ttl**: TTL cache consensi (3600)

#### Reporting
- **dashboard_service.cache_ttl**: TTL cache dashboard (900)
- **dashboard_service.supported_grains**: Granularità supportate
- **csv_exporter**: Configurazioni per export CSV

#### Segmentation
- **segment_manager.cache_ttl**: TTL cache segmenti (3600)
- **segment_manager.auto_refresh**: Auto refresh segmenti (true)
- **segment_manager.max_segment_size**: Dimensione massima segmento (100000)

#### AI Suggestors
- **openai_suggester.model**: Modello OpenAI ('gpt-4')
- **openai_suggester.temperature**: Temperatura (0.3)
- **openai_suggester.max_tokens**: Token massimi (2000)

#### Workflow Engine
- **workflow_engine.max_execution_time**: Tempo massimo esecuzione (300)
- **workflow_engine.batch_size**: Dimensione batch (100)
- **workflow_manager.enable_versioning**: Abilitazione versioning (true)

#### Security & Performance
- **security.rate_limit_enabled**: Abilitazione rate limiting (true)
- **security.max_requests_per_hour**: Richieste massime per ora (3600)
- **performance.enable_query_cache**: Abilitazione cache query (true)
- **performance.memory_limit**: Limite memoria ('512M')

---

## SYSTEM/LIBRARY/MAS/MASSERVICEPROVIDER.PHP

**Namespace:** `Opencart\Library\Mas`

**Descrizione:**  
Provider di servizi che registra tutti i servizi MAS nel ServiceContainer e nel Registry di OpenCart. Si occupa dell'inizializzazione e registrazione di gateway, dispatcher eventi, audit logger e servizi di reporting.

### Metodi

**register(Registry $registry): void**  
Input:
- $registry (Registry): Registry di OpenCart

Tipo di ritorno: void

Descrizione:  
Metodo statico che carica la configurazione MAS, inizializza il ServiceContainer e registra tutti i servizi principali:
- Event Dispatcher e Event Queue
- AI Gateway, Message Gateway, Payment Gateway
- Audit Logger
- Dashboard Service e CSV Exporter

Il metodo gestisce automaticamente il merge tra configurazione di default e override dal config globale di OpenCart.

---

La documentazione continua con tutti gli altri file della libreria MAS, inclusi i servizi di workflow, segmentazione, provider, helper, interfacce e servizi specifici. Ogni file segue lo stesso schema dettagliato con namespace, descrizione, proprietà e metodi completi.

---

## SYSTEM/LIBRARY/MAS/AUDIT/AUDITLOGGER.PHP

**Namespace:** `Opencart\Library\Mas\Audit`

**Descrizione:**  
Sistema centralizzato di audit logging per operazioni MAS. Registra tutti gli eventi significativi del sistema, azioni utente, modifiche dati, eventi di sicurezza e attività di compliance con contesto dettagliato e metadati.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| container | ServiceContainer | Container dei servizi MAS |
| log | Log | Logger di OpenCart |
| cache | Cache | Cache di OpenCart |
| db | DB | Database di OpenCart |
| session | Session | Sessione di OpenCart |
| config | array | Impostazioni di configurazione |
| enabled | bool | Abilitazione audit logging |
| categories | array | Categorie di eventi |
| severityLevels | array | Livelli di severità |
| alertEvents | array | Eventi che richiedono alert immediati |
| retentionDays | int | Periodo di retention in giorni |
| batchSize | int | Dimensione batch per processamento log |

### Metodi

**__construct(ServiceContainer $container, array $config = [])**  
Input:
- $container (ServiceContainer): Container dei servizi
- $config (array): Configurazione opzionale

Tipo di ritorno: void

Descrizione:  
Costruttore che inizializza il logger di audit con configurazione e dipendenze.

**logSecurity(string $action, array $context = [], string $severity = 'medium'): void**  
Input:
- $action (string): Azione di sicurezza
- $context (array): Contesto dell'evento
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un evento di sicurezza nel sistema di audit.

**logDataChange(string $action, string $table, int $recordId, array $before = [], array $after = [], string $severity = 'info'): void**  
Input:
- $action (string): Tipo di azione (create, update, delete)
- $table (string): Nome della tabella
- $recordId (int): ID del record
- $before (array): Dati prima della modifica
- $after (array): Dati dopo la modifica
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra una modifica ai dati con snapshot prima/dopo per compliance.

**logUserAction(string $action, array $context = [], string $severity = 'info'): void**  
Input:
- $action (string): Azione utente
- $context (array): Contesto dell'azione
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un'azione utente nel sistema.

**logSystem(string $action, array $context = [], string $severity = 'info'): void**  
Input:
- $action (string): Evento di sistema
- $context (array): Contesto dell'evento
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un evento di sistema.

**logWorkflow(string $action, int $workflowId, int $executionId = null, array $context = [], string $severity = 'info'): void**  
Input:
- $action (string): Azione workflow
- $workflowId (int): ID del workflow
- $executionId (int): ID dell'esecuzione (opzionale)
- $context (array): Contesto aggiuntivo
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un evento workflow con ID workflow ed esecuzione.

**logSegment(string $action, int $segmentId, array $context = [], string $severity = 'info'): void**  
Input:
- $action (string): Azione segmento
- $segmentId (int): ID del segmento
- $context (array): Contesto aggiuntivo
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un evento di segmentazione.

**logCampaign(string $action, int $campaignId, array $context = [], string $severity = 'info'): void**  
Input:
- $action (string): Azione campagna
- $campaignId (int): ID della campagna
- $context (array): Contesto aggiuntivo
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un evento di campagna.

**logCompliance(string $action, array $context = [], string $severity = 'high'): void**  
Input:
- $action (string): Evento di compliance
- $context (array): Contesto dell'evento
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra un evento di compliance (GDPR, privacy, etc.).

**logApi(string $endpoint, string $method, array $context = [], string $severity = 'info'): void**  
Input:
- $endpoint (string): Endpoint API
- $method (string): Metodo HTTP
- $context (array): Contesto della chiamata
- $severity (string): Livello di severità

Tipo di ritorno: void

Descrizione:  
Registra una chiamata API con endpoint e metodo.

---

## SYSTEM/LIBRARY/MAS/CONSENT/CONSENTMANAGER.PHP

**Namespace:** `Opencart\Library\Mas\Consent`

**Descrizione:**  
Manager centralizzato per i consensi clienti/utenti (GDPR, marketing, cookies, T&C). Gestisce creazione e ciclo di vita delle definizioni di consenso, logging di eventi accept/revoke, controllo versioni e export di compliance.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| TABLE_DEFINITION | const string | Tabella definizioni consensi ('mas_consent_definition') |
| TABLE_LOG | const string | Tabella log consensi ('mas_consent_log') |
| container | ServiceContainer | Container dei servizi MAS |
| log | Log | Logger di OpenCart |
| db | DB | Database di OpenCart |
| cache | Cache | Cache di OpenCart |
| ttl | int | TTL cache per definizioni in secondi |

### Metodi

**__construct(ServiceContainer $container)**  
Input:
- $container (ServiceContainer): Container dei servizi

Tipo di ritorno: void

Descrizione:  
Costruttore che inizializza il manager dei consensi con dipendenze dal container.

**createDefinition(array $data): string**  
Input:
- $data (array): Dati della definizione consenso (code, name, description, version, required)

Tipo di ritorno: string

Descrizione:  
Crea una nuova definizione di consenso. Valida i dati e inserisce nel database. Restituisce il codice della definizione.

**updateDefinition(string $code, array $data): void**  
Input:
- $code (string): Codice della definizione
- $data (array): Dati da aggiornare

Tipo di ritorno: void

Descrizione:  
Aggiorna una definizione di consenso esistente. Verifica l'esistenza e valida i dati.

**getDefinition(string $code): ?array**  
Input:
- $code (string): Codice della definizione

Tipo di ritorno: ?array

Descrizione:  
Recupera una definizione di consenso per codice. Utilizza cache per performance.

---

## SYSTEM/LIBRARY/MAS/EVENTS/EVENTDISPATCHER.PHP

**Namespace:** `Opencart\Library\Mas\Events`

**Descrizione:**  
Dispatcher di eventi centrale che supporta registrazione listener, firing eventi, gestione priorità, listener wildcard e integrazione con coda asincrona.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| listeners | array | Listener registrati [event => [priority => [callable, ...]]] |

### Metodi

**addListener(string $event, callable $listener, int $priority = 0): void**  
Input:
- $event (string): Nome dell'evento
- $listener (callable): Funzione listener
- $priority (int): Priorità (default: 0)

Tipo di ritorno: void

Descrizione:  
Registra un listener per un evento. I listener sono ordinati per priorità (valori più alti = priorità maggiore).

**dispatch(string $event, $payload = null): array**  
Input:
- $event (string): Nome dell'evento
- $payload (mixed): Dati dell'evento (opzionale)

Tipo di ritorno: array

Descrizione:  
Distribuisce un evento a tutti i listener rilevanti. Restituisce array dei valori di ritorno dei listener.

**getListenersForEvent(string $event): array**  
Input:
- $event (string): Nome dell'evento

Tipo di ritorno: array

Descrizione:  
Ottiene tutti i listener per un evento specifico, inclusi listener wildcard (metodo protetto).

**removeListener(string $event, callable $listener): bool**  
Input:
- $event (string): Nome dell'evento
- $listener (callable): Listener da rimuovere

Tipo di ritorno: bool

Descrizione:  
Rimuove un listener specifico. Restituisce true se rimosso con successo.

---

## SYSTEM/LIBRARY/MAS/INTERFACES/NODEINTERFACE.PHP

**Namespace:** `Opencart\Library\Mas\Interfaces`

**Descrizione:**  
Contratto per tutti i tipi di nodi workflow (trigger, action, delay, condition, etc.) utilizzati nel MAS Workflow Engine. I nodi definiscono la struttura degli step di workflow e la loro logica di runtime.

### Metodi di Interfaccia

**getId(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Restituisce l'identificatore univoco dell'istanza del nodo.

**getType(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce il tipo di nodo (es. 'trigger', 'action', 'delay', 'condition').

**getLabel(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Restituisce un'etichetta breve e leggibile per il nodo.

**getDescription(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Restituisce una descrizione del nodo.

**getConfig(): array**  
Input: Nessuno  
Tipo di ritorno: array

Descrizione:  
Restituisce l'array di configurazione per questo nodo.

**setConfig(array $config): void**  
Input:
- $config (array): Array di configurazione

Tipo di ritorno: void

Descrizione:  
Imposta l'array di configurazione per questo nodo.

**validate(): bool**  
Input: Nessuno  
Tipo di ritorno: bool

Descrizione:  
Valida la configurazione del nodo. Restituisce true se valida, altrimenti false.

**getConfigSchema(): array**  
Input: Nessuno  
Tipo di ritorno: array

Descrizione:  
Metodo statico che restituisce lo schema array per la configurazione del nodo.

**execute(array $context): array**  
Input:
- $context (array): Contesto workflow (payload, data, state, etc.)

Tipo di ritorno: array

Descrizione:  
Esegue la logica del nodo. Restituisce array con success (bool), output opzionale e error opzionale.

**toArray(): array**  
Input: Nessuno  
Tipo di ritorno: array

Descrizione:  
Serializza questo nodo (inclusa la sua config) in array per storage JSON.

**fromArray(array $data): self**  
Input:
- $data (array): Dati del nodo

Tipo di ritorno: self

Descrizione:  
Factory statico per creare istanza nodo da array (per deserializzazione).

---

## SYSTEM/LIBRARY/MAS/INTERFACES/CHANNELPROVIDERINTERFACE.PHP

**Namespace:** `Opencart\Library\Mas\Interfaces`

**Descrizione:**  
Contratto per tutte le classi provider (email, SMS, push, AI, etc.) utilizzate in MAS. Definisce l'interfaccia standard per l'invio di messaggi e la gestione della connessione.

### Costanti

| Costante | Valore | Descrizione |
|----------|--------|-------------|
| TYPE_EMAIL | 'email' | Tipo provider email |
| TYPE_SMS | 'sms' | Tipo provider SMS |
| TYPE_PUSH | 'push' | Tipo provider push notification |
| TYPE_AI | 'ai' | Tipo provider AI |

### Metodi di Interfaccia

**send(array $payload): array**  
Input:
- $payload (array): Payload normalizzato (dati destinatario, contenuto, opzioni)

Tipo di ritorno: array

Descrizione:  
Invia un messaggio o esegue l'azione primaria del provider. Restituisce array con success, message_id opzionale, error opzionale e meta opzionale.

**authenticate(array $config): bool**  
Input:
- $config (array): Configurazione (API keys, endpoints, etc.)

Tipo di ritorno: bool

Descrizione:  
Autentica o inizializza il provider con credenziali/config fornite.

**testConnection(): bool**  
Input: Nessuno  
Tipo di ritorno: bool

Descrizione:  
Testa connettività e credenziali senza inviare un messaggio reale.

**getName(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce il nome univoco del provider (es. "SendGrid", "Twilio").

**getDescription(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce una breve descrizione leggibile.

**getType(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce il tipo di provider (email, sms, push, ai).

**getVersion(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce la versione del provider.

**getCapabilities(): array**  
Input: Nessuno  
Tipo di ritorno: string[]

Descrizione:  
Metodo statico che restituisce array delle capacità del provider (es. ['bulk_send', 'tracking', 'templates']).

**getSetupSchema(): array**  
Input: Nessuno  
Tipo di ritorno: array<string, mixed>

Descrizione:  
Metodo statico che restituisce la definizione schema di setup completo per auto-discovery MAS.

**setConfig(array $config): void**  
Input:
- $config (array): Configurazione

Tipo di ritorno: void

Descrizione:  
Imposta o aggiorna la configurazione runtime dopo autenticazione.

---

## SYSTEM/LIBRARY/MAS/HELPERS/ARRAYHELPER.PHP

**Namespace:** `Opencart\Library\Mas\Helper`

**Descrizione:**  
Classe helper statica per operazioni su array. Fornisce metodi di utilità per manipolazione, filtraggio e trasformazione di array, specificamente adattati per uso interno MAS.

### Metodi Statici

**get(array $array, $key, $default = null): mixed**  
Input:
- $array (array): Array in cui cercare
- $key (string|int): Chiave da cercare
- $default (mixed): Valore di default se chiave non trovata

Tipo di ritorno: mixed

Descrizione:  
Ottiene un valore da un array per chiave, con fallback di default.

**set(array &$array, $key, $value): array**  
Input:
- $array (array): Array da modificare (passato per riferimento)
- $key (string|int): Chiave da impostare
- $value (mixed): Valore da assegnare

Tipo di ritorno: array

Descrizione:  
Imposta un valore in un array per chiave, creando l'array se non esiste.

**has(array $array, $key): bool**  
Input:
- $array (array): Array da controllare
- $key (string|int): Chiave da cercare

Tipo di ritorno: bool

Descrizione:  
Verifica se un array ha una chiave specifica.

**remove(array &$array, $key): array**  
Input:
- $array (array): Array da modificare (passato per riferimento)
- $key (string|int): Chiave da rimuovere

Tipo di ritorno: array

Descrizione:  
Rimuove un valore da un array per chiave.

**filter(array $array, callable $callback): array**  
Input:
- $array (array): Array da filtrare
- $callback (callable): Funzione callback

Tipo di ritorno: array

Descrizione:  
Filtra un array tramite funzione callback.

**map(array $array, callable $callback): array**  
Input:
- $array (array): Array da mappare
- $callback (callable): Funzione callback

Tipo di ritorno: array

Descrizione:  
Mappa un array attraverso una funzione callback.

---

## SYSTEM/LIBRARY/MAS/WORKFLOW/WORKFLOWMANAGER.PHP

**Namespace:** `Opencart\Library\Mas\Workflow`

**Descrizione:**  
Gestisce creazione, esecuzione, scheduling e gestione del ciclo di vita dei workflow. Gestisce nodi workflow (trigger, action, delay), serializzazione, validazione e integrazione con event dispatcher e sistema code.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| container | ServiceContainer | Container dei servizi MAS |
| registry | Registry | Registry di OpenCart |
| loader | Loader | Loader di OpenCart |
| log | Log | Logger di OpenCart |
| cache | Cache | Cache di OpenCart |
| db | DB | Database di OpenCart |
| nodeTypes | array<string, NodeInterface> | Tipi di nodi registrati |
| workflows | array<string, array> | Definizioni workflow caricate |
| executions | array<string, array> | Esecuzioni workflow attive |
| config | array | Impostazioni di configurazione |
| maxNodesPerWorkflow | int | Numero massimo di nodi per workflow |
| maxActivePerCustomer | int | Workflow attivi massimi per cliente |
| executionTimeout | int | Timeout esecuzione workflow in secondi |
| eventListeners | array<string, callable> | Listener di eventi |

### Metodi

**__construct(ServiceContainer $container)**  
Input:
- $container (ServiceContainer): Container dei servizi

Tipo di ritorno: void

Descrizione:  
Costruttore che inizializza il manager workflow con dipendenze dal container.

---

## SYSTEM/LIBRARY/MAS/PROVIDERS/ABSTRACTPROVIDER.PHP

**Namespace:** `Opencart\Library\Mas\Provider`

**Descrizione:**  
Classe base astratta per tutti i provider di canali (email, SMS, push, AI, etc.). Supporta auto-discovery provider, definizione schema, dichiarazione capacità, gestione configurazione, gestione connessione ed esecuzione azioni core.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| config | array | Configurazione runtime del provider |
| authenticated | bool | Se il provider è autenticato |
| lastError | ?string | Ultimo messaggio di errore incontrato |

### Metodi

**__construct(array $config = [])**  
Input:
- $config (array): Configurazione runtime opzionale

Tipo di ritorno: void

Descrizione:  
Costruttore che imposta opzionalmente la configurazione se fornita.

**send(array $payload): array**  
Input:
- $payload (array): Payload del messaggio

Tipo di ritorno: array

Descrizione:  
Metodo astratto per inviare messaggio o eseguire azione primaria del provider.

**authenticate(array $config): bool**  
Input:
- $config (array): Configurazione/credenziali

Tipo di ritorno: bool

Descrizione:  
Autentica o inizializza il provider con configurazione/credenziali fornite. Implementazione di default controlla campi richiesti da schema.

**testConnection(): bool**  
Input: Nessuno  
Tipo di ritorno: bool

Descrizione:  
Testa connettività e credenziali senza inviare messaggio reale. Può essere sovrascritto per controlli specifici di connessione.

**getName(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce il nome univoco del provider (DEVE essere sovrascritto).

**getDescription(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce breve descrizione leggibile (DEVE essere sovrascritto).

**getType(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce il tipo di provider (DEVE essere sovrascritto).

**getVersion(): string**  
Input: Nessuno  
Tipo di ritorno: string

Descrizione:  
Metodo statico che restituisce la versione semantica del provider.

**getCapabilities(): array**  
Input: Nessuno  
Tipo di ritorno: string[]

Descrizione:  
Metodo statico che restituisce array delle capacità del provider. Può essere sovrascritto per specificare ['bulk_send','tracking','template', etc.].

---

## SYSTEM/LIBRARY/MAS/SEGMENTATION/SEGMENTMANAGER.PHP

**Namespace:** `Opencart\Library\Mas\Segmentation`

**Descrizione:**  
Gestisce la segmentazione clienti con supporto per tipi di filtri multipli, materializzazione segmenti, caching, suggerimenti AI-powered e analytics. Gestisce analisi RFM, segmentazione comportamentale, modelling predittivo e aggiornamenti segmenti real-time.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| container | ServiceContainer | Container dei servizi MAS |
| registry | Registry | Registry di OpenCart |
| loader | Loader | Loader di OpenCart |
| log | Log | Logger di OpenCart |
| cache | Cache | Cache di OpenCart |
| db | DB | Database di OpenCart |
| filterTypes | array<string, SegmentFilterInterface> | Tipi di filtri registrati |
| segments | array<string, array> | Definizioni segmenti caricati |
| materializedSegments | array<string, array> | Dati segmenti materializzati |
| config | array | Impostazioni di configurazione |
| cacheMinutes | int | TTL cache in minuti |
| batchSize | int | Dimensione batch per materializzazione |
| maxSegmentSize | int | Dimensione massima segmento |
| eventListeners | array<string, callable> | Listener di eventi |

### Metodi

**__construct(ServiceContainer $container)**  
Input:
- $container (ServiceContainer): Container dei servizi

Tipo di ritorno: void

Descrizione:  
Costruttore che inizializza il manager segmentazione con dipendenze dal container.

---

## SYSTEM/LIBRARY/MAS/REPORTING/DASHBOARDSERVICE.PHP

**Namespace:** `Opencart\Library\Mas\Reporting`

**Descrizione:**  
Facade di alto livello che espone API di reporting unificati per l'interfaccia MAS (dashboard admin, widget, report esportati, endpoint REST). Aggrega e caches KPI core, genera dati time-series, offre drill-down helper e supporta filtri rapidi.

### Proprietà

| Proprietà | Tipo | Descrizione |
|-----------|------|-------------|
| container | ServiceContainer | Container dei servizi MAS |
| log | Log | Logger di OpenCart |
| cache | Cache | Cache di OpenCart |
| db | DB | Database di OpenCart |
| ttl | int | TTL cache di default in secondi |
| grains | array | Granularità consentite |

### Metodi

**__construct(ServiceContainer $container, array $config = [])**  
Input:
- $container (ServiceContainer): Container dei servizi
- $config (array): Configurazione opzionale

Tipo di ritorno: void

Descrizione:  
Costruttore che inizializza il servizio dashboard con configurazione.

**getKpiSummary(string $dateFrom, string $dateTo, array $filters = []): array**  
Input:
- $dateFrom (string): Data inizio formato Y-m-d
- $dateTo (string): Data fine formato Y-m-d
- $filters (array): Filtri opzionali [store_id, channel, segment_id, currency_code]

Tipo di ritorno: array

Descrizione:  
Restituisce indicatori di performance chiave per il periodo specificato. Include revenue, orders, AOV, conversion rate, visits, new customers con caching.

---

## RIEPILOGO COMPONENTI AGGIUNTIVI

La libreria MAS include molti altri componenti specializzati non documentati in dettaglio sopra ma essenziali per il funzionamento completo del sistema:

### Workflow Engine

**SYSTEM/LIBRARY/MAS/WORKFLOW/NODES/**
- **abstractNode.php**: Classe base astratta per tutti i tipi di nodi workflow
- **triggerNode.php**: Nodo trigger per avviare workflow automaticamente
- **actionNode.php**: Nodo azione per eseguire operazioni durante il workflow  
- **delayNode.php**: Nodo delay per introdurre attese nel workflow

**SYSTEM/LIBRARY/MAS/WORKFLOW/ACTIONS/**
- **sendEmailAction.php**: Azione specifica per invio email
- **sendSmsAction.php**: Azione specifica per invio SMS
- **customAction.php**: Azione personalizzabile per logiche custom

### Provider Ecosystem

**SYSTEM/LIBRARY/MAS/PROVIDERS/**
- **providerManager.php**: Manager per auto-discovery e gestione provider

**Email Providers:**
- **email/smtpProvider.php**: Provider SMTP generico per invio email

**SMS Providers:**
- **sms/twilioProvider.php**: Provider Twilio per invio SMS

**Push Providers:**
- **push/oneSignalProvider.php**: Provider OneSignal per push notifications

**AI Providers:**
- **ai/openAIProvider.php**: Provider OpenAI per servizi AI

### Servizi Gateway

**SYSTEM/LIBRARY/MAS/SERVICES/**
- **ai/AiGateway.php**: Gateway unificato per servizi AI
- **ai/openAISuggester.php**: Suggeritore AI basato su OpenAI
- **message/MessageGateway.php**: Gateway unificato per messaggistica  
- **payment/PaymentGateway.php**: Gateway unificato per pagamenti

### Segmentazione Avanzata

**SYSTEM/LIBRARY/MAS/SEGMENTATION/**
- **segmentSuggestor.php**: Motore suggerimenti segmenti AI-powered

**SYSTEM/LIBRARY/MAS/SEGMENTATION/FILTERS/**
- **behaviouralFilter.php**: Filtro per segmentazione comportamentale
- **demographicFilter.php**: Filtro per segmentazione demografica  
- **rfmFilter.php**: Filtro per analisi RFM (Recency, Frequency, Monetary)
- **predictiveFilter.php**: Filtro per segmentazione predittiva

### Sistema Eventi

**SYSTEM/LIBRARY/MAS/EVENTS/**
- **queue.php**: Sistema di code per eventi asincroni

### Reporting

**SYSTEM/LIBRARY/MAS/REPORTING/**
- **csvExporter.php**: Esportatore CSV per report e dati

### Helper Utilities

**SYSTEM/LIBRARY/MAS/HELPERS/**
- **dateHelper.php**: Utilità per manipolazione date e timezone

### Interfacce Specializzate

**SYSTEM/LIBRARY/MAS/INTERFACES/**
- **aISuggestorInterface.php**: Contratto per suggeritori AI
- **segmentFilterInterface.php**: Contratto per filtri di segmentazione

---

## ARCHITETTURA E FLUSSO DI LAVORO

### Dependency Injection Container
Il sistema utilizza un pattern IoC (Inversion of Control) attraverso il `ServiceContainer` che gestisce:
- Registrazione servizi con pattern singleton/factory
- Risoluzione automatica dipendenze  
- Gestione del ciclo di vita dei servizi
- Sistema di alias e tag per organizzazione servizi

### Event-Driven Architecture  
Il sistema è event-driven con:
- `EventDispatcher` per gestione eventi sincroni
- `Queue` per gestione eventi asincroni
- Pattern observer per comunicazione tra componenti
- Sistema di priorità per listener

### Provider Pattern
Architettura a plugin per estensibilità:
- `AbstractProvider` come base comune
- `ChannelProviderInterface` per standardizzazione
- Auto-discovery attraverso schema definition
- Fallback e circuit breaker per affidabilità

### Workflow Engine
Motore workflow visual con:
- Nodi tipizzati (trigger, action, delay, condition)
- Serializzazione/deserializzazione per persistenza
- Esecuzione asincrona con gestione stato
- Validazione e error handling avanzato

### Audit & Compliance
Sistema completo per compliance:
- Logging centralizzato di tutte le operazioni
- Retention policy configurabile
- Export per audit esterni
- Alert automatici per eventi critici

### AI Integration
Integrazione AI nativa con:
- Gateway unificato per multiple provider AI
- Suggerimenti automatici per segmentazione
- Ottimizzazione campagne basata su ML
- Predizioni comportamentali clienti

---

## CONCLUSIONI

La libreria MAS rappresenta una soluzione enterprise completa per l'automazione marketing in OpenCart 4.x, caratterizzata da:

**Architettura Modulare**: Design basato su dependency injection, interfacce standardizzate e pattern estensibili.

**Scalabilità**: Sistema di cache multi-livello, elaborazione batch, code asincrone e ottimizzazioni database.

**Compliance**: Audit trail completo, gestione consensi GDPR, retention policy e export conformi.

**AI-Powered**: Integrazione nativa con servizi AI per segmentazione intelligente, suggerimenti automatici e ottimizzazioni predittive.

**Estensibilità**: Architettura a plugin per provider personalizzati, nodi workflow custom e filtri di segmentazione specializzati.

**Observability**: Logging strutturato, metriche di performance, monitoring eventi e dashboard analytics avanzato.

La libreria è progettata per supportare crescita aziendale e requisiti enterprise mantenendo flessibilità per personalizzazioni specifiche del dominio.
