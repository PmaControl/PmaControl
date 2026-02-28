# Plan clair d’intégration InnoDB Cluster dans DOT3 (mode « équivalent Galera »)

## Objectif
Implémenter dans PmaControl une **détection + modélisation + rendu DOT/Graphviz** des clusters **MySQL InnoDB Cluster (Group Replication)**, avec un niveau de visibilité proche de l’existant Galera (état global, membres, rôles, santé, liens ProxySQL/MaxScale, vue UI).

---

## Prompt prêt à copier/coller (spécification complète)

> Tu travailles dans le dépôt `PmaControl`.
> 
> But: ajouter le support complet d’**InnoDB Cluster** dans le pipeline DOT3, de l’aspirateur de métadonnées jusqu’au rendu Graphviz/UI, avec une expérience proche de Galera mais adaptée aux spécificités Group Replication.
> 
> Contraintes:
> 1. Ne pas casser les flux existants Galera / réplication classique / ProxySQL / MaxScale.
> 2. Produire un rendu visuel et des tables de données exploitables même en présence d’informations partielles.
> 3. Ajouter un mode dégradé propre si `performance_schema` ou certaines vues ne sont pas disponibles.
> 
> ### Étape 0 — Cartographie du code existant
> - Identifier et documenter les zones actuelles:
>   - Construction de la collecte de métriques dans `App/Controller/Dot3.php` (bloc des variables/status aspirées).
>   - Génération des clusters (logique Galera) dans `Dot3`.
>   - Rendu Graphviz dans `App/Library/Graphviz.php` (`generateGalera`, `startCluster`, styles de nœuds/segments).
>   - Vues existantes:
>     - `App/view/GaleraCluster/index.view.php`
>     - `App/view/Mysql/clusterDisplay.view.php`
>     - `App/view/ProxySQL/cluster.view.php`
> - Lister les tables SQL déjà utilisées par DOT3 (`dot3_cluster`, `dot3_cluster__mysql_server`, etc.) pour réutiliser au maximum le modèle.
> 
> ### Étape 1 — Variables et sources à aspirer (InnoDB Cluster)
> Étendre la collecte Dot3 pour inclure ces données (avec fallback si indisponibles):
> 
> #### 1) Variables globales (SHOW VARIABLES)
> - `group_replication_group_name`
> - `group_replication_start_on_boot`
> - `group_replication_local_address`
> - `group_replication_group_seeds`
> - `group_replication_single_primary_mode`
> - `group_replication_enforce_update_everywhere_checks`
> - `group_replication_bootstrap_group`
> - `group_replication_member_expel_timeout`
> - `group_replication_consistency`
> - `group_replication_autorejoin_tries`
> - `group_replication_recovery_use_ssl`
> - `group_replication_ssl_mode`
> - `report_host`, `report_port`
> - `server_uuid`, `server_id`, `version`
> - `super_read_only`, `read_only`
> 
> #### 2) Status globaux (SHOW STATUS)
> - Variables `group_replication_%` exposées selon version MySQL.
> - Au minimum extraire celles disponibles, sans échouer si absentes.
> 
> #### 3) Performance Schema (prioritaire)
> - `performance_schema.replication_group_members`
>   - `CHANNEL_NAME`, `MEMBER_ID`, `MEMBER_HOST`, `MEMBER_PORT`, `MEMBER_STATE`, `MEMBER_ROLE`, `MEMBER_VERSION`, `MEMBER_COMMUNICATION_STACK`.
> - `performance_schema.replication_group_member_stats`
>   - latence/counters de transactions/certification utiles pour l’état du cluster.
> 
> #### 4) Métadonnées InnoDB Cluster (si MySQL Shell AdminAPI présent)
> - Schéma `mysql_innodb_cluster_metadata`:
>   - `clusters`, `instances`, et relations utiles pour nom logique du cluster.
> - Si indisponible: fallback sur `group_replication_group_name`.
> 
> #### 5) Intégration ProxySQL / MaxScale
> - Réutiliser la collecte existante `mysql_group_replication_hostgroups` déjà présente côté ProxySQL.
> - Ajouter les correspondances de rôle (PRIMARY/SECONDARY) vers statut DOT3.
> 
> ### Étape 2 — Normalisation des données (modèle interne)
> Créer une structure unifiée (type `innodb_cluster`) parallèle à `galera`:
> 
> - Identité cluster:
>   - `cluster_name` (priorité metadata adminapi, fallback group_name)
>   - `group_name` (UUID GR)
> - Santé cluster:
>   - `member_count`, `online_count`, `offline_count`, `recovering_count`, `error_count`
>   - `is_single_primary_mode` (bool)
>   - `primary_member_id` (si mode single-primary)
> - Nœuds:
>   - `id_mysql_server` (mapping interne PmaControl)
>   - `member_id` (UUID)
>   - `member_host`, `member_port`
>   - `member_state` (`ONLINE`, `RECOVERING`, `OFFLINE`, `ERROR`, `UNREACHABLE`, ...)
>   - `member_role` (`PRIMARY`, `SECONDARY`)
>   - `read_only`, `super_read_only`
>   - `version`
> - Seeds/topologie:
>   - `group_seeds` parsé en endpoints
>   - `local_address`
> 
> Gérer explicitement les cas:
> - nœud vu par mapping mais absent de `replication_group_members`;
> - incohérences d’IP/hostname entre `report_host`, `member_host`, inventaire PmaControl;
> - split-brain logique de monitoring (plusieurs « vues » d’un même group_name selon serveurs sondés).
> 
> ### Étape 3 — Algorithme de grouping (équivalent Galera)
> Implémenter une méthode dédiée dans `Dot3` (style `generateGroupInnoDBCluster`) qui:
> 1. Regroupe par `group_replication_group_name` (ou nom metadata).
> 2. Déduplique les membres par `MEMBER_ID` puis par `host:port`.
> 3. Attribue un thème de cluster:
>    - `INNODB_CLUSTER_OK` si quorum logique et ONLINE majoritaire.
>    - `INNODB_CLUSTER_WARN` si recovering/offline partiel.
>    - `INNODB_CLUSTER_CRIT` si pas de primary (single-primary) ou majorité indisponible.
> 4. Prépare une représentation utilisable par `Graphviz` en réutilisant au max le formalisme de `generateGalera`.
> 
> ### Étape 4 — Rendu Graphviz / DOT
> Étendre `App/Library/Graphviz.php`:
> - Ajouter `generateInnoDBCluster($clusters)`.
> - Créer un header cluster dédié:
>   - nom cluster,
>   - group_name,
>   - mode (`single-primary` / `multi-primary`),
>   - ratio ONLINE/total,
>   - version majoritaire.
> - Styling des nœuds:
>   - PRIMARY ONLINE: vert appuyé,
>   - SECONDARY ONLINE: vert clair/bleu,
>   - RECOVERING: orange,
>   - OFFLINE/ERROR: rouge/gris.
> - Liens:
>   - relier les membres au bloc cluster (comme Galera),
>   - conserver la lisibilité des liens ProxySQL/MaxScale.
> - Tooltips:
>   - inclure état GR, rôle, read_only/super_read_only, seeds, local_address.
> 
> Ajouter les clés de couleurs/états dans la config Dot3 (même endroit que thèmes Galera/ProxySQL existants):
> - `INNODB_CLUSTER_OK`
> - `INNODB_CLUSTER_WARN`
> - `INNODB_CLUSTER_CRIT`
> - `INNODB_MEMBER_PRIMARY`
> - `INNODB_MEMBER_SECONDARY`
> - `INNODB_MEMBER_RECOVERING`
> - `INNODB_MEMBER_OFFLINE`
> 
> ### Étape 5 — UI / pages de détail
> Ajouter une vue équivalente à Galera:
> - Nouveau contrôleur/vue recommandé: `InnoDBCluster` + `App/view/InnoDBCluster/index.view.php`.
> - Afficher par cluster:
>   - group name,
>   - mode de réplication,
>   - membres, état, rôle,
>   - indicateurs de drift (read_only incohérent, multi-primary inattendu, versions hétérogènes).
> - Ajouter liens depuis le graphe DOT vers cette vue (similaire `GaleraCluster/view/{id}`).
> 
> ### Étape 6 — Schéma SQL et persistance DOT3
> Vérifier si les tables existantes `dot3_cluster` + pivot suffisent.
> - Si insuffisant, proposer migration incrémentale dans `sql/incremental/` et `sql/incremental_v2/`.
> - Ajouter type de cluster `innodb` (ou `group_replication`) de manière rétrocompatible.
> - Conserver l’historisation déjà en place pour rejouer l’évolution des clusters.
> 
> ### Étape 7 — Compatibilité version MySQL
> Gérer différences 5.7/8.0+:
> - Certaines colonnes `replication_group_members` peuvent varier.
> - Certaines variables `group_replication_*` n’existent pas selon versions.
> - Si la vue perf schema n’est pas disponible: fallback sur variables + statut partiel avec drapeau `incomplete_data=true`.
> 
> ### Étape 8 — Sécurité, droits et résilience
> Documenter les privilèges minimaux du compte de monitoring:
> - accès lecture `performance_schema.*` ciblé,
> - `SHOW VARIABLES`, `SHOW STATUS`, `SELECT` sur metadata cluster si utilisé.
> 
> En cas d’erreur SQL/permission:
> - ne pas interrompre toute génération DOT;
> - logguer un warning contextualisé;
> - afficher un état “Unknown / partial visibility”.
> 
> ### Étape 9 — Tests à produire
> Ajouter des tests PHPUnit sous `tests/`:
> - mapping membre -> `id_mysql_server`;
> - déduplication de clusters;
> - calcul du thème (OK/WARN/CRIT) selon jeux de données;
> - rendu Graphviz minimal (présence sous-graph, labels, couleurs clés).
> 
> Ajouter fixtures:
> - single-primary sain,
> - recovering node,
> - primary absent,
> - metadata manquante.
> 
> ### Étape 10 — Démonstration / validation manuelle
> 1. Générer un DOT avec cluster InnoDB simulé.
> 2. Vérifier qu’aucune régression Galera.
> 3. Vérifier ProxySQL hostgroups Group Replication visibles.
> 4. Vérifier lien UI cluster.
> 5. Capturer screenshot si composant visuel modifié.
> 
> ### Livrables attendus
> - Code modifié: `Dot3`, `Graphviz`, vues/controllers cluster, SQL migration si nécessaire, tests.
> - Changelog technique: variables collectées + fallback.
> - Liste explicite des limitations connues.

