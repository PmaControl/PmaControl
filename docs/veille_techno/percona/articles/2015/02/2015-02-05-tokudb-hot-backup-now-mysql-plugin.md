---
title: TokuDB Hot Backup Now a MySQL Plugin
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-hot-backup-now-mysql-plugin/
  post_id: 9913
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2015-02-05T15:58:00'
published_at_gmt: '2015-02-05T15:58:00'
modified_at: '2026-05-05T22:24:12'
modified_at_gmt: '2026-05-05T22:24:12'
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
- MySQL
- NewSQL
- TokuDB
- tokudb hot backup mysql mariadb percona
tag_slugs:
- mysql
- newsql
- tokudb
- tokudb-hot-backup-mysql-mariadb-percona
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB Hot Backup Now a MySQL Plugin

Source: [Percona Blog](https://www.percona.com/blog/tokudb-hot-backup-now-mysql-plugin/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2015-02-05T15:58:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the recently released TokuDB 7.5.5 the implementation of TokuDB hot-backup moved from a patch to the MySQL Server, to MySQL Plugin. Why did we make this change? TokuDB hot backup makes a transactionally consistent copy of the TokuDB files while applications continue to read and write these files. Christian Rober wrote a nice series … Continued
