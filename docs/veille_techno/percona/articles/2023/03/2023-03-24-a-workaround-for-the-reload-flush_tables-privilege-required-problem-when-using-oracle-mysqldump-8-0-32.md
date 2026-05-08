---
title: A Workaround for The "RELOAD/FLUSH_TABLES privilege required" Problem When Using Oracle mysqldump 8.0.32
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-workaround-for-the-reload-flush_tables-privilege-required-problem-when-using-oracle-mysqldump-8-0-32/
  post_id: 26776
source_author:
  name: Yura Sorokin
  slug: yura-sorokin
  url: https://www.percona.com/blog/author/yura-sorokin/
  website: ''
published_at: '2023-03-24T13:10:24'
published_at_gmt: '2023-03-24T13:10:24'
modified_at: '2026-03-26T20:29:58'
modified_at_gmt: '2026-03-26T20:29:58'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_database_monitoring_blue_navy_colored_texture_35588dbb-cc91-4a19-bb33-743dccb4b525-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Workaround for The "RELOAD/FLUSH_TABLES privilege required" Problem When Using Oracle mysqldump 8.0.32

Source: [Percona Blog](https://www.percona.com/blog/a-workaround-for-the-reload-flush_tables-privilege-required-problem-when-using-oracle-mysqldump-8-0-32/)

Auteur source: [Yura Sorokin](https://www.percona.com/blog/author/yura-sorokin/)

Publication: 2023-03-24T13:10:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In MySQL Server 8.0.32, Oracle fixed Bug #105761: “mysqldump make a non-consistent backup with ‐‐single-transaction option” (this commit) which caused a wave of complaints from users who could no longer do backups with the mysqldump utility because of the lack of the required privileges. Bug #109701 “Fix for #33630199 in 8.0.32 introduces regression when ‐‐set-gtid-purged=OFF” … Continued

## Images et graphiques reperes

- featured / image: [A Workaround for The "RELOAD/FLUSH_TABLES privilege required" Problem When Using Oracle mysqldump 8.0.32](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_database_monitoring_blue_navy_colored_texture_35588dbb-cc91-4a19-bb33-743dccb4b525-1.png)

## Auteur source

Yura is a Principal Software Engineer at Percona, mostly working on Percona Server Core. You might have heard of him as an author of "Compressed Columns with Dictionaries", "SEQUENCE_TABLE()" and "C++ UDF wrappers". Before joining in July 2015 he was leading a cloud file service backend dev team which was focusing on client-side encryption. He has 20+ years of software development experience, primarily in C++. Yura holds Master degree in Computer Science from National Technical University of Ukraine. He lives in Kyiv, Ukraine.
