# Reconstruction de l'interface Replication Manager dans PmaControl

## Objectif

Ce document consolide l'analyse préalable à une reconstruction de l'interface
de Replication Manager ("repman") dans PmaControl, en s'appuyant sur :

- le dépôt source repman cloné localement dans `/tmp/replication-manager`
- la documentation API `doc/api_latest.md`
- la spécification OpenAPI `docs/swagger.json` et `docs/swagger.yaml`
- l'inventaire des écrans et contrôleurs déjà présents dans PmaControl

L'objectif n'est pas encore l'implémentation, mais la définition :

- des écrans à reproduire
- des appels API à consommer
- des écarts fonctionnels entre repman et PmaControl
- d'un plan d'intégration crédible dans l'architecture actuelle de PmaControl

## Sources analysées

### Replication Manager

- Dépôt cloné : `/tmp/replication-manager`
- Documentation API : `/tmp/replication-manager/doc/api_latest.md`
- Swagger JSON : `/tmp/replication-manager/docs/swagger.json`
- Swagger YAML : `/tmp/replication-manager/docs/swagger.yaml`
- Fichiers serveur API identifiés :
  - `/tmp/replication-manager/server/api.go`
  - `/tmp/replication-manager/server/api_cluster.go`
  - `/tmp/replication-manager/server/api_database.go`
  - `/tmp/replication-manager/server/api_proxy.go`
  - `/tmp/replication-manager/server/api_app.go`
  - `/tmp/replication-manager/server/repmanv3.go`

### PmaControl

- Contrôleurs : `/srv/www/pmacontrol/App/Controller`
- Vues : `/srv/www/pmacontrol/App/view`
- API REST locale déjà ajoutée : `/srv/www/pmacontrol/App/Controller/Api.php`

## Constats structurants

### 1. L'API repman est très large

La spécification Swagger expose `343` chemins HTTP. L'API ne couvre pas
uniquement la supervision, mais également :

- la gestion de clusters
- la topologie de réplication
- les actions opérationnelles
- les backups et archives
- la gestion des proxys
- la configuration et les variables
- les certificats
- les tests et tâches
- la gestion applicative
- les utilisateurs et abonnements

### 2. Le front repman n'est pas directement isolé dans le dépôt

Le dépôt cloné ne montre pas un répertoire front-end autonome évident
équivalent à un `ui/`, `webapp/` ou `frontend/` exploitable tel quel.

Conséquence :

- l'identification des écrans doit être inférée depuis les groupes d'API,
  les modèles OpenAPI et les handlers backend
- la reproduction dans PmaControl doit être pensée comme une réécriture UI,
  pas comme un port direct des templates de repman

### 3. PmaControl dispose déjà d'une base fonctionnelle partielle

PmaControl possède déjà plusieurs zones proches de repman :

- supervision serveur : `Server`, `MysqlServer`, `Monitoring`, `Dashboard`
- réplication : `Replication`, `MasterSlave`, `Slave`, `Galera`, `Cluster`
- backup et archives : `Backup`, `Archives`, `Binlog`
- schéma et base : `Database`, `Schema`, `Table`, `MysqlTable`
- proxys : `ProxySQL`, `Haproxy`, `MaxScale`
- audit et logs : `Audit`, `Log`, `PostMortem`
- configuration : `Variable`, `CompareConfig`, `Environment`, `Tag`

Conséquence :

- une partie de l'interface repman peut être reconstruite par agrégation
  d'écrans déjà existants
- le travail principal porte sur l'unification de l'expérience utilisateur,
  la navigation et la consommation directe de l'API repman

## Domaines fonctionnels identifiés dans la doc API repman

Les sections majeures extraites de `api_latest.md` sont :

- `auth`
- `cluster`
- `cluster_actions`
- `cluster_backup`
- `cluster_backups`
- `cluster_certificates`
- `cluster_graphite`
- `cluster_health`
- `cluster_maintenance`
- `cluster_monitor`
- `cluster_provision`
- `cluster_replication`
- `cluster_schema`
- `cluster_settings`
- `cluster_tags`
- `cluster_test`
- `cluster_topology`
- `cluster_traffics`
- `cluster_vault`
- `database`
- `database_actions`
- `database_backup`
- `database_logs`
- `database_maintenance`
- `database_provision`
- `database_queries`
- `database_replication`
- `database_schema`
- `database_tasks`
- `database_topology`
- `global_setting`
- `proxies`
- `public`
- `replication`
- `terminal`
- `user`

## Écrans à reconstruire dans PmaControl

La liste ci-dessous représente les écrans nécessaires pour proposer une
interface "équivalente repman" dans PmaControl. Elle est déduite à partir
des groupes d'API et des ressources manipulées.

### 1. Authentification et contexte

- écran de connexion API repman
- sélecteur de cluster
- vue d'accueil multi-clusters
- indicateur d'état global du manager

