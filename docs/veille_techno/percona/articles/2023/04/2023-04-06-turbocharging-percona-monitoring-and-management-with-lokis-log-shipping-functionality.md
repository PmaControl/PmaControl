---
title: Turbocharging Percona Monitoring and Management With Loki’s Log-shipping Functionality
source:
  name: Percona Blog
  url: https://www.percona.com/blog/turbocharging-percona-monitoring-and-management-with-lokis-log-shipping-functionality/
  post_id: 26839
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2023-04-06T13:13:22'
published_at_gmt: '2023-04-06T13:13:22'
modified_at: '2026-03-26T20:29:53'
modified_at_gmt: '2026-03-26T20:29:53'
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
- Insight for DBAs
- Monitoring
- MySQL
- Percona Software
- PostgreSQL
category_slugs:
- insight-for-dbas
- monitoring
- mysql
- percona-software
- postgresql
tags:
- Monitoring
- MySQL
- mysql-and-variants
- PostgreSQL
tag_slugs:
- monitoring
- mysql
- mysql-and-variants
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/3d-future-datacenter-concept-with-neon-lights-3d-illustration.jpg
image_count: 10
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Turbocharging Percona Monitoring and Management With Loki’s Log-shipping Functionality

Source: [Percona Blog](https://www.percona.com/blog/turbocharging-percona-monitoring-and-management-with-lokis-log-shipping-functionality/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2023-04-06T13:13:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll check how to integrate Percona Monitoring and Management (PMM) with Loki to be able to get not only metrics and queries from our database servers but also text-based information like logs. Loki is a log aggregation tool developed by Grafana Labs. It integrates easily with the Grafana instance that is … Continued

## Structure detectee

- H2: Setting up Loki
- H2: Configuring Loki in PMM
- H2: Setting up Promtail in the MySQL client node
- H2: Checking MySQL logs on PMM
- H2: Setting up Promtail in the PostgreSQL client node
- H2: There’s more!
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Turbocharging Percona Monitoring and Management With Loki’s Log-shipping Functionality](https://www.percona.com/wp-content/uploads/2026/03/3d-future-datacenter-concept-with-neon-lights-3d-illustration.jpg)
- content / image: [grafana loki](https://www.percona.com/wp-content/uploads/2026/03/1-17.png)
- content / image: [data sources loki](https://www.percona.com/wp-content/uploads/2026/03/2-17.png)
- content / graph_or_chart: [PMM dashboard](https://www.percona.com/wp-content/uploads/2026/03/3-17.png)
- content / image: [4-17.png](https://www.percona.com/wp-content/uploads/2026/03/4-17.png)
- content / image: [MySQL logs](https://www.percona.com/wp-content/uploads/2026/03/5-11.png)
- content / image: [6-10.png](https://www.percona.com/wp-content/uploads/2026/03/6-10.png)
- content / image: [7-9.png](https://www.percona.com/wp-content/uploads/2026/03/7-9.png)
- content / image: [PMM loki grafana](https://www.percona.com/wp-content/uploads/2026/03/8-9.png)
- content / image: [9-6.png](https://www.percona.com/wp-content/uploads/2026/03/9-6.png)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
