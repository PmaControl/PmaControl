---
title: Bypassing SST in Percona XtraDB Cluster with incremental backups
source:
  name: Percona Blog
  url: https://www.percona.com/blog/bypassing-sst-pxc-incremental-backups/
  post_id: 9381
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-07-16T09:00:42'
published_at_gmt: '2015-07-16T09:00:42'
modified_at: '2026-04-28T22:21:59'
modified_at_gmt: '2026-04-28T22:21:59'
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
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- binary logs
- galera
- Gcache
- GTID
- incremental backups
- IST
- Jay Janssen
- MySQL
- MySQL backups
- MySQL recovery
- Percona XtraDB Cluster
- Primary
- pxc
- SST
tag_slugs:
- binary-logs
- galera
- gcache
- gtid
- incremental-backups
- ist
- jay-janssen
- mysql
- mysql-backups
- mysql-recovery
- percona-xtradb-cluster
- primary
- pxc
- sst
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Bypassing SST in Percona XtraDB Cluster with incremental backups

Source: [Percona Blog](https://www.percona.com/blog/bypassing-sst-pxc-incremental-backups/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-07-16T09:00:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Beware the SST In Percona XtraDB Cluster (PXC) I often run across users who are fearful of SSTs on their clusters. I’ve always maintained that if you can’t cope with a SST, PXC may not be right for you, but that doesn’t change the fact that SSTs with multiple Terabytes of data can be quite … Continued

## Structure detectee

- H2: Beware the SST
- H2: Percona XtraBackup and Incrementals
- H2: But will it IST?

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
