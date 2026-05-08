---
title: Quickly finding unused indexes (and estimating their size)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/quickly-finding-unused-indexes-and-estimating-their-size/
  post_id: 6483
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2012-12-05T21:43:38'
published_at_gmt: '2012-12-05T21:43:38'
modified_at: '2026-05-05T17:40:50'
modified_at_gmt: '2026-05-05T17:40:50'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Quickly finding unused indexes (and estimating their size)

Source: [Percona Blog](https://www.percona.com/blog/quickly-finding-unused-indexes-and-estimating-their-size/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2012-12-05T21:43:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I had a customer recently who needed to reduce their database size on disk quickly without a lot of messy schema redesign and application recoding. They didn’t want to drop any actual data, and their index usage was fairly high, so we decided to look for unused indexes that could be removed. Collecting data It’s … Continued

## Structure detectee

- H2: Collecting data
- H2: Merging stats from several servers
- H2: Interpreting the data
- H2: Estimating the size of these indexes
- H2: Recovering filesystem space

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
