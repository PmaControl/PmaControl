---
title: How InnoDB handles REDO logging
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-innodb-handles-redo-logging/
  post_id: 2677
source_author:
  name: Ewen Fortune
  slug: ewen
  url: https://www.percona.com/blog/author/ewen/
  website: http://www.linkedin.com/efortune
published_at: '2011-02-03T16:39:58'
published_at_gmt: '2011-02-03T16:39:58'
modified_at: '2026-03-23T21:51:26'
modified_at_gmt: '2026-03-23T21:51:26'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB_REDO.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How InnoDB handles REDO logging

Source: [Percona Blog](https://www.percona.com/blog/how-innodb-handles-redo-logging/)

Auteur source: [Ewen Fortune](https://www.percona.com/blog/author/ewen/)

Publication: 2011-02-03T16:39:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Xaprb (Baron) recently blogged about how InnoDB performs a checkpoint , I thought it might be useful to explain another important mechanism that affects both response time and throughput – The transaction log.

## Images et graphiques reperes

- featured / image: [How InnoDB handles REDO logging](https://www.percona.com/wp-content/uploads/2026/03/InnoDB_REDO.png)
- content / image: [InnoDB_REDO-300x179.png](https://www.percona.com/wp-content/uploads/2026/03/InnoDB_REDO-300x179.png)

## Auteur source

Ewen has extensive background in networking and is MySQL Cluster certified. He is a former Percona employee.
