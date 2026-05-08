---
title: Graphing MySQL performance with Prometheus and Grafana
source:
  name: Percona Blog
  url: https://www.percona.com/blog/graphing-mysql-performance-with-prometheus-and-grafana/
  post_id: 14699
source_author:
  name: Roman Vynar
  slug: weber
  url: https://www.percona.com/blog/author/weber/
  website: ''
published_at: '2016-02-29T07:49:02'
published_at_gmt: '2016-02-29T07:49:02'
modified_at: '2026-05-05T19:36:46'
modified_at_gmt: '2026-05-05T19:36:46'
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
categories:
- Monitoring
- MySQL
category_slugs:
- monitoring
- mysql
tags:
- Grafana
- Prometheus
tag_slugs:
- grafana
- prometheus
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Graphing-MySQL-performance.png
image_count: 16
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Graphing MySQL performance with Prometheus and Grafana

Source: [Percona Blog](https://www.percona.com/blog/graphing-mysql-performance-with-prometheus-and-grafana/)

Auteur source: [Roman Vynar](https://www.percona.com/blog/author/weber/)

Publication: 2016-02-29T07:49:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post explains how you can quickly start using such trending tools as Prometheus and Grafana for monitoring and graphing of MySQL and system performance. Update, February 20, 2017: Since this blog post was published, we have released Percona Monitoring and Management (PMM), which is the easiest way to monitor MySQL and MongoDB using Grafana … Continued

## Structure detectee

- H2: Overview
- H2: Diagram
- H2: Prometheus setup
- H2: Prometheus exporters setup
- H2: Grafana setup
- H2: Samples
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Graphing MySQL performance with Prometheus and Grafana](https://www.percona.com/wp-content/uploads/2026/03/Graphing-MySQL-performance.png)
- content / image: [Prometheus](https://www.percona.com/wp-content/uploads/2026/03/3380462.png)
- content / image: [Grafana](https://www.percona.com/wp-content/uploads/2026/03/7195757.png)
- content / graph_or_chart: [Prometheus + Grafana diagram](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-02-28-at-21.53.32.png)
- content / image: [Prometheus web interface](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-02-28-at-22.47.08.png)
- content / image: [Prometheus status page](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-02-28-at-23.11.20.png)
- content / image: [Grafana datasource](https://www.percona.com/wp-content/uploads/2026/03/datasource.png)
- content / image: [Grafana screen](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-02-28-at-23.51.55.png)
- content / image: [sample1.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample1.png)
- content / image: [sample2.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample2.png)
- content / image: [sample3.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample3.png)
- content / image: [sample4.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample4.png)
- content / image: [sample6.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample6.png)
- content / image: [sample5.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample5.png)
- content / image: [sample7.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample7.png)
- content / image: [sample8.png](https://github.com/percona/grafana-dashboards/raw/master/assets/sample8.png)

## Auteur source

Lead Platform Engineer at Percona. Developing monitoring tools, automated scripts and leading Percona Monitoring and Management project.
