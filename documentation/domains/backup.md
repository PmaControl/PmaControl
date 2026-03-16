# Domaine Backup

## Role metier

`Backup` porte toute la chaine de sauvegarde logique et physique: definition des jobs, planification, execution, compression, chiffrement, transfert, telechargement, verification et relance. Le controleur ne sert pas seulement d'UI; il joue aussi le role d'orchestrateur de traitements techniques et de passerelle entre la base `pmacontrol`, le shell systeme, SSH, NAS/SCP et les moteurs de dump MySQL.

## Responsabilites principales

- parametrage des zones de stockage et des dumps
- creation et edition de schedules avec `crontab`
- lancement de dumps (`mysqldump`, `mydumper`, `myloader`)
- telechargement des artefacts
- verification de taille, hash et progression
- pilotage de scripts shell distants
- restauration ou rechargement cible

## Regles metier detaillees

### Definition d'un backup

Un backup n'est valide que s'il reference un serveur, un type de sauvegarde, une destination et un schedule exploitable. Le metier suppose donc l'existence coherente de:

- `backup_main`
- `backup_dump`
- `backup_storage_area`
- `crontab`
- serveur source et, selon le flux, serveur cible

### Validation de la destination

Avant de deposer une sauvegarde, le systeme verifie que la zone de stockage est atteignable. Cela passe par SSH/SCP, la creation de repertoire, puis des controles de taille et de hash une fois le transfert termine. La zone de stockage n'est donc pas un simple label applicatif; c'est une ressource d'infrastructure active.

### Cycle de vie d'un dump

Le cycle metier observable est le suivant:

1. selection de la source et des parametres d'extraction
2. execution du dump local ou distant
3. compression eventuelle
4. chiffrement eventuel
5. transfert vers la zone de stockage
6. verification d'integrite
7. historisation en base

### Journalisation et reprise

Le code conserve des journaux techniques, des PID, des metadonnees de fichiers et des etats intermediaires. Cela montre une logique de reprise orientee exploitant: l'application doit permettre de reprendre ou diagnostiquer un backup partiellement execute.

## Architecture interne du domaine

`Backup.php` concentre plusieurs sous-domaines qui devraient idealement etre separes:

- service de definition des jobs
- service d'execution shell
- service d'acces MySQL source
- service de transfert et de verifications NAS/SCP
- service de restauration
- service d'exposition UI

Aujourd'hui, ces responsabilites sont co-localisees, ce qui rend le domaine puissant mais fragile. Une erreur de validation ou de quoting shell peut impacter toute la chaine.

## Risques et points d'attention

- composition de commandes shell par concatenation
- manipulation de secrets dechiffres a chaud
- ecriture de scripts temporaires sur disque
- endpoints de telechargement de fichiers
- dependance forte au systeme et aux utilitaires presents

## Proposition de modernisation

- extraire un `BackupJobService`
- extraire un `BackupTransferService`
- unifier les DTO d'entree/sortie
- encapsuler toutes les commandes shell
- securiser les telechargements et les chemins
