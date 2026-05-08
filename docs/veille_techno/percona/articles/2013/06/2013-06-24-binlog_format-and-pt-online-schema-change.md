---
title: pt-online-schema-change and binlog_format
source:
  name: Percona Blog
  url: https://www.percona.com/blog/binlog_format-and-pt-online-schema-change/
  post_id: 6918
source_author:
  name: Mike Benshoof
  slug: mbenshoof
  url: https://www.percona.com/blog/author/mbenshoof/
  website: ''
published_at: '2013-06-24T10:00:31'
published_at_gmt: '2013-06-24T10:00:31'
modified_at: '2026-05-05T21:51:36'
modified_at_gmt: '2026-05-05T21:51:36'
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
- binlog_format
- Mike Benshoof
- pt-online-schema-change
tag_slugs:
- binlog_format
- mike-benshoof
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/iops.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# pt-online-schema-change and binlog_format

Source: [Percona Blog](https://www.percona.com/blog/binlog_format-and-pt-online-schema-change/)

Auteur source: [Mike Benshoof](https://www.percona.com/blog/author/mbenshoof/)

Publication: 2013-06-24T10:00:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Statement-based or row-based, or mixed? We’ve all seen this discussed at length so I’m not trying to rehash tired arguments. At a high level, the difference is simple: Statement based replication (SBR) replicates the SQL statements to the slave to be replayed Row based replication (RBR) replicates the actual rows changed to the slave to … Continued

## Images et graphiques reperes

- featured / image: [pt-online-schema-change and binlog_format](https://www.percona.com/wp-content/uploads/2026/03/iops.png)
- content / image: [Row Based - IOPs (iostat)](https://www.percona.com/wp-content/uploads/2026/03/iops1.png)
  Caption: Row Based – IOPs (iostat -mx 1)
- content / image: [SBR - Buffer Pool Reads (from disk)](https://www.percona.com/wp-content/uploads/2026/03/bp-reads-sbr.png)
  Caption: SBR – Buffer Pool Reads (from disk)
- content / image: [RBR - Buffer Pool Reads (from disk)](https://www.percona.com/wp-content/uploads/2026/03/bp-reads-rbr.png)
  Caption: RBR – Buffer Pool Reads (from disk)

## Auteur source

Michael joined Percona in 2012 as a US based consultant and is currently a Technical Account Manager. Prior to joining Percona, Michael spent several years in a DevOps role maintaining a SaaS application specializing in social networking. His experiences include application development and scaling, systems administration, along with database administration and design. He enjoys designing extensible and flexible solutions to problems. When not working, he enjoys time outdoors, grilling, most sports, and spending time with the family.
