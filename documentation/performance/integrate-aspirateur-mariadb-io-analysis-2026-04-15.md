# Analyse MariaDB IO - Integrate / Aspirateur

Date: 2026-04-15 18:07:59 CEST
Repo: `/srv/www/pmacontrol`
Branche: `commercial`

## Objectif

Cette note cible uniquement les IO et le coût côté MariaDB provoqués par `Aspirateur*` et `Integrate*`.

Le point important est le suivant:

- `Integrate.php` coûte surtout en CPU PHP, en petites écritures disque et en batch `INSERT`.
- la pression IO côté MariaDB vient principalement de `Aspirateur.php`, car c'est lui qui déclenche les lectures sur `information_schema`, `performance_schema`, `SHOW BINARY LOGS`, `SHOW CREATE`, `SHOW PROCESSLIST`, etc.

Autrement dit:

- si l'objectif est de réduire les IO MariaDB, il faut prioriser les collectes de `Aspirateur`
- si l'objectif est de réduire les IO filesystem / CPU PHP, il faut prioriser `Integrate`

## Résumé exécutif

Les plus gros consommateurs potentiels de IO MariaDB dans le code actuel sont:

1. la collecte de digest sur `performance_schema.events_statements_summary_by_digest`
2. la collecte complète des tables via `information_schema.TABLES`
3. la collecte du processlist enrichi avec `information_schema.innodb_trx`
4. la collecte de `INFORMATION_SCHEMA.INNODB_METRICS`
5. `SHOW BINARY LOGS`
6. les lectures répétées de `information_schema.schemata`, `plugins`, `engines`, `metadata_lock_info`, `disks`

Les deux plus gros candidats à optimisation côté MariaDB sont clairement:

- `getDigest()`
- `getDatabase()` / `getTablesFromInformationSchema()` / `eachHour()`

## Ce qui coûte côté MariaDB

### 1. `getDigest()` lit large dans `performance_schema`

Référence:

- [Aspirateur.php:4657](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L4657)

Requête:

```sql
SELECT *
FROM performance_schema.events_statements_summary_by_digest
WHERE LAST_SEEN > NOW() - INTERVAL 2 DAY;
```

Pourquoi c'est cher:

- `SELECT *` lit toutes les colonnes, y compris des champs larges
- la fenêtre `2 DAY` est large pour une collecte périodique
- sur un serveur avec beaucoup de diversité SQL, cette table peut être volumineuse
- la requête est ensuite retransformée ligne par ligne en PHP, puis réexportée

Impact:

- lecture CPU/mémoire côté MariaDB
- plus de pages lues dans `performance_schema`
- plus de trafic réseau MariaDB -> PHP
- plus de CPU PHP ensuite

Priorité: `Très haute`

Gains possibles:

- ne plus faire `SELECT *`
- limiter aux colonnes réellement exploitées
- réduire la fenêtre temporelle
- collecter uniquement les digests vus depuis le dernier curseur local
- éventuellement limiter aux `TOP N` les plus actifs ou les plus coûteux

### 2. `getDatabase()` et `getTablesFromInformationSchema()` scannent toutes les tables

Références:

- [Aspirateur.php:3212](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L3212)
- [Aspirateur.php:4917](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L4917)
- [Aspirateur.php:5045](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L5045)

Requêtes:

```sql
select * from information_schema.SCHEMATA;
```

```sql
select TABLE_CATALOG, TABLE_SCHEMA, TABLE_NAME, TABLE_TYPE, ENGINE, ROW_FORMAT,
       TABLE_COLLATION, CREATE_OPTIONS, TABLE_COMMENT, TEMPORARY
from information_schema.TABLES
```

ou:

```sql
SELECT * FROM information_schema.tables;
```

Pourquoi c'est cher:

- `information_schema.TABLES` peut être très coûteux sur des instances avec beaucoup de schémas/tables
- chaque exécution provoque un balayage logique important du dictionnaire
- ensuite le code charge tout en mémoire PHP
- le hourly path `eachHour()` refait encore ce travail pour tous les serveurs

Impact:

- forte lecture metadata côté MariaDB
- latence élevée sur gros catalogues
- consommation mémoire PHP

Priorité: `Très haute`

Gains possibles:

- éviter `SELECT *`
- filtrer les schémas système si non nécessaires
- basculer par défaut vers une collecte incrémentale ou par lot
- utiliser une stratégie de snapshot moins fréquente
- conserver `SHOW TABLES` / `SHOW CREATE` seulement pour les serveurs ciblés ou à très faible cardinalité

