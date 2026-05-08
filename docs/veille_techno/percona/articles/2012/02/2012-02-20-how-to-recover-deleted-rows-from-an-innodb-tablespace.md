---
title: How to recover deleted rows from an InnoDB Tablespace
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-recover-deleted-rows-from-an-innodb-tablespace/
  post_id: 3360
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-02-20T18:42:09'
published_at_gmt: '2012-02-20T18:42:09'
modified_at: '2026-03-23T22:15:13'
modified_at_gmt: '2026-03-23T22:15:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to recover deleted rows from an InnoDB Tablespace

Source: [Percona Blog](https://www.percona.com/blog/how-to-recover-deleted-rows-from-an-innodb-tablespace/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-02-20T18:42:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous post I explained how it could be possible to recover, on some specific cases, a single table from a full backup in order to save time and make the recovery process more straightforward. Now the scenario is worse because we don’t have a backup or the backup restore process doesn’t work. How … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