---

## Démarches concrètes dans ce dépôt (checklist opératoire)

### 1) Fichiers à auditer / modifier en priorité
- `App/Controller/Dot3.php` (aspiration, grouping, pipeline de génération DOT).
- `App/Library/Graphviz.php` (nouveau rendu cluster InnoDB + styles).
- `App/view/Mysql/clusterDisplay.view.php` (éventuelle intégration listing cluster).
- `App/view/ProxySQL/cluster.view.php` (corrélation hostgroups GR).
- `App/view/GaleraCluster/index.view.php` (référence fonctionnelle pour dupliquer pattern UI).
- `sql/incremental/*.sql` et `sql/incremental_v2/*.sql` (si extension schéma nécessaire).
- `tests/` (nouvelles classes de test + fixtures).

### 2) Variables minimales à récupérer (MVP viable)
- Identité cluster: `group_replication_group_name`.
- Topologie: `group_replication_group_seeds`, `group_replication_local_address`.
- Mode: `group_replication_single_primary_mode`.
- Membres: `performance_schema.replication_group_members`.
- État nœud local: `super_read_only`, `read_only`, `server_uuid`.

### 3) Mapping état/role => thème DOT3
- `ONLINE + PRIMARY` => nœud « writer ».
- `ONLINE + SECONDARY` => nœud « reader ».
- `RECOVERING` => état transitoire.
- `OFFLINE|ERROR|UNREACHABLE` => nœud dégradé.

