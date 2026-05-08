---
title: 'Range Queries: Is the Bottleneck Seeks or Bandwidth?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/range-queries-is-the-bottleneck-seeks-or-bandwidth/
  post_id: 9442
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2008-01-22T02:25:27'
published_at_gmt: '2008-01-22T02:25:27'
modified_at: '2026-03-25T18:06:56'
modified_at_gmt: '2026-03-25T18:06:56'
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

# Range Queries: Is the Bottleneck Seeks or Bandwidth?

Source: [Percona Blog](https://www.percona.com/blog/range-queries-is-the-bottleneck-seeks-or-bandwidth/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2008-01-22T02:25:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last time I talked about point queries. The conclusion was that big databases and point queries don’t mix. It’s ok to do them from time to time, but it’s not how you’re going to use your database, unless you have a lot of time. Today, I’d like to talk about range queries, which seem much … Continued
