# MAS (Marketing Automation Suite) - Documentazione Tecnica Completa

## Indice

1. [Introduzione](#introduzione)
2. [Architettura del Sistema](#architettura-del-sistema)
3. [Namespace e Struttura](#namespace-e-struttura)
4. [Core Classes](#core-classes)
   - [MAS](#1-mas-classe-principale)
   - [AutomationAction](#2-automationaction)
   - [AutomationTrigger](#3-automationtrigger)
   - [BaseAction](#4-baseaction)
   - [BaseCondition](#5-basecondition)
   - [BaseTrigger](#6-basetrigger)
   - [Campaign](#7-campaign)
   - [ConsentManager](#8-consentmanager)
   - [EventDispatcher](#9-eventdispatcher)
   - [LandingPage](#10-landingpage)
   - [ProviderInterface](#11-providerinterface)
   - [ProviderManager](#12-providermanager)
   - [ReportManager](#13-reportmanager)
   - [Segment](#14-segment)
   - [Workflow](#15-workflow)
5. [Exception Classes](#exception-classes)
6. [Helper Classes](#helper-classes)
7. [Model Classes](#model-classes)
8. [Provider Classes](#provider-classes)
9. [Esempi di Utilizzo](#esempi-di-utilizzo)
10. [Best Practices](#best-practices)

---

## Introduzione

La libreria MAS (Marketing Automation Suite) è un sistema completo per l'automazione del marketing integrato con OpenCart. Fornisce strumenti avanzati per la gestione di campagne, segmentazione utenti, workflow di automazione, gestione dei provider, reportistica e landing pages.

**Namespace Base:** `Opencart\System\Library\Mas`

**Struttura Directory:**
```
system/library/mas/
├── core/              # Classi principali del sistema
├── exceptions/        # Eccezioni personalizzate
├── helper/            # Classi di supporto e utility
├── model/             # Modelli per la gestione dei dati
└── model/providers/   # Provider per servizi esterni
```

---

## Architettura del Sistema

### Componenti Principali

1. **Core System** - Gestione centrale del sistema MAS
2. **Provider Management** - Gestione dei provider esterni (email, SMS, social, AI)
3. **Campaign Management** - Gestione delle campagne di marketing
4. **Automation System** - Sistema di automazione con workflow, azioni e trigger
5. **Segmentation System** - Sistema di segmentazione utenti avanzato
6. **Landing Pages** - Gestione delle landing pages
7. **Reporting System** - Sistema di reportistica e analytics avanzato
8. **Consent Management** - Gestione del consenso e GDPR

---

## Namespace e Struttura

### Struttura dei Namespace

```php
Opencart\System\Library\Mas\
├── Core\              # Classi principali
├── Exceptions\        # Eccezioni personalizzate
├── Helper\            # Utility e helper
├── Model\             # Modelli dati
└── Model\Providers\   # Provider esterni
```

---

## Core Classes

### 1. MAS (Classe Principale)

**Namespace:** `Opencart\System\Library\Mas\Core\MAS`

**Descrizione:** Classe principale del sistema che gestisce l'inizializzazione, la configurazione e la comunicazione con OpenCart.

#### Proprietà

```php
protected Registry $registry;           // Registro OpenCart
protected ProviderManager $providerManager;  // Gestore dei provider
protected array $providers = [];       // Array dei provider registrati
protected array $config = [];         // Configurazione del sistema
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza la classe MAS
- **Parametri:** 
  - `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `init()`
- **Descrizione:** Inizializza il sistema MAS
- **Parametri:** Nessuno
- **Restituisce:** void
- **Funzionalità:** Carica la configurazione e inizializza i provider

##### `loadConfig()`
- **Descrizione:** Carica la configurazione del sistema
- **Parametri:** Nessuno
- **Restituisce:** void
- **Visibilità:** protected

##### `getProviderManager()`
- **Descrizione:** Restituisce il gestore dei provider
- **Parametri:** Nessuno
- **Restituisce:** ProviderManager

##### `getConfig()`
- **Descrizione:** Restituisce la configurazione del sistema
- **Parametri:** Nessuno
- **Restituisce:** array

##### `setConfig(array $config)`
- **Descrizione:** Imposta la configurazione del sistema
- **Parametri:**
  - `$config` (array): Array di configurazione
- **Restituisce:** void

##### `dispatchEvent(string $event, array $data = [])`
- **Descrizione:** Invia un evento al sistema
- **Parametri:**
  - `$event` (string): Nome dell'evento
  - `$data` (array): Dati dell'evento (opzionale)
- **Restituisce:** void

##### `registerProvider(string $type, string $name, object $provider)`
- **Descrizione:** Registra un provider nel sistema
- **Parametri:**
  - `$type` (string): Tipo di provider
  - `$name` (string): Nome del provider
  - `$provider` (object): Istanza del provider
- **Restituisce:** void

##### `getProvider(string $type, string $name)`
- **Descrizione:** Restituisce un provider specifico
- **Parametri:**
  - `$type` (string): Tipo di provider
  - `$name` (string): Nome del provider
- **Restituisce:** object|null

##### `getProvidersByType(string $type)`
- **Descrizione:** Restituisce tutti i provider di un tipo specifico
- **Parametri:**
  - `$type` (string): Tipo di provider
- **Restituisce:** array

### 2. AutomationAction

**Namespace:** `Opencart\System\Library\Mas\Core\AutomationAction`

**Descrizione:** Gestisce le azioni di automazione per la marketing automation suite.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected array $actions = [];        // Array delle azioni registrate
protected array $history = [];        // Cronologia delle azioni
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza il gestore delle azioni
- **Parametri:** 
  - `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `registerAction(string $id, string $name, callable $callback, array $config = [])`
- **Descrizione:** Registra una nuova azione di automazione
- **Parametri:**
  - `$id` (string): ID univoco dell'azione
  - `$name` (string): Nome dell'azione
  - `$callback` (callable): Funzione da eseguire
  - `$config` (array): Configurazione dell'azione (opzionale)
- **Restituisce:** void

##### `removeAction(string $id)`
- **Descrizione:** Rimuove un'azione per ID
- **Parametri:**
  - `$id` (string): ID dell'azione da rimuovere
- **Restituisce:** bool

##### `getAction(string $id)`
- **Descrizione:** Restituisce un'azione per ID
- **Parametri:**
  - `$id` (string): ID dell'azione
- **Restituisce:** array|null

##### `getAllActions()`
- **Descrizione:** Restituisce tutte le azioni registrate
- **Parametri:** Nessuno
- **Restituisce:** array

##### `executeAction(string $id, array $context)`
- **Descrizione:** Esegue un'azione con il contesto dato
- **Parametri:**
  - `$id` (string): ID dell'azione
  - `$context` (array): Contesto di esecuzione
- **Restituisce:** mixed

##### `executeAllActions(array $context)`
- **Descrizione:** Esegue tutte le azioni con il contesto dato
- **Parametri:**
  - `$context` (array): Contesto di esecuzione
- **Restituisce:** array

##### `getActionHistory(string $id)`
- **Descrizione:** Restituisce la cronologia di un'azione
- **Parametri:**
  - `$id` (string): ID dell'azione
- **Restituisce:** array

##### `syncWithOpenCart()`
- **Descrizione:** Sincronizza le definizioni delle azioni con OpenCart
- **Parametri:** Nessuno
- **Restituisce:** bool

### 3. AutomationTrigger

**Namespace:** `Opencart\System\Library\Mas\Core\AutomationTrigger`

**Descrizione:** Gestisce i trigger di automazione per la marketing automation suite.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected array $triggers = [];       // Array dei trigger registrati
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza il gestore dei trigger
- **Parametri:** 
  - `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `registerTrigger(string $id, string $name, callable $callback, array $config = [])`
- **Descrizione:** Registra un nuovo trigger di automazione
- **Parametri:**
  - `$id` (string): ID univoco del trigger
  - `$name` (string): Nome del trigger
  - `$callback` (callable): Funzione di valutazione
  - `$config` (array): Configurazione del trigger (opzionale)
- **Restituisce:** void

##### `removeTrigger(string $id)`
- **Descrizione:** Rimuove un trigger per ID
- **Parametri:**
  - `$id` (string): ID del trigger da rimuovere
- **Restituisce:** bool

##### `getTrigger(string $id)`
- **Descrizione:** Restituisce un trigger per ID
- **Parametri:**
  - `$id` (string): ID del trigger
- **Restituisce:** array|null

##### `getAllTriggers()`
- **Descrizione:** Restituisce tutti i trigger registrati
- **Parametri:** Nessuno
- **Restituisce:** array

##### `evaluateTrigger(string $id, array $context)`
- **Descrizione:** Valuta un trigger con il contesto dato
- **Parametri:**
  - `$id` (string): ID del trigger
  - `$context` (array): Contesto di valutazione
- **Restituisce:** mixed

##### `evaluateAllTriggers(array $context)`
- **Descrizione:** Valuta tutti i trigger con il contesto dato
- **Parametri:**
  - `$context` (array): Contesto di valutazione
- **Restituisce:** array

##### `syncWithOpenCart()`
- **Descrizione:** Sincronizza le definizioni dei trigger con OpenCart
- **Parametri:** Nessuno
- **Restituisce:** bool

### 4. BaseAction

**Namespace:** `Opencart\System\Library\Mas\Core\BaseAction`

**Descrizione:** Classe base astratta per le azioni di automazione. Fornisce funzionalità comuni per tutte le azioni.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected string $id;                  // ID dell'azione
protected string $name;                // Nome dell'azione
protected array $config;               // Configurazione dell'azione
protected string|null $lastExecuted;  // Timestamp ultima esecuzione
protected array $logs = [];           // Log delle esecuzioni
```

#### Metodi

##### `__construct(Registry $registry, string $id, string $name, array $config = [])`
- **Descrizione:** Inizializza l'azione base
- **Parametri:** 
  - `$registry` (Registry): Registro OpenCart
  - `$id` (string): ID dell'azione
  - `$name` (string): Nome dell'azione
  - `$config` (array): Configurazione (opzionale)
- **Restituisce:** void

##### `getId()`
- **Descrizione:** Restituisce l'ID univoco dell'azione
- **Parametri:** Nessuno
- **Restituisce:** string

##### `getName()`
- **Descrizione:** Restituisce il nome dell'azione
- **Parametri:** Nessuno
- **Restituisce:** string

##### `getConfig()`
- **Descrizione:** Restituisce la configurazione dell'azione
- **Parametri:** Nessuno
- **Restituisce:** array

##### `setConfig(array $config)`
- **Descrizione:** Imposta la configurazione dell'azione
- **Parametri:**
  - `$config` (array): Nuova configurazione
- **Restituisce:** void

##### `getLastExecuted()`
- **Descrizione:** Restituisce il timestamp dell'ultima esecuzione
- **Parametri:** Nessuno
- **Restituisce:** string|null

##### `setLastExecuted(string $timestamp)`
- **Descrizione:** Imposta il timestamp dell'ultima esecuzione
- **Parametri:**
  - `$timestamp` (string): Timestamp da impostare
- **Restituisce:** void

##### `log(string $message, array $context = [])`
- **Descrizione:** Aggiunge una voce al log dell'azione
- **Parametri:**
  - `$message` (string): Messaggio da loggare
  - `$context` (array): Contesto aggiuntivo (opzionale)
- **Restituisce:** void

##### `getLogs()`
- **Descrizione:** Restituisce i log dell'azione
- **Parametri:** Nessuno
- **Restituisce:** array

##### `validateConfig()`
- **Descrizione:** Valida la configurazione dell'azione
- **Parametri:** Nessuno
- **Restituisce:** bool

##### `execute(array $context)` (abstract)
- **Descrizione:** Esegue l'azione (metodo astratto da implementare)
- **Parametri:**
  - `$context` (array): Contesto di esecuzione
- **Restituisce:** mixed

##### `syncWithOpenCart()`
- **Descrizione:** Sincronizza lo stato dell'azione con OpenCart
- **Parametri:** Nessuno
- **Restituisce:** bool

### 5. BaseCondition

**Namespace:** `Opencart\System\Library\Mas\Core\BaseCondition`

**Descrizione:** Classe base astratta per le condizioni di automazione. Fornisce funzionalità comuni per tutte le condizioni.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected string $id;                  // ID della condizione
protected string $name;                // Nome della condizione
protected array $config;               // Configurazione della condizione
protected string|null $lastEvaluated; // Timestamp ultima valutazione
protected array $logs = [];           // Log delle valutazioni
```

#### Metodi

##### `__construct(Registry $registry, string $id, string $name, array $config = [])`
- **Descrizione:** Inizializza la condizione base
- **Parametri:** 
  - `$registry` (Registry): Registro OpenCart
  - `$id` (string): ID della condizione
  - `$name` (string): Nome della condizione
  - `$config` (array): Configurazione (opzionale)
- **Restituisce:** void

##### `getId()`
- **Descrizione:** Restituisce l'ID univoco della condizione
- **Restituisce:** string

##### `getName()`
- **Descrizione:** Restituisce il nome della condizione
- **Restituisce:** string

##### `getConfig()` / `setConfig(array $config)`
- **Descrizione:** Getter/Setter per la configurazione
- **Restituisce:** array / void

##### `getLastEvaluated()` / `setLastEvaluated(string $timestamp)`
- **Descrizione:** Getter/Setter per timestamp ultima valutazione
- **Restituisce:** string|null / void

##### `log(string $message, array $context = [])`
- **Descrizione:** Aggiunge voce al log della condizione
- **Restituisce:** void

##### `getLogs()`
- **Descrizione:** Restituisce i log della condizione
- **Restituisce:** array

##### `validateConfig()`
- **Descrizione:** Valida la configurazione della condizione
- **Restituisce:** bool

##### `evaluate(array $context)` (abstract)
- **Descrizione:** Valuta la condizione (metodo astratto)
- **Parametri:** `$context` (array): Contesto di valutazione
- **Restituisce:** bool

### 6. BaseTrigger

**Namespace:** `Opencart\System\Library\Mas\Core\BaseTrigger`

**Descrizione:** Classe base astratta per i trigger di automazione.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected string $id;                  // ID del trigger
protected string $name;                // Nome del trigger
protected array $config;               // Configurazione del trigger
protected string|null $lastTriggered; // Timestamp ultima attivazione
protected array $logs = [];           // Log delle attivazioni
```

#### Metodi

##### `__construct(Registry $registry, string $id, string $name, array $config = [])`
- **Descrizione:** Inizializza il trigger base
- **Parametri:** Registry, ID, nome, configurazione
- **Restituisce:** void

##### `getId()` / `getName()` / `getConfig()` / `setConfig(array $config)`
- **Descrizione:** Metodi getter/setter standard
- **Restituisce:** string / array / void

##### `getLastTriggered()` / `setLastTriggered(string $timestamp)`
- **Descrizione:** Gestione timestamp ultima attivazione
- **Restituisce:** string|null / void

##### `log(string $message, array $context = [])`
- **Descrizione:** Logging del trigger
- **Restituisce:** void

##### `getLogs()` / `validateConfig()` / `syncWithOpenCart()`
- **Descrizione:** Metodi di supporto
- **Restituisce:** array / bool / bool

##### `check(array $context)` (abstract)
- **Descrizione:** Verifica se il trigger deve attivarsi
- **Parametri:** `$context` (array): Contesto di valutazione
- **Restituisce:** bool

### 7. Campaign

**Namespace:** `Opencart\System\Library\Mas\Core\Campaign`

**Descrizione:** Gestisce le campagne di marketing automation (già documentata sopra).

### 8. ConsentManager

**Namespace:** `Opencart\System\Library\Mas\Core\ConsentManager`

**Descrizione:** Gestisce i consensi utente per il marketing automation e conformità GDPR.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected array $consents = [];       // Consensi registrati
protected array $consentHistory = []; // Cronologia consensi
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza il gestore consensi
- **Parametri:** `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `registerConsent(int $userId, string $consentType, bool $granted, array $context = [])`
- **Descrizione:** Registra un nuovo consenso per un utente
- **Parametri:**
  - `$userId` (int): ID utente
  - `$consentType` (string): Tipo di consenso
  - `$granted` (bool): Consenso concesso o negato
  - `$context` (array): Contesto aggiuntivo
- **Restituisce:** void

##### `updateConsent(int $userId, string $consentType, bool $granted, array $context = [])`
- **Descrizione:** Aggiorna un consenso esistente
- **Parametri:** Come registerConsent
- **Restituisce:** void

##### `revokeConsent(int $userId, string $consentType, array $context = [])`
- **Descrizione:** Revoca un consenso utente
- **Parametri:**
  - `$userId` (int): ID utente
  - `$consentType` (string): Tipo di consenso
  - `$context` (array): Contesto
- **Restituisce:** void

##### `hasConsent(int $userId, string $consentType)`
- **Descrizione:** Verifica se un utente ha concesso un consenso
- **Parametri:**
  - `$userId` (int): ID utente
  - `$consentType` (string): Tipo di consenso
- **Restituisce:** bool|null (true=concesso, false=negato, null=non registrato)

##### `getUserConsents(int $userId)`
- **Descrizione:** Restituisce tutti i consensi di un utente
- **Parametri:** `$userId` (int): ID utente
- **Restituisce:** array

##### `getUserConsentHistory(int $userId)`
- **Descrizione:** Restituisce la cronologia consensi di un utente
- **Parametri:** `$userId` (int): ID utente
- **Restituisce:** array

### 9. EventDispatcher

**Namespace:** `Opencart\System\Library\Mas\Core\EventDispatcher`

**Descrizione:** Gestisce centralmente gli eventi del sistema (già documentata sopra).

### 10. LandingPage

**Namespace:** `Opencart\System\Library\Mas\Core\LandingPage`

**Descrizione:** Gestisce le landing pages per le campagne di marketing.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected array $landingPages = [];   // Landing pages registrate
protected array $history = [];        // Cronologia modifiche
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza il gestore landing pages
- **Parametri:** `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `createLandingPage(string $id, string $title, string $content, array $config = [])`
- **Descrizione:** Crea una nuova landing page
- **Parametri:**
  - `$id` (string): ID univoco della landing page
  - `$title` (string): Titolo della pagina
  - `$content` (string): Contenuto HTML
  - `$config` (array): Configurazione (opzionale)
- **Restituisce:** array

##### `updateLandingPage(string $id, array $data)`
- **Descrizione:** Aggiorna una landing page esistente
- **Parametri:**
  - `$id` (string): ID della landing page
  - `$data` (array): Dati da aggiornare
- **Restituisce:** bool

##### `deleteLandingPage(string $id)`
- **Descrizione:** Elimina una landing page
- **Parametri:** `$id` (string): ID della landing page
- **Restituisce:** bool

##### `getLandingPage(string $id)`
- **Descrizione:** Restituisce una landing page per ID
- **Parametri:** `$id` (string): ID della landing page
- **Restituisce:** array|null

##### `publishLandingPage(string $id, string $url)`
- **Descrizione:** Pubblica una landing page con URL specifico
- **Parametri:**
  - `$id` (string): ID della landing page
  - `$url` (string): URL di pubblicazione
- **Restituisce:** bool

##### `unpublishLandingPage(string $id)`
- **Descrizione:** Rimuove una landing page dalla pubblicazione
- **Parametri:** `$id` (string): ID della landing page
- **Restituisce:** bool

### 11. ProviderInterface

**Namespace:** `Opencart\System\Library\Mas\Core\ProviderInterface`

**Descrizione:** Interfaccia per tutti i provider del sistema MAS.

#### Metodi Richiesti

##### `send(array $data)`
- **Descrizione:** Invia dati tramite il provider
- **Parametri:** `$data` (array): Dati da inviare
- **Restituisce:** bool

##### `authenticate(array $credentials)`
- **Descrizione:** Autentica con il provider
- **Parametri:** `$credentials` (array): Credenziali
- **Restituisce:** bool

##### `testConnection()`
- **Descrizione:** Testa la connessione al provider
- **Restituisce:** bool

##### `getName()`
- **Descrizione:** Restituisce il nome del provider
- **Restituisce:** string

##### `getDescription()`
- **Descrizione:** Restituisce la descrizione del provider
- **Restituisce:** string

##### `getConfigForm()`
- **Descrizione:** Restituisce il form di configurazione per l'admin
- **Restituisce:** array

### 12. ProviderManager

**Namespace:** `Opencart\System\Library\Mas\Core\ProviderManager`

**Descrizione:** Gestisce la registrazione e configurazione dei provider (già documentata sopra).

### 13. ReportManager

**Namespace:** `Opencart\System\Library\Mas\Core\ReportManager`

**Descrizione:** Gestisce report e analytics per la marketing automation suite.

#### Proprietà

```php
protected Registry $registry;          // Registro OpenCart
protected array $reports = [];        // Report registrati
protected array $reportHistory = [];  // Cronologia report
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza il gestore report
- **Parametri:** `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `createReport(string $id, string $name, array $metrics = [], array $filters = [], array $options = [])`
- **Descrizione:** Crea un nuovo report
- **Parametri:**
  - `$id` (string): ID univoco del report
  - `$name` (string): Nome del report
  - `$metrics` (array): Metriche da includere
  - `$filters` (array): Filtri da applicare
  - `$options` (array): Opzioni aggiuntive
- **Restituisce:** array

##### `updateReport(string $id, array $data)`
- **Descrizione:** Aggiorna un report esistente
- **Parametri:**
  - `$id` (string): ID del report
  - `$data` (array): Dati da aggiornare
- **Restituisce:** bool

##### `generateReport(string $id, array $context = [])`
- **Descrizione:** Genera i dati di un report
- **Parametri:**
  - `$id` (string): ID del report
  - `$context` (array): Contesto aggiuntivo
- **Restituisce:** array

##### `exportReport(string $id, string $format = 'csv')`
- **Descrizione:** Esporta un report in formato specifico
- **Parametri:**
  - `$id` (string): ID del report
  - `$format` (string): Formato (csv, pdf, json)
- **Restituisce:** array

### 14. Segment

**Namespace:** `Opencart\System\Library\Mas\Core\Segment`

**Descrizione:** Gestisce la segmentazione utenti (già documentata sopra).

### 15. Workflow

**Namespace:** `Opencart\System\Library\Mas\Core\Workflow`

**Descrizione:** Gestisce i workflow di automazione (già documentata sopra).

---

## Exception Classes

### Gerarchia delle Eccezioni

```
MASException (base)
├── AutomationException
├── CampaignException
├── DataException
├── LandingPageException
├── NotificationException
├── ProviderException
├── ReportException
└── SegmentException
```

### 1. MASException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\MASException`

**Descrizione:** Eccezione base per tutte le eccezioni del sistema MAS.

#### Proprietà

```php
protected array $context = [];        // Contesto aggiuntivo dell'errore
```

#### Metodi

##### `__construct(string $message, int $code = 0, array $context = [])`
- **Descrizione:** Inizializza l'eccezione
- **Parametri:**
  - `$message` (string): Messaggio di errore
  - `$code` (int): Codice di errore
  - `$context` (array): Contesto aggiuntivo
- **Restituisce:** void

##### `getContext()`
- **Descrizione:** Restituisce il contesto dell'errore
- **Restituisce:** array

### 2. AutomationException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\AutomationException`

**Descrizione:** Eccezione per errori di automazione.

**Estende:** MASException

### 3. CampaignException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\CampaignException`

**Descrizione:** Eccezione per errori nelle campagne.

**Estende:** MASException

### 4. DataException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\DataException`

**Descrizione:** Eccezione per errori nei dati.

**Estende:** MASException

### 5. LandingPageException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\LandingPageException`

**Descrizione:** Eccezione per errori nelle landing pages.

**Estende:** MASException

### 6. NotificationException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\NotificationException`

**Descrizione:** Eccezione per errori nelle notifiche.

**Estende:** MASException

### 7. ProviderException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\ProviderException`

**Descrizione:** Eccezione per errori dei provider.

**Estende:** MASException

### 8. ReportException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\ReportException`

**Descrizione:** Eccezione per errori nei report.

**Estende:** MASException

### 9. SegmentException

**Namespace:** `Opencart\System\Library\Mas\Exceptions\SegmentException`

**Descrizione:** Eccezione per errori nella segmentazione.

**Estende:** MASException

---

## Helper Classes

### 1. Logger

**Namespace:** `Opencart\System\Library\Mas\Helper\Logger`

**Descrizione:** Sistema di logging avanzato con supporto per file, database, API e cloud storage.

#### Proprietà

```php
protected \DB $db;                     // Database OpenCart
protected \Config $config;            // Configurazione OpenCart
protected string $logDir;             // Directory log
protected array $logConfig;           // Configurazione logging
```

#### Metodi

##### `__construct(Registry $registry)`
- **Descrizione:** Inizializza il logger
- **Parametri:** `$registry` (Registry): Registro OpenCart
- **Restituisce:** void

##### `setConfig(array $config)`
- **Descrizione:** Imposta la configurazione del logger
- **Parametri:** `$config` (array): Configurazione
- **Restituisce:** void

##### `log(string $message, string $level = 'info', array $context = [])`
- **Descrizione:** Registra un messaggio di log
- **Parametri:**
  - `$message` (string): Messaggio da loggare
  - `$level` (string): Livello (debug, info, warning, error, critical)
  - `$context` (array): Contesto aggiuntivo
- **Restituisce:** bool

### 2. DataHelper

**Namespace:** `Opencart\System\Library\Mas\Helper\DataHelper`

**Descrizione:** Helper per manipolazione e trasformazione dati.

#### Metodi

##### `sanitize($data)`
- **Descrizione:** Sanitizza i dati
- **Parametri:** `$data` (mixed): Dati da sanitizzare
- **Restituisce:** mixed

##### `transform(array $data, array $rules)`
- **Descrizione:** Trasforma i dati secondo regole specifiche
- **Parametri:**
  - `$data` (array): Dati da trasformare
  - `$rules` (array): Regole di trasformazione
- **Restituisce:** array

### 3. TemplateHelper

**Namespace:** `Opencart\System\Library\Mas\Helper\TemplateHelper`

**Descrizione:** Helper per gestione template e rendering.

#### Metodi

##### `render(string $template, array $data = [])`
- **Descrizione:** Renderizza un template con dati
- **Parametri:**
  - `$template` (string): Nome del template
  - `$data` (array): Dati per il template
- **Restituisce:** string

##### `compile(string $template)`
- **Descrizione:** Compila un template
- **Parametri:** `$template` (string): Template da compilare
- **Restituisce:** string

### 4. Utils

**Namespace:** `Opencart\System\Library\Mas\Helper\Utils`

**Descrizione:** Utility generali per il sistema MAS (già documentata sopra).

---

## Model Classes

### 1. CampaignModel

**Namespace:** `Opencart\System\Library\Mas\Model\CampaignModel`

**Descrizione:** Modello per gestione dati delle campagne (già documentata sopra).

### 2. ConsentModel

**Namespace:** `Opencart\System\Library\Mas\Model\ConsentModel`

**Descrizione:** Modello per gestione dati dei consensi utente.

#### Metodi

##### `addConsent(array $data)`
- **Descrizione:** Aggiunge un consenso al database
- **Parametri:** `$data` (array): Dati del consenso
- **Restituisce:** int

##### `updateConsent(int $consentId, array $data)`
- **Descrizione:** Aggiorna un consenso esistente
- **Parametri:**
  - `$consentId` (int): ID del consenso
  - `$data` (array): Dati da aggiornare
- **Restituisce:** bool

##### `getConsent(int $consentId)`
- **Descrizione:** Restituisce un consenso per ID
- **Parametri:** `$consentId` (int): ID del consenso
- **Restituisce:** array|null

### 3. EventModel

**Namespace:** `Opencart\System\Library\Mas\Model\EventModel`

**Descrizione:** Modello per gestione eventi del sistema.

#### Metodi

##### `addEvent(array $data)`
- **Descrizione:** Registra un evento nel database
- **Parametri:** `$data` (array): Dati dell'evento
- **Restituisce:** int

##### `getEvents(array $filters = [])`
- **Descrizione:** Restituisce eventi con filtri
- **Parametri:** `$filters` (array): Filtri di ricerca
- **Restituisce:** array

### 4. LandingPageModel

**Namespace:** `Opencart\System\Library\Mas\Model\LandingPageModel`

**Descrizione:** Modello per gestione dati delle landing pages.

#### Metodi

##### `addLandingPage(array $data)`
- **Descrizione:** Aggiunge una landing page al database
- **Parametri:** `$data` (array): Dati della landing page
- **Restituisce:** int

##### `updateLandingPage(int $pageId, array $data)`
- **Descrizione:** Aggiorna una landing page esistente
- **Parametri:**
  - `$pageId` (int): ID della landing page
  - `$data` (array): Dati da aggiornare
- **Restituisce:** bool

### 5. NotificationModel

**Namespace:** `Opencart\System\Library\Mas\Model\NotificationModel`

**Descrizione:** Modello per gestione notifiche sistema.

#### Metodi

##### `addNotification(array $data)`
- **Descrizione:** Aggiunge una notifica
- **Parametri:** `$data` (array): Dati della notifica
- **Restituisce:** int

##### `markAsRead(int $notificationId)`
- **Descrizione:** Marca una notifica come letta
- **Parametri:** `$notificationId` (int): ID della notifica
- **Restituisce:** bool

### 6. ProviderModel

**Namespace:** `Opencart\System\Library\Mas\Model\ProviderModel`

**Descrizione:** Modello per gestione configurazioni provider.

#### Metodi

##### `addProvider(array $data)`
- **Descrizione:** Aggiunge un provider al database
- **Parametri:** `$data` (array): Dati del provider
- **Restituisce:** int

##### `updateProvider(int $providerId, array $data)`
- **Descrizione:** Aggiorna un provider esistente
- **Parametri:**
  - `$providerId` (int): ID del provider
  - `$data` (array): Dati da aggiornare
- **Restituisce:** bool

### 7. ReportModel

**Namespace:** `Opencart\System\Library\Mas\Model\ReportModel`

**Descrizione:** Modello per gestione dati dei report.

#### Metodi

##### `addReport(array $data)`
- **Descrizione:** Aggiunge un report al database
- **Parametri:** `$data` (array): Dati del report
- **Restituisce:** int

##### `generateReportData(int $reportId, array $filters = [])`
- **Descrizione:** Genera i dati di un report
- **Parametri:**
  - `$reportId` (int): ID del report
  - `$filters` (array): Filtri da applicare
- **Restituisce:** array

### 8. SegmentModel

**Namespace:** `Opencart\System\Library\Mas\Model\SegmentModel`

**Descrizione:** Modello per gestione dati dei segmenti utente.

#### Metodi

##### `addSegment(array $data)`
- **Descrizione:** Aggiunge un segmento al database
- **Parametri:** `$data` (array): Dati del segmento
- **Restituisce:** int

##### `updateSegment(int $segmentId, array $data)`
- **Descrizione:** Aggiorna un segmento esistente
- **Parametri:**
  - `$segmentId` (int): ID del segmento
  - `$data` (array): Dati da aggiornare
- **Restituisce:** bool

##### `evaluateSegment(int $segmentId)`
- **Descrizione:** Valuta un segmento e restituisce utenti corrispondenti
- **Parametri:** `$segmentId` (int): ID del segmento
- **Restituisce:** array

---

## Provider Classes

### Struttura Provider

```
model/providers/
├── ai/              # Provider AI e Machine Learning
├── email/           # Provider Email (SMTP, API)
├── sms/             # Provider SMS
└── social/          # Provider Social Media
```

### Provider AI

**Namespace:** `Opencart\System\Library\Mas\Model\Providers\Ai`

**Descrizione:** Provider per servizi di intelligenza artificiale e machine learning.

#### Esempi di Provider AI

- **OpenAI** - Integrazione con GPT, DALL-E
- **Google AI** - Vertex AI, AutoML
- **Azure AI** - Cognitive Services
- **AWS AI** - SageMaker, Rekognition

### Provider Email

**Namespace:** `Opencart\System\Library\Mas\Model\Providers\Email`

**Descrizione:** Provider per servizi di invio email.

#### Esempi di Provider Email

- **SMTP** - Server SMTP generico
- **SendGrid** - API SendGrid
- **Mailgun** - API Mailgun
- **Amazon SES** - Amazon Simple Email Service

### Provider SMS

**Namespace:** `Opencart\System\Library\Mas\Model\Providers\Sms`

**Descrizione:** Provider per servizi di invio SMS.

#### Esempi di Provider SMS

- **Twilio** - API Twilio
- **AWS SNS** - Amazon Simple Notification Service
- **Vonage** - API Vonage (ex Nexmo)

### Provider Social

**Namespace:** `Opencart\System\Library\Mas\Model\Providers\Social`

**Descrizione:** Provider per social media e messaging.

#### Esempi di Provider Social

- **Facebook** - Facebook Graph API
- **Instagram** - Instagram API
- **Twitter** - Twitter API
- **LinkedIn** - LinkedIn API
- **WhatsApp** - WhatsApp Business API

---

## Esempi di Utilizzo

### 1. Inizializzazione del Sistema

```php
<?php
use Opencart\System\Library\Mas\Core\MAS;

// Inizializza il sistema MAS
$mas = new MAS($registry);
$mas->init();

// Configura il sistema
$mas->setConfig([
    'debug' => true,
    'log_level' => 'info',
    'provider_timeout' => 30
]);
```

### 2. Gestione delle Campagne

```php
<?php
use Opencart\System\Library\Mas\Model\CampaignModel;
use Opencart\System\Library\Mas\Exceptions\CampaignException;

$campaignModel = new CampaignModel($registry);

try {
    // Crea campagna
    $campaignId = $campaignModel->addCampaign([
        'name' => 'Black Friday 2024',
        'description' => 'Campagna promozionale Black Friday',
        'type' => 'email',
        'status' => 'draft',
        'settings' => [
            'subject' => 'Offerte Black Friday!',
            'template' => 'black_friday_template',
            'sender' => 'marketing@shop.com'
        ],
        'segments' => [1, 2, 3], // Segmenti target
        'schedule' => [
            'type' => 'scheduled',
            'start_date' => '2024-11-29 09:00:00',
            'end_date' => '2024-11-29 23:59:59'
        ]
    ]);

    // Aggiungi contenuto
    $campaignModel->addContent($campaignId, [
        'type' => 'email_html',
        'content' => [
            'html' => '<h1>Black Friday Deals!</h1>',
            'text' => 'Black Friday Deals!'
        ]
    ]);

    // Avvia campagna
    $campaignModel->startCampaign($campaignId);

    // Registra metriche
    $campaignModel->recordMetric($campaignId, 'emails_sent', 1500);
    $campaignModel->recordMetric($campaignId, 'opens', 450);
    $campaignModel->recordMetric($campaignId, 'clicks', 150);

} catch (CampaignException $e) {
    echo "Errore campagna: " . $e->getMessage();
}
```

### 3. Gestione dei Workflow

```php
<?php
use Opencart\System\Library\Mas\Core\Workflow;
use Opencart\System\Library\Mas\Core\AutomationAction;
use Opencart\System\Library\Mas\Core\AutomationTrigger;

$workflow = new Workflow($registry);
$actions = new AutomationAction($registry);
$triggers = new AutomationTrigger($registry);

// Registra azioni
$actions->registerAction('send_welcome_email', 'Send Welcome Email', function($context, $registry) {
    // Logica invio email di benvenuto
    return ['status' => 'sent', 'email' => $context['email']];
});

$actions->registerAction('add_to_newsletter', 'Add to Newsletter', function($context, $registry) {
    // Logica aggiunta newsletter
    return ['status' => 'added', 'user_id' => $context['user_id']];
});

// Registra trigger
$triggers->registerTrigger('user_registered', 'User Registered', function($context, $registry) {
    return isset($context['event']) && $context['event'] === 'user_register';
});

// Crea workflow
$workflow->createWorkflow('welcome_sequence', 'Welcome Sequence', [
    [
        'type' => 'trigger',
        'trigger_id' => 'user_registered'
    ],
    [
        'type' => 'action',
        'action_id' => 'send_welcome_email',
        'delay' => 0
    ],
    [
        'type' => 'action',
        'action_id' => 'add_to_newsletter',
        'delay' => 3600 // 1 ora dopo
    ]
]);

// Esegui workflow
$result = $workflow->executeWorkflow('welcome_sequence', [
    'event' => 'user_register',
    'user_id' => 123,
    'email' => 'user@example.com'
]);
```

### 4. Gestione dei Consensi (GDPR)

```php
<?php
use Opencart\System\Library\Mas\Core\ConsentManager;

$consentManager = new ConsentManager($registry);

// Registra consenso
$consentManager->registerConsent(
    userId: 123,
    consentType: 'marketing_emails',
    granted: true,
    context: [
        'ip' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0...',
        'source' => 'registration_form'
    ]
);

// Verifica consenso prima di inviare email
if ($consentManager->hasConsent(123, 'marketing_emails')) {
    // Invia email marketing
} else {
    // Non inviare email
}

// Revoca consenso
$consentManager->revokeConsent(123, 'marketing_emails', [
    'reason' => 'user_request',
    'source' => 'unsubscribe_link'
]);
```

### 5. Gestione dei Provider

```php
<?php
use Opencart\System\Library\Mas\Core\ProviderManager;

$providerManager = new ProviderManager($registry);

// Configura provider email
$emailProvider = new EmailProvider($registry);
$emailProvider->setConfig([
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your@gmail.com',
    'password' => 'your_password',
    'encryption' => 'tls'
]);

// Registra provider
$providerManager->registerProvider('email', 'gmail', $emailProvider);

// Test connessione
if ($emailProvider->testConnection()) {
    echo "Connessione email OK";
} else {
    echo "Errore connessione email";
}

// Invia email
$result = $emailProvider->send([
    'to' => 'customer@example.com',
    'subject' => 'Test Email',
    'body' => 'This is a test email'
]);
```

### 6. Report e Analytics

```php
<?php
use Opencart\System\Library\Mas\Core\ReportManager;

$reportManager = new ReportManager($registry);

// Crea report
$reportManager->createReport('monthly_campaign', 'Monthly Campaign Report', [
    'campaigns_sent' => 'count',
    'total_opens' => 'sum',
    'total_clicks' => 'sum',
    'conversion_rate' => 'percentage'
], [
    'date_range' => 'last_30_days',
    'status' => 'completed'
]);

// Genera report
$reportData = $reportManager->generateReport('monthly_campaign');

// Esporta report
$csvData = $reportManager->exportReport('monthly_campaign', 'csv');
```

---

## Best Practices

### 1. Gestione degli Errori

```php
<?php
use Opencart\System\Library\Mas\Exceptions\MASException;

try {
    $mas->init();
    // Operazioni MAS
} catch (MASException $e) {
    // Log dell'errore
    $logger->log('MAS Error: ' . $e->getMessage(), 'error', $e->getContext());
    
    // Notifica amministratore
    $notificationModel->addNotification([
        'type' => 'error',
        'message' => 'MAS System Error: ' . $e->getMessage(),
        'context' => $e->getContext()
    ]);
}
```

### 2. Configurazione Sicura

```php
<?php
// Configurazione sicura dei provider
$providerConfig = [
    'api_key' => $this->config->get('mas_sendgrid_api_key'),
    'webhook_secret' => $this->config->get('mas_webhook_secret'),
    'encrypt_data' => true,
    'rate_limit' => 100, // richieste/minuto
    'timeout' => 30
];

// Validazione dati
$utils = new Utils($registry);
$cleanData = $utils->sanitize($inputData);
```

### 3. Performance e Scalabilità

```php
<?php
// Usa cache per configurazioni
$config = $cache->get('mas_config_' . $providerId);
if (!$config) {
    $config = $providerModel->getProviderConfig($providerId);
    $cache->set('mas_config_' . $providerId, $config, 3600);
}

// Batch processing
$batchSize = 100;
$users = array_chunk($segmentUsers, $batchSize);
foreach ($users as $userBatch) {
    $this->processBatch($userBatch);
}
```

### 4. Logging e Monitoraggio

```php
<?php
$logger = new Logger($registry);
$logger->setConfig([
    'destination' => 'file',
    'log_level' => 'info',
    'rotation' => true,