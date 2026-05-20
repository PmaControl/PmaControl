---
title: Copying InnoDB tables between servers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/copying-innodb-tables-between-servers/
  post_id: 1957
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-07-31T17:41:32'
published_at_gmt: '2009-07-31T17:41:32'
modified_at: '2026-04-28T21:01:06'
modified_at_gmt: '2026-04-28T21:01:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:percona-xtrabackup
- search:xtrabackup
- tag:xtrabackup:153
categories:
- Percona Software
category_slugs:
- percona-software
tags:
- export
- import
- InnoDB
- Tips
- xtrabackup
- XtraDB
tag_slugs:
- export
- import
- innodb
- tips
- xtrabackup
- xtradb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Copying InnoDB tables between servers

Source: [Percona Blog](https://www.percona.com/blog/copying-innodb-tables-between-servers/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-07-31T17:41:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The feature I announced some time ago https://www.percona.com/blog/2009/06/08/impossible-possible-moving-innodb-tables-between-servers/ is now available in our latest releases of XtraBackup 0.8.1 and XtraDB-6. Now I am going to show how to use it (the video will be also available on percona.tv). Let’s take tpcc schema and running standard MySQL ® 5.0.83, and assume we want to copy order_line … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
