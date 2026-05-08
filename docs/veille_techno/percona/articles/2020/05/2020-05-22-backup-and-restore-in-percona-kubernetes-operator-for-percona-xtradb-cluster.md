---
title: Backup and Restore in Percona Operator for MySQL based on Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backup-and-restore-in-percona-kubernetes-operator-for-percona-xtradb-cluster/
  post_id: 22394
source_author:
  name: Stephen Thorn
  slug: stephen-thorn
  url: https://www.percona.com/blog/author/stephen-thorn/
  website: ''
published_at: '2020-05-22T13:46:39'
published_at_gmt: '2020-05-22T13:46:39'
modified_at: '2026-05-05T17:25:00'
modified_at_gmt: '2026-05-05T17:25:00'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Cloud
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- cloud
- insight-for-dbas
- mysql
- percona-software
tags:
- insight for DBAs
- Kubernetes
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- insight-for-dbas
- kubernetes
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Backup-and-Restore-in-Percona-Kubernetes-Operator-for-Percona-XtraDB-Cluster.png
image_count: 11
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Backup and Restore in Percona Operator for MySQL based on Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/backup-and-restore-in-percona-kubernetes-operator-for-percona-xtradb-cluster/)

Auteur source: [Stephen Thorn](https://www.percona.com/blog/author/stephen-thorn/)

Publication: 2020-05-22T13:46:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Database backups are a fundamental requirement in almost every implementation, no matter the size of the company or the nature of the application. Taking a backup should be a simple task that can be automated to ensure it’s done consistently and on schedule. Percona has an enterprise-grade backup tool, Percona XtraBackup, that can be used … Continued

## Structure detectee

- H2: Backup Types
- H2: Persistent Volume Backups
- H2: Object Storage Backups (S3)
- H2: S3 Backup Configuration
- H4: deploy/backup-s3.yaml
- H4: deploy/cr.yaml
- H4: deploy/backup/backup.yaml
- H2: On-Demand S3 Backup and Restore

## Images et graphiques reperes

- featured / image: [Backup and Restore in Percona Operator for MySQL based on Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Backup-and-Restore-in-Percona-Kubernetes-Operator-for-Percona-XtraDB-Cluster.png)
- content / image: [Backup and Restore in Percona Operator for MySQL based on Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Backup-and-Restore-in-Percona-Kubernetes-Operator-for-Percona-XtraDB-Cluster-300x168.png)
- content / image: [Screen-Shot-2020-05-18-at-10.10.19-AM-300x229.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-05-18-at-10.10.19-AM-300x229.png)
- content / image: [Screen-Shot-2020-05-15-at-5.18.15-AM-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-05-15-at-5.18.15-AM-scaled.png)
- content / image: [Screen-Shot-2020-05-15-at-5.51.42-AM-1024x284.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-05-15-at-5.51.42-AM-1024x284.png)
- content / image: [Screen-Shot-2020-05-15-at-5.53.49-AM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-05-15-at-5.53.49-AM.png)
- content / image: [Screen-Shot-2020-05-15-at-11.31.26-AM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-05-15-at-11.31.26-AM.png)
- content / image: [Picture6.png](https://www.percona.com/wp-content/uploads/2026/03/Picture6.png)
- content / image: [Picture7.png](https://www.percona.com/wp-content/uploads/2026/03/Picture7.png)
- content / image: [Screen-Shot-2020-05-17-at-7.48.06-AM-300x168.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-05-17-at-7.48.06-AM-300x168.png)
- content / image: [Picture8.png](https://www.percona.com/wp-content/uploads/2026/03/Picture8.png)

## Auteur source

Stephen graduated from the U.S. Naval Academy with a degree in Information Technology and served 5 years as an Infantry Officer in the U.S. Marine Corps. After leaving active duty in the Marine Corps he worked for a robotics company for 2 years. Stephen joined Percona in 2019 as a Solutions Engineer and supports the sales and customer success teams by providing technical assistance in explaining Percona's software and services. Stephen is an AWS Solutions Architect Associate, Google Cloud Platform Associate Cloud Engineer, Certified Kubernetes Administrator, and a Professional Scrum Master.
