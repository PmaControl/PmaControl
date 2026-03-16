# Domaine Cleaner

## Role metier

`Cleaner` est le domaine de purge, d'archivage et de reorganisation de donnees. C'est probablement le bloc metier le plus riche du projet avec `98` methodes visibles. Il gere des jobs complexes qui peuvent impacter fortement l'integrite referentielle et la volumetrie.

## Ce que fait réellement Cleaner

- analyse les dependances entre tables
- calcule l'ordre de suppression
- cree des tables temporaires de travail
- exporte, archive et pousse les donnees purgées
- compare l'etat avant/apres
- pilote des boucles longues et des workers
- loggue finement les etapes techniques

## Regles metier detaillees

### Regle 1: ne pas casser la referentialite

Le domaine suppose que toute purge doit tenir compte des FK reelles, virtuelles ou calculees. La purge n'est donc jamais une simple instruction `DELETE`; c'est un graphe d'impact. C'est pour cela que Cleaner manipule des ordres de suppression, des tables impactees, des chemins et des controles de circularite.

### Regle 2: pouvoir archiver avant de detruire

Une suppression metier importante peut exiger une sauvegarde ou une archive avant la purge. Les fonctions de compression, chiffrement et push d'archive montrent que la conservation des donnees supprimees fait partie du metier.

### Regle 3: tracer et reprendre

Le code gere des logs, des PID, des signaux, des etats de daemon et des sorties de details. Le produit est pense pour des jobs longs, observables, et parfois interrompus puis relances.

### Regle 4: supporter les anomalies de schema

Le domaine ne repose pas uniquement sur la verite des FKs MySQL. Il sait travailler avec des FK virtuelles, des exceptions et des heuristiques, ce qui est capital dans les SI historiques ou les contraintes sont incompletes.

## Lecture d'architecture

Cleaner est a la fois:

- un moteur de graphe de dependances
- un orchestrateur de purge
- un systeme d'archive
- un moteur de logs d'exploitation
- une UI d'administration

Cette concentration explique pourquoi ce controleur est lourd. Sa vraie decomposition cible devrait separer:

- calcul d'ordre
- execution de purge
- gestion d'archives
- supervision/daemon
- exposition web

## Risques principaux

- operations destructives massives
- usage de shell et de fichiers temporaires
- desactivation ponctuelle de `FOREIGN_KEY_CHECKS`
- derive possible entre modele calcule et schema reel

## Recommandations

- transformer les strategies de purge en objets explicites
- isoler les parcours destructifs dans des workers securises
- ajouter des dry-run obligatoires sur les operations critiques
- versionner les plans de purge et leurs resultats
