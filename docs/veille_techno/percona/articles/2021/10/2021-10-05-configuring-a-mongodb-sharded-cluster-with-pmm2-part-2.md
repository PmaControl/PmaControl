---
title: Configuring a MongoDB Sharded Cluster with PMM2 – Part 2
source:
  name: Percona Blog
  url: https://www.percona.com/blog/configuring-a-mongodb-sharded-cluster-with-pmm2-part-2/
  post_id: 24901
source_author:
  name: Vinodh Krishnaswamy
  slug: vinodh-krishnaswamy
  url: https://www.percona.com/blog/author/vinodh-krishnaswamy/
  website: ''
published_at: '2021-10-05T11:16:12'
published_at_gmt: '2021-10-05T11:16:12'
modified_at: '2026-03-26T20:15:03'
modified_at_gmt: '2026-03-26T20:15:03'
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
- MongoDB
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- monitoring
- percona-software
tags:
- MongoDB
- Percona Monitoring and Management
- sharding
tag_slugs:
- mongodb
- percona-monitoring-and-management
- sharding
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Configure-MongoDB-Sharded-Cluster.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Configuring a MongoDB Sharded Cluster with PMM2 – Part 2

Source: [Percona Blog](https://www.percona.com/blog/configuring-a-mongodb-sharded-cluster-with-pmm2-part-2/)

Auteur source: [Vinodh Krishnaswamy](https://www.percona.com/blog/author/vinodh-krishnaswamy/)

Publication: 2021-10-05T11:16:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As a DBA, it is important to monitor a database to help us troubleshoot or to understand the health of an instance. Percona Monitoring and Management (PMM v2) is open-source and does a great job in monitoring the databases like MongoDB, MySQL, PostgreSQL, etc. In this blog post, we will see how to configure a … Continued

## Structure detectee

- H2: Prepare DB for Monitoring
- H3: Add PMM Users to the DB
- H3: Enabling Profiler
- H3: Add MongoDB Instance to the pmm-client
- H3: Check the Inventory Service
- H2: From My Test
- H2: PMM Dashboards
- H3: Cluster Summary
- H3: ReplSet Summary:
- H3: MongoDB Instance Overview:
- H3: WiredTiger Details:
- H3: QAN:
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Configuring a MongoDB Sharded Cluster with PMM2 – Part 2](https://www.percona.com/wp-content/uploads/2026/03/Configure-MongoDB-Sharded-Cluster.png)
- content / image: [Configure MongoDB Sharded Cluster](https://www.percona.com/wp-content/uploads/2026/03/Configure-MongoDB-Sharded-Cluster-300x168.png)
- content / image: [Cluster Summary](https://www.percona.com/wp-content/uploads/2026/03/PMM-2-1024x449.png)
- content / image: [ReplSet Summary](https://www.percona.com/wp-content/uploads/2026/03/PMM-3-1024x492.png)
- content / image: [MongoDB Instance Overview](https://www.percona.com/wp-content/uploads/2026/03/PMM-1-2-1024x512.png)
- content / image: [WiredTiger Details](https://www.percona.com/wp-content/uploads/2026/03/PMM-4-1024x526.png)
- content / image: [QAN](https://www.percona.com/wp-content/uploads/2026/03/PMM-5-1024x493.png)

## Auteur source

Vinodh Krishnaswamy is a member of Support Team! Prior to joining Percona, he worked as a MySQL and MongoDB DBA in companies such as iGate, Datavail, and Sify Ltd. He is a trainer and has provided training programs on MySQL and MongoDB. He enjoys writing Shell script and loves driving, reading books, and playing table tennis.
