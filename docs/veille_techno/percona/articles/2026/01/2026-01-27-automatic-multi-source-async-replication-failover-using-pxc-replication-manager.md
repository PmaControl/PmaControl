---
title: Automatic "Multi-Source" Async Replication Failover Using PXC Replication Manager
source:
  name: Percona Blog
  url: https://www.percona.com/blog/automatic-multi-source-async-replication-failover-using-pxc-replication-manager/
  post_id: 35635
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2026-01-27T14:04:52'
published_at_gmt: '2026-01-27T14:04:52'
modified_at: '2026-04-17T14:59:05'
modified_at_gmt: '2026-04-17T14:59:05'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MariaDB
- MySQL
- Storage Engine
category_slugs:
- insight-for-dbas
- insight-for-developers
- mariadb
- mysql
- storage-engine
tags:
- asynchronous MySQL replication
- automatic failover
- High Availability
- InnoDB
- MariaDB
- MySQL
- Percona Server for MySQL
- Percona XtraDB Cluster
- Replication
tag_slugs:
- asynchronous-mysql-replication
- automatic-failover
- high-availability
- innodb
- mariadb
- mysql
- percona-server
- percona-xtradb-cluster
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Automatic-22Multi-Source22-Async-Replication-Failover-Using-PXC-Replication-Manager.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Automatic "Multi-Source" Async Replication Failover Using PXC Replication Manager

Source: [Percona Blog](https://www.percona.com/blog/automatic-multi-source-async-replication-failover-using-pxc-replication-manager/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2026-01-27T14:04:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The replication manager script can be particularly useful in complex PXC/Galera topologies that require Async/Multi-source replication. This will ease the auto source and replica failover to ensure all replication channels are healthy and in sync. If certain nodes shouldn’t be part of a async/multi-source replication, we can disable the replication manager script there to tightly controlled the flow. Alternatively, node participation can be controlled by adjusting the weights in the percona.weight table, allowing replication behavior to be managed more precisely.

## Structure detectee

- H2: Topology:
- H2: Async Replication syncing flow:
- H2: PXC/Async configurations
- H2: Replication Manager configuration
- H2: Asynchronous Replication Setup
- H2: Replication Manager Cron Setup
- H2: Testing Source Failover For Multi-Source Channel
- H2: Testing Replica Failover For Multi-Source channel
- H2: Important consideration:
- H2: Summary

## Images et graphiques reperes

- featured / image: [Automatic "Multi-Source" Async Replication Failover Using PXC Replication Manager](https://www.percona.com/wp-content/uploads/2026/03/Automatic-22Multi-Source22-Async-Replication-Failover-Using-PXC-Replication-Manager.jpg)
- content / image: [Async Multi-Source](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2026-01-24-at-11.39.43-AM-scaled-1.png)
  Caption: Async Multi-Source topology

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
