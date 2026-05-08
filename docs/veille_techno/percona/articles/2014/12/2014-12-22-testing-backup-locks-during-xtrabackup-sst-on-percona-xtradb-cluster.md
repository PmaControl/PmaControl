---
title: Testing backup locks during Xtrabackup SST on Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/testing-backup-locks-during-xtrabackup-sst-on-percona-xtradb-cluster/
  post_id: 8860
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2014-12-22T13:00:33'
published_at_gmt: '2014-12-22T13:00:33'
modified_at: '2026-05-04T22:31:06'
modified_at_gmt: '2026-05-04T22:31:06'
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
- Amazon Web Services
- AWS
- backup locks
- galera
- InnoDB
- Jay Janssen
- MySQL
- Percona Server for MySQL
- Percona XtraDB Cluster
- Primary
- Xtrabackup SST
tag_slugs:
- amazon-web-services
- aws
- backup-locks
- galera
- innodb
- jay-janssen
- mysql
- percona-server
- percona-xtradb-cluster
- primary
- xtrabackup-sst
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Testing backup locks during Xtrabackup SST on Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/testing-backup-locks-during-xtrabackup-sst-on-percona-xtradb-cluster/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2014-12-22T13:00:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Background on Backup Locks I was very excited to see Backup locks support in release notes for the latest Percona XtraDB Cluster 5.6.21 release. For those who are not aware, backup locks offer an alternative to FLUSH TABLES WITH READ LOCK (FTWRL) in Xtrabackup. While Xtrabackup can hot-copy Innodb, everything else in MySQL must be locked (usually … Continued

## Structure detectee

- H2: Background on Backup Locks
- H2: What this means for Percona XtraDB Cluster (PXC)
- H2: Seeing it in action
- H2: Compared to Percona XtraDB Cluster 5.5
- H2: Implications

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
