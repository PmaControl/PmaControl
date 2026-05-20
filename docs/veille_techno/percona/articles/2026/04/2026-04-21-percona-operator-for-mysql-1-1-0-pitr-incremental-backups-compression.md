---
title: 'Percona Operator for MySQL 1.1.0: PITR, Incremental Backups, and Compression'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-operator-for-mysql-1-1-0-pitr-incremental-backups-compression/
  post_id: 43824
source_author:
  name: Slava Sarzhan
  slug: slava-sarzhan
  url: https://www.percona.com/blog/author/slava-sarzhan/
  website: ''
published_at: '2026-04-21T13:21:35'
published_at_gmt: '2026-04-21T13:21:35'
modified_at: '2026-04-21T13:21:35'
modified_at_gmt: '2026-04-21T13:21:35'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:xtrabackup
categories:
- Cloud
- MySQL
- Open Source
- Operator
- Percona Software
category_slugs:
- cloud
- mysql
- open-source
- operator-2
- percona-software
tags:
- Backups
- 'Cloud Category: Cloud'
- Kubernetes
- MySQL
- Open Source
- Percona Operator for MySQL
- point in time recovery
tag_slugs:
- backups
- cloud-category-cloud
- kubernetes
- mysql
- open-source
- percona-operator-for-mysql
- point-in-time-recovery
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/featured-2.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Operator for MySQL 1.1.0: PITR, Incremental Backups, and Compression

Source: [Percona Blog](https://www.percona.com/blog/percona-operator-for-mysql-1-1-0-pitr-incremental-backups-compression/)

Auteur source: [Slava Sarzhan](https://www.percona.com/blog/author/slava-sarzhan/)

Publication: 2026-04-21T13:21:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The latest release of the Percona Operator for MySQL, 1.1.0, is here. It brings point-in-time recovery, incremental backups, zstd backup compression, configurable asynchronous replication retries, and a set of stability fixes. This post walks through the highlights and how they help your MySQL deployments on Kubernetes. Percona Operator for MySQL 1.1.0 Running stateful databases … Continued

## Structure detectee

- H2: Percona Operator for MySQL 1.1.0
- H2: Point-in-Time Recovery (Tech Preview)
- H2: Incremental Backups (Tech Preview)
- H2: Backup Compression with zstd
- H2: Asynchronous Replication Retry Configuration
- H2: Other Improvements
- H2: Conclusion
- H2: Try It Out

## Images et graphiques reperes

- featured / image: [Percona Operator for MySQL 1.1.0: PITR, Incremental Backups, and Compression](https://www.percona.com/wp-content/uploads/2026/04/featured-2.png)
- content / image: [hero-1024x375.png](https://www.percona.com/wp-content/uploads/2026/04/hero-1024x375.png)
- content / image: [pitr-1024x342.png](https://www.percona.com/wp-content/uploads/2026/04/pitr-1024x342.png)
- content / image: [incremental-1024x256.png](https://www.percona.com/wp-content/uploads/2026/04/incremental-1024x256.png)
- content / image: [compression-1024x256.png](https://www.percona.com/wp-content/uploads/2026/04/compression-1024x256.png)
- content / image: [async-retry-1024x256.png](https://www.percona.com/wp-content/uploads/2026/04/async-retry-1024x256.png)

## Auteur source

Head of Cloud Native Engineering from Lviv, Ukraine, with a passion for building smarter, more efficient Kubernetes solutions. I joined Percona in January 2019, starting as a build/release engineer managing Jenkins farms and automating testing and release processes. Soon after, I moved to the cloud team, creating testing infrastructure and developing new features for Kubernetes operators. Over the years, I’ve grown into leadership, guiding distributed engineering teams to deliver scalable, production-ready solutions while fostering a culture of ownership, clarity, and continuous improvement. Kubernetes isn’t just a tool for me, it’s a playground for innovation. I focus on solving complex problems, improving systems, and sharing insights with the community.
