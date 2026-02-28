# Pack d’implémentation + PR (branche `commercial`)

Ce document remplace une roadmap trop théorique par un **plan exécutable**, avec une **implémentation concrète** et une **PR dédiée par domaine**.

## Règles de livraison (obligatoires)
- Branche de base: `commercial`
- Une branche par domaine, dérivée de `commercial`
- Une PR par domaine, avec tests et critères d’acceptation vérifiables
- Convention: `commercial/<domaine>/<lot>`

---

## 1) Compatibility Matrix
### Implémentation
- **SQL**: créer tables `compat_engine`, `compat_version`, `compat_feature`, `compat_result`.
- **Backend**:
  - `App/Controller/CompatibilityController.php`
  - `App/Model/CompatibilityFeature.php`
  - `App/Model/CompatibilityResult.php`
  - endpoint `GET /api/compatibility/matrix`
- **UI**:
  - `App/View/Compatibility/index.php` (grille + filtres engine/version/capability)
- **Tests**:
  - `tests/App/Controller/CompatibilityControllerTest.php`
  - `tests/App/Service/CompatibilityMatrixServiceTest.php`

### PR dédiée
- Branche: `commercial/compatibility/matrix-mvp`
- Titre PR: `feat(compatibility): add mysql protocol family compatibility matrix`
- Scope PR: modèle + API + écran matrice + export JSON

---

## 2) Upgrade Advisor multi-moteurs
### Implémentation
- **SQL**: tables `upgrade_path`, `upgrade_rule`, `upgrade_check_result`.
- **Backend**:
  - `App/Controller/UpgradeAdvisorController.php`
  - `App/Service/UpgradeAdvisorManager.php`
  - endpoint `POST /api/upgrade/simulate`
- **Règles initiales**:
  - MySQL 8.0 -> 8.4
  - MariaDB 10.6 -> 11.4
- **Tests**:
  - `tests/App/Service/UpgradeAdvisorManagerTest.php`
  - fixtures SQL de règles

### PR dédiée
- Branche: `commercial/upgrade/advisor-mvp`
- Titre PR: `feat(upgrade): add multi-engine upgrade advisor with rule engine`
- Scope PR: moteur de règles + simulation + checklist JSON

---

## 3) Config Diff & Baselines
### Implémentation
- **SQL**: `config_baseline`, `config_variable`, `config_diff_report`.
- **Backend**:
  - `App/Controller/ConfigDiffController.php`
  - `App/Service/ConfigBaselineManager.php`
  - endpoint `POST /api/config/diff`
- **UI**:
  - `App/View/ConfigDiff/index.php` (current/recommended/default-next)
- **Tests**:
  - `tests/App/Service/ConfigBaselineManagerTest.php`

### PR dédiée
- Branche: `commercial/config/diff-baselines`
- Titre PR: `feat(config): add baseline catalog and config diff report`
- Scope PR: baseline + diff + priorisation

---

## 4) Schema Change Center (safe DDL)
### Implémentation
- **SQL**: `ddl_plan`, `ddl_execution`, `ddl_audit_log`.
- **Backend**:
  - `App/Controller/SchemaChangeController.php`
  - `App/Service/SafeDdlManager.php`
  - wrappers `bin/ddl/ptosc-wrapper.sh` et `bin/ddl/ghost-wrapper.sh`
- **UI**:
  - écran plan/execute avec drapeau `dry-run`
- **Tests**:
  - `tests/App/Service/SafeDdlManagerTest.php`

### PR dédiée
- Branche: `commercial/ddl/schema-change-center`
- Titre PR: `feat(ddl): add safe schema change planner and execution hooks`
- Scope PR: plan d’impact + exécution protégée + audit trail

---

## 5) Query Digest & Regression Lab
### Implémentation
- **SQL**: `query_digest_sample`, `query_digest_aggregate`, `query_regression`.
- **Backend**:
  - `App/Controller/QueryDigestController.php`
  - `App/Service/QueryDigestManager.php`
  - endpoint `POST /api/query-digest/import`
- **UI**:
  - top latency/p95/p99 + comparaison avant/après
- **Tests**:
  - `tests/App/Service/QueryDigestManagerTest.php`

### PR dédiée
- Branche: `commercial/perf/query-digest-lab`
- Titre PR: `feat(perf): add query digest ingestion and regression analysis`
- Scope PR: parser + ranking + régression

---

## 6) Topology & Failover Cockpit
### Implémentation
- **SQL**: `topology_node`, `topology_link`, `failover_action_log`.
- **Backend**:
  - `App/Controller/TopologyController.php`
  - `App/Service/FailoverManager.php`
  - endpoint `POST /api/topology/action`
- **UI**:
  - graphe + actions promote/switchover/reroute avec préchecks
- **Tests**:
  - `tests/App/Service/FailoverManagerTest.php`

### PR dédiée
- Branche: `commercial/topology/failover-cockpit`
- Titre PR: `feat(topology): add replication graph and safe failover actions`
- Scope PR: visualisation + garde-fous + journal

---

## 7) Proxy Layer Manager
### Implémentation
- **SQL**: `proxy_instance`, `proxy_profile`, `proxy_policy_audit`.
- **Backend**:
  - `App/Controller/ProxyLayerController.php`
  - `App/Service/ProxyProfileManager.php`
  - adaptateurs ProxySQL/MaxScale/Router
- **UI**:
  - profils RW split + policy firewall + compliance TLS/auth
- **Tests**:
  - `tests/App/Service/ProxyProfileManagerTest.php`

### PR dédiée
- Branche: `commercial/proxy/layer-manager`
- Titre PR: `feat(proxy): add unified proxy profile manager and hardening checks`
- Scope PR: profils, simulation, compliance report

---

## 8) Migration Wizard MySQL -> X
### Implémentation
- **SQL**: `migration_project`, `migration_gap`, `migration_checklist`.
- **Backend**:
  - `App/Controller/MigrationWizardController.php`
  - `App/Service/MigrationWizardManager.php`
  - cibles: TiDB, SingleStore, ClickHouse
- **UI**:
  - wizard en 5 étapes + export runbook
- **Tests**:
  - `tests/App/Service/MigrationWizardManagerTest.php`

### PR dédiée
- Branche: `commercial/migration/wizard-mysql-protocol`
- Titre PR: `feat(migration): add mysql protocol migration wizard for target engines`
- Scope PR: gap analysis + runbook + validation

---

## 9) Vitess Planner
### Implémentation
- **SQL**: `vitess_rule`, `vitess_analysis_report`.
- **Backend**:
  - `App/Controller/VitessPlannerController.php`
  - `App/Service/VitessPlannerManager.php`
- **UI**:
  - score de readiness + requêtes non compat + recommandations
- **Tests**:
  - `tests/App/Service/VitessPlannerManagerTest.php`

### PR dédiée
- Branche: `commercial/vitess/planner`
- Titre PR: `feat(vitess): add query suitability planner and sharding recommendations`
- Scope PR: règles + scoring + rapport

---

## 10) EOL / Lifecycle Radar
### Implémentation
- **SQL**: `lifecycle_engine`, `lifecycle_version`, `lifecycle_alert`.
- **Backend**:
  - `App/Controller/LifecycleController.php`
  - `App/Service/LifecycleRadarManager.php`
- **UI**:
  - radar support window + alertes 90/180/365 jours
- **Tests**:
  - `tests/App/Service/LifecycleRadarManagerTest.php`

### PR dédiée
- Branche: `commercial/lifecycle/eol-radar`
- Titre PR: `feat(lifecycle): add support lifecycle radar and proactive alerts`
- Scope PR: import lifecycle + alerting + portefeuille

---

## Ordre recommandé d’ouverture des PR (branche `commercial`)
1. `commercial/compatibility/matrix-mvp`
2. `commercial/upgrade/advisor-mvp`
3. `commercial/config/diff-baselines`
4. `commercial/ddl/schema-change-center`
5. `commercial/perf/query-digest-lab`
6. `commercial/topology/failover-cockpit`
7. `commercial/proxy/layer-manager`
8. `commercial/migration/wizard-mysql-protocol`
9. `commercial/vitess/planner`
10. `commercial/lifecycle/eol-radar`

## Définition de terminé (pour chaque PR)
- Migration SQL incluse + rollback documenté
- API testée (unit + intégration)
- UI livrée avec état vide/erreur/chargement
- Documentation opérateur dans `documentation/`
- Commandes de validation listées dans la description PR
