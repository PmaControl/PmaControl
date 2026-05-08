---
title: More on MySQL transaction descriptors optimization
source:
  name: Percona Blog
  url: https://www.percona.com/blog/more-on-mysql-transaction-descriptors-optimization/
  post_id: 6869
source_author:
  name: Alexey Kopytov
  slug: alexey-kopytov
  url: https://www.percona.com/blog/author/alexey-kopytov/
  website: ''
published_at: '2013-04-26T10:00:38'
published_at_gmt: '2013-04-26T10:00:38'
modified_at: '2026-04-28T21:52:20'
modified_at_gmt: '2026-04-28T21:52:20'
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
tags:
- MySQL transaction descriptors
- Optimization
tag_slugs:
- mysql-transaction-descriptors
- optimization
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/point_select_8t1M_hppro2_cisco1_ct720.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# More on MySQL transaction descriptors optimization

Source: [Percona Blog](https://www.percona.com/blog/more-on-mysql-transaction-descriptors-optimization/)

Auteur source: [Alexey Kopytov](https://www.percona.com/blog/author/alexey-kopytov/)

Publication: 2013-04-26T10:00:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Since my first post on MySQL transaction descriptors optimization introduced in Percona Server 5.5.30-30.2 and a followup by Dimitri Kravchuk, we have received a large number of questions on why the benchmark results in both posts look rather different. We were curious as well, so we tried to answer that question by retrying benchmarks on … Continued

## Structure detectee

- H3: Hardware:
- H3: Results:
- H3: Server Configuration:
- H3: warmup
- H3: SysBench-0.5/lua:
- H4: POINT_SELECT QPS test
- H4: POINT_SELECT + UPDATE QPS test

## Images et graphiques reperes

- featured / image: [More on MySQL transaction descriptors optimization](https://www.percona.com/wp-content/uploads/2026/03/point_select_8t1M_hppro2_cisco1_ct720.png)
- content / image: [point_select.socket0.16vcpu.vs.socket0.8.socket1.8](https://www.percona.com/wp-content/uploads/2026/03/point_select.socket0.16vcpu.vs_.socket0.8.socket1.8.png)
- content / image: [point_select1_vs_point_select9_update_pk1](https://www.percona.com/wp-content/uploads/2026/03/point_select1_vs_point_select9_update_pk1.png)

## Auteur source

Alexey Kopytov is a Principal Software Engineer at Percona. Before joining Percona in 2010 he was a member of the MySQL development team at Oracle. His focus at Percona is development of both Percona Server and Percona XtraBackup.
