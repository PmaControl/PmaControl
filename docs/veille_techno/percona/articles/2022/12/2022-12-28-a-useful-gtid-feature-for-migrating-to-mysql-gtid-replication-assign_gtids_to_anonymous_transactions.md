---
title: A Useful GTID Feature for Migrating to MySQL GTID Replication – ASSIGN_GTIDS_TO_ANONYMOUS_TRANSACTIONS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-useful-gtid-feature-for-migrating-to-mysql-gtid-replication-assign_gtids_to_anonymous_transactions/
  post_id: 26425
source_author:
  name: Gaurav Pareek
  slug: gaurav-pareek
  url: https://www.percona.com/blog/author/gaurav-pareek/
  website: ''
published_at: '2022-12-28T14:19:48'
published_at_gmt: '2022-12-28T14:19:48'
modified_at: '2026-03-26T20:30:22'
modified_at_gmt: '2026-03-26T20:30:22'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrating-to-MySQL-GTID-Replication.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Useful GTID Feature for Migrating to MySQL GTID Replication – ASSIGN_GTIDS_TO_ANONYMOUS_TRANSACTIONS

Source: [Percona Blog](https://www.percona.com/blog/a-useful-gtid-feature-for-migrating-to-mysql-gtid-replication-assign_gtids_to_anonymous_transactions/)

Auteur source: [Gaurav Pareek](https://www.percona.com/blog/author/gaurav-pareek/)

Publication: 2022-12-28T14:19:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In managed services, we get requests to migrate from traditional to GTID-based replication. However, the customer does not want to first enable the GTID on the source node (production). Before MySQL 8.0.23, replication from the disabled GTID source to an enabled GTID replica was impossible. In this blog, I will talk about a new MySQL … Continued

## Structure detectee

- H2: Acceptable inputs

## Images et graphiques reperes

- featured / image: [A Useful GTID Feature for Migrating to MySQL GTID Replication – ASSIGN_GTIDS_TO_ANONYMOUS_TRANSACTIONS](https://www.percona.com/wp-content/uploads/2026/03/Migrating-to-MySQL-GTID-Replication.png)
- content / image: [Migrating to MySQL GTID Replication](https://www.percona.com/wp-content/uploads/2026/03/Migrating-to-MySQL-GTID-Replication-300x157.png)

## Auteur source

Gaurav has worked as a DBA for more than 7 years, he has worked in healthcare and finance. He likes cricket, travelling and trekking. He Joined percona in 2021.
