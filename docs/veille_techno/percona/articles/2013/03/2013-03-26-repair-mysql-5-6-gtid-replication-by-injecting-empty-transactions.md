---
title: Repair MySQL 5.6 GTID replication by injecting empty transactions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/repair-mysql-5-6-gtid-replication-by-injecting-empty-transactions/
  post_id: 6753
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2013-03-26T14:37:17'
published_at_gmt: '2013-03-26T14:37:17'
modified_at: '2026-04-28T21:51:32'
modified_at_gmt: '2026-04-28T21:51:32'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- MySQL
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-5.6-gtid-replication.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Repair MySQL 5.6 GTID replication by injecting empty transactions

Source: [Percona Blog](https://www.percona.com/blog/repair-mysql-5-6-gtid-replication-by-injecting-empty-transactions/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2013-03-26T14:37:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a previous post I explained how to repair MySQL 5.6 GTID replication using two different methods. I didn’t mention the famous SET GLOBAL SQL_SLAVE_SKIP_COUNTER = n for a simple reason, it doesn’t work anymore if you are using MySQL GTID. Then the question is: Is there any easy way to skip a single transaction? … Continued

## Structure detectee

- H3: Is there any easy way to skip a single transaction?
- H3: MySQL 5.6 GTID

## Images et graphiques reperes

- featured / image: [Repair MySQL 5.6 GTID replication by injecting empty transactions](https://www.percona.com/wp-content/uploads/2026/03/mysql-5.6-gtid-replication.jpeg)

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
