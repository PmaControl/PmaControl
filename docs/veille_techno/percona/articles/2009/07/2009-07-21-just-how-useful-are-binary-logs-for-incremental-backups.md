---
title: Just how useful are binary logs for incremental backups?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/just-how-useful-are-binary-logs-for-incremental-backups/
  post_id: 1954
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2009-07-21T20:26:23'
published_at_gmt: '2009-07-21T20:26:23'
modified_at: '2026-03-23T21:29:48'
modified_at_gmt: '2026-03-23T21:29:48'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
category_slugs:
- insight-for-dbas
tags:
- backup
- Backups
- binary logs
- MySQL
tag_slugs:
- backup
- backups
- binary-logs
- mysql
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Just how useful are binary logs for incremental backups?

Source: [Percona Blog](https://www.percona.com/blog/just-how-useful-are-binary-logs-for-incremental-backups/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2009-07-21T20:26:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We’ve written about replication slaves lagging behind masters before, but one of the other side effects of the binary log being serialized, is that it also limits the effectiveness of using it for incremental backup.Â Let me make up some numbers for the purposes of this example: We have 2 Servers in a Master-Slave topology. … Continued

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