### 3. `eachHour()` amplifie fortement la charge metadata

Référence:

- [Aspirateur.php:5045](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L5045)

Le code fait, pour tous les serveurs MySQL disponibles:

- `information_schema.disks`
- `information_schema.tables` ou fallback `SHOW TABLES`
- `SHOW CREATE TABLE information_schema.tables`

Pourquoi c'est important:

- ce n'est pas une collecte ponctuelle sur un serveur
- c'est une boucle sur tous les serveurs
- donc même une requête "acceptable" seule devient chère à l'échelle

Priorité: `Très haute`

Gains possibles:

- étaler la collecte sur plusieurs cycles
- ne pas recalculer le catalogue complet chaque heure
- séparer les serveurs "petits" et "gros"
- faire un refresh complet journalier, puis un delta plus léger

### 4. `getProcesslist()` joint `processlist` avec `innodb_trx`

Référence:

- [Aspirateur.php:4721](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L4721)

Requête MySQL 8:

```sql
SELECT p.*,
       IFNULL(t.trx_rows_locked, '0') AS trx_rows_locked,
       IFNULL(t.trx_state, '') AS trx_state,
       IFNULL(t.trx_operation_state, '') AS trx_operation_state,
       IFNULL(t.trx_rows_modified, '0') AS trx_rows_modified,
       IFNULL(t.trx_concurrency_tickets, '') AS trx_concurrency_tickets,
       IFNULL(TIMESTAMPDIFF(SECOND, t.trx_started, NOW()), '') AS trx_time
FROM performance_schema.processlist p
LEFT JOIN information_schema.innodb_trx t ON p.ID = t.trx_mysql_thread_id
WHERE p.command NOT IN ('Sleep', 'Binlog Dump')
AND p.user NOT IN ('system user', 'event_scheduler')
AND TIME > 0
```

Pourquoi c'est coûteux:

- `p.*` remonte toutes les colonnes
- la jointure avec `innodb_trx` ajoute du coût
- la requête est lancée fréquemment
- elle peut devenir bruyante sur des serveurs avec beaucoup de threads actifs

Priorité: `Haute`

Gains possibles:

- limiter les colonnes du processlist
- relever seulement les sessions utiles au dashboard
- éviter cette collecte trop fréquente sur les gros serveurs
- prévoir un mode light sans jointure `innodb_trx`

### 5. `getInnodbMetrics()` prend toute la table `INNODB_METRICS`

Référence:

- [Aspirateur.php:4093](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L4093)

Requête:

```sql
SELECT * FROM INFORMATION_SCHEMA.INNODB_METRICS;
```

Pourquoi c'est coûteux:

- lecture complète
- ensuite filtrage `ENABLED` fait en PHP au lieu du SQL
- chaque ligne active est ré-encodée en JSON

Priorité: `Haute`

Gains possibles:

- filtrer directement en SQL: `WHERE ENABLED = 1`
- ne sélectionner que les colonnes utilisées
- réduire la fréquence

### 6. `SHOW BINARY LOGS` peut devenir coûteux avec beaucoup de fichiers

Référence:

- [Aspirateur.php:3123](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L3123)

Requête:

```sql
SHOW BINARY LOGS;
```

Pourquoi c'est coûteux:

- coût quasi nul sur petit historique
- coût non négligeable si le serveur garde beaucoup de binlogs
- le code charge toute la liste, toutes les tailles, puis fait `array_sum`

Priorité: `Moyenne à haute`

Gains possibles:

- ne pas interroger si le retention policy est déjà connue et stable
- garder uniquement first/last/count/total via une stratégie incrémentale locale
- réduire la fréquence sur les serveurs à gros historique binlog

### 7. `getSchema()` lit toute la liste des bases

Référence:

- [Aspirateur.php:3574](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L3574)

Requête:

```sql
SELECT * FROM information_schema.schemata
```

Pourquoi c'est moins grave:

- coût généralement modéré
- mais inutile si rien n'a changé
- le fallback `SHOW DATABASES` reste lui aussi full-list

Priorité: `Moyenne`

Gains possibles:

- ne pas collecter aussi souvent
- ne lire que les colonnes nécessaires

### 8. `getElemFromTable()` pour plugins, engines, metadata_lock_info, disks

Références:

- [Aspirateur.php:4390](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L4390)
- [Aspirateur.php:830](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L830)
- [Aspirateur.php:843](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L843)
- [Aspirateur.php:854](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L854)
- [Aspirateur.php:5115](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L5115)

