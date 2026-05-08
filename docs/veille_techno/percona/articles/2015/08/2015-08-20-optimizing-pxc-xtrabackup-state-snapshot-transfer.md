---
title: Optimizing PXC Xtrabackup State Snapshot Transfer
source:
  name: Percona Blog
  url: https://www.percona.com/blog/optimizing-pxc-xtrabackup-state-snapshot-transfer/
  post_id: 9922
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-08-20T15:38:09'
published_at_gmt: '2015-08-20T15:38:09'
modified_at: '2026-05-05T22:30:07'
modified_at_gmt: '2026-05-05T22:30:07'
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
- tag:xtrabackup:153
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- galera
- Jay Janssen
- MySQL
- Percona XtraDB Cluster
- Primary
- pxc
- SST
- State Snapshot Transfer
- xtrabackup
tag_slugs:
- galera
- jay-janssen
- mysql
- percona-xtradb-cluster
- primary
- pxc
- sst
- state-snapshot-transfer
- xtrabackup
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Optimizing PXC Xtrabackup State Snapshot Transfer

Source: [Percona Blog](https://www.percona.com/blog/optimizing-pxc-xtrabackup-state-snapshot-transfer/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-08-20T15:38:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

State Snapshot Transfer (SST) at a glance PXC uses a protocol called State Snapshot Transfer to provision a node joining an existing cluster with all the data it needs to synchronize. This is analogous to cloning a slave in asynchronous replication: you take a full backup of one node and copy it to the new … Continued

## Structure detectee

- H2: State Snapshot Transfer (SST) at a glance
- H2: The Environment
- H2: Baseline
- H2: –use-memory
- H2: wsrep_slave_threads
- H2: Compression
- H2: Dedicated donor
- H2: Conclusion

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
