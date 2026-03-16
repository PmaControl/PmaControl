# Reverse Engineering Complet de PmaControl

- Genere le: 2026-03-12 01:42:25
- Perimetre analyse: `App/Controller`, `App/Library`, `App/model/IdentifierPmacontrol`, `App/view`, `documentation/*.md`
- Volumetrie applicative:
  - Controles web/CLI: 123
  - Bibliotheques metier et techniques: 49
  - Modeles de schema: 161
  - Fichiers de vue: 298
  - Tables documentees dans la base `pmacontrol`: 162
  - Fiches controleurs disponibles dans `documentation/controller/`: 123

## 1. Resume executif

PmaControl est une plateforme PHP historique de pilotage d'infrastructure MySQL/MariaDB orientee DBA, capable d'operer a la fois comme application web, comme outillage CLI et comme moteur de traitements planifies. Le depot combine des fonctions de monitoring, de cartographie topologique, d'analyse de schema, de backup/restauration, de nettoyage de donnees, de gestion de cluster et d'integration d'outils externes tels que SSH, ProxySQL, MaxScale, Graphviz, LDAP et plus recemment une API REST de configuration.

L'application est fortement centree base de donnees: la base `pmacontrol` n'est pas un simple stockage d'etat, elle joue aussi le role de referentiel d'infrastructure, de journal de jobs, de cache analytique et de couche d'orchestration pour les collectes. Une part importante de la logique metier est repartie entre gros controleurs transactionnels et bibliotheques statiques. La couche de vues est importante, mais le coeur des regles reside surtout dans les controleurs, les modeles de schema et les scripts de collecte.

Le design global montre un systeme "monolithe operateur" : un seul codebase regroupe l'administration, l'observation, les actions remediatives, les exports, la documentation, les workflows de backup, la topologie et les integrations. Ce n'est pas un back-office CRUD classique; c'est un cockpit d'exploitation complet pour fermes MySQL.

## 2. Architecture d'execution

### 2.1 Modes d'execution

Le meme code sert deux modes:

- mode web via `App/Webroot/Bootstrap.php`
- mode CLI via les appels `php index.php Controller action [params]`

Le bootstrap initialise Composer, la configuration Glial, Monolog, la session, la langue, la connexion BD et les composants DI (`auth`, `acl`, `js`, `config`, `log`). Cote web, `App/Webroot/Router.php` transforme l'URL en triplet `langue / controller / action / params`.

### 2.2 Routage et contrat URL

Le routeur est minimaliste et stable:

- premier segment: langue
- deuxieme segment: controleur
- troisieme segment: action
- segments suivants: parametres
- format `cle:valeur` ou `cle:souscle:valeur` mappe directement vers `$_GET`

Ce routage explique pourquoi beaucoup de controleurs lisent a la fois les parametres route et les superglobales. Le contrat d'URL est simple mais tres permissif, ce qui accelere le developpement historique au prix d'un couplage fort entre URL, superglobales et logique metier.

### 2.3 Pile technique

- Framework principal: Glial / Synapse
- Langage: PHP 8.2+
- Persistence: MariaDB / MySQL
- Graphes: Graphviz
- Journalisation: Monolog
- Transport distant: SSH, SCP
- Integration REST: controleur `Api`
- Generation documentaire: Markdown + PDF

## 3. Cartographie fonctionnelle

### 3.1 Domaines dominants

Le code se structure de facto autour des domaines ci-dessous:

1. **Referentiel d'infrastructure**
   - serveurs MySQL
   - alias DNS
   - clients/organisations
   - environnements
   - tags
   - zones de stockage backup

2. **Collecte et monitoring**
   - `Aspirateur`, `Listener`, `Worker`, `Integrate`, `Dashboard`
   - collecte d'etat MySQL, systeme, replication, ProxySQL, MaxScale
   - stockage des series temporelles dans les tables `ts_*`

3. **Topologie et visualisation**
   - `Dot3`, `Graph`, `Architecture`, `Replication`, `Cluster`
   - generation d'arbres de dependances, vues cluster, cartes de flux

