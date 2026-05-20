---
title: Using PXC Replication Manager to Auto Manage Both Source and Replica Failover in Galera-Based Environments
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-pxc-replication-manager-to-auto-manage-both-source-and-replica-failover-in-galera-based-environments/
  post_id: 35546
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2026-01-12T13:41:22'
published_at_gmt: '2026-01-12T13:41:22'
modified_at: '2026-03-26T20:25:06'
modified_at_gmt: '2026-03-26T20:25:06'
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
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- async replication
- asynchronous MySQL replication
- auto-failover
- Galera Cluster
- MariaDB
- MySQL
- MySQL 8.4
- mysqlfailover
- pxc
tag_slugs:
- async-replication
- asynchronous-mysql-replication
- auto-failover
- galera-cluster
- mariadb
- mysql
- mysql-8-4
- mysqlfailover
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Using-PXC-Replication-Manager-to-Auto-Manage-Both-Source-and-Replica-Failover-in-Galera-Based-Environments.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using PXC Replication Manager to Auto Manage Both Source and Replica Failover in Galera-Based Environments

Source: [Percona Blog](https://www.percona.com/blog/using-pxc-replication-manager-to-auto-manage-both-source-and-replica-failover-in-galera-based-environments/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2026-01-12T13:41:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will be discussing the PXC Replication Manager script/tool which basically facilitates both source and replica failover when working with multiple PXC clusters, across different DC/Networks connected via asynchronous replication mechanism. Such topologies emerge from requirements like database version upgrades, reporting or streaming for applications, separate disaster recovery or backup solutions, … Continued

## Structure detectee

- H2: Topology
- H2: Minimal configuration for PXC/Galera and async replication
- H2: Bootstrap/PXC Ready
- H2: Manual Asynchronous Replication setup
- H2: PXC Rep lication Manager Setup/Configuration
- H1: Testing Replica Failover
- H2: Testing Source Failover
- H2: Important considerations
- H2: References
- H2: Final Thought

## Images et graphiques reperes

- featured / image: [Using PXC Replication Manager to Auto Manage Both Source and Replica Failover in Galera-Based Environments](https://www.percona.com/wp-content/uploads/2026/03/Using-PXC-Replication-Manager-to-Auto-Manage-Both-Source-and-Replica-Failover-in-Galera-Based-Environments.jpg)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
