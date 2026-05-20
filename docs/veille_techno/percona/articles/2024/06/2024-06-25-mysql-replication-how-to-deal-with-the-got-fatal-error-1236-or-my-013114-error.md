---
title: 'MySQL Replication: How To Deal With the ‘Got Fatal Error 1236’ or MY-013114 Error'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-replication-how-to-deal-with-the-got-fatal-error-1236-or-my-013114-error/
  post_id: 28726
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-06-25T17:51:45'
published_at_gmt: '2024-06-25T17:51:45'
modified_at: '2026-03-26T20:26:14'
modified_at_gmt: '2026-03-26T20:26:14'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-toolkit
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- error 1236
- GTID
- MySQL
- mysql-and-variants
- Replication
tag_slugs:
- error-1236
- gtid
- mysql
- mysql-and-variants
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Got-Fatal-Error-1236.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Replication: How To Deal With the ‘Got Fatal Error 1236’ or MY-013114 Error

Source: [Percona Blog](https://www.percona.com/blog/mysql-replication-how-to-deal-with-the-got-fatal-error-1236-or-my-013114-error/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-06-25T17:51:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Replication has been the core functionality, allowing high availability in MySQL for decades already. However, you may still encounter replication errors that keep you awake at night. One of the most common and challenging to deal with starts with: “Got fatal error 1236 from source when reading data from binary log“. This blog post is … Continued

## Structure detectee

- H2: Errant GTIDs
- H2: The max_allowed_packet is too small?
- H2: Missing binary log file
- H2: Out of disk space
- H3: Summary

## Images et graphiques reperes

- featured / image: [MySQL Replication: How To Deal With the ‘Got Fatal Error 1236’ or MY-013114 Error](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Got-Fatal-Error-1236.jpg)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
