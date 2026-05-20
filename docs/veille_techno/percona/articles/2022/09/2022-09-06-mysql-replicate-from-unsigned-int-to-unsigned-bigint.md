---
title: MySQL Replicate From Unsigned-int to Unsigned-bigint
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-replicate-from-unsigned-int-to-unsigned-bigint/
  post_id: 25944
source_author:
  name: Edwin Wang
  slug: edwin-wang
  url: https://www.percona.com/blog/author/edwin-wang/
  website: ''
published_at: '2022-09-06T12:21:41'
published_at_gmt: '2022-09-06T12:21:41'
modified_at: '2026-03-26T20:31:15'
modified_at_gmt: '2026-03-26T20:31:15'
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
- ALL_LOSSY
- ALL_NON_LOSSY
- ALL_SIGNED
- ALL_UNSIGNED
- mysql-and-variants
- replication different data type
tag_slugs:
- all_lossy
- all_non_lossy
- all_signed
- all_unsigned
- mysql-and-variants
- replication-different-data-type
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Replicate.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Replicate From Unsigned-int to Unsigned-bigint

Source: [Percona Blog](https://www.percona.com/blog/mysql-replicate-from-unsigned-int-to-unsigned-bigint/)

Auteur source: [Edwin Wang](https://www.percona.com/blog/author/edwin-wang/)

Publication: 2022-09-06T12:21:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We often see an int column of a table that needs to be changed to unsigned-int and then unsigned-bigint due to the value being out of range. Sometimes, there may even be blockers that prevent us from directly altering the table or applying pt-online-schema-change on the primary, which requires the rotation solution: apply the change … Continued

## Images et graphiques reperes

- featured / image: [MySQL Replicate From Unsigned-int to Unsigned-bigint](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Replicate.png)
- content / image: [MySQL Replicate](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Replicate-300x157.png)

## Auteur source

A father with 1 wife, 2 kids, and 2 dogs. A DBA with 20 years of experience in RDBMS i.e. MySQL, Oracle Etc. Currently working at Percona as Senior Mysql Database Administrator working on different environments and scenarios, including database installation/configuration/maintenance, trouble-shooting, design, performance tuning, DB High Availability architecture, and other infrastructure-related issues, AWS cloud, ansible, GCP, etc.
