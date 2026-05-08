---
title: Why %util number from iostat is meaningless for MySQL capacity planning
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-util-number-from-iostat-is-meaningless-for-mysql-capacity-planning/
  post_id: 8313
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-06-25T10:00:04'
published_at_gmt: '2014-06-25T10:00:04'
modified_at: '2026-03-25T17:35:14'
modified_at_gmt: '2026-03-25T17:35:14'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- '%util'
- iostat
- MySQL capacity planning
tag_slugs:
- util
- iostat
- mysql-capacity-planning
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why %util number from iostat is meaningless for MySQL capacity planning

Source: [Percona Blog](https://www.percona.com/blog/why-util-number-from-iostat-is-meaningless-for-mysql-capacity-planning/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-06-25T10:00:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Earlier this month I wrote about vmstat iowait cpu numbers and some of the comments I got were advertising the use of util% as reported by the iostat tool instead. I find this number even more useless for MySQL performance tuning and capacity planning. Now let me start by saying this is a really tricky and deceptive number. Many … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
