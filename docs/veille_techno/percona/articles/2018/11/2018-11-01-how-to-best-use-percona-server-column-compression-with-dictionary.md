---
title: How To Best Use Percona Server Column Compression With Dictionary
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-best-use-percona-server-column-compression-with-dictionary/
  post_id: 19560
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2018-11-01T14:04:35'
published_at_gmt: '2018-11-01T14:04:35'
modified_at: '2026-05-05T19:34:17'
modified_at_gmt: '2026-05-05T19:34:17'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- compression
- Database Compression Methods
- json
- MySQL compression
tag_slugs:
- compression
- database-compression-methods
- json
- mysql-compression
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Database-Compression-Methods.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Best Use Percona Server Column Compression With Dictionary

Source: [Percona Blog](https://www.percona.com/blog/how-to-best-use-percona-server-column-compression-with-dictionary/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2018-11-01T14:04:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Very often, database performance is affected by the inability to cache all the required data in memory. Disk IO, even when using the fastest devices, takes much more time than a memory access. With MySQL/InnoDB, the main memory cache is the InnoDB buffer pool. There are many strategies we can try to fit as much … Continued

## Structure detectee

- H2: A simple use case
- H3: Femtozip to the rescue

## Images et graphiques reperes

- content / image: [column compression](https://www.percona.com/wp-content/uploads/2026/03/Database-Compression-Methods.jpg)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
