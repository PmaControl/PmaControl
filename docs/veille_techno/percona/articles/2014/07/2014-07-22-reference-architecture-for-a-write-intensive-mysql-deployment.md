---
title: Reference architecture for a write-intensive MySQL deployment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reference-architecture-for-a-write-intensive-mysql-deployment/
  post_id: 8386
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2014-07-22T14:31:37'
published_at_gmt: '2014-07-22T14:31:37'
modified_at: '2026-03-25T17:37:45'
modified_at_gmt: '2026-03-25T17:37:45'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Percona Cloud Tools
- Percona Server for MySQL
- TokuDB
- Ubuntu
- write-intensive MySQL deployments
tag_slugs:
- percona-cloud-tools
- percona-server
- tokudb
- ubuntu
- write-intensive-mysql-deployments
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Reference architecture for a write-intensive MySQL deployment

Source: [Percona Blog](https://www.percona.com/blog/reference-architecture-for-a-write-intensive-mysql-deployment/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2014-07-22T14:31:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We designed Percona Cloud Tools (both hardware and software setup) to handle a very high-intensive MySQL write workload. For example, we already observe inserts of 1bln+ datapoints per day. So I wanted to share what kind of hardware we use to achieve this result. Let me describe what we use, and later I will explain … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
