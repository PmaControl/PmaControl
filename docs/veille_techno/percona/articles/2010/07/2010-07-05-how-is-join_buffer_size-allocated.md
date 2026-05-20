---
title: How is join_buffer_size allocated?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-is-join_buffer_size-allocated/
  post_id: 2400
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-07-05T14:56:31'
published_at_gmt: '2010-07-05T14:56:31'
modified_at: '2026-05-04T21:27:33'
modified_at_gmt: '2026-05-04T21:27:33'
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

# How is join_buffer_size allocated?

Source: [Percona Blog](https://www.percona.com/blog/how-is-join_buffer_size-allocated/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-07-05T14:56:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When examining MySQL configuration, we quite often want to know how various buffer sizes are used. This matters because some buffers (sort_buffer_size for example) are allocated to their full size immediately as soon as they are needed, but others are effectively a “max size” and the corresponding buffers are allocated only as big as needed … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
