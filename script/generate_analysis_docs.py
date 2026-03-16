#!/usr/bin/env python3

from __future__ import annotations

import re
from collections import Counter
from datetime import datetime
from pathlib import Path


ROOT = Path("/srv/www/pmacontrol")
DOC = ROOT / "documentation"
CTRL = ROOT / "App" / "Controller"
LIB = ROOT / "App" / "Library"
MODEL = ROOT / "App" / "model" / "IdentifierPmacontrol"
VIEW = ROOT / "App" / "view"


def count_php_files(path: Path) -> int:
    return len(list(path.rglob("*.php")))


def count_any_files(path: Path) -> int:
    return len([p for p in path.rglob("*") if p.is_file()])


def top_view_folders() -> list[tuple[str, int]]:
    counter = Counter(p.parent.name for p in VIEW.rglob("*") if p.is_file())
    return counter.most_common(20)


def top_controller_complexity() -> list[tuple[str, int, int]]:
    items: list[tuple[str, int, int]] = []
    for file in CTRL.rglob("*.php"):
        text = file.read_text(encoding="utf-8", errors="ignore")
        funcs = len(re.findall(r"\bfunction\s+[A-Za-z0-9_]+\s*\(", text))
        db_calls = text.count("sql_query(") + text.count("sql_save(")
        items.append((file.stem, funcs, db_calls))
    return sorted(items, key=lambda row: (row[1], row[2], row[0]), reverse=True)[:20]


def table_count() -> int:
    doc = (DOC / "pmacontrol_tables_documentation.md").read_text(encoding="utf-8", errors="ignore")
    return len(re.findall(r"^## Table `", doc, flags=re.MULTILINE))


def controller_list() -> list[str]:
    return sorted(p.stem for p in CTRL.rglob("*.php"))


def library_list() -> list[str]:
    return sorted(p.stem for p in LIB.rglob("*.php"))


def table_prefixes() -> list[tuple[str, int]]:
    counter: Counter[str] = Counter()
    for file in MODEL.glob("*.php"):
        counter[file.stem.split("_")[0]] += 1
    return counter.most_common(20)


def controller_docs_metrics() -> tuple[int, list[str]]:
    doc_dir = DOC / "controller"
    files = sorted(p.name for p in doc_dir.glob("*.md") if p.name != "README.md")
    return len(files), files[:15]


def write(path: Path, content: str) -> None:
    path.write_text(content.strip() + "\n", encoding="utf-8")


def reverse_engineering_doc() -> str:
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    controllers = count_php_files(CTRL)
    libraries = count_php_files(LIB)
    models = count_php_files(MODEL)
    views = count_any_files(VIEW)
    tables = table_count()
    top_complex = top_controller_complexity()
    top_views = top_view_folders()
    prefixes = table_prefixes()
    controller_docs, _ = controller_docs_metrics()

    complexity_lines = "\n".join(
        f"- `{name}`: {funcs} methodes, {db_calls} appels BD visibles"
        for name, funcs, db_calls in top_complex
    )
    view_lines = "\n".join(f"- `{name}`: {count} fichiers de vue" for name, count in top_views)
    prefix_lines = "\n".join(f"- Prefixe `{name}`: {count} tables" for name, count in prefixes)

    return f"""
# Reverse Engineering Complet de PmaControl

- Genere le: {now}
- Perimetre analyse: `App/Controller`, `App/Library`, `App/model/IdentifierPmacontrol`, `App/view`, `documentation/*.md`
- Volumetrie applicative:
  - Controles web/CLI: {controllers}
  - Bibliotheques metier et techniques: {libraries}
  - Modeles de schema: {models}
  - Fichiers de vue: {views}
  - Tables documentees dans la base `pmacontrol`: {tables}
  - Fiches controleurs disponibles dans `documentation/controller/`: {controller_docs}

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

{complexity_lines}

Ces fichiers concentrent l'essentiel du patrimoine fonctionnel et sont donc les plus critiques pour tout refactoring, toute mise en securite et toute evolution de contrat metier.

### 3.3 Zones de vues les plus actives

{view_lines}

Cela confirme un biais fort vers les ecrans d'operations: l'interface n'est pas generique, elle est organisee par domaine d'exploitation.

## 4. Modele de donnees reverse-engineere

La base `pmacontrol` compte {tables} tables documentees. Les prefixes de tables les plus significatifs sont:

{prefix_lines}

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
"""


