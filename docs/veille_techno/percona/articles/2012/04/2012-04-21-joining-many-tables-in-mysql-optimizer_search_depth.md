---
title: Joining many tables in MySQL – optimizer_search_depth
source:
  name: Percona Blog
  url: https://www.percona.com/blog/joining-many-tables-in-mysql-optimizer_search_depth/
  post_id: 3483
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-04-21T03:13:08'
published_at_gmt: '2012-04-21T03:13:08'
modified_at: '2026-03-23T22:19:07'
modified_at_gmt: '2026-03-23T22:19:07'
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

# Joining many tables in MySQL – optimizer_search_depth

Source: [Percona Blog](https://www.percona.com/blog/joining-many-tables-in-mysql-optimizer_search_depth/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-04-21T03:13:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Working on customer case today I ran into interesting problem – query joining about 20 tables (thank you ORM by joining all tables connected with foreign keys just in case) which would take 5 seconds even though in the read less than 1000 rows and doing it completely in memory. The plan optimizer picked was … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