4. **Sauvegarde, archivage et restauration**
   - `Backup`, `Archives`, `Recover`, `StorageArea`, `Load`
   - orchestration de dumps, archives, transferts, restauration cible

5. **Qualite du schema et hygiene**
   - `Schema`, `ForeignKey`, `Compare`, `CompareConfig`, `Cleaner`
   - virtualisation et suggestions de FK, comparaison de schemas, nettoyage

6. **Securite, comptes et administration**
   - `User`, `Acl`, `Ldap`, `Administration`, `Api`, `Telegram`
   - gestion des acces, roles, abonnements, integrations externes

7. **Analyse SQL et performance**
   - `Query`, `Mysql`, `MysqlServer`, `MysqlDatabase`, `MysqlTable`, `Digest`
   - digests, metadata locks, disponibilite, diagnostics offline

### 3.2 Controleurs les plus lourds

Les gros centres de gravite techniques du projet sont:

- `Cleaner`: 98 methodes, 68 appels BD visibles
- `Schema`: 85 methodes, 11 appels BD visibles
- `Aspirateur`: 62 methodes, 61 appels BD visibles
- `Dot3`: 62 methodes, 24 appels BD visibles
- `MysqlServer`: 55 methodes, 7 appels BD visibles
- `Backup`: 43 methodes, 57 appels BD visibles
- `Mysql`: 40 methodes, 37 appels BD visibles
- `Query`: 36 methodes, 38 appels BD visibles
- `Server`: 33 methodes, 29 appels BD visibles
- `Benchmark`: 33 methodes, 25 appels BD visibles
- `ProxySQL`: 32 methodes, 31 appels BD visibles
- `ForeignKey`: 31 methodes, 27 appels BD visibles
- `Database`: 30 methodes, 58 appels BD visibles
- `Pmacontrol`: 30 methodes, 0 appels BD visibles
- `Install`: 28 methodes, 8 appels BD visibles
- `User`: 27 methodes, 32 appels BD visibles
- `Worker`: 27 methodes, 26 appels BD visibles
- `Control`: 25 methodes, 22 appels BD visibles
- `Slave`: 24 methodes, 26 appels BD visibles
- `Audit`: 24 methodes, 8 appels BD visibles

Ces fichiers concentrent l'essentiel du patrimoine fonctionnel et sont donc les plus critiques pour tout refactoring, toute mise en securite et toute evolution de contrat metier.

### 3.3 Zones de vues les plus actives

- `Pmacontrol`: 28 fichiers de vue
- `Site`: 19 fichiers de vue
- `Cleaner`: 16 fichiers de vue
- `Server`: 16 fichiers de vue
- `ProxySQL`: 11 fichiers de vue
- `Database`: 10 fichiers de vue
- `Mysql`: 9 fichiers de vue
- `ForeignKey`: 8 fichiers de vue
- `User`: 8 fichiers de vue
- `Binlog`: 7 fichiers de vue
- `Common`: 7 fichiers de vue
- `Backup`: 7 fichiers de vue
- `Docker`: 6 fichiers de vue
- `Layout`: 6 fichiers de vue
- `Benchmark`: 6 fichiers de vue
- `Archives`: 5 fichiers de vue
- `MysqlDatabase`: 5 fichiers de vue
- `Audit`: 4 fichiers de vue
- `Spider`: 4 fichiers de vue
- `MysqlUser`: 4 fichiers de vue

Cela confirme un biais fort vers les ecrans d'operations: l'interface n'est pas generique, elle est organisee par domaine d'exploitation.

## 4. Modele de donnees reverse-engineere

La base `pmacontrol` compte 162 tables documentees. Les prefixes de tables les plus significatifs sont:

- Prefixe `ts`: 23 tables
- Prefixe `kb`: 17 tables
- Prefixe `mysql`: 8 tables
- Prefixe `dot3`: 6 tables
- Prefixe `docker`: 6 tables
- Prefixe `backup`: 6 tables
- Prefixe `foreign`: 5 tables
- Prefixe `archive`: 3 tables
- Prefixe `benchmark`: 3 tables
- Prefixe `translation`: 3 tables
- Prefixe `binlog`: 3 tables
- Prefixe `plugin`: 3 tables
- Prefixe `link`: 3 tables
- Prefixe `geolocalisation`: 3 tables
- Prefixe `worker`: 3 tables
- Prefixe `history`: 3 tables
- Prefixe `haproxy`: 3 tables
- Prefixe `cleaner`: 2 tables
- Prefixe `event`: 2 tables
- Prefixe `pmacli`: 2 tables

