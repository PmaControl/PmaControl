---
title: Tips for Designing Grafana Dashboards
source:
  name: Percona Blog
  url: https://www.percona.com/blog/designing-grafana-dashboards/
  post_id: 21220
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2019-11-22T14:28:42'
published_at_gmt: '2019-11-22T14:28:42'
modified_at: '2026-05-05T20:52:55'
modified_at_gmt: '2026-05-05T20:52:55'
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
- tag:pmm:2167
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- MySQL
- Percona Monitoring and Management
- Percona Software
- PMM
tag_slugs:
- mysql
- percona-monitoring-and-management
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Designing-Grafana-Dashboards.png
image_count: 25
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tips for Designing Grafana Dashboards

Source: [Percona Blog](https://www.percona.com/blog/designing-grafana-dashboards/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2019-11-22T14:28:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As Grafana powers our star product – Percona Monitoring and Management (PMM) – we have developed a lot of experience creating Grafana Dashboards over the last few years. In this article, I will share some of the considerations for designing Grafana Dashboards. As usual, when it comes to questions of design they are quite subjective, … Continued

## Structure detectee

- H3: Design Practical Dashboards
- H3: Do Not Place Too Many Graphs Side by Side
- H3: Use Proper Units
- H3: Mind Decimals
- H3: Label your Axis
- H3: Use Shared Crosshair or Tooltip
- H3: Pick Colors
- H3: Fill Stacking Graphs
- H3: Do Not Abuse Double Axis
- H3: Separate Data of Different Scales on Different Graphs
- H3: Consider Staircase Graphs
- H3: Provide Multiple Resolutions
- H3: Multiple Aggregates for the Same Metrics
- H3: Use Help and Panel Links
- H4: Summary

## Images et graphiques reperes

- featured / image: [Tips for Designing Grafana Dashboards](https://www.percona.com/wp-content/uploads/2026/03/Designing-Grafana-Dashboards.png)
- content / image: [Grafana Dashboards](https://www.percona.com/wp-content/uploads/2026/03/1-7-1024x359.png)
- content / image: [Grafana Dashboards2](https://www.percona.com/wp-content/uploads/2026/03/2-6-1024x369.png)
- content / image: [Grafana Dashboards3](https://www.percona.com/wp-content/uploads/2026/03/3-7-1024x361.png)
- content / image: [Grafana Dashboards4](https://www.percona.com/wp-content/uploads/2026/03/4-9-1024x363.png)
- content / image: [Grafana Dashboards5](https://www.percona.com/wp-content/uploads/2026/03/5-3-1024x199.png)
- content / image: [Grafana Dashboards 6](https://www.percona.com/wp-content/uploads/2026/03/6-3-1024x200.png)
- content / image: [Grafana Dashboards 6](https://www.percona.com/wp-content/uploads/2026/03/7-2-1024x199.png)
- content / image: [Grafana Dashboards 7](https://www.percona.com/wp-content/uploads/2026/03/8-2-1024x399.png)
- content / image: [9-1024x364.png](https://www.percona.com/wp-content/uploads/2026/03/9-1024x364.png)
- content / image: [10-3-1024x397.png](https://www.percona.com/wp-content/uploads/2026/03/10-3-1024x397.png)
- content / image: [11-2-1024x397.png](https://www.percona.com/wp-content/uploads/2026/03/11-2-1024x397.png)
- content / image: [12-1024x370.png](https://www.percona.com/wp-content/uploads/2026/03/12-1024x370.png)
- content / image: [13-1024x185.png](https://www.percona.com/wp-content/uploads/2026/03/13-1024x185.png)
- content / image: [14-1024x316.png](https://www.percona.com/wp-content/uploads/2026/03/14-1024x316.png)
- content / image: [15-1024x314.png](https://www.percona.com/wp-content/uploads/2026/03/15-1024x314.png)
- content / image: [16-1024x322.png](https://www.percona.com/wp-content/uploads/2026/03/16-1024x322.png)
- content / image: [20-1024x315.png](https://www.percona.com/wp-content/uploads/2026/03/20-1024x315.png)
- content / image: [21-2-1024x399.png](https://www.percona.com/wp-content/uploads/2026/03/21-2-1024x399.png)
- content / image: [22-1-1024x402.png](https://www.percona.com/wp-content/uploads/2026/03/22-1-1024x402.png)
- content / image: [23-1-1024x369.png](https://www.percona.com/wp-content/uploads/2026/03/23-1-1024x369.png)
- content / image: [24-1024x361.png](https://www.percona.com/wp-content/uploads/2026/03/24-1024x361.png)
- content / image: [image19.png](https://www.percona.com/blog/designing-grafana-dashboards/images/image19.png)
- content / image: [25-1024x461.png](https://www.percona.com/wp-content/uploads/2026/03/25-1024x461.png)
- content / image: [26-1024x656.png](https://www.percona.com/wp-content/uploads/2026/03/26-1024x656.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
