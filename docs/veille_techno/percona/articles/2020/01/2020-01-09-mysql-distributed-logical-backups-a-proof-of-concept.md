---
title: 'MySQL Distributed Logical Backups: a Proof of Concept'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-distributed-logical-backups-a-proof-of-concept/
  post_id: 21406
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2020-01-09T18:22:55'
published_at_gmt: '2020-01-09T18:22:55'
modified_at: '2026-05-04T21:05:20'
modified_at_gmt: '2026-05-04T21:05:20'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- backup
- MySQL
tag_slugs:
- backup
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Distributed-Backups.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Distributed Logical Backups: a Proof of Concept

Source: [Percona Blog](https://www.percona.com/blog/mysql-distributed-logical-backups-a-proof-of-concept/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2020-01-09T18:22:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The importance of having periodic backups is a given in Database life. There are different flavors: binary ones (Percona XtraBackup), binlog backups, disk snapshots (lvm, ebs, etc) and the classic ones: logical backups, the ones that you can take with tools like mysqldump, mydumper, or mysqlpump. Each of them with a specific purpose, MTTRs, retention … Continued

## Structure detectee

- H2: Distributed Backups (or Using all the Slaves Available)
- H3: Tests!
- H2: Concepts
- H3: Stage 1: Preparation
- H3: Stage 2: Guarantee Consistency
- H3: Requirements
- H3: We Would Like Your Feedback!

## Images et graphiques reperes

- featured / image: [MySQL Distributed Logical Backups: a Proof of Concept](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Distributed-Backups.png)
- content / graph_or_chart: [Graph from the Orchestrator GUI](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-01-01-at-10.06.25-1024x463.png)
  Caption: Graph from the Orchestrator GUI

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
