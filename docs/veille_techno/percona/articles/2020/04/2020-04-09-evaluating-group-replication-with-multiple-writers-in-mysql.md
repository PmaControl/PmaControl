---
title: Evaluating Group Replication with Multiple Writers in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-group-replication-with-multiple-writers-in-mysql/
  post_id: 22174
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-04-09T17:01:34'
published_at_gmt: '2020-04-09T17:01:34'
modified_at: '2026-04-27T21:36:33'
modified_at_gmt: '2026-04-27T21:36:33'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- Benchmarks
- MySQL
- Replication
tag_slugs:
- benchmarks
- mysql
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Group-Replication-Multiple-Writers-MySQL.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating Group Replication with Multiple Writers in MySQL

Source: [Percona Blog](https://www.percona.com/blog/evaluating-group-replication-with-multiple-writers-in-mysql/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-04-09T17:01:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I want to evaluate Group Replication Scaling capabilities to handle several writers, that is, when the read-write connection is established to multiple nodes, and in this case, two nodes. This setup is identical to my previous post, Evaluating Group Replication Scaling Capabilities in MySQL. For this test, I deploy multi-node bare metal … Continued

## Structure detectee

- H2: Results
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Evaluating Group Replication with Multiple Writers in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Group-Replication-Multiple-Writers-MySQL.png)
- content / image: [image2-4.png](https://www.percona.com/wp-content/uploads/2026/03/image2-4.png)
- content / image: [Group Replication with Multiple Writers in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-09-at-9.58.07-AM-1024x468.png)
- content / image: [Group Replication with Multiple Writers in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-09-at-9.58.49-AM-1024x877.png)
- content / image: [Screen-Shot-2020-04-09-at-9.59.32-AM-1024x440.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-09-at-9.59.32-AM-1024x440.png)
- content / image: [Screen-Shot-2020-04-09-at-10.00.08-AM-1024x434.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-09-at-10.00.08-AM-1024x434.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
