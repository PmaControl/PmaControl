---
title: 'Synchronous Replication in MySQL: Galera vs. Group Replication'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/battle-for-synchronous-replication-in-mysql-galera-vs-group-replication/
  post_id: 16208
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2017-02-24T20:46:32'
published_at_gmt: '2017-02-24T20:46:32'
modified_at: '2026-05-05T18:29:22'
modified_at_gmt: '2026-05-05T18:29:22'
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
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Codership
- galera
- group replication
- Synchronous Replication
tag_slugs:
- codership
- galera
- group-replication
- synchronous-replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Availability-Solutions-6-e1487968755582.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Synchronous Replication in MySQL: Galera vs. Group Replication

Source: [Percona Blog](https://www.percona.com/blog/battle-for-synchronous-replication-in-mysql-galera-vs-group-replication/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2017-02-24T20:46:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

UPDATE: Some of the language in the original post was considered overly-critical of Oracle by some community members. This was not my intent, and I’ve modified the language to be less so. I’ve also changed the term “synchronous” (which the use of is inaccurate and misleading) to “virtually synchronous.” This term is more accurate and … Continued

## Structure detectee

- H3: More Than Asynchronous Replication
- H3: Multi-Master vs. Master-Slave
- H3: Replication: Majority vs. All
- H3: Schema Requirements
- H3: GTID
- H3: WAN Support
- H3: State Transfers
- H3: Auto Increment Settings
- H3: Multi-Threaded Slave Side Applying
- H3: Flow Control
- H3: Network Hiccup/Partition Handling
- H4: Article with Similar Subject

## Images et graphiques reperes

- featured / image: [Synchronous Replication in MySQL: Galera vs. Group Replication](https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Availability-Solutions-6-e1487968755582.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
