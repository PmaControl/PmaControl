---
title: Making Sense of MySQL Group Replication Consistency Levels
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-sense-of-group-replication-consistency-levels/
  post_id: 21526
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-01-23T17:13:02'
published_at_gmt: '2020-01-23T17:13:02'
modified_at: '2026-04-27T21:30:05'
modified_at_gmt: '2026-04-27T21:30:05'
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
- MySQL
- Percona Software
tag_slugs:
- mysql
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Group-Replication-Consistency.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making Sense of MySQL Group Replication Consistency Levels

Source: [Percona Blog](https://www.percona.com/blog/making-sense-of-group-replication-consistency-levels/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-01-23T17:13:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

From the initial release, one of the biggest complaints I had about Group Replication is that it allowed “stale” reads and there was no way to prevent them or to even know that you read “stale” data. That was a huge limitation. Thankfully, Oracle released features to control the consistency levels, and it was exactly … Continued

## Structure detectee

- H2: Setup:
- H2: group_replication_consistency=’BEFORE’;
- H2: group_replication_consistency=’AFTER’;

## Images et graphiques reperes

- featured / image: [Making Sense of MySQL Group Replication Consistency Levels](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Group-Replication-Consistency.png)
- content / image: [MySQL Group Replication Consistency](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Group-Replication-Consistency-300x168.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
