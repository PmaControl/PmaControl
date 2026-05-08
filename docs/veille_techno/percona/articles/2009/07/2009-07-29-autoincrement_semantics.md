---
title: Autoincrement Semantics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/autoincrement_semantics/
  post_id: 9485
source_author:
  name: kuszmaul
  slug: kuszmaul
  url: https://www.percona.com/blog/author/kuszmaul/
  website: ''
published_at: '2009-07-29T23:28:00'
published_at_gmt: '2009-07-29T23:28:00'
modified_at: '2026-03-25T18:13:32'
modified_at_gmt: '2026-03-25T18:13:32'
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

# Autoincrement Semantics

Source: [Percona Blog](https://www.percona.com/blog/autoincrement_semantics/)

Auteur source: [kuszmaul](https://www.percona.com/blog/author/kuszmaul/)

Publication: 2009-07-29T23:28:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post I’m going to talk about how TokuDB’s implementation of auto increment works, and contrast it to the behavior of MyISAM and InnoDB. We feel that the TokuDB behavior is easier to understand, more standard-compliant and offers higher performance (especially when implemented with Fractal Tree indexes). In TokuDB, each table can have an … Continued
