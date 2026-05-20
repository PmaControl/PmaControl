---
title: MySQL Orchestrator Failover Behavior During Replication Lag
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-orchestrator-failover-behavior-during-replication-lag/
  post_id: 34993
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2025-06-20T17:33:43'
published_at_gmt: '2025-06-20T17:33:43'
modified_at: '2026-03-26T20:25:26'
modified_at_gmt: '2026-03-26T20:25:26'
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
- Insight for DBAs
- MySQL
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- storage-engine
tags:
- asynchronous MySQL replication
- MySQL
- MySQL High Availability
- mysql orchestrator
- mysql-and-variants
- orchestrator
tag_slugs:
- asynchronous-mysql-replication
- mysql
- mysql-high-availability
- mysql-orchestrator
- mysql-and-variants
- orchestrator
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Orchestrator-Failover.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Orchestrator Failover Behavior During Replication Lag

Source: [Percona Blog](https://www.percona.com/blog/mysql-orchestrator-failover-behavior-during-replication-lag/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2025-06-20T17:33:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Managing farms of MySQL servers under a replication environment is very efficient with the help of a MySQL orchestrator tool. This ensures a smooth transition happens when there is any ad hoc failover or a planned/graceful switchover comes into action. Several configuration parameters play a crucial role in controlling and influencing failover behavior. In this … Continued

## Structure detectee

- H2: FailMasterPromotionIfSQLThreadNotUpToDate
- H2: DelayMasterPromotionIfSQLThreadNotUpToDate
- H2: FailMasterPromotionOnLagMinutes
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL Orchestrator Failover Behavior During Replication Lag](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Orchestrator-Failover.jpg)
- content / image: [mysql performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-9.png)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
