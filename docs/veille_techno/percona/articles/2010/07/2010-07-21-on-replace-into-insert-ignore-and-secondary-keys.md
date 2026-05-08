---
title: On "Replace Into", "Insert Ignore", and Secondary Keys
source:
  name: Percona Blog
  url: https://www.percona.com/blog/on-replace-into-insert-ignore-and-secondary-keys/
  post_id: 9522
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-07-21T20:51:55'
published_at_gmt: '2010-07-21T20:51:55'
modified_at: '2026-04-28T22:38:51'
modified_at_gmt: '2026-04-28T22:38:51'
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
- B-Tree
- disk seek
- Fractal Trees
- insert ignore
- MySQL
- replace into
- TokuDB
tag_slugs:
- b-tree
- disk-seek
- fractal-trees
- insert-ignore
- mysql
- replace-into
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# On "Replace Into", "Insert Ignore", and Secondary Keys

Source: [Percona Blog](https://www.percona.com/blog/on-replace-into-insert-ignore-and-secondary-keys/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-07-21T20:51:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In posts on June 30 and July 6, I explained how implementing the commands “replace into” and “insert ignore” with TokuDB’s fractal trees data structures can be two orders of magnitude faster than implementing them with B-trees. Towards the end of each post, I hinted at that there are some caveats that complicate the story … Continued
