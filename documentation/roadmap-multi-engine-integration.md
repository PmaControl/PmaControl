# Roadmap d’intégration complète des fonctionnalités manquantes (PmaControl)

## 1) Objectif global

Faire de PmaControl la plateforme de pilotage **#1 pour la famille “MySQL protocol”** (MySQL, MariaDB, Vitess, TiDB, ClickHouse, SingleStore, ProxySQL/MaxScale/Router), avec une exécution livrée par **PR indépendantes par domaine**.

---

## 2) Principes d’exécution

### 2.1 Cadre de livraison
- 1 domaine fonctionnel = 1 epic produit = 1 série de PR dédiées.
- Chaque domaine est livré en 3 couches :
  1. **Data/Intel** (collecte + normalisation),
  2. **Règles/Services** (moteur d’analyse),
  3. **UI/Workflow** (écran opérateur + actions guidées).
- Les PR restent petites (1000 lignes max ciblées) et testables isolément.

### 2.2 Convention de branches / PR
- Branche: `feature/<domaine>-<sous-scope>`
- PR: `feat(<domaine>): <capability>`
- Labels recommandés: `domain:<name>`, `impact:high|medium|low`, `risk:low|med|high`, `ops`, `security`, `migration`.

### 2.3 Modèle de données transverse (à créer en premier)
Nouveau schéma logique “intel catalog” :
- `intel_source` (vendor, url, type, fréquence)
- `intel_release_note` (vendor, produit, version, date, raw_ref)
- `intel_change` (type: breaking/default/removed/security/perf/compat)
- `intel_rule` (condition + recommandation + sévérité)
- `compat_capability` (feature, status, evidence)
- `upgrade_check` (check SQL, expected state, remediation)

---

## 3) Pipeline “Intel Ingestion” (fondation obligatoire)

## 3.1 Sources
- Release notes: MySQL, MariaDB, ProxySQL, MaxScale, Vitess, TiDB, ClickHouse, SingleStore.
- GitHub Releases: ProxySQL, Orchestrator, outils DBA.
- Docs compat/limitations: Vitess/TiDB/ClickHouse/SingleStore.
- Curated lists: awesome-mysql / awesome db tools.
- Blogs ops validés manuellement (whitelist).

## 3.2 Etapes techniques
1. **Connecteurs** (HTML/PDF/GitHub API/Markdown).
2. **Extraction** (version, composant, type de changement, impact, preuve).
3. **Normalisation** (taxonomie commune).
4. **Scorage** (sévérité + confidence).
5. **Publication** vers modules produits.

## 3.3 Règles de qualité
- Aucun item exploitable sans `source_url` + `version` + `evidence snippet`.
- Déduplication par `(vendor, product, version, fingerprint)`.
- Horodatage ingestion + checksum source.

## 3.4 PR du domaine “Intel Ingestion”
- PR-INTEL-1: schéma DB + repositories.
- PR-INTEL-2: connecteurs MySQL/MariaDB/ProxySQL.
- PR-INTEL-3: connecteurs Vitess/TiDB/ClickHouse/SingleStore.
- PR-INTEL-4: parser PDF + normaliseur + tests de non-régression.

---

## 4) Plan détaillé par fonctionnalité (avec PR par domaine)

## Domaine A — Compatibility Matrix (MySQL Protocol Family)
### But
Afficher “ce qui marche / ce qui casse” par moteur et version.

### Fonctionnalités
- Matrice par moteur/version:
  - auth plugins,
  - TLS/SSL,
  - charset/collation,
  - SQL features,
  - limitations connues.
- Simulateur “mon client/driver fonctionne ?”.
- Export CSV/JSON pour audit.

### Implémentation
- Backend: service `CompatibilityMatrixService` alimenté par `compat_capability`.
- UI: écran grille + filtres (engine/version/category/severity).
- Alerting: badge “incompatible bloquant”.

### Critères d’acceptation
- Au moins 6 moteurs couverts.
- 1 clic pour comparaison inter-moteurs.
- Données traçables vers source.

### PRs
- PR-COMPAT-1: modèle + API REST.
- PR-COMPAT-2: écran matrice + filtres.
- PR-COMPAT-3: simulateur driver/client + export.

---

## Domaine B — Upgrade Advisor multi-moteurs
### But
Préparer un upgrade sans surprise (breaking/default/removed).

### Fonctionnalités
- Sélecteur source/target version.
- Liste ordonnée de checks pré-upgrade.
- SQL auto-généré de détection.
- Plan d’exécution et rollback logique.

