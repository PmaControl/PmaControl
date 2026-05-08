---
title: Using Blue/Green Deployment For (near) Zero-Downtime Primary Key Updates in RDS MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-blue-green-deployment-for-near-zero-downtime-primary-key-updates-in-rds-mysql/
  post_id: 29228
source_author:
  name: Roberto De Bem
  slug: roberto-garciadebem
  url: https://www.percona.com/blog/author/roberto-garciadebem/
  website: ''
published_at: '2025-01-22T14:51:31'
published_at_gmt: '2025-01-22T14:51:31'
modified_at: '2026-03-26T20:25:46'
modified_at_gmt: '2026-03-26T20:25:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Amazon Aurora
- AWS RDS
- Blue/Green
- cloud
- High Availability
- insight for DBAs
- MySQL
- mysql-and-variants
tag_slugs:
- amazon-aurora
- aws-rds
- blue-green
- cloud
- high-availability
- insight-for-dbas
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Blue-Green-Deployment-RDS-MySQL.jpg
image_count: 11
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Blue/Green Deployment For (near) Zero-Downtime Primary Key Updates in RDS MySQL

Source: [Percona Blog](https://www.percona.com/blog/using-blue-green-deployment-for-near-zero-downtime-primary-key-updates-in-rds-mysql/)

Auteur source: [Roberto De Bem](https://www.percona.com/blog/author/roberto-garciadebem/)

Publication: 2025-01-22T14:51:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Large tables can pose challenges for many operations when working with a database. Occasionally, we may need to modify the table definition. Since RDS replication does not use asynchronous for its replication, the typical switchover procedure is not feasible. However, the Blue/Green feature of RDS utilizes asynchronous replication, which allows us to update the table … Continued

## Structure detectee

- H3: Conclusion
- H3: FAQ
- H3: Is there any rollback?
- H3: Can I do MySQL upgrades with it?
- H3: What happens if any error on the replica happens?

## Images et graphiques reperes

- featured / image: [Using Blue/Green Deployment For (near) Zero-Downtime Primary Key Updates in RDS MySQL](https://www.percona.com/wp-content/uploads/2026/03/Blue-Green-Deployment-RDS-MySQL.jpg)
- content / image: [Screenshot-2024-12-11-at-16.40.52.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-16.40.52.png)
- content / image: [Screenshot-2024-12-11-at-16.41.05.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-16.41.05.png)
- content / image: [Screenshot-2024-12-11-at-16.41.44.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-16.41.44.png)
- content / image: [Screenshot-2024-12-11-at-16.42.17.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-16.42.17.png)
- content / image: [Screenshot-2024-12-11-at-17.18.39.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-17.18.39.png)
- content / image: [Screenshot-2024-12-11-at-17.28.33.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-17.28.33.png)
- content / image: [Screenshot-2024-12-26-at-16.27.25-1.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-26-at-16.27.25-1.png)
- content / image: [Screenshot-2024-12-11-at-17.29.43.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-17.29.43.png)
- content / image: [Screenshot-2024-12-11-at-17.32.19.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-12-11-at-17.32.19.png)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)
