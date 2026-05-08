---
title: 'utf8 data on latin1 tables: converting to utf8 without downtime or double encoding'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/utf8-data-on-latin1-tables-converting-to-utf8-without-downtime-or-double-encoding/
  post_id: 7457
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2013-10-16T05:00:24'
published_at_gmt: '2013-10-16T05:00:24'
modified_at: '2026-04-28T21:56:53'
modified_at_gmt: '2026-04-28T21:56:53'
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
tags:
- latin1 tables
- utf8
- utf8 horror stories
tag_slugs:
- latin1-tables
- utf8
- utf8-horror-stories
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# utf8 data on latin1 tables: converting to utf8 without downtime or double encoding

Source: [Percona Blog](https://www.percona.com/blog/utf8-data-on-latin1-tables-converting-to-utf8-without-downtime-or-double-encoding/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2013-10-16T05:00:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Here’s a problem some or most of us have encountered. You have a latin1 table defined like below, and your application is storing utf8 data to the column on a latin1 connection. Obviously, double encoding occurs. Now your development team decided to use utf8 everywhere, but during the process you can only have as little … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