def security_audit_doc() -> str:
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    return f"""
# Audit Securite de PmaControl

- Genere le: {now}
- Type: audit statique de code + lecture architecture + validation ponctuelle des integrations
- Sources: `App/Controller`, `App/Library`, `App/Webroot`, `documentation/*`, API REST, integration Telegram/MCP

## 1. Resume executif

Le projet dispose d'une forte puissance operationnelle mais expose aussi une surface d'attaque importante: SQL dynamique, appels shell, manipulation de secrets, endpoints de configuration, lecture directe des superglobales et actions d'administration riches. La principale faiblesse n'est pas un bug unique; c'est l'accumulation de patterns permissifs dans un monolithe administrateur.

Le niveau de risque global est **eleve** pour une exposition internet directe, et **moyen a eleve** pour une exposition restreinte a un reseau d'exploitation. Le produit peut rester exploitable en environnement de confiance, mais il devrait etre considere comme sensible tant qu'un durcissement structurel n'a pas ete effectue.

## 2. Constat principal

### Critique 1. SQL dynamique concatene avec donnees de requete

Plusieurs controleurs construisent des requetes SQL par concatenation directe de `$_POST`, `$_GET` ou parametres de route. Exemples typiques:

- `App/Controller/Tag.php`
- `App/Controller/Client.php`
- `App/Controller/Environment.php`
- plusieurs sections de `Backup.php`, `Alias.php`, `Server.php`, `ForeignKey.php`, `ProxySQL.php`

Risque:

- injection SQL
- corruption du referentiel
- escalade horizontale par modification d'objets non autorises

Remediation:

- interdire toute interpolation directe
- introduire un layer de requetes preparees ou un builder impose
- centraliser la validation des champs modifiables

### Critique 2. Execution shell avec parametres composes dynamiquement

Le code fait un usage massif de `shell_exec`, `passthru` et de commandes shell composees par concatenation, surtout dans:

- `App/Controller/Backup.php`
- `App/Controller/Aspirateur.php`
- `App/Controller/Load.php`

Risque:

- command injection
- execution arbitraire sur l'hote applicatif
- exfiltration de secrets via arguments ou fichiers temporaires

Remediation:

- remplacer les shell strings par des appels encapsules et echappes
- introduire une allow-list de binaires
- isoler les operations shell dans des workers non privilegies

### Critique 3. Secrets dechiffrables a chaud

Les mots de passe SSH, MySQL et autres secrets sont stockes chiffrables/dechiffrables par l'application. On observe des usages repetes de:

- `Crypt::decrypt`
- `Chiffrement::decrypt`

Risque:

- tout code ayant acces au runtime peut lire les secrets
- fuite dans logs, dumps memoire, debug, erreurs ou variables shell

Remediation:

- passer a un secret store externe ou a minima a une cle de chiffrement hors repo/hors DB
- journaliser les usages de dechiffrement
- minimiser la fenetre de vie des secrets en memoire

## 3. Risques eleves

### 3.1 CSRF et actions mutables

De nombreuses actions modifient l'etat via POST ou GET sans mecanisme CSRF visible:

- updates inline de tags, clients, environnements
- suppressions
- toggles d'etat serveur
- operations d'administration

Risque:

- modification non voulue depuis un navigateur authentifie

### 3.2 Controles d'autorisation disperses

L'ACL existe, mais le pattern global reste heterogene. Certaines actions web, CLI ou API reposent plus sur le contexte de routage et la convention que sur une verification explicite de droits metier.

Risque:

- divergence entre intention ACL et execution reelle
- endpoints techniques exposes plus largement que prevu

### 3.3 Fichiers et chemins manipules dynamiquement

Plusieurs modules ecrivent, lisent, telechargent ou suppriment des fichiers:

- dumps
- archives
- scripts shell
- fichiers md5
- caches pivots

Risque:

- path traversal
- overwrite de fichiers sensibles
- fuite de fichiers via endpoints de download

## 4. Risques moyens

### 4.1 Exposition de details internes

Le bootstrap active un mode debug en fonction d'IP ou d'environnement et certains controleurs retournent SQL, stack traces ou JSON techniques. En contexte mal cloisonne, cela facilite la reconnaissance.

### 4.2 Surface reseau large

Le produit ouvre ou pilote:

- connexions MySQL
- SSH/SCP
- LDAP
- appels HTTP sortants
- integrations Telegram/Ollama

Sans politique reseau stricte, la plateforme devient un point de rebond lateral.

### 4.3 Monolithe a privileges eleves

La meme application peut:

- lire et ecrire en base
- lancer des commandes shell
- manipuler des secrets
- initier des connexions SSH
- reconfigurer des proxys

Le blast radius d'un compte compromis est donc important.

## 5. Bonnes pratiques deja presentes

- presence d'un systeme ACL/Auth
- journalisation Monolog
- API REST documentee
- RBAC sur le serveur MCP Telegram
- segmentation metier par clients/environnements
- historique et tables de suivi pour plusieurs workflows

## 6. Priorites de remediation

### Priorite 1

- sortir le SQL dynamique du code de presentation
- encapsuler toute execution shell
- mettre en place CSRF systematique
- revoir la gestion et le stockage des secrets

### Priorite 2

- isoler les workers/system calls du frontal web
- generaliser les DTO/validators pour l'API et les formulaires
- renforcer l'audit trail des operations sensibles
- interdire les ecritures filesystem hors repertoires allow-list

### Priorite 3

- normaliser le design des controleurs
- supprimer le dead code/commentaires de debug
- augmenter la couverture de tests sur les parcours sensibles
- segmenter les privileges BD et SSH par fonction

## 7. Controles recommandes

- WAF ou reverse proxy restrictif
- acces applicatif reserve au reseau d'exploitation
- rotation de secrets
- supervision des appels shell
- analyse SAST continue
- revue de droits par domaine (backup, schema, admin, monitoring)
- separation compte lecture/compte action

## 8. Conclusion securite

PmaControl est un outil d'administration puissant qui doit etre traite comme un composant privilegie de production. Son principal enjeu securite n'est pas l'absence de fonctionnalites de securite, mais la cohabitation de capacites tres sensibles dans un codebase historiquement permissif. L'audit recommande un durcissement par couches: validation, authorisation, secret management, encapsulation shell, isolation des workers et reduction des privileges.
"""


