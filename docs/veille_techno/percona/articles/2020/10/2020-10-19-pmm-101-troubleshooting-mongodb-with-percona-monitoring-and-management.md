---
title: 'PMM 101: Troubleshooting MongoDB with Percona Monitoring and Management'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pmm-101-troubleshooting-mongodb-with-percona-monitoring-and-management/
  post_id: 23352
source_author:
  name: Mike Grayson
  slug: mike-grayson
  url: https://www.percona.com/blog/author/mike-grayson/
  website: ''
published_at: '2020-10-19T16:06:07'
published_at_gmt: '2020-10-19T16:06:07'
modified_at: '2026-03-26T20:16:22'
modified_at_gmt: '2026-03-26T20:16:22'
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
- Insight for DBAs
- Insight for Developers
- MongoDB
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mongodb
- monitoring
- percona-software
tags:
- MongoDB
- Monitoring
- Percona Monitoring and Management
- QAN
tag_slugs:
- mongodb
- monitoring
- percona-monitoring-and-management
- qan
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-MongoDB-with-Percona-Monitoring-and-Management.png
image_count: 12
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PMM 101: Troubleshooting MongoDB with Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/pmm-101-troubleshooting-mongodb-with-percona-monitoring-and-management/)

Auteur source: [Mike Grayson](https://www.percona.com/blog/author/mike-grayson/)

Publication: 2020-10-19T16:06:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Monitoring and Management (PMM) is an open-source tool developed by Percona that allows you to monitor and manage your MongoDB, MySQL, and PostgreSQL databases. This blog will give you an overview of troubleshooting your MongoDB deployments with PMM. Let’s start with a basic understanding of the architecture of PMM. PMM has two main architectural … Continued

## Structure detectee

- H2: Query Analytics
- H2: Metrics Monitor
- H3: Overall System Performance View
- H3: WiredTiger Metrics
- H3: Database Metrics
- H3: Node Overview Metrics
- H3: Takeaways

## Images et graphiques reperes

- featured / image: [PMM 101: Troubleshooting MongoDB with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-MongoDB-with-Percona-Monitoring-and-Management.png)
- content / image: [Troubleshooting MongoDB with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-MongoDB-with-Percona-Monitoring-and-Management-300x168.png)
- content / image: [Percona Monitoring and Management query analytics](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-1.31.19-PM-scaled.png)
- content / image: [Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-1.33.25-PM-726x1024.png)
- content / image: [Screen-Shot-2020-10-16-at-1.36.33-PM-614x1024.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-1.36.33-PM-614x1024.png)
- content / image: [Screen-Shot-2020-10-16-at-2.01.27-PM-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.01.27-PM-scaled.png)
- content / image: [Percona Monitoring and Management system overview](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.01.36-PM-scaled.png)
- content / image: [Percona Monitoring and Management wiredtiger](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.05.53-PM-1-1024x255.png)
- content / image: [Screen-Shot-2020-10-16-at-2.10.00-PM-1024x240.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.10.00-PM-1024x240.png)
- content / image: [WiredTiger Cache Activity](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.13.31-PM-1024x239.png)
- content / image: [Percona Monitoring and Management database metrics](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.16.47-PM-1024x336.png)
- content / image: [Node Overview Metrics](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-10-16-at-2.18.42-PM-scaled.png)

## Auteur source

Mike is a database engineer who focuses on MongoDB for the Percona Managed Services Team. He helps keep our Managed Services customers MongoDB databases available and performant. He is AWS and Azure certified.
