---
title: Point-In-Time Recovery in Percona Operator for MySQL Based on Percona XtraDB Cluster – Architecture Decisions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/point-in-time-recovery-in-kubernetes-operator-for-percona-xtradb-cluster-architecture-decisions/
  post_id: 23979
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2021-02-24T16:15:52'
published_at_gmt: '2021-02-24T16:15:52'
modified_at: '2026-03-23T18:18:41'
modified_at_gmt: '2026-03-23T18:18:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- XtraBackup
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Cloud
- Monitoring
- MySQL
- Percona Software
category_slugs:
- cloud
- monitoring
- mysql
- percona-software
tags:
- cloud
- containers
- DBaaS
- Kubernetes
- Monitoring
- mysql-and-variants
- Percona Software
- Percona XtraDB Cluster
tag_slugs:
- cloud
- containers
- dbaas
- kubernetes
- monitoring
- mysql-and-variants
- percona-software
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Point-In-Time-Recovery-in-Kubernetes-Operator.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Point-In-Time Recovery in Percona Operator for MySQL Based on Percona XtraDB Cluster – Architecture Decisions

Source: [Percona Blog](https://www.percona.com/blog/point-in-time-recovery-in-kubernetes-operator-for-percona-xtradb-cluster-architecture-decisions/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2021-02-24T16:15:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Point-In-Time Recovery (PITR) for MySQL databases is an important feature that is essential and covers common use cases, like a recovery to the latest possible transaction or roll-back the database to a specific date before some bad query was executed. Percona Operator for MySQL based on Percona XtraDB Cluster (PXC) added support for PITR in version … Continued

## Structure detectee

- H2: Architecture Decisions
- H3: Store Binary Logs on Object Storage
- H3: Use Global Transaction ID
- H3: User-Defined Functions
- H3: Find the node with the oldest binary log
- H3: Storageless binlog uploader
- H3: Binlog upload delay
- H3: Recovery
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Point-In-Time Recovery in Percona Operator for MySQL Based on Percona XtraDB Cluster – Architecture Decisions](https://www.percona.com/wp-content/uploads/2026/03/Point-In-Time-Recovery-in-Kubernetes-Operator.png)
- content / image: [Point-In-Time Recovery in Kubernetes Operator](https://www.percona.com/wp-content/uploads/2026/03/Point-In-Time-Recovery-in-Kubernetes-Operator-300x157.png)
- content / image: [pitr-gtid-1024x742.png](https://www.percona.com/wp-content/uploads/2026/03/pitr-gtid-1024x742.png)
- content / image: [binlog uploader pod](https://www.percona.com/wp-content/uploads/2026/03/object-storage.png)
- content / image: [pitr-pipe-1024x564.png](https://www.percona.com/wp-content/uploads/2026/03/pitr-pipe-1024x564.png)
- content / image: [point in time recovery](https://www.percona.com/wp-content/uploads/2026/03/recovery-binlog.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
