---
title: 'Amazon RDS MySQL Minor Upgrades: Not So Fast!'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/amazon-rds-mysql-minor-upgrades-not-so-fast/
  post_id: 21443
source_author:
  name: Alok Pathak
  slug: alok-pathak
  url: https://www.percona.com/blog/author/alok-pathak/
  website: ''
published_at: '2020-01-14T13:56:47'
published_at_gmt: '2020-01-14T13:56:47'
modified_at: '2026-04-29T14:45:36'
modified_at_gmt: '2026-04-29T14:45:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- cloud
- DBA
- MySQL
tag_slugs:
- cloud
- dba
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/aws-rds-mysql-minor-upgrades.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Amazon RDS MySQL Minor Upgrades: Not So Fast!

Source: [Percona Blog](https://www.percona.com/blog/amazon-rds-mysql-minor-upgrades-not-so-fast/)

Auteur source: [Alok Pathak](https://www.percona.com/blog/author/alok-pathak/)

Publication: 2020-01-14T13:56:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The promise of DBaaS like RDS is to reduce operational overhead (among other things) and one of the stellar cases is upgrades (major and minor). The suggested procedure involves just a couple of steps. For example, using AWS Console, you can enable “Auto minor upgrade” or modify the DB instance and schedule the upgrade to … Continued

## Structure detectee

- H2: The Problem
- H3: Why?
- H2: Monitoring the Change Buffer Merges
- H4: Error log:
- H4: InnoDB status:
- H3: Percona Monitoring and Management
- H3: Does Multi-AZ Save Me?
- H2: Speeding Things Up
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Amazon RDS MySQL Minor Upgrades: Not So Fast!](https://www.percona.com/wp-content/uploads/2026/03/aws-rds-mysql-minor-upgrades.png)
- content / image: [aws rds mysql minor upgrades](https://www.percona.com/wp-content/uploads/2026/03/aws-rds-mysql-minor-upgrades-300x168.png)
- content / image: [Change_Buffer.png](https://www.percona.com/wp-content/uploads/2026/03/Change_Buffer.png)

## Auteur source

Alok joined Percona in May 2014 as a Consultant. He is currently working as Senior MySQL DBA in Managed Services. He has previously worked as MySQL DBA for various product and service based companies in India. His career interests include Performance Tuning, Query Optimization, High Availability, Database Clustering and Automation. When not working, he enjoys listening to music and watching sports.
