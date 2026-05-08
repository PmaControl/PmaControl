---
title: Better Prometheus rate() Function with VictoriaMetrics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/better-prometheus-rate-function-with-victoriametrics/
  post_id: 21781
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-02-28T15:08:47'
published_at_gmt: '2020-02-28T15:08:47'
modified_at: '2026-04-27T21:31:26'
modified_at_gmt: '2026-04-27T21:31:26'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- Monitoring
- MySQL
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- monitoring
- mysql
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/prometheus-rate-function.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Better Prometheus rate() Function with VictoriaMetrics

Source: [Percona Blog](https://www.percona.com/blog/better-prometheus-rate-function-with-victoriametrics/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-02-28T15:08:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are a lot of things I love about Prometheus; its data model is fantastic for monitoring applications and PromQL language is often more expressive than SQL for data retrieval needs you have in the observability space. One thing, though, I hate about Prometheus with a deep passion is the behavior of its rate() and … Continued

## Structure detectee

- H2: So What’s the Problem, and Why is it Such a Big Deal?
- H3: Existing “Solutions”
- H2: VictoriaMetrics to the Rescue
- H3: 1h Range
- H3: 5min Range
- H3: Summary

## Images et graphiques reperes

- featured / image: [Better Prometheus rate() Function with VictoriaMetrics](https://www.percona.com/wp-content/uploads/2026/03/prometheus-rate-function.png)
- content / image: [prometheus rate function](https://www.percona.com/wp-content/uploads/2026/03/prometheus-rate-function-300x168.png)
- content / image: [Prometheus vs VictoriaMetrics](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-02-27-at-3.09.52-PM.png)
- content / image: [Prometheus vs VictoriaMetrics](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-02-27-at-3.11.19-PM.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
