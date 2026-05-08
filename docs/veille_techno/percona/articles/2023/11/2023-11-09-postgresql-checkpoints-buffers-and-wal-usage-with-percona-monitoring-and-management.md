---
title: PostgreSQL Checkpoints, Buffers, and WAL Usage with Percona Monitoring and Management
source:
  name: Percona Blog
  url: https://www.percona.com/blog/postgresql-checkpoints-buffers-and-wal-usage-with-percona-monitoring-and-management/
  post_id: 27673
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2023-11-09T14:55:26'
published_at_gmt: '2023-11-09T14:55:26'
modified_at: '2026-03-26T20:07:43'
modified_at_gmt: '2026-03-26T20:07:43'
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
- tag:percona-monitoring-and-management:2166
categories:
- Monitoring
- Percona Software
- PostgreSQL
category_slugs:
- monitoring
- percona-software
- postgresql
tags:
- Agustin PP
- Percona Monitoring and Management
- Percona Software
- PostgreSQL
tag_slugs:
- agustin-planetpostgresql
- percona-monitoring-and-management
- percona-software
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Checkpoints-Buffers-and-WAL-Usage-with-Percona-Monitoring-and-Management.jpg
image_count: 11
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PostgreSQL Checkpoints, Buffers, and WAL Usage with Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/postgresql-checkpoints-buffers-and-wal-usage-with-percona-monitoring-and-management/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2023-11-09T14:55:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will discuss how to extend Percona Monitoring and Management (PMM) to get PostgreSQL metrics on checkpointing activity, internal buffers, and WAL usage. With this data, we’ll be able to better understand and tune our Postgres servers. We’ll assume there are working PostgreSQL and PMM environments set up already. You can … Continued

## Structure detectee

- H2: Creating a custom query collector
- H2: Importing the custom PMM dashboard
- H2: Using the new dashboard
- H3: Checkpointing section
- H3: Buffers section
- H2: WAL usage section
- H2: Hints and tips
- H2: Getting even more data
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [PostgreSQL Checkpoints, Buffers, and WAL Usage with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Checkpoints-Buffers-and-WAL-Usage-with-Percona-Monitoring-and-Management.jpg)
- content / graph_or_chart: [Percona Monitoring and Management Dashboard](https://www.percona.com/wp-content/uploads/2026/03/1image5.png)
- content / image: [Import via Grafana](https://www.percona.com/wp-content/uploads/2026/03/2image8.png)
- content / graph_or_chart: [Import dashboard from Grafana](https://www.percona.com/wp-content/uploads/2026/03/3image9.png)
- content / image: [4image3.png](https://www.percona.com/wp-content/uploads/2026/03/4image3.png)
- content / image: [Checkpointing PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/5image10.png)
- content / image: [6image2.png](https://www.percona.com/wp-content/uploads/2026/03/6image2.png)
- content / image: [7image1.png](https://www.percona.com/wp-content/uploads/2026/03/7image1.png)
- content / image: [8image4.png](https://www.percona.com/wp-content/uploads/2026/03/8image4.png)
- content / image: [Checkpointing PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/9image6.png)
- content / image: [10image7.png](https://www.percona.com/wp-content/uploads/2026/03/10image7.png)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
