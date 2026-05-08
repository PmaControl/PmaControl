---
title: The Depth of a B-tree
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the_depth_of_a_b_tree/
  post_id: 3279
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2009-04-28T23:06:00'
published_at_gmt: '2009-04-28T23:06:00'
modified_at: '2026-05-04T21:37:48'
modified_at_gmt: '2026-05-04T21:37:48'
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

# The Depth of a B-tree

Source: [Percona Blog](https://www.percona.com/blog/the_depth_of_a_b_tree/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2009-04-28T23:06:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Schlomi Noach recently wrote a useful primer on the depth of B-trees and how that plays out for point queries — in both clustered indexes, like InnoDB, and in unclustered indexes, like MyISAM. Here, I’d like to talk about the effect of B-tree depth on insertions and range queries. And, of course, I’ll talk about … Continued
