# Audit Securite de PmaControl

- Genere le: 2026-03-12 01:42:25
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
