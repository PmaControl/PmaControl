---
title: Gh-ost benchmark against pt-online-schema-change performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/gh-ost-benchmark-against-pt-online-schema-change-performance/
  post_id: 17106
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2017-07-12T18:31:48'
published_at_gmt: '2017-07-12T18:31:48'
modified_at: '2026-05-05T18:45:40'
modified_at_gmt: '2026-05-05T18:45:40'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- benchmark
- gh-ost
- MySQL
- Percona
- pt-onine-schema-change
tag_slugs:
- benchmark
- gh-ost
- mysql
- cap-percona
- pt-onine-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/gh-ost-benchmark-small.png
image_count: 5
graph_or_chart_count: 5
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Gh-ost benchmark against pt-online-schema-change performance

Source: [Percona Blog](https://www.percona.com/blog/gh-ost-benchmark-against-pt-online-schema-change-performance/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2017-07-12T18:31:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I will run a gh-ost benchmark against the performance of pt-online-schema-change. When gh-ost came out, I was very excited. As MySQL ROW replication became commonplace, you could use it to track changes instead of triggers. This practice is cleaner and safer compared to Percona Toolkit’s pt-online-schema-change. Since gh-ost doesn’t need triggers, … Continued

## Structure detectee

- H4: Benchmark Setup Details
- H4: Tests Details
- H4: Idle Load
- H4: Light Background Load
- H4: Heavy Background Load
- H4: Online Schema Change Performance Impact
- H4: Summary

## Images et graphiques reperes

- featured / graph_or_chart: [Gh-ost benchmark against pt-online-schema-change performance](https://www.percona.com/wp-content/uploads/2026/03/gh-ost-benchmark-small.png)
- content / graph_or_chart: [gh-ost benchmark 1](https://www.percona.com/wp-content/uploads/2026/03/gh-ost-benchmark-1.png)
- content / graph_or_chart: [gh-ost benchmark 2](https://www.percona.com/wp-content/uploads/2026/03/gh-ost-benchmark-2.png)
- content / graph_or_chart: [gh-ost benchmark 3](https://www.percona.com/wp-content/uploads/2026/03/gh-ost-benchmark-3.png)
- content / graph_or_chart: [gh-ost benchmark 4](https://www.percona.com/wp-content/uploads/2026/03/gh-ost-benchmark-4.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
