---
title: 'Prevent MySQL ERROR 1040 (00000): Too many connections'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/prevent-mysql-downtime-set-max_user_connections/
  post_id: 8404
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-07-29T12:05:33'
published_at_gmt: '2014-07-29T12:05:33'
modified_at: '2026-05-04T22:25:20'
modified_at_gmt: '2026-05-04T22:25:20'
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
- max_user_connections
- max_user_connections=20
- MySQL downtime
- OOM killer
tag_slugs:
- max_user_connections
- max_user_connections20
- mysql-downtime
- oom-killer
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Prevent MySQL ERROR 1040 (00000): Too many connections

Source: [Percona Blog](https://www.percona.com/blog/prevent-mysql-downtime-set-max_user_connections/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-07-29T12:05:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the common causes of downtime with MySQL is running out of connections. Have you ever seen this error? “ERROR 1040 (00000): Too many connections.” If you’re working with MySQL long enough you surely have. This is quite a nasty error as it might cause complete downtime… transient errors with successful transactions mixed with … Continued

## Structure detectee

- H2: Prevent MySQL ERROR 1040

## Images et graphiques reperes

- featured / image: [Prevent MySQL ERROR 1040 (00000): Too many connections](https://www.percona.com/wp-content/uploads/2026/03/mysql.jpg)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
