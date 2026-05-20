---
title: Is VoltDB really as scalable as they claim?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-voltdb-really-as-scalable-as-they-claim/
  post_id: 2715
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-02-28T23:24:07'
published_at_gmt: '2011-02-28T23:24:07'
modified_at: '2026-03-23T21:52:54'
modified_at_gmt: '2026-03-23T21:52:54'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
category_slugs:
- benchmarks
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/k-factor-0-usl-model-vs-actual.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is VoltDB really as scalable as they claim?

Source: [Percona Blog](https://www.percona.com/blog/is-voltdb-really-as-scalable-as-they-claim/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-02-28T23:24:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Before I begin, a disclaimer. VoltDB is not a customer, and did not pay Percona or me to investigate VoltDB’s scalability or publish this blog post. More disclaimers at the end. Short version: VoltDB is very scalable; it should scale to 120 partitions, 39 servers, and 1.6 million complex transactions per second at over 300 … Continued

## Images et graphiques reperes

- featured / image: [Is VoltDB really as scalable as they claim?](https://www.percona.com/wp-content/uploads/2026/03/k-factor-0-usl-model-vs-actual.png)
- content / image: [Results for k-factor 0](https://www.percona.com/wp-content/uploads/2026/03/k-factor-0-usl-model-vs-actual-150x150.png)
  Caption: Results for k-factor 0
- content / image: [Results for k-factor 1](https://www.percona.com/wp-content/uploads/2026/03/k-factor-1-usl-model-vs-actual-150x150.png)
  Caption: Results for k-factor 1
- content / image: [Results for k-factor 2](https://www.percona.com/wp-content/uploads/2026/03/k-factor-2-usl-model-vs-actual-150x150.png)
  Caption: Results for k-factor 2
- content / image: [Actual and modeled results for k-factors 0, 1, and 2](https://www.percona.com/wp-content/uploads/2026/03/usl-model-vs-actual.png)
  Caption: Actual and modeled results for k-factors 0, 1, and 2
- content / image: [Actual and modeled results with partitions for k-factors 0, 1, and 2](https://www.percona.com/wp-content/uploads/2026/03/usl-model-vs-actual-partitions.png)
  Caption: Actual and modeled results with partitions for k-factors 0, 1, and 2

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
