---
title: Percona XtraDB Cluster Transaction Replay Anomaly
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-transaction-replay-anomaly/
  post_id: 16705
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-04-23T16:05:42'
published_at_gmt: '2017-04-23T16:05:42'
modified_at: '2026-05-05T19:47:04'
modified_at_gmt: '2026-05-05T19:47:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- constraint violation
- galera replay transaction
- replay anomaly
tag_slugs:
- constraint-violation
- galera-replay-transaction
- replay-anomaly
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster Transaction Replay Anomaly

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-transaction-replay-anomaly/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-04-23T16:05:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at a transaction replay anomaly in Percona XtraDB Cluster. Introduction Percona XtraDB Cluster/Galera replays a transaction if the data is non-conflicting but, the transaction happens to have conflicting locks. Anomaly Let’s understand this with an example: Let’s assume a two-node cluster (node-1 and node-2) Base table “t” is created … Continued

## Structure detectee

- H3: Introduction
- H4: Anomaly

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster Transaction Replay Anomaly](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
