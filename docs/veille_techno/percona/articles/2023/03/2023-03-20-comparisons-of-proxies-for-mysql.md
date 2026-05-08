---
title: Comparisons of Proxies for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparisons-of-proxies-for-mysql/
  post_id: 26736
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2023-03-20T15:05:21'
published_at_gmt: '2023-03-20T15:05:21'
modified_at: '2026-03-26T20:30:00'
modified_at_gmt: '2026-03-26T20:30:00'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- Benchmarks
- Cloud
- MySQL
- ProxySQL
category_slugs:
- benchmarks
- cloud
- mysql
- proxysql
tags:
- haproxy
- Kubernetes
- MySQL Router
- Operator
- ProxySQL
- scaling
tag_slugs:
- haproxy
- kubernetes
- mysql-router
- operator
- proxysql
- scaling
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_underwater_high_tech_computer_server_a_dolpin_i_9337e5c5-e3c5-41dd-b0b1-e6504186488b.png
image_count: 11
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparisons of Proxies for MySQL

Source: [Percona Blog](https://www.percona.com/blog/comparisons-of-proxies-for-mysql/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2023-03-20T15:05:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

With a special focus on Percona Operator for MySQL Overview HAProxy, ProxySQL, MySQL Router (AKA MySQL Proxy); in the last few years, I had to answer multiple times on what proxy to use and in what scenario. When designing an architecture, many components need to be considered before deciding on the best solution. When deciding … Continued

## Structure detectee

- H2: Overview
- H2: The environment
- H2: The tests
- H2: Results
- H3: Test 1
- H4: Brief summary
- H2: Test 2
- H4: Brief summary
- H2: Conclusions
- H3: References

## Images et graphiques reperes

- featured / image: [Comparisons of Proxies for MySQL](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_underwater_high_tech_computer_server_a_dolpin_i_9337e5c5-e3c5-41dd-b0b1-e6504186488b.png)
- content / image: [MySQL on Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/proxys_gr_comparison-default.png)
- content / image: [events_rate2-1024x684.png](https://www.percona.com/wp-content/uploads/2026/03/events_rate2-1024x684.png)
- content / graph_or_chart: [latency threads](https://www.percona.com/wp-content/uploads/2026/03/latency95_rate-1024x723.png)
- content / image: [node-summary-cpu-1024x411.png](https://www.percona.com/wp-content/uploads/2026/03/node-summary-cpu-1024x411.png)
- content / image: [HAProxy](https://www.percona.com/wp-content/uploads/2026/03/node-summary-cpu-saturation-1024x405.png)
- content / image: [mysql events](https://www.percona.com/wp-content/uploads/2026/03/events_perf-1024x607.png)
- content / graph_or_chart: [mysql latency](https://www.percona.com/wp-content/uploads/2026/03/latency95_perf-1024x680.png)
- content / image: [ProxySQL and MySQL Router](https://www.percona.com/wp-content/uploads/2026/03/node-summary-cpu-perf-1024x406.png)
- content / image: [proxysql usage](https://www.percona.com/wp-content/uploads/2026/03/node-summary-cpu-saturation-perf-1024x408.png)
- content / image: [MySQL service is organized](https://www.percona.com/wp-content/uploads/2026/03/proxys_gr_comparison-haproxy.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