### 4.1 Lecture metier du schema

- `mysql_*`: coeur du referentiel serveurs, schemas, tables, privileges, requetes, digests
- `ts_*`: couche de time series et variables de monitoring
- `backup_*`, `archive_*`: cycle de vie des sauvegardes et de leur conservation
- `dot3_*`: cartographie topologique et vues de graphes
- `foreign_*`: moteur de suggestions, exceptions et projections de cles etrangeres
- `worker_*`, `job`: orchestration asynchrone et supervision de taches
- `plugin_*`: systeme d'extensions
- `webservice_*`: exposition et import de donnees externes

### 4.2 Referentialite logique

La referentialite est partagee entre:

- des FKs reelles en base
- des verifications `reference_to` dans les modeles de schema Glial
- des conventions applicatives implicites

Cette combinaison montre un systeme qui a grandi par couches successives: certaines relations sont strictes, d'autres seulement appliquees par le code. Le reverse engineering doit donc toujours lire en meme temps les modeles, les controleurs et les workflows.

## 5. Regles metier detaillees

### 5.1 Regles d'identite serveur

- un serveur MySQL est defini par une organisation (`id_client`), un environnement (`id_environment`), un nom logique, une IP, un port et des credentials
- `name` et le couple `ip:port` sont traites comme des identifiants forts
- un serveur peut etre marque `is_monitored`, `is_proxy`, `is_vip`, `is_acknowledged`
- la suppression metier de serveur est generalement douce (`is_deleted=1`) plutot que destructive
- `display_name` sert a la presentation operateur et peut diverger du `hostname`

### 5.2 Regles d'environnement et de segmentation

- les donnees sont segmentees par client et environnement
- l'environnement porte des attributs de presentation (`class`, `letter`, `key`) qui servent a l'UI autant qu'a la categorisation
- certains environnements systeme ont des identifiants proteges et ne doivent pas etre supprimes arbitrairement
- le client `99` est traite comme une valeur de repli technique dans des workflows de suppression

### 5.3 Regles de decouverte et d'alias

- les alias DNS permettent de rattacher un endpoint observe a un serveur connu
- la resolution peut venir du DNS, du hostname expose, des IP SSH, de `wsrep_node_address` ou d'autres signaux d'infrastructure
- les alias provenant de SSH sont purges s'ils ne sont plus visibles dans l'inventaire courant
- cette couche est essentielle pour maintenir la coherence de topologie lorsque NAT, VIP, proxys ou noms differents coexistent

### 5.4 Regles de monitoring et de collecte

- `Aspirateur` collecte l'etat serveur, systeme, replication, variables, plugins, metadata locks, processlist, statistiques OS, etc.
- `Listener` et `Integrate` consolident ensuite les mesures dans les structures `ts_*`
- le monitoring n'est pas seulement passif: certaines detections basculent des attributs metier comme `is_proxy`, l'etat des VIP ou la cartographie des clusters
- la base devient donc un miroir enrichi de l'infrastructure reelle

### 5.5 Regles de cluster et de proxy

- la plateforme modelise explicitement Galera, Master/Slave, ProxySQL, MaxScale, HAProxy
- les vues de topologie utilisent des donnees agrandies par alias, wsrep, groupes de serveurs et graphes DOT
- les outils de cluster supposent qu'un meme ensemble logique peut etre observe via plusieurs points d'entree
- les proxys sont des citoyens de premiere classe, pas des simples details reseau

### 5.6 Regles de sauvegarde

- une sauvegarde est associee a un serveur, un type, un schedule et une zone de stockage
- les workflows enchainent dump, compression, chiffrement, transfert, verification de taille et de hash
- les zones de stockage sont des ressources metier validees par SSH
- les archives historisent l'etat des traitements, la progression, les checksums et les temps de traitement
- les restaurations et imports sont pilotes depuis l'application, parfois via shell et scripts distants

