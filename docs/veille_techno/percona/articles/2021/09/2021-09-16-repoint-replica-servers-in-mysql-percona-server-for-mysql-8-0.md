---
title: Repoint Replica Servers in MySQL/Percona Server for MySQL 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/repoint-replica-servers-in-mysql-percona-server-for-mysql-8-0/
  post_id: 24810
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2021-09-16T11:57:59'
published_at_gmt: '2021-09-16T11:57:59'
modified_at: '2026-04-28T14:59:47'
modified_at_gmt: '2026-04-28T14:59:47'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Server for MySQL
tag_slugs:
- mysql
- mysql-and-variants
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Repoint-Replica-Servers-in-MySQL-3.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Repoint Replica Servers in MySQL/Percona Server for MySQL 8.0

Source: [Percona Blog](https://www.percona.com/blog/repoint-replica-servers-in-mysql-percona-server-for-mysql-8-0/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2021-09-16T11:57:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When doing migrations or failovers in MySQL, there is usually a need to do a topology change and repoint replica servers to obtain replication data from a different server. For example, given servers {A, B, and C} and the following topology: If you need to repoint C to be a replica of B, i.e: You … Continued

## Structure detectee

- H2: If Using File/Position-Based Replication:
- H2: If Using GTID-Based Replication:
- H3: Doing the opposite replication change from chain replication (A->B->C) into one primary with two replicas should be simpler:
- H2: If Using File/Position-Based Replication:
- H2: If Using GTID-Based Replication:
- H3: Conclusion:

## Images et graphiques reperes

- featured / image: [Repoint Replica Servers in MySQL/Percona Server for MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/Repoint-Replica-Servers-in-MySQL-3.png)
- content / image: [MySQL Topology](https://www.percona.com/wp-content/uploads/2026/03/Topology_2replicas.jpg)
- content / image: [repoint mysql](https://www.percona.com/wp-content/uploads/2026/03/Topology_chainrep.jpg)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
