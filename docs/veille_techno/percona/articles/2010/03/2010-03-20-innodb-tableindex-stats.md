---
title: InnoDB TABLE/INDEX stats
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-tableindex-stats/
  post_id: 2255
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-03-20T15:05:41'
published_at_gmt: '2010-03-20T15:05:41'
modified_at: '2026-03-23T21:39:20'
modified_at_gmt: '2026-03-23T21:39:20'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- InnoDB
- XtraDB
tag_slugs:
- innodb
- xtradb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB TABLE/INDEX stats

Source: [Percona Blog](https://www.percona.com/blog/innodb-tableindex-stats/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-03-20T15:05:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Released and new coming features I did not mentioned two additional INFORMATION_SCHEMA tables available in XtraDB:It is INNODB_TABLE_STATS INNODB_INDEX_STATS These table show statistics about InnoDB tables ( taken from InnoDB data dictionary). INNODB_TABLE_STATS is | table_name | table name in InnoDB internal style (‘database/table’) | | rows | estimated number of all rows | … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
