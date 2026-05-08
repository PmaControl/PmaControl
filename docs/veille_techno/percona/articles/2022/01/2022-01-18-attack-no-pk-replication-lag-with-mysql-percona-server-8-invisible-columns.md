---
title: Attack No-PK Replication Lag with MySQL/Percona Server 8 Invisible Columns!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/attack-no-pk-replication-lag-with-mysql-percona-server-8-invisible-columns/
  post_id: 25351
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2022-01-18T13:32:03'
published_at_gmt: '2022-01-18T13:32:03'
modified_at: '2026-05-05T17:50:48'
modified_at_gmt: '2026-05-05T17:50:48'
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
- MySQL
- mysql-and-variants
- Percona Server for MySQL
tag_slugs:
- mysql
- mysql-and-variants
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/no-primary-key-replication-lag-mysql.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Attack No-PK Replication Lag with MySQL/Percona Server 8 Invisible Columns!

Source: [Percona Blog](https://www.percona.com/blog/attack-no-pk-replication-lag-with-mysql-percona-server-8-invisible-columns/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2022-01-18T13:32:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The most common issue when using row-based replication (RBR) is replication lag due to the lack of Primary keys. The problem is that any replicated DML will do a full table scan for each modified row on the replica. This bug report explains it more in-depth: https://bugs.mysql.com/bug.php?id=53375 For example, if a delete is executed on … Continued

## Structure detectee

- H2: What If…?
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Attack No-PK Replication Lag with MySQL/Percona Server 8 Invisible Columns!](https://www.percona.com/wp-content/uploads/2026/03/no-primary-key-replication-lag-mysql.png)
- content / image: [no primary key replication lag mysql](https://www.percona.com/wp-content/uploads/2026/03/no-primary-key-replication-lag-mysql-300x169.png)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
