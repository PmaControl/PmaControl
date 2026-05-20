---
title: Why do we care about MySQL Performance at High Concurrency?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-do-we-care-about-performance-at-high-concurrency/
  post_id: 6644
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2013-02-26T15:47:03'
published_at_gmt: '2013-02-26T15:47:03'
modified_at: '2026-04-28T21:50:45'
modified_at_gmt: '2026-04-28T21:50:45'
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
- High Concurrency
- MySQL Performance
- Peter Zaitsev
- Threads_Running
tag_slugs:
- high-concurrency
- mysql-performance
- peter-zaitsev
- threads_running
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why do we care about MySQL Performance at High Concurrency?

Source: [Percona Blog](https://www.percona.com/blog/why-do-we-care-about-performance-at-high-concurrency/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2013-02-26T15:47:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In many MySQL Benchmarks we can see performance compared with rather high level of concurrency. In some cases reaching 4,000 or more concurrent threads which hammer databases as quickly as possible resulting in hundreds or even thousands concurrently active queries. The question is how common is it in production ? The typical metrics to use … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
