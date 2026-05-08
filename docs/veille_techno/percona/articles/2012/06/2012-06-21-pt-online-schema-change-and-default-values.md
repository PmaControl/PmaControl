---
title: pt-online-schema-change and default values
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pt-online-schema-change-and-default-values/
  post_id: 3649
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-06-21T13:56:36'
published_at_gmt: '2012-06-21T13:56:36'
modified_at: '2026-04-28T21:36:42'
modified_at_gmt: '2026-04-28T21:36:42'
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
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# pt-online-schema-change and default values

Source: [Percona Blog](https://www.percona.com/blog/pt-online-schema-change-and-default-values/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-06-21T13:56:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When I’m doing conventional ALTER TABLE in MySQL I can ignore default value and it will be assigned based on the column type. For example this alter table sbtest add column v varchar(100) not null would work even though we do not specify default value. MySQL will assign empty string as default default value for … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