def improvements_doc() -> str:
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    return f"""
# Liste Complete des Ameliorations de PmaControl

- Genere le: {now}
- Objectif: backlog d'amelioration transverse, couvrant architecture, securite, tests, DX, performance et gouvernance

## 1. Architecture et code

1. Decouper les gros controleurs (`Cleaner`, `Schema`, `Aspirateur`, `Backup`, `Dot3`) en services orientés domaine.
2. Sortir la logique metier des actions web vers des classes applicatives testables.
3. Introduire des DTO pour les entrees API, formulaires et jobs.
4. Remplacer les acces directs aux superglobales par une couche request explicite.
5. Formaliser les contrats de sortie HTML/JSON/fichier.
6. Unifier le style de code et les conventions de nommage.
7. Supprimer le code mort, les commentaires historiques et les chemins de debug temporaires.
8. Centraliser la construction d'URLs et de routes.
9. Introduire des exceptions metier dediees par domaine.
10. Documenter explicitement les dependances entre controleurs et bibliotheques.

## 2. Base de donnees et acces SQL

11. Remplacer toute interpolation SQL par requetes preparees.
12. Introduire un repository layer pour les entites critiques.
13. Normaliser les transactions dans les parcours d'ecriture.
14. Ajouter des contraintes BD manquantes la ou le metier les suppose deja.
15. Distinguer clairement referentiel, time series, caches et journaux.
16. Rationaliser les tables `ts_*` et documenter leur retention.
17. Ajouter des index verifies par usage sur les tables de topologie, backup et monitoring.
18. Mettre en place une strategie de migrations coherentement versionnee.
19. Mesurer et limiter la croissance des tables system-versioned.
20. Mieux separer les ecritures techniques des donnees de gouvernance.

## 3. Securite

21. Ajouter une protection CSRF globale.
22. Renforcer la validation des entrees route, GET et POST.
23. Centraliser les checks d'autorisation metier.
24. Isoler les appels shell et interdire la concatenation brute.
25. Basculer les secrets vers un vault ou un stockage externe.
26. Reduire les privileges des comptes MySQL utilises par l'application.
27. Segmenter les cles SSH par cas d'usage.
28. Journaliser toutes les operations sensibles avec acteur, cible et resultat.
29. Filtrer les logs pour eviter la fuite de secrets et chemins sensibles.
30. Revoir l'exposition des endpoints de telechargement et d'import.

## 4. Testabilite et qualite

31. Ajouter des tests unitaires sur les services extraits des gros controleurs.
32. Ajouter des tests d'integration sur API, backup, restore et topologie.
33. Mettre en place des fixtures SQL minimales et reutilisables.
34. Ajouter une base de tests dediee ou un environnement ephemeral.
35. Introduire un pipeline CI pour lint, phpunit et verification doc.
36. Ajouter une analyse statique PHPStan/Psalm graduellement.
37. Couvrir les parcours d'erreur et d'autorisation.
38. Tester explicitement les workflows CLI.
39. Ajouter des snapshots de graphes et de documents generes.
40. Mettre en place une strategie de non-regression sur les exports.

## 5. Performance et exploitation

41. Identifier les requetes les plus couteuses des ecrans lourds.
42. Mettre en cache les vues purement analytiques.
43. Deplacer les taches longues hors requete web.
44. Industrialiser la gestion des fichiers pivots, locks et md5.
45. Documenter les timeouts reseau par integration.
46. Ajouter des garde-fous de concurrence sur workers et cleaner.
47. Rendre observable la file de jobs et les echecs techniques.
48. Ajouter des budgets de retention sur logs, archives et historiques.
49. Segreguer les volumes filesystem temporaires et definitifs.
50. Prevoir une politique de reprise sur incident pour backup et collecte.

## 6. UX operateur et documentation

51. Uniformiser les messages d'erreur et de succes.
52. Ajouter une doc fonctionnelle par domaine, pas seulement par classe.
53. Expliquer dans l'UI les impacts des operations destructives.
54. Ajouter un guide operateur pour backup, cleaner, schema, cluster.
55. Generer un catalogue de routes/action par controleur.
56. Ajouter un glossaire metier des objets `mysql_*`, `ts_*`, `dot3_*`, `backup_*`.
57. Documenter les prerequis systeme exacts par module.
58. Ajouter des exemples JSON/cURL pour toutes les integrations.
59. Produire une matrice ACL lisible par role.
60. Documenter les parcours d'onboarding et de support.

## 7. Modernisation technique

61. Introduire progressivement des types retour PHP natifs.
62. Eliminer les `var` et proprietes dynamiques legacy.
63. Ajouter `strict_types=1` la ou c'est viable.
64. Remplacer les utilitaires statiques les plus couplants par des services injectes.
65. Isoler la couche Glial legacy derriere des adapters.
66. Reduire le nombre de points d'entree qui ecrivent en base depuis le web.
67. Factoriser les wrappers HTTP/SSH/SQL.
68. Ajouter une strategy de compatibilite PHP documentee.
69. Standardiser le packaging des scripts CLI.
70. Simplifier la relation entre docs generees et docs hand-written.

## 8. Ordre recommande de chantier

### Lot 1

- SQL, CSRF, secret management, shell hardening

### Lot 2

- extraction services sur `Api`, `Server`, `Client`, `Environment`, `Tag`, `Alias`, `StorageArea`

### Lot 3

- refonte par domaine des blocs lourds `Backup`, `Cleaner`, `Aspirateur`, `Schema`, `Dot3`

### Lot 4

- CI/CD, static analysis, test data management, observabilite

## 9. Conclusion

La liste d'ameliorations est volontairement exhaustive et structurante. Le projet n'a pas besoin d'une simple couche cosmetique; il a besoin d'une mise a niveau par domaines, en commencant par la securite et les gros noeuds de complexite, puis en consolidant la testabilite et la gouvernance documentaire.
"""


def main() -> None:
    write(DOC / "reverse_engineering_complete.md", reverse_engineering_doc())
    write(DOC / "audit_securite.md", security_audit_doc())
    write(DOC / "ameliorations_completes.md", improvements_doc())


if __name__ == "__main__":
    main()
