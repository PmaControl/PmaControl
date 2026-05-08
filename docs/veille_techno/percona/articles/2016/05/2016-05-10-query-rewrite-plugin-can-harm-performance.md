---
title: Query Rewrite plugin can harm performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/query-rewrite-plugin-can-harm-performance/
  post_id: 15074
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-05-10T17:53:20'
published_at_gmt: '2016-05-10T17:53:20'
modified_at: '2026-05-05T19:38:42'
modified_at_gmt: '2026-05-05T19:38:42'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Query-Rewrite-plugin-can-harm-performance.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Query Rewrite plugin can harm performance

Source: [Percona Blog](https://www.percona.com/blog/query-rewrite-plugin-can-harm-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-05-10T17:53:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss how the Query Rewrite plugin can harm performance. MySQL 5.7 comes with Query Rewrite plugin, which allows you to modify queries coming to the server. (You can view the details here: https://dev.mysql.com/doc/refman/5.7/en/rewriter-query-rewrite-plugin.html.) It is based on the audit plugin API, and unfortunately it suffers from serious scalability issues (which … Continued

## Images et graphiques reperes

- featured / image: [Query Rewrite plugin can harm performance](https://www.percona.com/wp-content/uploads/2026/03/Query-Rewrite-plugin-can-harm-performance.png)
- content / image: [Query Rewrite plugin can harm performance](https://www.percona.com/wp-content/uploads/2026/03/thr.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
