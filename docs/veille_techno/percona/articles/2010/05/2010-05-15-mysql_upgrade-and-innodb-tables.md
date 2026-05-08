---
title: mysql_upgrade and Innodb Tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql_upgrade-and-innodb-tables/
  post_id: 2318
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-05-15T01:48:20'
published_at_gmt: '2010-05-15T01:48:20'
modified_at: '2026-03-23T21:41:26'
modified_at_gmt: '2026-03-23T21:41:26'
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
- Bugs
- InnoDB
tag_slugs:
- bugs
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# mysql_upgrade and Innodb Tables

Source: [Percona Blog](https://www.percona.com/blog/mysql_upgrade-and-innodb-tables/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-05-15T01:48:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Upgrading from MySQL 5.0 to MySQL 5.1 or Percona Server 5.1 you may run into issues with mysql_upgrade – it will identify some tables to be upgraded and will attempt to run REPAIR TABLE for them. This will fail with “The storage engine for the table doesn’t support repair” error message. This seems to confuse … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
