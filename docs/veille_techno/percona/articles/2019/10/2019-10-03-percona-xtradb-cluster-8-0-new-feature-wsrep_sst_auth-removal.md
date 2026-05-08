---
title: 'Percona XtraDB Cluster 8.0 New Feature: wsrep_sst_auth Removal'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-8-0-new-feature-wsrep_sst_auth-removal/
  post_id: 21014
source_author:
  name: Kenn Takara
  slug: kenn-takara
  url: https://www.percona.com/blog/author/kenn-takara/
  website: ''
published_at: '2019-10-03T13:05:35'
published_at_gmt: '2019-10-03T13:05:35'
modified_at: '2026-04-27T21:23:34'
modified_at_gmt: '2026-04-27T21:23:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Percona Software
- ProxySQL
category_slugs:
- insight-for-dbas
- mysql
- percona-software
- proxysql
tags:
- cluster
- galera
- Percona Software
- Percona XtraDB Cluster
- pxc
tag_slugs:
- cluster
- galera
- percona-software
- percona-xtradb-cluster
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Experimental-Binary-XtraDB-8.0.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster 8.0 New Feature: wsrep_sst_auth Removal

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-8-0-new-feature-wsrep_sst_auth-removal/)

Auteur source: [Kenn Takara](https://www.percona.com/blog/author/kenn-takara/)

Publication: 2019-10-03T13:05:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The problem In PXC 5.6 and 5.7, when using xtrabackup-v2 as the SST method, the DBA must create a user with the appropriate privileges for use by Percona XtraBackup (PXB). The username and password of this backup user are specified in the wsrep_sst_auth variable. This is a problem because this username and password was being … Continued

## Structure detectee

- H2: The problem
- H2: The PXC 8.0 solution
- H2: New PXC internal user accounts
- H3: mysql.pxc.internal.session
- H3: mysql.pxc.sst.user
- H3: mysql.pxc.sst.role
- H2: Program flow

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster 8.0 New Feature: wsrep_sst_auth Removal](https://www.percona.com/wp-content/uploads/2026/03/Experimental-Binary-XtraDB-8.0.jpg)
- content / image: [Experimental Binary XtraDB 8.0](https://www.percona.com/wp-content/uploads/2026/03/Experimental-Binary-XtraDB-8.0-300x157.jpg)
