---
title: Why InnoDB index cardinality varies strangely
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-innodb-index-cardinality-varies-strangely/
  post_id: 1596
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2009-09-28T13:06:08'
published_at_gmt: '2009-09-28T13:06:08'
modified_at: '2026-04-28T20:34:24'
modified_at_gmt: '2026-04-28T20:34:24'
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
- InnoDB
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why InnoDB index cardinality varies strangely

Source: [Percona Blog](https://www.percona.com/blog/why-innodb-index-cardinality-varies-strangely/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2009-09-28T13:06:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is a very old draft, from early 2007 in fact. At that time I started to look into something interesting with the index cardinality statistics reported by InnoDB tables. The cardinality varies because it’s derived from estimates, and I know a decent amount about that. The interesting thing I wanted to look into was … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