API pressenties :

- `POST /api/login`
- `GET /api/clusters`
- `GET /api/monitor`
- `GET /api/status`

### 2. Tableau de bord cluster

- synthèse d'état du cluster
- santé globale
- statut des nœuds
- statut de réplication
- backlog, jobs, alertes et événements actifs

API pressenties :

- `GET /api/clusters/{clusterName}`
- `GET /api/clusters/{clusterName}/health`
- `GET /api/clusters/{clusterName}/status`
- `GET /api/clusters/{clusterName}/top`
- `GET /api/clusters/{clusterName}/jobs`

### 3. Topologie et incidents

- topologie maître/réplicas
- état des rôles et des liens
- historique des crashes
- détection des split-brain / incohérences
- détails d'un nœud dans la topologie

API pressenties :

- `GET /api/clusters/{clusterName}/topology`
- `GET /api/clusters/{clusterName}/topology/crashes`
- `GET /api/clusters/{clusterName}/servers/{serverName}`

### 4. Gestion détaillée des serveurs

- fiche serveur
- maintenance
- reseed
- provisionnement
- redémarrage, optimisation, flush logs
- backup de logs
- paramètres serveur et état des tâches

API pressenties :

- `GET /api/clusters/{clusterName}/servers/{serverName}`
- `GET /api/clusters/{clusterName}/servers/{serverName}/actions/*`

### 5. Actions cluster

- failover
- switchover
- rolling restart
- rolling reprovision
- bootstrap / nettoyage de la réplication
- suppression cluster

API pressenties :

- `POST /api/clusters/{clusterName}/actions/failover`
- `POST /api/clusters/{clusterName}/actions/switchover`
- `POST /api/clusters/{clusterName}/actions/rolling`
- `POST /api/clusters/{clusterName}/actions/replication/bootstrap/{topology}`
- `POST /api/clusters/{clusterName}/actions/replication/cleanup`
- `DELETE /api/clusters/actions/delete/{clusterName}`

### 6. Backups et archives

- liste des backups
- statistiques de backup
- archives et purge
- file de tâches archives/backups
- déclenchement de backup logique/physique

API pressenties :

- `GET /api/clusters/{clusterName}/backups`
- `GET /api/clusters/{clusterName}/backups/stats`
- `GET /api/clusters/{clusterName}/archives`
- `GET /api/clusters/{clusterName}/archives/stats`
- `GET /api/clusters/{clusterName}/archives/task-queue`

### 7. Schéma et variables

- aperçu du schéma
- différences de variables
- paramètres cluster
- bascule de settings
- configuration globale du manager

API pressenties :

- `GET /api/clusters/{clusterName}/schema`
- `GET /api/clusters/{clusterName}/diffvariables`
- `GET /api/clusters/{clusterName}/settings`
- `POST /api/clusters/{clusterName}/settings/actions/set/{settingName}/{settingValue}`
- `POST /api/clusters/settings/actions/set/{settingName}/{settingValue}`

### 8. Proxys

- liste des proxys par cluster
- détail d'un proxy
- besoin de restart / reprovision
- état runtime et configuration

API pressenties :

- `GET /api/clusters/{clusterName}/proxies/{proxyName}`
- `GET /api/clusters/{clusterName}/proxies/{proxyName}/actions/need-restart`
- `GET /api/clusters/{clusterName}/proxies/{proxyName}/actions/need-reprov`

### 9. Réplication

- état réplication cluster
- actions de rebootstrap
- actions de reset / repositionnement
- vue dédiée incidents de réplication

API pressenties :

- ressources `cluster_replication`
- ressources `database_replication`
- ressources `replication`

### 10. Requêtes, logs et diagnostics

- vues des logs serveur
- diagnostics SQL
- tâches en cours
- requêtes PFS / top queries
- exécution terminal ou outils d'assistance

API pressenties :

- groupes `database_logs`
- groupes `database_queries`
- groupes `database_tasks`
- groupe `terminal`

### 11. Certificats, tags, sécurité

- liste et gestion des certificats clients
- tags cluster
- abonnements utilisateurs
- gestion des accès utilisateurs

API pressenties :

- `GET /api/clusters/{clusterName}/certificates`
- groupes `cluster_tags`
- `POST /api/clusters/{clusterName}/subscribe`
- groupes `user`

### 12. Tests, maintenance et opérations avancées

- exécution de tests cluster
- affichage des résultats de tests
- maintenance cluster
- opérations de provisioning
- restauration de service

API pressenties :

- `POST /api/clusters/{clusterName}/tests/actions/run/all`
- `POST /api/clusters/{clusterName}/tests/actions/run/{testName}`
- groupes `cluster_maintenance`
- groupes `cluster_provision`

## Correspondance avec les écrans déjà présents dans PmaControl

