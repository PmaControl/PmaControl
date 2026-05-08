---
title: Resolving the MySQL Active-Active Replication Dilemma
source:
  name: Percona Blog
  url: https://www.percona.com/blog/resolving-the-mysql-active-active-replication-dilemma/
  post_id: 24248
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2021-04-28T17:42:20'
published_at_gmt: '2021-04-28T17:42:20'
modified_at: '2026-05-05T16:37:22'
modified_at_gmt: '2026-05-05T16:37:22'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- pxc
tag_slugs:
- mysql
- mysql-and-variants
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Active-Active-Replication-Dilemma.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Resolving the MySQL Active-Active Replication Dilemma

Source: [Percona Blog](https://www.percona.com/blog/resolving-the-mysql-active-active-replication-dilemma/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2021-04-28T17:42:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Multi-writer replication has been a challenge in the MySQL ecosystem for years before truly dedicated solutions were introduced – first Galera (and so Percona XtradDB Cluster (PXC)) replication (around 2011), and then Group Replication (first GA in 2016). Now, with both multi-writer technologies available, do we still need traditional asynchronous replication, set up in active-active … Continued

## Structure detectee

- H2: Failure Test
- H2: Is Replication Broken?
- H2: Lesson learned?

## Images et graphiques reperes

- featured / image: [Resolving the MySQL Active-Active Replication Dilemma](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Active-Active-Replication-Dilemma.png)
- content / image: [MySQL Active-Active Replication Dilemma](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Active-Active-Replication-Dilemma-300x157.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
