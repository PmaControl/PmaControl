---
title: Attempting to Quantify Fragmentation Effects
source:
  name: Percona Blog
  url: https://www.percona.com/blog/attempting_to_quantify_fragmentation_effects/
  post_id: 3287
source_author:
  name: Tokutek
  slug: tokutek
  url: https://www.percona.com/blog/author/tokutek/
  website: ''
published_at: '2009-09-19T04:41:00'
published_at_gmt: '2009-09-19T04:41:00'
modified_at: '2026-05-04T21:38:42'
modified_at_gmt: '2026-05-04T21:38:42'
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

# Attempting to Quantify Fragmentation Effects

Source: [Percona Blog](https://www.percona.com/blog/attempting_to_quantify_fragmentation_effects/)

Auteur source: [Tokutek](https://www.percona.com/blog/author/tokutek/)

Publication: 2009-09-19T04:41:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We often hear from customers and MySQL experts that fragmentation causes problems such as wasting disk space, increasing backup times, and degrading performance. Typical remedies include periodic “optimize table” or dump and re-load (for example, see Project Golden Gate). Unfortunately, these techniques impact database availability and/or require additional administrative cost and complexity. Tokutek’s Fractal Tree … Continued

## Structure detectee

- H3: Initial Load – 50M Rows
- H3: Deleting 10M Rows
- H3: Summary
- H3: Going Further
- H3: Additional Details