### Couverture déjà proche

Les zones suivantes de PmaControl sont réutilisables conceptuellement :

- supervision cluster/réplication :
  - `App/Controller/Cluster.php`
  - `App/Controller/Replication.php`
  - `App/Controller/Galera.php`
- supervision serveur :
  - `App/Controller/Server.php`
  - `App/Controller/MysqlServer.php`
  - `App/Controller/Monitoring.php`
  - `App/Controller/Dashboard.php`
- backups :
  - `App/Controller/Backup.php`
  - `App/Controller/Archives.php`
  - `App/Controller/Binlog.php`
- schéma / métadonnées :
  - `App/Controller/Schema.php`
  - `App/Controller/Database.php`
  - `App/Controller/Table.php`
  - `App/Controller/Variable.php`
- proxys :
  - `App/Controller/ProxySQL.php`
  - `App/Controller/Haproxy.php`
  - `App/Controller/MaxScale.php`

### Manques majeurs

Les écarts les plus importants par rapport à repman sont :

- absence d'une navigation cluster-first unifiée
- absence d'un client API repman centralisé
- absence d'un écran consolidé "actions cluster"
- absence d'une UI homogène pour jobs, task queues et health
- absence probable d'une vue complète de topologie repman
- absence d'une couche d'authentification dédiée à l'API repman
- absence d'écrans unifiés pour utilisateurs, certificats, tags et tests

## Proposition d'architecture dans PmaControl

### Principe général

Ne pas tenter de greffer les `343` routes directement dans les contrôleurs
existants. Il est préférable d'introduire une couche dédiée repman :

- un client HTTP repman
- des DTO / tableaux normalisés de réponse
- des contrôleurs UI dédiés à l'intégration repman
- des vues dédiées, mais réutilisant les composants visuels existants

### Structure recommandée

- `App/Library/Repman/Client.php`
  - gestion auth, token, timeout, erreurs
- `App/Library/Repman/ClusterService.php`
  - lecture et normalisation des ressources cluster
- `App/Library/Repman/ProxyService.php`
  - lecture des proxys et actions associées
- `App/Library/Repman/BackupService.php`
  - backups, archives, task queues
- `App/Library/Repman/TopologyService.php`
  - topologie, crashs, health, jobs
- `App/Controller/Repman.php`
  - écrans principaux
- `App/Controller/RepmanApi.php`
  - éventuels endpoints internes pour appels AJAX côté UI
- `App/view/Repman/*`
  - écrans dédiés

## Ordre d'implémentation recommandé

### Phase 1 : fondation

- authentification repman
- sélecteur de cluster
- tableau de bord cluster
- topologie simplifiée
- fiche serveur lecture seule

### Phase 2 : exploitation

- backups et archives
- proxys
- variables et settings
- jobs et task queues
- historique des crashes

### Phase 3 : actions

- failover / switchover
- maintenance
- rolling restart / reprovision
- bootstrap réplication
- tests cluster

### Phase 4 : administration avancée

- certificats
- tags
- utilisateurs / abonnements
- terminal / diagnostics avancés
- provisioning avancé

## Risques techniques

### 1. Modèle de données très différent

Repman est orienté "cluster manager" et expose des ressources métier déjà
agrégées. PmaControl est historiquement plus proche d'un outillage
d'observabilité, d'administration et d'analyse technique.

Conséquence :

- il faudra transformer la navigation PmaControl pour la centrer davantage
  sur le cluster et ses actions

### 2. Écart de volumétrie fonctionnelle

Reproduire "tous les écrans" signifie couvrir un spectre large :

- monitoring
- exploitation
- automatisation
- sécurité
- backup
- gouvernance cluster

Cela dépasse une simple intégration d'API ponctuelle.

### 3. Gestion des droits et des actions destructrices

Les routes repman incluent des actions à fort impact :

- failover
- switchover
- reset replication
- provision
- reseed
- suppression cluster

Il faudra donc prévoir :

- confirmation UI forte
- journalisation
- contrôle d'accès fin
- traces d'audit

## Recommandation

La bonne approche consiste à construire un module PmaControl dédié à repman,
avec un périmètre initial limité aux écrans suivants :

- login / sélection cluster
- dashboard cluster
- topologie
- fiche serveur
- backups / archives
- proxys
- variables / settings
- historique des crashes et jobs

Ce socle couvrira l'essentiel de la valeur opérationnelle, tout en laissant les
actions sensibles pour une seconde itération.

## Étape suivante

La suite logique est de produire une spécification fonctionnelle détaillée
écran par écran, avec pour chaque vue :

- objectif métier
- route PmaControl cible
- appels repman consommés
- composants UI requis
- actions disponibles
- gestion des erreurs
- dépendances de sécurité

Ce document peut ensuite servir de base à l'implémentation effective dans
PmaControl.
