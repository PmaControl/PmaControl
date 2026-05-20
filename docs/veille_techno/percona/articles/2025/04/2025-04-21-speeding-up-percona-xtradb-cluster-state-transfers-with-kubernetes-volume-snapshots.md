---
title: Speeding Up Percona XtraDB Cluster State Transfers with Kubernetes Volume Snapshots
source:
  name: Percona Blog
  url: https://www.percona.com/blog/speeding-up-percona-xtradb-cluster-state-transfers-with-kubernetes-volume-snapshots/
  post_id: 34821
source_author:
  name: Juan Arruti
  slug: juan-arruti
  url: https://www.percona.com/blog/author/juan-arruti/
  website: ''
published_at: '2025-04-21T14:08:22'
published_at_gmt: '2025-04-21T14:08:22'
modified_at: '2026-03-26T20:25:36'
modified_at_gmt: '2026-03-26T20:25:36'
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
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- cloud
- Kubernetes
- MySQL
- mysql-and-variants
- pxc
tag_slugs:
- cloud
- kubernetes
- mysql
- mysql-and-variants
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-state-transfers.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Speeding Up Percona XtraDB Cluster State Transfers with Kubernetes Volume Snapshots

Source: [Percona Blog](https://www.percona.com/blog/speeding-up-percona-xtradb-cluster-state-transfers-with-kubernetes-volume-snapshots/)

Auteur source: [Juan Arruti](https://www.percona.com/blog/author/juan-arruti/)

Publication: 2025-04-21T14:08:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When using the Percona Operator for MySQL based on Percona XtraDB Cluster (PXC), it’s common to encounter scenarios where cluster nodes request a full State Snapshot Transfer (SST) when rejoining the cluster. One typical scenario where a State Snapshot Transfer (SST) is required is when a node has been offline long enough that the GCache … Continued

## Structure detectee

- H2: SST based on K8s Volume Snapshots:
- H4: Disclaimer:
- H3: Prerequisites:
- H2: Online:
- H2: Offline:
- H2: Conclusion:

## Images et graphiques reperes

- featured / image: [Speeding Up Percona XtraDB Cluster State Transfers with Kubernetes Volume Snapshots](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-state-transfers.jpg)
- content / image: [MySQL performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-7.png)

## Auteur source

Juan Pablo joined Percona in 2016 as a member of Technical Services Team. Before coming to Percona, he worked as DBA in several companies such as IBM, Turner and Oracle.
