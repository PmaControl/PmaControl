---
title: Optimizing Percona XtraDB Cluster for write hotspots
source:
  name: Percona Blog
  url: https://www.percona.com/blog/optimizing-percona-xtradb-cluster-write-hotspots/
  post_id: 9295
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-06-03T13:50:27'
published_at_gmt: '2015-06-03T13:50:27'
modified_at: '2026-04-28T22:20:37'
modified_at_gmt: '2026-04-28T22:20:37'
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
- galera
- global counter
- InnoDB
- Percona XtraDB Cluster
- Primary
- pxc
- Stephane Combaudon
- write hotspots
tag_slugs:
- galera
- global-counter
- innodb
- percona-xtradb-cluster
- primary
- pxc
- stephane-combaudon
- write-hotspots
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/step1.gif
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Optimizing Percona XtraDB Cluster for write hotspots

Source: [Percona Blog](https://www.percona.com/blog/optimizing-percona-xtradb-cluster-write-hotspots/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-06-03T13:50:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some applications have a heavy write workload on a few records – for instance when incrementing a global counter: this is called a write hotspot. Because you cannot update the same row simultaneously from multiple threads, this can lead to performance degradation. When using Percona XtraDB Cluster (PXC), some users try to solve this specific … Continued

## Structure detectee

- H2: Simultaneous writes on a standalone InnoDB server
- H2: Simultaneous writes on multiple nodes (PXC)
- H2: “In-flight” transactions and certification test
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Optimizing Percona XtraDB Cluster for write hotspots](https://www.percona.com/wp-content/uploads/2026/03/step1.gif)
- content / image: [step2](https://www.percona.com/wp-content/uploads/2026/03/step2.gif)
- content / image: [step3](https://www.percona.com/wp-content/uploads/2026/03/step3.gif)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
