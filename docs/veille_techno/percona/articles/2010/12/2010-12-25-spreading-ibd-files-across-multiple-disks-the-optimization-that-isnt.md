---
title: Spreading .ibd files across multiple disks; the optimization that isn’t
source:
  name: Percona Blog
  url: https://www.percona.com/blog/spreading-ibd-files-across-multiple-disks-the-optimization-that-isnt/
  post_id: 2566
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2010-12-25T16:50:34'
published_at_gmt: '2010-12-25T16:50:34'
modified_at: '2026-03-23T21:48:52'
modified_at_gmt: '2026-03-23T21:48:52'
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
- Backups
- InnoDB
- innodb_file_per_table
- lvm
- optimizations
- silly advice given
- Tips
tag_slugs:
- backups
- innodb
- innodb_file_per_table
- lvm
- optimizations
- silly-advice-given
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Spreading .ibd files across multiple disks; the optimization that isn’t

Source: [Percona Blog](https://www.percona.com/blog/spreading-ibd-files-across-multiple-disks-the-optimization-that-isnt/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2010-12-25T16:50:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Inspired by Baron’s earlier post, here is one I hear quite frequently – “If you enable innodb_file_per_table, each table is it’s own .ibd file.Â You can then relocate the heavy hit tables to a different location and create symlinks to the original location.” There are a few things wrong with this advice:

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
