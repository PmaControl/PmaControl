---
title: Understanding PMM QAN – Graphs & Metrics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-pmm-qan-graphs-metrics/
  post_id: 18837
source_author:
  name: Vinodh Krishnaswamy
  slug: vinodh-krishnaswamy
  url: https://www.percona.com/blog/author/vinodh-krishnaswamy/
  website: ''
published_at: '2018-06-08T12:33:57'
published_at_gmt: '2018-06-08T12:33:57'
modified_at: '2026-05-05T20:25:02'
modified_at_gmt: '2026-05-05T20:25:02'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
- Percona Toolkit
matched_filters:
- search:percona-toolkit
- search:pmm
- tag:pmm:2167
categories:
- Insight for DBAs
- Percona Software
category_slugs:
- insight-for-dbas
- percona-software
tags:
- MongoDB
- PMM
- pmm qan
- pmm-admin
- QAN
tag_slugs:
- mongodb
- pmm
- pmm-qan
- pmm-admin
- qan
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/understanding-pmm-qan.jpg
image_count: 11
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding PMM QAN – Graphs & Metrics

Source: [Percona Blog](https://www.percona.com/blog/understanding-pmm-qan-graphs-metrics/)

Auteur source: [Vinodh Krishnaswamy](https://www.percona.com/blog/author/vinodh-krishnaswamy/)

Publication: 2018-06-08T12:33:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I will share with you how to set up the PMM QAN for MongoDB and the formulas behind the metrics and graphs that you see on the QAN dashboard. When one of my customers wanted to load test and understand the behavior of the queries in their MongoDB instance through a … Continued

## Structure detectee

- H2: PMM – a brief introduction
- H2: PMM QAN
- H3: Add QAN metrics
- H3: PMM QAN Summary Table
- H4: Load
- H4: Count
- H4: Latency
- H2: Using the graph
- H4: How can we actually use these metrics/graphs to identify a problem or potential for performance improvement?
- H2: Tips and Hints
- H3: For MongoDB:
- H3: For MySQL:
- H3: Some useful references

## Images et graphiques reperes

- featured / image: [Understanding PMM QAN – Graphs & Metrics](https://www.percona.com/wp-content/uploads/2026/03/understanding-pmm-qan.jpg)
- content / image: [understanding PMM QAN](https://www.percona.com/wp-content/uploads/2026/03/understanding-pmm-qan-300x199.jpg)
- content / image: [PMM Architecture](https://www.percona.com/wp-content/uploads/2026/03/PMM-ARch.png)
- content / image: [QAN Summary Table](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2018-04-26-at-8.25.18-PM-scaled.png)
- content / image: [PMM QAN Load](https://www.percona.com/wp-content/uploads/2026/03/2-2-1024x375.png)
- content / image: [PMM QAN Load Query Time](https://www.percona.com/wp-content/uploads/2026/03/2-3-scaled.png)
- content / image: [PMM QAN Count](https://www.percona.com/wp-content/uploads/2026/03/3-4.png)
- content / image: [PMM QAN Count 2](https://www.percona.com/wp-content/uploads/2026/03/4-6-1024x140.png)
- content / graph_or_chart: [PMM QAN Latency](https://www.percona.com/wp-content/uploads/2026/03/5-1.png)
- content / graph_or_chart: [PMM QAN Latency 2](https://www.percona.com/wp-content/uploads/2026/03/6-2-1024x132.png)
- content / image: [PMM QAN EXPLAIN](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2018-06-03-at-4.10.27-PM-scaled.png)

## Auteur source

Vinodh Krishnaswamy is a member of Support Team! Prior to joining Percona, he worked as a MySQL and MongoDB DBA in companies such as iGate, Datavail, and Sify Ltd. He is a trainer and has provided training programs on MySQL and MongoDB. He enjoys writing Shell script and loves driving, reading books, and playing table tennis.
