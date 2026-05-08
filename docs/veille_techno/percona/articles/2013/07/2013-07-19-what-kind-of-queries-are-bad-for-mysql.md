---
title: What kind of queries are bad for MySQL?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-kind-of-queries-are-bad-for-mysql/
  post_id: 7200
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2013-07-19T10:00:07'
published_at_gmt: '2013-07-19T10:00:07'
modified_at: '2026-03-25T17:04:05'
modified_at_gmt: '2026-03-25T17:04:05'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- MySQL
- queries
- Vadim Tkachenko
tag_slugs:
- mysql
- queries
- vadim-tkachenko
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What kind of queries are bad for MySQL?

Source: [Percona Blog](https://www.percona.com/blog/what-kind-of-queries-are-bad-for-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2013-07-19T10:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In writing a recommendation for our Web development team on how to use MySQL, I came up with the following list, which I want to share: What kind of queries are bad for MySQL? Any query is bad. Send a query only if you must. (Hint: use caching like memcache or redis) Queries that examine … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
