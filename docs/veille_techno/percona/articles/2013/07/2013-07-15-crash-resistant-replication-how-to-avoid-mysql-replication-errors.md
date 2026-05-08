---
title: 'Crash-resistant replication: How to avoid MySQL replication errors'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/crash-resistant-replication-how-to-avoid-mysql-replication-errors/
  post_id: 7108
source_author:
  name: Muhammad Irfan
  slug: mirfan
  url: https://www.percona.com/blog/author/mirfan/
  website: ''
published_at: '2013-07-15T10:00:48'
published_at_gmt: '2013-07-15T10:00:48'
modified_at: '2026-03-25T17:02:17'
modified_at_gmt: '2026-03-25T17:02:17'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- crash safe replication
- crash-resistant replication
- innodb_recovery_update_relay_log
- Oracle MySQL 5.6
tag_slugs:
- crash-safe-replication
- crash-resistant-replication
- innodb_recovery_update_relay_log
- oracle-mysql-5-6
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/3.png
image_count: 10
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Crash-resistant replication: How to avoid MySQL replication errors

Source: [Percona Blog](https://www.percona.com/blog/crash-resistant-replication-how-to-avoid-mysql-replication-errors/)

Auteur source: [Muhammad Irfan](https://www.percona.com/blog/author/mirfan/)

Publication: 2013-07-15T10:00:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server’s “crash-resistant replication” feature is useful in versions 5.1 through 5.5. However, in Percona Server 5.6 it’s replaced with Oracle MySQL 5.6’s “crash safe replication” feature, which has it’s own implementation (you can read more about it here). A MySQL slave normally stores its position in files master.info and relay-log.info which are updated by … Continued

## Images et graphiques reperes

- featured / image: [Crash-resistant replication: How to avoid MySQL replication errors](https://www.percona.com/wp-content/uploads/2026/03/3.png)
- content / image: [Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-1.jpg)
- content / image: [1](https://www.percona.com/wp-content/uploads/2026/03/1.png)
- content / image: [2](https://www.percona.com/wp-content/uploads/2026/03/23.png)
- content / image: [3](https://www.percona.com/wp-content/uploads/2026/03/34.png)
- content / image: [4 (2)](https://www.percona.com/wp-content/uploads/2026/03/4-2.png)
- content / image: [5](https://www.percona.com/wp-content/uploads/2026/03/5.png)
- content / image: [6](https://www.percona.com/wp-content/uploads/2026/03/6.png)
- content / image: [7](https://www.percona.com/wp-content/uploads/2026/03/71.png)
- content / image: [8](https://www.percona.com/wp-content/uploads/2026/03/8.png)

## Auteur source

Muhammad Irfan is vastly experienced in LAMP Stack. Prior to joining Percona Support, he worked in the role of MySQL DBA & LAMP Administrator, maintained high traffic websites, and worked as a Consultant. His professional interests focus on MySQL scalability and on performance optimization.
