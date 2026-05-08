---
title: 'Keepalived for Source Failover: Percona XtraDB Cluster to Percona Server for MySQL'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/keepalived-for-source-failover-percona-xtradb-cluster-to-percona-server-for-mysql/
  post_id: 27742
source_author:
  name: Totel
  slug: aristotle-po
  url: https://www.percona.com/blog/author/aristotle-po/
  website: ''
published_at: '2023-12-08T15:06:45'
published_at_gmt: '2023-12-08T15:06:45'
modified_at: '2026-03-26T20:26:54'
modified_at_gmt: '2026-03-26T20:26:54'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Server for MySQL
tag_slugs:
- mysql
- mysql-and-variants
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Keepalived-for-Source-Failover-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Keepalived for Source Failover: Percona XtraDB Cluster to Percona Server for MySQL

Source: [Percona Blog](https://www.percona.com/blog/keepalived-for-source-failover-percona-xtradb-cluster-to-percona-server-for-mysql/)

Auteur source: [Totel](https://www.percona.com/blog/author/aristotle-po/)

Publication: 2023-12-08T15:06:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this article, we will demonstrate how to achieve asynchronous replication automatic source failover when our replica is a Percona Server for MySQL (PS) and the source is a Percona XtraDB Cluster (PXC) cluster, using virtual IP (VIP) managed by Keepalived. Let us consider our architecture below with async replication from PXC to Percona Server … Continued

## Structure detectee

- H2: Why not use the below MySQL built-in functionality instead of Keepalived?
- H2: Process
- H3: Assumptions:
- H3: Prerequisites:
- H3: Steps:

## Images et graphiques reperes

- featured / image: [Keepalived for Source Failover: Percona XtraDB Cluster to Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Keepalived-for-Source-Failover-MySQL.jpg)

## Auteur source

Supports MySQL with previous experience in Oracle and PostgreSQL databases. Likes Bash and SQL scripting. Uses Ubuntu for workstation and RHEL derivatives for VM/Container. Hobbies are gardening and pets(dogs, chickens and ducks).
