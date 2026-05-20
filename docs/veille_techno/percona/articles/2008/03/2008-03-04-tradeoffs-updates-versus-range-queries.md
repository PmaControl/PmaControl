---
title: 'Tradeoffs: Updates versus Range Queries'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tradeoffs-updates-versus-range-queries/
  post_id: 9445
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2008-03-04T20:14:57'
published_at_gmt: '2008-03-04T20:14:57'
modified_at: '2026-03-25T18:07:15'
modified_at_gmt: '2026-03-25T18:07:15'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tradeoffs: Updates versus Range Queries

Source: [Percona Blog](https://www.percona.com/blog/tradeoffs-updates-versus-range-queries/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2008-03-04T20:14:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Sorry for the delay, now on to range queries and lenient updates. Let’s call them queries and updates, for short. So far, I’ve shown that B-trees (and any of a number of other data structures) are very far from the “tight bound.” I’ll say a bound is a tight if it’s a lower bound and … Continued
