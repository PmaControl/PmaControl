---
title: 'Aurora vs RDS: How to Choose the Right AWS Database Solution'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-should-i-use-amazon-aurora-and-when-should-i-use-rds-mysql/
  post_id: 18976
source_author:
  name: Ananias Tsalouchidis
  slug: ananias-tsalouchidis
  url: https://www.percona.com/blog/author/ananias-tsalouchidis/
  website: ''
published_at: '2023-07-01T13:46:47'
published_at_gmt: '2023-07-01T13:46:47'
modified_at: '2026-03-26T20:29:32'
modified_at_gmt: '2026-03-26T20:29:32'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:proxysql
- search:xtrabackup
categories:
- Cloud
- Insight for DBAs
- MySQL
- ProxySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
- proxysql
tags:
- Amazon Aurora
- Amazon RDS
- AWS
- MySQL
- RDS
- RDS MySQL
tag_slugs:
- amazon-aurora
- amazon-rds
- aws
- mysql
- rds
- rds-mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_icon_of_an_electronic_cloud_orange_sunrise_colo_d9e9f2d4-db38-4a73-982c-622370d50ee7.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Aurora vs RDS: How to Choose the Right AWS Database Solution

Source: [Percona Blog](https://www.percona.com/blog/when-should-i-use-amazon-aurora-and-when-should-i-use-rds-mysql/)

Auteur source: [Ananias Tsalouchidis](https://www.percona.com/blog/author/ananias-tsalouchidis/)

Publication: 2023-07-01T13:46:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in July 2018 and was updated in July 2023. Now that Database-as-a-service (DBaaS) is in high demand, there are multiple questions regarding AWS services that cannot always be answered easily: When should I use Aurora and when should I use RDS MySQL? What are the differences between Aurora and RDS? … Continued

## Structure detectee

- H2: Understanding DBaaS
- H2: What is Amazon Aurora?
- H3: Aurora Features
- H2: What is Amazon RDS?
- H3: RDS Features
- H2: Exploring the Similarities Between Aurora vs. RDS
- H3: Aurora vs. RDS: Comparing the key differences
- H3: Performance considerations
- H3: Capacity Planning
- H3: Replication
- H3: Architecture
- H3: Monitoring
- H3: Costs
- H2: Support for RDS Services
- H2: In Summary: Should I Use Aurora or RDS?
- H2: Looking for a Database Migration Solution? Percona can Help
- H2: FAQ
- H3: What is the difference between Amazon Aurora vs Amazon RDS?
- H3: How does the performance of Amazon Aurora compare to Amazon RDS?
- H3: How does high availability work in Amazon Aurora vs Amazon RDS?
- H3: Can I use read replicas in both Amazon Aurora and Amazon RDS?
- H3: What are the cost differences between Aurora vs RDS?
- H2: Want to learn more?

## Images et graphiques reperes

- featured / image: [Aurora vs RDS: How to Choose the Right AWS Database Solution](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_icon_of_an_electronic_cloud_orange_sunrise_colo_d9e9f2d4-db38-4a73-982c-622370d50ee7.png)
- content / image: [Need help with your cloud migration?](https://www.percona.com/wp-content/uploads/2026/03/fb6aecba-f02e-455a-acef-1d61a9464c31.png)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

Ananias is a Principal MySQL DBA who joined Percona on May 2017. He holds a BSc and a MSc in computer science and has a 10+ years working experience as a systems and databases administrator. He loves databases and perl scripting. He has worked for big companies and academic institutions and has also been involved into numerous research programs.
