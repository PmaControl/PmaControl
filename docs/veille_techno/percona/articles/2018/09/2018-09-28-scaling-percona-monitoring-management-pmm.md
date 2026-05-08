---
title: Scaling Percona Monitoring and Management (PMM)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/scaling-percona-monitoring-management-pmm/
  post_id: 19415
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2018-09-28T12:29:34'
published_at_gmt: '2018-09-28T12:29:34'
modified_at: '2026-03-20T22:03:50'
modified_at_gmt: '2026-03-20T22:03:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Benchmarks
- Hardware and Storage
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- benchmarks
- hardware-and-storage
- insight-for-dbas
- mysql
- percona-software
tags:
- configuration
- Scalability
- scale
tag_slugs:
- configuration
- scalability
- scale
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PMM-tested-with-1000-nodes.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Scaling Percona Monitoring and Management (PMM)

Source: [Percona Blog](https://www.percona.com/blog/scaling-percona-monitoring-management-pmm/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2018-09-28T12:29:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Starting with PMM 1.13, PMM uses Prometheus 2 for metrics storage, which tends to be heaviest resource consumer of CPU and RAM. With Prometheus 2 Performance Improvements, PMM can scale to more than 1000 monitored nodes per instance in default configuration. In this blog post we will look into PMM scaling and capacity planning—how to … Continued

## Structure detectee

- H2: What drives resource usage in PMM ?
- H3: Capacity planning to scale PMM
- H2: Summary

## Images et graphiques reperes

- featured / image: [Scaling Percona Monitoring and Management (PMM)](https://www.percona.com/wp-content/uploads/2026/03/PMM-tested-with-1000-nodes.png)
- content / image: [Performance PMM 1000 nodes load](https://www.percona.com/wp-content/uploads/2026/03/Performance-PMM-1000-nodes-load.png)
- content / graph_or_chart: [set home dashboard for PMM](https://www.percona.com/wp-content/uploads/2026/03/set-home-dashboard-for-PMM.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
