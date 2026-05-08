---
title: 'Percona XtraDB Cluster: Setting up a simple cluster'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-setting-simple-cluster/
  post_id: 7374
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2013-09-23T10:00:44'
published_at_gmt: '2013-09-23T10:00:44'
modified_at: '2026-05-04T22:08:11'
modified_at_gmt: '2026-05-04T22:08:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-toolkit
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- galera
- Percona XtraDB Cluster
- pxc
tag_slugs:
- galera
- percona-xtradb-cluster
- pxc
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster: Setting up a simple cluster

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-setting-simple-cluster/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2013-09-23T10:00:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster (PXC) is different enough from async replication that it can be a bit of a puzzle how to do things the Galera way. This post will attempt to illustrate the basics of setting up 2 node PXC cluster from scratch. Requirements Two servers (could be VMs) that can talk to each other. … Continued

## Structure detectee

- H2: Requirements
- H2: Install the software
- H2: Disable IPtables and SElinux
- H2: Configure the cluster nodes
- H2: Bootstrap node1
- H2: Prep for SST
- H2: Start node2
- H2: Summary

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
