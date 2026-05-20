---
title: Statistics of InnoDB tables and indexes available in xtrabackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/statistics-of-innodb-tables-and-indexes-available-in-xtrabackup/
  post_id: 1993
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-09-14T16:52:53'
published_at_gmt: '2009-09-14T16:52:53'
modified_at: '2026-04-28T21:01:52'
modified_at_gmt: '2026-04-28T21:01:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:xtrabackup
categories:
- Percona Software
category_slugs:
- percona-software
tags:
- Backups
- InnoDB
- Tools
tag_slugs:
- backups
- innodb
- tools
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Statistics of InnoDB tables and indexes available in xtrabackup

Source: [Percona Blog](https://www.percona.com/blog/statistics-of-innodb-tables-and-indexes-available-in-xtrabackup/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-09-14T16:52:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you ever wondered how big is that or another index in InnoDB … you had to calculate it yourself by multiplying size of row (which I should add is harder in the case of a VARCHAR – since you need to estimate average length) on count of records. And it still would be quite … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