### 4) Définition de done (DoD)
- Un InnoDB Cluster apparaît comme un sous-graphe dédié dans DOT.
- Chaque membre est correctement rattaché au cluster et colorisé.
- Le mode single/multi primary est visible.
- Les liens ProxySQL GR restent lisibles.
- Les tests unitaires nouveaux passent.
- Pas de régression sur graphes Galera.

---

## Points d’attention spécifiques InnoDB Cluster (vs Galera)
- Le protocole est Group Replication (pas wsrep): ne pas réutiliser aveuglément les heuristiques Galera.
- La source de vérité des membres est `performance_schema.replication_group_members`.
- Le rôle PRIMARY/SECONDARY peut changer rapidement: privilégier un rendu « état courant » et robuste au churn.
- En multi-primary, l’absence de PRIMARY unique n’est pas une erreur.
- Le metadata schema `mysql_innodb_cluster_metadata` peut être absent selon déploiement.

---

## Risques & mitigation
- **Droits SQL insuffisants** → fallback partiel + warning non bloquant.
- **Hostnames non résolus/mapping ambigu** → stratégie de matching multi-critères (`member_host`, `report_host`, IP réelle, port).
- **Versions MySQL hétérogènes** → parsing permissif de colonnes.
- **Graphes illisibles** → regrouper par sous-graph, limiter les arêtes parasites, garder `constraint=false` sur certains liens.

---

## Résultat attendu
Une implémentation InnoDB Cluster complète dans DOT3, opérable en production, avec une ergonomie équivalente à Galera tout en respectant les spécificités Group Replication.
