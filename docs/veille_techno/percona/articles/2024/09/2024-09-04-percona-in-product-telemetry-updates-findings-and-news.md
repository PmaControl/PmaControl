---
title: 'Percona In-Product Telemetry: Updates, Findings, and News'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-in-product-telemetry-updates-findings-and-news/
  post_id: 28950
source_author:
  name: Bartek Gatz
  slug: bartek-gatz
  url: https://www.percona.com/blog/author/bartek-gatz/
  website: ''
published_at: '2024-09-04T14:18:40'
published_at_gmt: '2024-09-04T14:18:40'
modified_at: '2026-03-26T20:25:59'
modified_at_gmt: '2026-03-26T20:25:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:percona-xtrabackup
- search:pmm
- search:proxysql
- search:xtrabackup
categories:
- Database Trends
- MongoDB
- MySQL
- Percona Software
- PostgreSQL
category_slugs:
- database-trends
- mongodb
- mysql
- percona-software
- postgresql
tags:
- Percona Software
tag_slugs:
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/indoor-vertical-farm-isometric-view-hydroponic-microgreens-plant-factory-growing-with-led-lights-sustainable-agriculture-3d-illustration.jpg
image_count: 16
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona In-Product Telemetry: Updates, Findings, and News

Source: [Percona Blog](https://www.percona.com/blog/percona-in-product-telemetry-updates-findings-and-news/)

Auteur source: [Bartek Gatz](https://www.percona.com/blog/author/bartek-gatz/)

Publication: 2024-09-04T14:18:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This article is, in fact, two topics merged into one publication. Both are related to anonymous statistical data collection within Percona releases of database engines: MySQL, MongoDB, and PostgreSQL. In the first part of this article, I will share some of our findings and observations and discuss the various conclusions we have drawn from them. … Continued

## Structure detectee

- H2: Chapter 1
- H3: Telemetry findings and observations: sharing back with the community
- H3: Key findings for MySQL
- H3: Percona decisions
- H3: Percona decisions
- H3: Key findings for MongoDB
- H3: Percona decisions
- H3: Percona decisions
- H3: Key findings for PostgreSQL
- H3: Percona decisions
- H3: Percona decisions
- H3: Most popular operating systems
- H3: Percona decisions
- H2: Chapter 2
- H3: New and improved telemetry mechanisms
- H3: Why did we decide to create a new version of the telemetry?
- H3: What will we collect in future versions of Percona Pillars?
- H2: Feedback is welcome

## Images et graphiques reperes

- featured / image: [Percona In-Product Telemetry: Updates, Findings, and News](https://www.percona.com/wp-content/uploads/2026/03/indoor-vertical-farm-isometric-view-hydroponic-microgreens-plant-factory-growing-with-led-lights-sustainable-agriculture-3d-illustration.jpg)
- content / image: [Image 1: Breakdown of new deployment events by version for Percona Server for MySQL for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.48.19-1024x540.png)
  Caption: Image 1: Breakdown of new deployment events by version for Percona Server for MySQL for the last 90 days
- content / image: [Image 2: Breakdown of active instances by version for MySQL and MariaDB monitored by PMM for the last 90 days. The version numbers 5.x, and 8.x show the MySQL versions. The other numbers show MariaDB. Please note that the percentages of MySQL vs. MariaDB versions might not represent the true market presence - it is only what we see through PMM.](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.49.45-1024x704.png)
  Caption: Image 2: Breakdown of active instances by version for MySQL and MariaDB monitored by PMM for the last 90 days. The version numbers 5.x, and 8.x show the MySQL versions. The other numbers show MariaDB. Please note that the percentages of MySQL vs. MariaDB versions might not represent the true market presence – it is only what we see through PMM.
- content / image: [Image 3: Breakdown of new deployment events by deployment method for Percona Server for MySQL for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.53.27-1024x492.png)
  Caption: Image 3: Breakdown of new deployment events by deployment method for Percona Server for MySQL for the last 90 days
- content / image: [Image 4: Breakdown of new deployment events by hardware architecture for Percona Server for MySQL for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.54.21-1024x486.png)
  Caption: Image 4: Breakdown of new deployment events by hardware architecture for Percona Server for MySQL for the last 90 days
- content / image: [Image 5: Breakdown of new deployment events by version for Percona Server for MongoDB for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.55.39-1024x628.png)
  Caption: Image 5: Breakdown of new deployment events by version for Percona Server for MongoDB for the last 90 days
- content / image: [Image 6: Dynamics of active instances by version for MongoDB monitored by PMM for the last 12 months](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.56.52-1024x472.png)
  Caption: Image 6: Dynamics of active instances by version for MongoDB monitored by PMM for the last 12 months
- content / image: [Image 7: Breakdown of new deployment events by deployment method for Percona Server for MongoDB for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.58.02-1024x486.png)
  Caption: Image 7: Breakdown of new deployment events by deployment method for Percona Server for MongoDB for the last 90 days
- content / image: [Image 8: Breakdown of new deployment events by hardware architecture for Percona Server for MongoDB for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-14.58.54-1024x496.png)
  Caption: Image 8: Breakdown of new deployment events by hardware architecture for Percona Server for MongoDB for the last 90 days
- content / image: [Image 9: Breakdown of new deployment events by version for Percona Server for PostgreSQL for last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.00.09-1024x516.png)
  Caption: Image 9: Breakdown of new deployment events by version for Percona Server for PostgreSQL for last 90 days
- content / image: [Image 10: Weekly breakdown of the percentage of PostgreSQL major versions for the last 12 months](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.01.17-1024x497.png)
  Caption: Image 10: Weekly breakdown of the percentage of PostgreSQL major versions for the last 12 months
- content / image: [Image 11: Breakdown of new deployment events by deployment method for Percona Server for PostgreSQL for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.03.37-1024x450.png)
  Caption: Image 11: Breakdown of new deployment events by deployment method for Percona Server for PostgreSQL for the last 90 days
- content / image: [Image 12: Breakdown of new deployment events by hardware architecture for Percona Server for PostgreSQL for the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.04.40-1024x502.png)
  Caption: Image 12: Breakdown of new deployment events by hardware architecture for Percona Server for PostgreSQL for the last 90 days
- content / image: [Screenshot-2024-09-03-at-15.06.10-1024x534.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.06.10-1024x534.png)
- content / image: [Screenshot-2024-09-03-at-15.05.56-1024x519.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.05.56-1024x519.png)
- content / image: [Image 13, 14, 15: Most popular OSes used for deployment of MySQL, MongoDB, and PostgreSQL during the last 90 days](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-09-03-at-15.05.43-1024x510.png)
  Caption: Image 13, 14, 15: Most popular OSes used for deployment of MySQL, MongoDB, and PostgreSQL during the last 90 days
