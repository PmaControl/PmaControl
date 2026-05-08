---
title: 'Percona XtraDB Cluster 8.0 (experimental release) : SST Improvements'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-8-0-experimental-release-sst-improvements/
  post_id: 20964
source_author:
  name: Kenn Takara
  slug: kenn-takara
  url: https://www.percona.com/blog/author/kenn-takara/
  website: ''
published_at: '2019-10-04T12:15:54'
published_at_gmt: '2019-10-04T12:15:54'
modified_at: '2026-03-20T22:49:36'
modified_at_gmt: '2026-03-20T22:49:36'
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
- MySQL
- Open Source
- Percona XtraDB Cluster
- pxc
tag_slugs:
- mysql
- open-source
- percona-xtradb-cluster
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/xtradb-sst-improvements.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster 8.0 (experimental release) : SST Improvements

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-8-0-experimental-release-sst-improvements/)

Auteur source: [Kenn Takara](https://www.percona.com/blog/author/kenn-takara/)

Publication: 2019-10-04T12:15:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Starting with the experimental release of Percona XtraDB Cluster 8.0, we have made changes to the SST process to make the process more robust and easier to use. mysqldump and rsync are no longer supported SST methods. Support for mysqldump was deprecated starting with PXC 5.7 and has now been completely removed. MySQL 8.0 introduced … Continued

## Structure detectee

- H4: mysqldump and rsync are no longer supported SST methods.
- H4: A separate Percona XtraBackup installation is no longer required.
- H4: SST logging now uses MySQL error logging
- H4: The wsrep_sst_auth variable has been removed.
- H4: PXC SST auto-upgrade

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster 8.0 (experimental release) : SST Improvements](https://www.percona.com/wp-content/uploads/2026/03/xtradb-sst-improvements.jpg)
- content / image: [xtradb sst improvements](https://www.percona.com/wp-content/uploads/2026/03/xtradb-sst-improvements-300x168.jpg)
