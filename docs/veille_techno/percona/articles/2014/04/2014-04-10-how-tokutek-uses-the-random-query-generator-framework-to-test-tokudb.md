---
title: How Tokutek uses the Random Query Generator framework to test TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-tokutek-uses-the-random-query-generator-framework-to-test-tokudb/
  post_id: 9861
source_author:
  name: Joel.Epstein
  slug: ''
  url: ''
  website: ''
published_at: '2014-04-10T11:38:57'
published_at_gmt: '2014-04-10T11:38:57'
modified_at: '2026-03-25T18:28:27'
modified_at_gmt: '2026-03-25T18:28:27'
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
- testing
- TokuDB
tag_slugs:
- mysql
- testing
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Tokutek uses the Random Query Generator framework to test TokuDB

Source: [Percona Blog](https://www.percona.com/blog/how-tokutek-uses-the-random-query-generator-framework-to-test-tokudb/)

Auteur source: [Joel.Epstein](https://www.percona.com/blog/how-tokutek-uses-the-random-query-generator-framework-to-test-tokudb/)

Publication: 2014-04-10T11:38:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

During a typical release cycle for TokuDB at Tokutek, we spend time qualifying and hardening the product using numerous tools. For example, we run stress and unit tests directly on the Fractal Tree indexes, MySQL Test Runner (MTR) tests on the storage engine as well as numerous performance benchmarks to prevent regressions. In addition, we … Continued
