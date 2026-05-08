---
title: 'MySQL with Diagrams Part One: Replication Architecture'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-with-diagrams-part-one-replication-architecture/
  post_id: 29135
source_author:
  name: Yunus Uyanik
  slug: yunus-uyanik
  url: https://www.percona.com/blog/author/yunus-uyanik/
  website: ''
published_at: '2024-12-12T14:35:56'
published_at_gmt: '2024-12-12T14:35:56'
modified_at: '2026-03-26T20:25:51'
modified_at_gmt: '2026-03-26T20:25:51'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-with-Diagrams-Replication-Architecture.jpg
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL with Diagrams Part One: Replication Architecture

Source: [Percona Blog](https://www.percona.com/blog/mysql-with-diagrams-part-one-replication-architecture/)

Auteur source: [Yunus Uyanik](https://www.percona.com/blog/author/yunus-uyanik/)

Publication: 2024-12-12T14:35:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this series, “MySQL with Diagrams,” I’ll use diagrams to explain internals, architectures, and structures as detailed as possible. In basic terms, here’s how replication works: the transactions are written into a binary log on the source side, carried into the replica, and applied. The replica’s connection metadata repository contains information that the replication receiver … Continued

## Structure detectee

- H3: Binary log dump thread
- H3: Replication I/O receiver thread
- H3: Replication SQL applier thread
- H4: Configuration for Optimal Performance

## Images et graphiques reperes

- featured / image: [MySQL with Diagrams Part One: Replication Architecture](https://www.percona.com/wp-content/uploads/2026/03/MySQL-with-Diagrams-Replication-Architecture.jpg)
- content / graph_or_chart: [MySQL with Diagrams](https://www.percona.com/wp-content/uploads/2026/03/mysqlreplication-diagram-1-scaled.png)
