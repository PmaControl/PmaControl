---
title: How To Skip Replication Errors in GTID-Based Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-skip-replication-errors-in-gtid-based-replication/
  post_id: 26375
source_author:
  name: Mani Paluru
  slug: mani-paluru
  url: https://www.percona.com/blog/author/mani-paluru/
  website: ''
published_at: '2022-12-08T15:14:24'
published_at_gmt: '2022-12-08T15:14:24'
modified_at: '2026-03-26T20:30:26'
modified_at_gmt: '2026-03-26T20:30:26'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Replication-Errors-in-GTID-Based-Replication.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Skip Replication Errors in GTID-Based Replication

Source: [Percona Blog](https://www.percona.com/blog/how-to-skip-replication-errors-in-gtid-based-replication/)

Auteur source: [Mani Paluru](https://www.percona.com/blog/author/mani-paluru/)

Publication: 2022-12-08T15:14:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I’m going to discuss how to easily skip the replication errors in GTID (Global Transaction Identifier)-based replication. In the MySQL world, if replication is broken we all use the famous SET GLOBAL SQL_SLAVE_SKIP_COUNTER=1; to skip the replication error. It always works if it’s a traditional binlogs events-based replication and is helpful to … Continued

## Images et graphiques reperes

- featured / image: [How To Skip Replication Errors in GTID-Based Replication](https://www.percona.com/wp-content/uploads/2026/03/Replication-Errors-in-GTID-Based-Replication.png)
- content / image: [Replication Errors in GTID-Based Replication](https://www.percona.com/wp-content/uploads/2026/03/Replication-Errors-in-GTID-Based-Replication-300x157.png)
- content / image: [Get Started with Percona Support Today!](https://www.percona.com/wp-content/uploads/2026/03/88bc52f5-291c-4a9c-8e34-ec7742d30ac5.png)

## Auteur source

Mani Works as DBA for Percona in Managed services team, He has several years of experience in managing multiple Mysql client companies.
