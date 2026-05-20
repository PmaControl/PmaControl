---
title: A TokuDB Stall Caused by a Big Transaction and How It was Fixed
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-tokudb-stall-caused-by-abig-transactions-and-how-it-was-fixed/
  post_id: 3013
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2013-09-20T16:23:07'
published_at_gmt: '2013-09-20T16:23:07'
modified_at: '2026-03-23T22:03:30'
modified_at_gmt: '2026-03-23T22:03:30'
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
- Fractal Tree™ indexes
- MariaDB
- MySQL
- TokuDB
- tokumx
tag_slugs:
- fractal-tree-indexes
- mariadb
- mysql
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A TokuDB Stall Caused by a Big Transaction and How It was Fixed

Source: [Percona Blog](https://www.percona.com/blog/a-tokudb-stall-caused-by-abig-transactions-and-how-it-was-fixed/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2013-09-20T16:23:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of our customers sometimes observed lots of simple insertions taking far longer than expected to complete. Usually these insertions completed in milliseconds, but the insertions sometimes were taking hundreds of seconds. These stalls indicated the existence of a serialization bug in the Fractal Tree index software, so the hunt was on. We found that … Continued
