---
title: Multi-Valued INSERTs, AUTO_INCREMENT & Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multi-valued-inserts-auto_increment-and-percona-xtradb-cluster/
  post_id: 8529
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2014-09-09T14:38:39'
published_at_gmt: '2014-09-09T14:38:39'
modified_at: '2026-05-04T22:26:41'
modified_at_gmt: '2026-05-04T22:26:41'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- auto_increment
- autoincrement
- Ernie Souhrada
- Multi-Valued INSERTs
- Percona XtraDB Cluster
- pxc
- Replication
tag_slugs:
- auto_increment
- autoincrement
- ernie-souhrada
- multi-valued-inserts
- percona-xtradb-cluster
- pxc
- replication
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multi-Valued INSERTs, AUTO_INCREMENT & Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/multi-valued-inserts-auto_increment-and-percona-xtradb-cluster/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2014-09-09T14:38:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A common migration path from standalone MySQL/Percona Server to a Percona XtraDB Cluster (PXC) environment involves some measure of time where one node in the new cluster has been configured as a slave of the production master that the cluster is slated to replace. In this way, the new cluster acts as a slave of … Continued

## Structure detectee

- H4: binlog_format
- H4: wsrep_auto_increment_control
- H4: Fixing it when it’s broken
- H4: The tl;dr version

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
