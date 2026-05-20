---
title: Sysbench with support of multi-tables workload
source:
  name: Percona Blog
  url: https://www.percona.com/blog/sysbench-with-support-of-multi-tables-workload/
  post_id: 2993
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-04-29T07:00:01'
published_at_gmt: '2011-04-29T07:00:01'
modified_at: '2026-04-28T21:27:45'
modified_at_gmt: '2026-04-28T21:27:45'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Sysbench with support of multi-tables workload

Source: [Percona Blog](https://www.percona.com/blog/sysbench-with-support-of-multi-tables-workload/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-04-29T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We just pushed to sysbench support for workload against multiple tables ( traditionally it used only single table). It is available from launchpad source tree lp:sysbench . This is set of LUA scripts for sysbench 0.5 ( it supports scripting), and it works following way: – you should use –test=tests/db/oltp.lua to run … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
