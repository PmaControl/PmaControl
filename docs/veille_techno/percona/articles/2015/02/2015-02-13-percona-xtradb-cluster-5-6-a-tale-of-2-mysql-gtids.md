---
title: 'Percona XtraDB Cluster 5.6: a tale of 2 GTIDs'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-5-6-a-tale-of-2-mysql-gtids/
  post_id: 9055
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-02-13T14:27:53'
published_at_gmt: '2015-02-13T14:27:53'
modified_at: '2026-05-04T22:33:49'
modified_at_gmt: '2026-05-04T22:33:49'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- Galera GTID
- MySQL GTID
- Percona XtraDB Cluster
- Primary
- pxc
- Stephane Combaudon
tag_slugs:
- galera-gtid
- mysql-gtid
- percona-xtradb-cluster
- primary
- pxc
- stephane-combaudon
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PXC.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster 5.6: a tale of 2 GTIDs

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-5-6-a-tale-of-2-mysql-gtids/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-02-13T14:27:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Say you have a cluster with 3 nodes using Percona XtraDB Cluster (PXC) 5.6 and one asynchronous replica connected to node1. If asynchronous replication is using GTIDs, moving the replica so that it is connected to node2 is trivial, right? Actually replication can easily break for reasons that may not be obvious at first sight. … Continued

## Structure detectee

- H2: Summary
- H2: Galera GTID vs MySQL GTID
- H2: MySQL GTID generation when writing to the cluster
- H2: How can local transactions show up?
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster 5.6: a tale of 2 GTIDs](https://www.percona.com/wp-content/uploads/2026/03/PXC.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
