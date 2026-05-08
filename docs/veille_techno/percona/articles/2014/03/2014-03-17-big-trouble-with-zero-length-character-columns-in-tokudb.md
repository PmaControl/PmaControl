---
title: Big trouble with zero-length character columns in TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/big-trouble-with-zero-length-character-columns-in-tokudb/
  post_id: 9856
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2014-03-17T14:06:14'
published_at_gmt: '2014-03-17T14:06:14'
modified_at: '2026-03-25T18:28:15'
modified_at_gmt: '2026-03-25T18:28:15'
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
- MySQL
- TokuDB
tag_slugs:
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Big trouble with zero-length character columns in TokuDB

Source: [Percona Blog](https://www.percona.com/blog/big-trouble-with-zero-length-character-columns-in-tokudb/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2014-03-17T14:06:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

What good is a zero-length character column in a MySQL table? A zero-length character column has type of ‘char(0)’. If it is nullable, then it can at least store one bit. If it is not nullable, then the value for this column in all rows is a null string. IMO, not very useful. However, the … Continued
