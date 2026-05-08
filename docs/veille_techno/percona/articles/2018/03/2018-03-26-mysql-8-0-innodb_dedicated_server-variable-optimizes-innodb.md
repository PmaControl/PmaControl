---
title: New MySQL 8.0 innodb_dedicated_server Variable Optimizes InnoDB from the Get-Go
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-0-innodb_dedicated_server-variable-optimizes-innodb/
  post_id: 18311
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2018-03-26T20:26:50'
published_at_gmt: '2018-03-26T20:26:50'
modified_at: '2026-03-20T21:45:24'
modified_at_gmt: '2026-03-20T21:45:24'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
tag_slugs:
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/eight-point-oh.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# New MySQL 8.0 innodb_dedicated_server Variable Optimizes InnoDB from the Get-Go

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-0-innodb_dedicated_server-variable-optimizes-innodb/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2018-03-26T20:26:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll look at the MySQL 8.0 innodb_dedicated_server variable. MySQL 8.0 introduces a new variable called innodb_dedicated_server. When enabled, it auto tunes innodb_buffer_pool_size, innodb_log_file_size and innodb_flush_method at startup (if these variables are not explicitly defined in my.cnf). The new MySQL 8.0 variable automatically sizes the following variables based on the RAM size of … Continued

## Images et graphiques reperes

- featured / image: [New MySQL 8.0 innodb_dedicated_server Variable Optimizes InnoDB from the Get-Go](https://www.percona.com/wp-content/uploads/2026/03/eight-point-oh.png)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
