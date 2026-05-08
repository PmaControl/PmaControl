---
title: Observations on Better Resource Usage with Percona Monitoring and Management v2.12.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/observations-on-better-resource-usage-with-percona-monitoring-and-management-v2-12-0/
  post_id: 23600
source_author:
  name: Puneet Kala
  slug: puneet-kala
  url: https://www.percona.com/blog/author/puneet-kala/
  website: ''
published_at: '2020-12-23T15:35:29'
published_at_gmt: '2020-12-23T15:35:29'
modified_at: '2026-05-05T21:09:20'
modified_at_gmt: '2026-05-05T21:09:20'
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
- tag:pmm:2167
categories:
- Insight for DBAs
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- percona-software
tags:
- Monitoring
- mysql-and-variants
- Percona Monitoring and Management
- Percona Software
- PMM
tag_slugs:
- monitoring
- mysql-and-variants
- percona-monitoring-and-management
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Better-Resource-Usage-with-Percona-Monitoring-and-Management.png
image_count: 11
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Observations on Better Resource Usage with Percona Monitoring and Management v2.12.0

Source: [Percona Blog](https://www.percona.com/blog/observations-on-better-resource-usage-with-percona-monitoring-and-management-v2-12-0/)

Auteur source: [Puneet Kala](https://www.percona.com/blog/author/puneet-kala/)

Publication: 2020-12-23T15:35:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Monitoring and Management (PMM) v2.12.0 comes with a lot of improvements and one of the most talked-about is the usage of VictoriaMetricsDB. The reason we are doing this comparison is that PMM 2.12.0 is a release in which we integrate VictoriaMetricsDB and replace Prometheus as its default method of data ingestion. A reason for … Continued

## Structure detectee

- H2: Benchmark Setup Details
- H2: Disk Space Usage
- H2: Memory Utilization
- H2: CPU Usage
- H2: Observations

## Images et graphiques reperes

- featured / image: [Observations on Better Resource Usage with Percona Monitoring and Management v2.12.0](https://www.percona.com/wp-content/uploads/2026/03/Better-Resource-Usage-with-Percona-Monitoring-and-Management.png)
- content / image: [Better Resource Usage with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Better-Resource-Usage-with-Percona-Monitoring-and-Management-300x168.png)
- content / graph_or_chart: [Percona Monitoring and Management benchmark](https://www.percona.com/wp-content/uploads/2026/03/Comparison-with-250-services_24_hours.png)
- content / image: [Disk Usage PMM 2.11.1](https://www.percona.com/wp-content/uploads/2026/03/Disk-Usage-PMM-Server-2.11.1_24_Hours-1024x471.png)
- content / image: [Disk Usage PMM 2.12.0](https://www.percona.com/wp-content/uploads/2026/03/Disk-Usage-PMM-Server_2_12_0_24_Hours-1024x470.png)
- content / image: [Memory Utilization PMM 2.11.1](https://www.percona.com/wp-content/uploads/2026/03/Memory-Utilization-PMM-2.11.1_24_Hours-1024x471.png)
- content / image: [Free Memory PMM 2.11.1](https://www.percona.com/wp-content/uploads/2026/03/Free-Memory-PMM-2.11.1_24_Hours-1024x463.png)
- content / image: [Memory Utilization 2.11.1](https://www.percona.com/wp-content/uploads/2026/03/Memory-Utilization-PMM_2.12.0_24_Hours-scaled.png)
- content / image: [Free Memory 2.12.0](https://www.percona.com/wp-content/uploads/2026/03/Free-Memory-PMM-2.12.0_24_Hours-1024x471.png)
- content / image: [CPU Usage 2.11.1](https://www.percona.com/wp-content/uploads/2026/03/CPU-Usage-2.11.1_24_Hours-1024x468.png)
- content / image: [CPU Usage 2.12.0](https://www.percona.com/wp-content/uploads/2026/03/CPU-Usage-2.12.0_24_Hours-1024x469.png)

## Auteur source

Puneet is the Frontend/Web QA Automation Engineer for the Percona Monitoring and Management team.
