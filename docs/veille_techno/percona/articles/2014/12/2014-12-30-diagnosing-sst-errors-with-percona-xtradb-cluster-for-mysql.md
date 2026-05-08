---
title: Diagnosing SST errors with Percona XtraDB Cluster for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/diagnosing-sst-errors-with-percona-xtradb-cluster-for-mysql/
  post_id: 8912
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-12-30T08:00:41'
published_at_gmt: '2014-12-30T08:00:41'
modified_at: '2026-05-04T22:31:33'
modified_at_gmt: '2026-05-04T22:31:33'
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
- galera
- MySQL
- Percona XtraDB Cluster
- Primary
- SST errors
- Stephane Combaudon
tag_slugs:
- galera
- mysql
- percona-xtradb-cluster
- primary
- sst-errors
- stephane-combaudon
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Diagnosing SST errors with Percona XtraDB Cluster for MySQL

Source: [Percona Blog](https://www.percona.com/blog/diagnosing-sst-errors-with-percona-xtradb-cluster-for-mysql/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-12-30T08:00:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

State Snapshot Transfer (SST) is used in Percona XtraDB Cluster (PXC) when a new node joins the cluster or to resync a failed node if Incremental State Transfer (IST) is no longer available. SST is triggered automatically but there is no magic: If it is not configured properly, it will not work and new nodes … Continued

## Structure detectee

- H2: Port for SST is not open
- H2: SST is not correctly configured
- H2: Galera versions do not match
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Diagnosing SST errors with Percona XtraDB Cluster for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
