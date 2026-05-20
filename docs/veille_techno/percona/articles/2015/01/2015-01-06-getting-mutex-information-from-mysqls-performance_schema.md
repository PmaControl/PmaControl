---
title: Getting mutex information from MySQL’s performance_schema
source:
  name: Percona Blog
  url: https://www.percona.com/blog/getting-mutex-information-from-mysqls-performance_schema/
  post_id: 8949
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2015-01-06T08:00:07'
published_at_gmt: '2015-01-06T08:00:07'
modified_at: '2026-05-04T20:59:28'
modified_at_gmt: '2026-05-04T20:59:28'
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
- Miguel Angel Nieto
- Morgan Tocker
- mutex
- MySQL
- Oracle
- PERFORMANCE_SCHEMA
- Primary
- rw-lock
tag_slugs:
- miguel-angel-nieto
- morgan-tocker
- mutex
- mysql
- oracle
- performance_schema
- primary
- rw-lock
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Getting mutex information from MySQL’s performance_schema

Source: [Percona Blog](https://www.percona.com/blog/getting-mutex-information-from-mysqls-performance_schema/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2015-01-06T08:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We have been using SHOW ENGINE INNODB MUTEX command for years. It shows us mutex and rw-lock information that could be useful during service troubleshooting in case of performance problems. As Morgan Tocker announced in his blog post the command will be removed from MySQL 5.7 and we have to use performance_schema to get that … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
