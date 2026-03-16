# Domaine Schema

## Role metier

`Schema` industrialise l'export, la normalisation, la comparaison et la migration des schemas MySQL. Ce n'est pas seulement un export SQL: c'est un veritable depot documentaire et comparatif de la structure des bases.

## Fonctions metier visibles

- export tables, vues, procedures, fonctions, triggers, events
- assemblage de scripts d'import complets
- initialisation et maintenance d'un depot Git de schema
- snapshots numerotes
- comparaison de modeles entre serveurs
- UI de comparaison
- migration de repositories de schema

## Regles metier detaillees

### Le schema est un actif versionne

Le domaine pose implicitement une regle forte: la structure MySQL doit etre suivie comme du code. Les snapshots Git, la numerotation et les comparaisons indiquent que le produit considere le DDL comme un patrimoine gouverne.

### La comparaison doit etre intelligible

Les methodes de normalisation de `CREATE TABLE`, de split de definitions et de rendu diff montrent que l'objectif n'est pas juste de dire "different" mais d'expliquer *ou* et *comment* le schema diverge.

### Les routines et objets derives comptent autant que les tables

Le domaine integre triggers, procedures, fonctions, events et vues. Il traite donc le schema comme un ensemble complet d'objets SQL, pas seulement comme un catalogue de tables.

## Lecture d'architecture

Ce domaine est deja plus moderne que d'autres parties du code:

- signatures typees
- sous-fonctions explicites
- logique plus deterministe
- meilleure separation entre export, formatage et comparaison

Il reste toutefois charge de responsabilites Git, filesystem, SQL et UI dans le meme controleur.

## Chantiers recommandes

- extraire le moteur de diff dans une bibliotheque dediee
- isoler toute interaction Git dans un adapter
- formaliser un format de snapshot stable
- augmenter les tests de non-regression sur la normalisation DDL
