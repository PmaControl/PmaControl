---
title: Monitoring a PostgreSQL Patroni Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/monitoring-a-postgresql-patroni-cluster/
  post_id: 27053
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2023-06-09T13:42:03'
published_at_gmt: '2023-06-09T13:42:03'
modified_at: '2026-03-26T20:07:53'
modified_at_gmt: '2026-03-26T20:07:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- Monitoring
- PostgreSQL
category_slugs:
- insight-for-dbas
- monitoring
- postgresql
tags:
- Agustin PP
- Monitoring
- PostgreSQL
tag_slugs:
- agustin-planetpostgresql
- monitoring
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Monitoring-a-PostgreSQL-Patroni-Cluster.jpeg
image_count: 8
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Monitoring a PostgreSQL Patroni Cluster

Source: [Percona Blog](https://www.percona.com/blog/monitoring-a-postgresql-patroni-cluster/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2023-06-09T13:42:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Monitoring and Management (PMM) boasts many functionalities that support its extension, be it by using Custom Queries, Custom Scripts, or by collecting data from already available External Exporters. In this short blog post, we will see how to quickly (and easily) monitor a PostgreSQL cluster managed by Patroni. I will assume you already have … Continued

## Structure detectee

- H2: Adding the Patroni metrics to PMM
- H2: Importing the new Patroni Dashboard
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Monitoring a PostgreSQL Patroni Cluster](https://www.percona.com/wp-content/uploads/2026/03/Monitoring-a-PostgreSQL-Patroni-Cluster.jpeg)
- content / graph_or_chart: [Patroni Advanced Data Exploration dashboard](https://www.percona.com/wp-content/uploads/2026/03/pg1.png)
- content / image: [PostgreSQL Enterprise](https://www.percona.com/wp-content/uploads/2026/03/Postgres-Enterprise-Real-Cost-DIY.png)
- content / image: [1-18.png](https://www.percona.com/wp-content/uploads/2026/03/1-18.png)
- content / image: [2-18.png](https://www.percona.com/wp-content/uploads/2026/03/2-18.png)
- content / image: [3-18.png](https://www.percona.com/wp-content/uploads/2026/03/3-18.png)
- content / image: [d1.png](https://www.percona.com/wp-content/uploads/2026/03/d1.png)
- content / image: [d2.png](https://www.percona.com/wp-content/uploads/2026/03/d2.png)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
