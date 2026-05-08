---
title: InnoDB’s multi-versioning handling can be Achilles’ heel
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodbs-multi-versioning-handling-can-be-achilles-heel/
  post_id: 8861
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-12-17T16:05:43'
published_at_gmt: '2014-12-17T16:05:43'
modified_at: '2026-05-04T20:58:37'
modified_at_gmt: '2026-05-04T20:58:37'
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
- Index Scan
- InnoDB buffer pool
- InnoDB tables
- multi-versioning
- Multiversion concurrency control
- MySQL
- Peter Zaitsev
- Primary
- UNDO space
tag_slugs:
- index-scan
- innodb-buffer-pool
- innodb-tables
- multi-versioning
- multiversion-concurrency-control
- mysql
- peter-zaitsev
- primary
- undo-space
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/img_548215986329c.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB’s multi-versioning handling can be Achilles’ heel

Source: [Percona Blog](https://www.percona.com/blog/innodbs-multi-versioning-handling-can-be-achilles-heel/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-12-17T16:05:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I believe InnoDB storage engine architecture is great for a lot of online workloads, however, there are no silver bullets in technology and all design choices have their trade offs. In this blog post I’m going to talk about one important InnoDB limitation that you should consider. InnoDB is a multiversion concurrency control (MVCC) storage … Continued

## Images et graphiques reperes

- featured / image: [InnoDB’s multi-versioning handling can be Achilles’ heel](https://www.percona.com/wp-content/uploads/2026/03/img_548215986329c.png)
- content / image: [img_548215de923ab.png](https://www.percona.com/wp-content/uploads/2026/03/img_548215de923ab.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
