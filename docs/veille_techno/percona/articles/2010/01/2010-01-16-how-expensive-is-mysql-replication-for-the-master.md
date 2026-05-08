---
title: How expensive is MySQL Replication for the Master
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-expensive-is-mysql-replication-for-the-master/
  post_id: 2197
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-01-16T16:50:26'
published_at_gmt: '2010-01-16T16:50:26'
modified_at: '2026-03-23T21:37:53'
modified_at_gmt: '2026-03-23T21:37:53'
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
- Replication
tag_slugs:
- replication
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How expensive is MySQL Replication for the Master

Source: [Percona Blog](https://www.percona.com/blog/how-expensive-is-mysql-replication-for-the-master/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-01-16T16:50:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I generally thought about MySQL replication as being quite low overhead on Master, depending on number of Slaves. What kind of load extra Slave causes ? Well it just gets a copy of binary log streamed to it. All slaves typically get few last events in binary log so it is in cash. In most … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