Points notables:

- `plugins` et `engines`: coût faible à modéré, mais inutilement refaits si l'objectif est la supervision courante
- `metadata_lock_info`: peut être vide la plupart du temps, mais la lecture reste une requête de plus
- `disks`: dépend du plugin, mais reste une lecture supplémentaire

Priorité:

- `plugins` / `engines`: `Basse`
- `metadata_lock_info`: `Moyenne`
- `disks`: `Moyenne`

## Fréquence et amplification

Le vrai problème n'est pas seulement la requête unitaire. C'est la multiplication:

- `tryMysqlConnection()` enchaîne plusieurs collectes à chaque passage
- certaines sont toutes les secondes / dizaines de secondes selon `refresh`
- certaines tournent sur beaucoup de serveurs
- `eachHour()` repasse en masse sur l'inventaire

Donc une requête acceptable seule devient coûteuse car elle est:

- répétée
- parallélisée par worker
- appliquée à de nombreux serveurs

## Ce qui vient surtout de `Integrate`

`Integrate.php` tape moins MariaDB en lecture analytique. Son coût principal côté SGBD est plutôt:

- gros `INSERT ... VALUES (...)`
- `UPDATE ts_max_date`
- `INSERT INTO ts_date_by_server`

Références:

- [Integrate.php:507](/srv/www/pmacontrol/App/Controller/Integrate.php#L507)
- [Integrate.php:563](/srv/www/pmacontrol/App/Controller/Integrate.php#L563)
- [Integrate.php:638](/srv/www/pmacontrol/App/Controller/Integrate.php#L638)

En pratique:

- c'est plus du write load que du read IO
- ce n'est probablement pas le premier point à traiter si la question est "où MariaDB lit trop"

## Priorisation recommandée

### Priorité 1

- réduire `getDigest()`
- réduire la collecte complète de `information_schema.TABLES`
- étaler ou réduire `eachHour()`

### Priorité 2

- alléger `getProcesslist()`
- filtrer `INNODB_METRICS` en SQL
- revoir la fréquence de `SHOW BINARY LOGS`

### Priorité 3

- réduire `getSchema()`
- espacer `plugins`, `engines`, `metadata_lock_info`, `disks`

## Recommandations concrètes

### A. `getDigest()`

- remplacer `SELECT *` par une liste de colonnes stricte
- réduire la fenêtre `LAST_SEEN`
- stocker un curseur de dernière collecte
- limiter le volume collecté par exécution

### B. `information_schema.TABLES`

- ne plus faire de snapshot complet horaire sur tous les serveurs
- passer à un refresh complet journalier
- faire un refresh allégé entre temps
- distinguer les serveurs à petit et gros catalogue

### C. `getProcesslist()`

- ne garder que les colonnes utilisées par l'écran
- faire un mode léger pour la collecte courante
- garder le mode enrichi uniquement pour le diagnostic

### D. `INNODB_METRICS`

- filtrer `ENABLED = 1` directement en SQL
- ne pas remonter des colonnes inutiles

### E. `SHOW BINARY LOGS`

- ne plus recalculer la liste complète trop souvent
- si l'objectif est juste la volumétrie, garder un cache incrémental local

## Estimation qualitative du gain

Si tu veux gagner du IO MariaDB rapidement:

- `getDigest()` + `information_schema.TABLES` + `eachHour()` donneront le meilleur retour
- `getProcesslist()` arrive juste derrière sur les serveurs chargés
- `INNODB_METRICS` est un bon gain simple

Ordre de retour sur investissement:

1. digest
2. tables / eachHour
3. processlist
4. innodb_metrics
5. binary logs

## Conclusion

Si on parle strictement de IO côté MariaDB, le vrai sujet n'est pas `Integrate`. Le vrai sujet est `Aspirateur`, en particulier:

- `performance_schema.events_statements_summary_by_digest`
- `information_schema.TABLES`
- `information_schema.schemata`
- `information_schema.innodb_metrics`
- `information_schema.processlist` / `performance_schema.processlist`
- `SHOW BINARY LOGS`

`Integrate` reste important pour les performances globales, mais plutôt sur:

- les écritures MariaDB
- le CPU PHP
- le filesystem local

Si tu veux la suite utile, je peux faire un deuxième document avec:

- un plan d'optimisation concret
- le gain estimé par point
- le risque fonctionnel
- le diff de comportement attendu par collecte