### 5.7 Regles de nettoyage et d'archivage

- `Cleaner` encapsule des jobs de purge, de copie, d'archivage et parfois de rechargement
- l'historique de nettoyage et les tables d'archive servent a tracer les operations et les volumes
- le code montre un parti pris operateur: la plateforme ne se contente pas de constater, elle agit sur les donnees

### 5.8 Regles de schema et de cles etrangeres

- les FKs reelles sont completees par des FKs virtuelles, des listes noires et des suggestions
- le systeme sait comparer, proposer et parfois reconstruire des relations absentes
- `Schema`, `ForeignKey` et `Compare*` servent autant a l'audit de qualite qu'a la production d'artefacts de gouvernance schema

### 5.9 Regles de securite et d'identite

- l'acces web est gouverne par ACL Glial et `Auth`
- le LDAP peut etre active comme source d'identite
- le code gere plusieurs couches de secrets: mots de passe MySQL, SSH, webservice, sauvegardes, config
- les wrappers `Crypt` / `Chiffrement` indiquent une culture "secret stocke puis dechiffre a l'execution"

### 5.10 Regles API et integration

- l'API REST actuelle cible principalement les ressources de configuration modifiables depuis l'UI
- le projet sait aussi importer des donnees par webservice et exposer des integrations Telegram / MCP
- le style d'integration reste tres operationnel: peu d'abstraction, beaucoup de logique proche du cas d'usage

## 6. Reverse engineering des flux principaux

### 6.1 Flux web

1. le bootstrap charge Composer, config et dependances
2. le routeur parse `glial_path`
3. ACL/Auth resolvent l'autorisation
4. le controleur est instancie
5. l'action manipule BD, fichiers, reseau, shell ou vues
6. la vue restitue soit HTML, soit JSON, soit fichier

### 6.2 Flux CLI

1. `index.php Controller action params`
2. le bootstrap positionne `$_SYSTEM`
3. le controleur est execute sans pile web
4. les sorties peuvent etre texte, JSON, fichiers pivots, locks, md5, artefacts de collecte

### 6.3 Flux de collecte

1. inventaire serveur depuis le referentiel
2. ouverture connexions MySQL / SSH
3. lecture variables, etats, statistiques
4. serialisation vers pivots ou structures temporaires
5. integration en base `ts_*` ou tables de travail
6. calcul de vues derivees, topologie et alertes

### 6.4 Flux backup

1. definition d'un backup et de sa zone de stockage
2. programmation cron/job
3. execution de dump
4. compression/chiffrement/transfert
5. verification hash et taille
6. historisation `backup_*` / `archive_*`

## 7. Forces structurelles du systeme

- couverture fonctionnelle tres large pour l'exploitation MySQL
- couplage fort entre observabilite, remediations et referentiel
- connaissance profonde des topologies MySQL complexes
- presence d'une base documentaire et d'une couche de schema riche
- possibilite d'operer en web, CLI et automatisation

## 8. Limites structurelles observees

- gros controleurs multi-responsabilites
- dependance importante aux superglobales et a la composition dynamique des URLs
- forte heterogeneite du style de code
- beaucoup d'effets de bord shell/reseau/fichiers dans les actions
- frontiere floue entre presentation, orchestration et domaine

## 9. Conclusion d'ingenierie inverse

PmaControl n'est pas seulement une application de monitoring. C'est un systeme d'exploitation MySQL complet qui a accumule des couches de connaissances DBA sur la topologie, la sauvegarde, la gouvernance de schema et l'automatisation. Le reverse engineering montre une richesse metier elevee, mais aussi une dette structurelle equivalente: le code est puissant parce qu'il concentre beaucoup de logique dans peu d'endroits, ce qui accelere l'operationnel mais fragilise la maintenabilite, la securite et l'evolutivite.

Pour toute modernisation, il faut raisonner par domaines metier et non par simple refactoring technique: inventaire/monitoring, sauvegarde, schema, topologie, securite, workers, API et administration doivent devenir des sous-systemes explicites avec contrats, DTO et services dedies.
