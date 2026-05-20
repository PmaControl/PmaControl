---
title: 'Fast Updates : Coming Soon in TokuMX v2.0'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fast-updates-coming-soon-in-tokumx-v2-0/
  post_id: 3321
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2014-09-29T21:37:20'
published_at_gmt: '2014-09-29T21:37:20'
modified_at: '2026-03-23T22:13:58'
modified_at_gmt: '2026-03-23T22:13:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- Announcement
- Benchmarking
- Fractal Tree™ indexes
- MongoDB
- tokumx
tag_slugs:
- announcement
- benchmarking
- fractal-tree-indexes
- mongodb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fast Updates : Coming Soon in TokuMX v2.0

Source: [Percona Blog](https://www.percona.com/blog/fast-updates-coming-soon-in-tokumx-v2-0/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2014-09-29T21:37:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Coming in TokuMX v2.0 is a feature we’re calling “Fast Updates”. Fast updates permit certain update operations to bypass the read-modify-write behavior that most databases require (including MongoDB and the current release of TokuMX). In this blog I’ll cover how Fast Updates work by describing a simple schema and workload, plus I’ll measure the performance … Continued

## Structure detectee

- H2: Fast Updates – Overview
- H2: Non-indexed Point Updates by Primary Key
- H2: Non-indexed Updates by Secondary Key
- H2: The Fine Print
- H2: Example Schema and workload
- H2: Decrease a single players hit_points
- H2: Increase a player’s gold
- H2: Increase a team’s gold
- H2: Increase a player’s experience
- H2: Fast Updates: The Benchmarks
- H2: Fast Updates By Primary Key
- H2: Fast Updates By Secondary Keys
- H2: To learn more about TokuMX:
