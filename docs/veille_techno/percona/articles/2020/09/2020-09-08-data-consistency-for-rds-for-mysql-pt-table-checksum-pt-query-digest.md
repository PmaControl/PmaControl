---
title: Checking Data Consistency for RDS for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/data-consistency-for-rds-for-mysql-pt-table-checksum-pt-query-digest/
  post_id: 23049
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2020-09-08T16:07:51'
published_at_gmt: '2020-09-08T16:07:51'
modified_at: '2026-05-04T21:08:27'
modified_at_gmt: '2026-05-04T21:08:27'
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
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- cloud
- insight for DBAs
- MySQL
- mysql-and-variants
- RDS
tag_slugs:
- cloud
- insight-for-dbas
- mysql
- mysql-and-variants
- rds
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/data-consistency-RDS-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checking Data Consistency for RDS for MySQL

Source: [Percona Blog](https://www.percona.com/blog/data-consistency-for-rds-for-mysql-pt-table-checksum-pt-query-digest/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2020-09-08T16:07:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL for RDS and DBaaS, in general, are very controlled environments by the vendors, meaning that there are missing things like a SUPER grant for the root user (and any user in general). This has some implications on operations, one of them being the impossibility of running pt-table-checksum to verify data consistency between a primary … Continued

## Structure detectee

- H2: The Proof of Concept
- H3: Fine print

## Images et graphiques reperes

- featured / image: [Checking Data Consistency for RDS for MySQL](https://www.percona.com/wp-content/uploads/2026/03/data-consistency-RDS-MySQL.png)
- content / image: [data consistency RDS MySQL](https://www.percona.com/wp-content/uploads/2026/03/data-consistency-RDS-MySQL-300x168.png)

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
