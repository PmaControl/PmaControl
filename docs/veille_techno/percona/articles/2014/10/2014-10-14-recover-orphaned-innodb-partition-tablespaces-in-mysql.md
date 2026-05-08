---
title: Recover orphaned InnoDB partition tablespaces in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recover-orphaned-innodb-partition-tablespaces-in-mysql/
  post_id: 8632
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2014-10-14T15:25:45'
published_at_gmt: '2014-10-14T15:25:45'
modified_at: '2026-05-05T16:55:44'
modified_at_gmt: '2026-05-05T16:55:44'
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
category_slugs:
- mysql
tags:
- InnoDB partition
- Jervin Real
- MySQL 5.7
- MySQL Data Recovery
- Primary
- tablespaces
- Transportable Tablespaces
tag_slugs:
- innodb-partition
- jervin-real
- mysql-5-7
- mysql-data-recovery
- primary
- tablespaces
- transportable-tablespaces
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recover orphaned InnoDB partition tablespaces in MySQL

Source: [Percona Blog](https://www.percona.com/blog/recover-orphaned-innodb-partition-tablespaces-in-mysql/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2014-10-14T15:25:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A few months back, Michael wrote about reconnecting orphaned *.ibd files using MySQL 5.6. I will show you the same procedure, this time for partitioned tables. An InnoDB partition is also a self-contained tablespace in itself so you can use the same method described in the previous post. To begin with, I have an example … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
