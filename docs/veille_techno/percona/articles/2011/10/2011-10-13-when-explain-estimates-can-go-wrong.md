---
title: Why the MySQL EXPLAIN Output can be Wrong
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-explain-estimates-can-go-wrong/
  post_id: 3152
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2011-10-13T10:56:35'
published_at_gmt: '2011-10-13T10:56:35'
modified_at: '2026-05-04T21:35:58'
modified_at_gmt: '2026-05-04T21:35:58'
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

# Why the MySQL EXPLAIN Output can be Wrong

Source: [Percona Blog](https://www.percona.com/blog/when-explain-estimates-can-go-wrong/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2011-10-13T10:56:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have been working with a few customer cases and one interesting case popped up. The customer was facing a peculiar problem where the rows column in the EXPLAIN output of the query was totally off. The actual number of rows was 18 times more than the number of rows reported by MySQL in the … Continued

## Structure detectee

- H2: Testing EXPLAIN in MySQL 5.1 vs MySQL 5.5
- H2: The MySQL EXPLAIN Bug in 5.1
