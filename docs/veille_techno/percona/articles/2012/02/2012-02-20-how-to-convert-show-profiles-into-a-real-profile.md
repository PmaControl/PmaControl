---
title: How to convert MySQL’s SHOW PROFILES into a real profile
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-convert-show-profiles-into-a-real-profile/
  post_id: 3374
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2012-02-20T16:21:28'
published_at_gmt: '2012-02-20T16:21:28'
modified_at: '2026-04-28T21:32:43'
modified_at_gmt: '2026-04-28T21:32:43'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to convert MySQL’s SHOW PROFILES into a real profile

Source: [Percona Blog](https://www.percona.com/blog/how-to-convert-show-profiles-into-a-real-profile/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2012-02-20T16:21:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

SHOW PROFILES shows how much time MySQL spends in various phases of query execution, but it isn’t a full-featured profile. By that, I mean that it doesn’t show similar phases aggregated together, doesn’t sort them by worst-first, and doesn’t show the relative amount of time consumed. I’ll profile the “nicer_but_slower_film_list” included with the Sakila sample … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