### Implémentation
- Règles versionnées (`upgrade_check`).
- Génération checklist par sévérité.
- “Run simulation” sur inventaire config + schémas.

### Critères d’acceptation
- Couverture initiale: MySQL 8.0→8.4 et MariaDB 10.6→11.4.
- 0 recommandation sans remédiation proposée.

### PRs
- PR-UPGRADE-1: moteur de règles + scoring.
- PR-UPGRADE-2: générateur SQL checks.
- PR-UPGRADE-3: UI checklist + export runbook.

---

## Domaine C — Config Diff & Baselines
### But
Comparer la conf réelle à un baseline “golden config”.

### Fonctionnalités
- Baselines par profil (OLTP NVMe, OLAP, Galera, Vitess).
- Diff: valeur actuelle vs recommandée vs nouveau défaut.
- Explication du risque + priorité de correction.

### Implémentation
- Catalogues de variables par moteur/version.
- Moteur de diff + impact estimator.
- Historique des changements.

### Critères d’acceptation
- Affichage des defaults changés entre versions.
- Suggestion de patch prête à appliquer.

### PRs
- PR-CONFIG-1: baseline catalog + persistance.
- PR-CONFIG-2: moteur diff + priorisation.
- PR-CONFIG-3: UI compare + patch preview.

---

## Domaine D — Schema Change Center (safe DDL)
### But
Rendre les changements de schéma prévisibles et sûrs.

### Fonctionnalités
- Mode plan: estimation locks/impact/réplication.
- Mode execute: wrappers pt-osc / gh-ost (si disponible).
- Audit trail complet (qui, quoi, quand, résultat).

### Implémentation
- Analyseur DDL + estimateur volumétrie.
- Orchestrateur de jobs avec états.
- Connecteurs outils externes avec garde-fous.

### Critères d’acceptation
- Refus d’exécution si risque critique non validé.
- Rapport avant/après archivé.

### PRs
- PR-DDL-1: planificateur d’impact.
- PR-DDL-2: exécuteur job + intégration outillage.
- PR-DDL-3: écran runbook + audit.

---

## Domaine E — Query Digest & Regression Lab
### But
Identifier les régressions de performance avant production.

### Fonctionnalités
- Ingestion slow logs / perf schema digests.
- Classements type pt-query-digest.
- Comparaison “avant/après” release.

### Implémentation
- Pipeline parse + fingerprint SQL.
- Métriques p95/p99, tmp tables, rows examined.
- Suggestion d’optimisation contextualisée.

### Critères d’acceptation
- Top N requêtes par impact.
- Détection automatique de régression > seuil.

### PRs
- PR-DIGEST-1: parser + stockage agrégé.
- PR-DIGEST-2: moteur ranking/régression.
- PR-DIGEST-3: UI analysis + diff timelines.

---

## Domaine F — Topology & Failover Cockpit
### But
Piloter la HA/réplication avec actions sécurisées.

### Fonctionnalités
- Graphe topologie (replication, Galera, shards Vitess).
- Actions: promote/switchover/reroute.
- Guardrails: préchecks automatiques.

### Implémentation
- Collecte d’état cluster périodique.
- Moteur de décision “safe action”.
- Journal opérationnel et annulation quand possible.

### Critères d’acceptation
- Toute action exige un état “safe”.
- Vue temps réel + historique incidents.

### PRs
- PR-TOPO-1: modèle topo + collecteurs.
- PR-TOPO-2: rendu graphe + statuts.
- PR-TOPO-3: actions orchestrées + garde-fous.

---

## Domaine G — Proxy Layer Manager (ProxySQL/MaxScale/Router)
### But
Unifier routage, firewall SQL et hardening proxy.

### Fonctionnalités
- Profils de routage read/write split.
- Gestion firewall/allowlist SQL (MaxScale).
- Version watch + recommandations hardening.

### Implémentation
- Adaptateurs par produit proxy.
- Templates de configuration validés.
- Vérification compliance (TLS, auth, policy).

### Critères d’acceptation
- Déploiement profil sans downtime.
- Contrôle de dérive config.

### PRs
- PR-PROXY-1: adaptateurs ProxySQL/MaxScale/Router.
- PR-PROXY-2: UI profils + simulation flux.
- PR-PROXY-3: sécurité/firewall + compliance report.

---

