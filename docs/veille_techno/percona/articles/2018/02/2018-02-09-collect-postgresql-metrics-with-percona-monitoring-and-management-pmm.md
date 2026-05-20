---
title: Collect PostgreSQL Metrics with Percona Monitoring and Management (PMM)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/collect-postgresql-metrics-with-percona-monitoring-and-management-pmm/
  post_id: 17947
source_author:
  name: Nickolay Ihalainen
  slug: nickolay-ihalainen
  url: https://www.percona.com/blog/author/nickolay-ihalainen/
  website: ''
published_at: '2018-02-09T23:17:47'
published_at_gmt: '2018-02-09T23:17:47'
modified_at: '2026-03-26T20:11:28'
modified_at_gmt: '2026-03-26T20:11:28'
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
- Monitoring
- Percona Software
- PostgreSQL
category_slugs:
- monitoring
- percona-software
- postgresql
tags:
- data collection
- Metrics
- Monitoring
- Percona Monitoring and Management
- PMM
- posgresql
- Postgres
- Statistics
tag_slugs:
- data-collection
- metrics
- monitoring
- percona-monitoring-and-management
- pmm
- posgresql
- postgres
- statistics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Collecting-PostgreSQL-Information-using-Percona-Monitoring-and-Management.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Collect PostgreSQL Metrics with Percona Monitoring and Management (PMM)

Source: [Percona Blog](https://www.percona.com/blog/collect-postgresql-metrics-with-percona-monitoring-and-management-pmm/)

Auteur source: [Nickolay Ihalainen](https://www.percona.com/blog/author/nickolay-ihalainen/)

Publication: 2018-02-09T23:17:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this article, we’ll describe how to collect PostgreSQL metrics with Percona Monitoring and Management (PMM). We designed Percona Monitoring and Management (PMM) to be the best tool for MySQL and MongoDB performance investigation. At the same time, it’s built on mature opensource components: Prometheus’ time series database and Grafana. Starting from PMM 1.4.0. it’s possible to … Continued

## Structure detectee

- H2: Demo
- H2: PMM-PostgreSQL Demo Under the Hood
- H3: PMM Server
- H3: PostgreSQL Setup
- H3: PMM Client Setup on PostgreSQL Host
- H4: PMM 1.7.0 external:service
- H3: Exporter Setup
- H4: Run external exporter directly on database server
- H3: Grafana Setup
- H2: Notice

## Images et graphiques reperes

- featured / image: [Collect PostgreSQL Metrics with Percona Monitoring and Management (PMM)](https://www.percona.com/wp-content/uploads/2026/03/Collecting-PostgreSQL-Information-using-Percona-Monitoring-and-Management.png)
- content / image: [collect PostgreSQL metrics with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/pmm-postgres-working-Postgres_exporter-1024x560.png)
  Caption: PMM PostgreSQL postgres_exporter template
- content / image: [Watch the recorded webinar](https://www.percona.com/wp-content/uploads/2026/03/bcd15108-3d2a-4829-9f60-354d84a856d6.png)

## Auteur source

Nickolay joined Percona in December 2010, after working for several years at what is now the most popular cinema site in Russia. During the time he was there, Nickolay and a small team of developers were responsible for scaling the site into one which now serves over a million unique visitors per day. Prior to that, he worked for several other companies, including NetUp, which provides ISP billing and IPTV solutions, and eHouse, the oldest Russian e-commerce company. Nickolay has a great deal of experience in both systems administration and programming. His experience includes extensive hands-on work with a broad range of technologies, including SQL, MySQL, PHP, C, C++, Python, Java, XML, OS parameter tuning (Linux, Solaris), caching techniques (e.g., memcached), RAID, file systems, SMTP, POP3, Apache, networking and network data formats, and many others. He is an expert in scalability, performance, and system reliability.