## Domaine H — Migration Wizard “MySQL protocol to X”
### But
Guider les migrations vers TiDB/SingleStore/ClickHouse.

### Fonctionnalités
- Assistant pas à pas:
  - inventaire,
  - compat,
  - écarts,
  - plan de migration,
  - validation post-cutover.
- Warnings dédiés ClickHouse MySQL interface.

### Implémentation
- Règles de mapping types/features.
- Génération de runbook spécifique cible.
- Checklist de validation applicative.

### Critères d’acceptation
- Sortie actionnable en < 30 min.
- Liste explicite “blocking vs non-blocking”.

### PRs
- PR-MIG-1: moteur gap analysis multi-cibles.
- PR-MIG-2: wizard UI + runbook export.
- PR-MIG-3: validateur post-migration.

---

## Domaine I — Vitess Planner
### But
Vérifier qu’une charge SQL est “Vitess-friendly”.

### Fonctionnalités
- Détection requêtes non compatibles vtgate.
- Suggestions clés de sharding/routage.
- Explications “pourquoi cette requête casse”.

### Implémentation
- Analyse syntaxique + règles de compat Vitess.
- Scoring de “sharding readiness”.
- Liens directs vers patterns de correction.

### Critères d’acceptation
- Rapport exploitable sur un lot de requêtes.
- Taux faux positifs contrôlé (baseline interne).

### PRs
- PR-VITESS-1: règles compat + parser.
- PR-VITESS-2: scoring readiness.
- PR-VITESS-3: UI planner + recommandations.

---

## Domaine J — EOL / Lifecycle Radar
### But
Prioriser les migrations selon fin de support.

### Fonctionnalités
- Calendrier support par moteur/version.
- Alertes proactives (90/180/365 jours).
- Vue portefeuille multi-clusters.

### Implémentation
- Collecte lifecycle officielle.
- Moteur d’alertes + notifications.
- Priorisation business (criticité cluster).

### Critères d’acceptation
- Couverture de tous moteurs supportés PmaControl.
- Rapport exécutif mensuel automatique.

### PRs
- PR-EOL-1: modèle lifecycle + import.
- PR-EOL-2: UI radar + filtres criticité.
- PR-EOL-3: notifications + rapports.

---

## 5) Découpage macro roadmap (phases)

## Phase 1 — Différenciation immédiate (10-12 semaines)
- Intel Ingestion (socle)
- Compatibility Matrix
- Upgrade Advisor
- Config Diff

### KPI phase 1
- 80% des upgrades couverts par checklist auto.
- 50% du temps d’analyse pré-upgrade économisé.

## Phase 2 — Stickiness opérateur (8-10 semaines)
- Schema Change Center
- Query Digest & Regression Lab
- Proxy Layer Manager

### KPI phase 2
- -30% incidents DDL.
- +40% détection précoce de régressions.

## Phase 3 — Leadership plateforme (12-16 semaines)
- Migration Wizard
- Vitess Planner
- Topology Cockpit
- EOL Radar

### KPI phase 3
- 1 runbook migration complet généré automatiquement par cluster.
- Diminution des migrations “surprises” liées à EOL.

---

## 6) Sécurité, conformité, exploitation

- Hardening defaults par moteur/version.
- Vérifications TLS/auth/chiffrement en continu.
- Journal d’audit immuable des actions opérateur.
- Mode “dry-run” obligatoire pour actions risquées.

---

## 7) Gouvernance PR (ce que l’équipe doit appliquer)

Pour respecter “une PR par domaine différent”, ouvrir au minimum 10 tracks PR:
- Track A: COMPAT
- Track B: UPGRADE
- Track C: CONFIG
- Track D: DDL
- Track E: DIGEST
- Track F: TOPO
- Track G: PROXY
- Track H: MIGRATION
- Track I: VITESS
- Track J: EOL

Chaque track suit la même definition of done:
1. API + tests,
2. UI + tests,
3. Documentation opérateur + runbook.

---

## 8) Quick-start exécutable (2 premières semaines)

### Sprint 1
- Mettre en place le schéma `intel_*`.
- Brancher sources MySQL 8.4 + MariaDB 11.4.
- Produire 20 règles Upgrade Advisor.

### Sprint 2
- Livrer écran Compatibility Matrix (MVP).
- Livrer Config Diff (MVP).
- Générer export checklist upgrade.

Résultat attendu à J+14: un premier cycle “collecte → analyse → recommandation” déjà utilisable en production assistée.
