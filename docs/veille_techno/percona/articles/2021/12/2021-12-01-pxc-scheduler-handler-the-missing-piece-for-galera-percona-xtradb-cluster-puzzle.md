---
title: 'PXC Scheduler Handler: The Missing Piece for Galera/Percona XtraDB Cluster Puzzle'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pxc-scheduler-handler-the-missing-piece-for-galera-percona-xtradb-cluster-puzzle/
  post_id: 24828
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2021-12-01T14:06:28'
published_at_gmt: '2021-12-01T14:06:28'
modified_at: '2026-04-28T15:00:02'
modified_at_gmt: '2026-04-28T15:00:02'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
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
- mysql-and-variants
- Percona XtraDB Cluster
tag_slugs:
- mysql
- mysql-and-variants
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Scheduler-Handler.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PXC Scheduler Handler: The Missing Piece for Galera/Percona XtraDB Cluster Puzzle

Source: [Percona Blog](https://www.percona.com/blog/pxc-scheduler-handler-the-missing-piece-for-galera-percona-xtradb-cluster-puzzle/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2021-12-01T14:06:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Working on a real case scenario in a five node Percona XtraDB Cluster (PXC), we were forced to use wsrep_sync_wait = 1, because the app does reads-after-write and we send reads to all the nodes. We had the idea to leave some nodes in DESYNC mode to reduce the flow control messages during peak load … Continued

## Structure detectee

- H2: Environment
- H2: DESYNC Test
- H2: Consistency Test
- H2: Cluster Behavior During Load Increases
- H2: Architecture
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [PXC Scheduler Handler: The Missing Piece for Galera/Percona XtraDB Cluster Puzzle](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Scheduler-Handler.png)
- content / image: [Percona XtraDB Cluster Scheduler Handler](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-Scheduler-Handler-300x157.png)
- content / image: [DESYNC Test](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL_2021_07_27_11_09-1024x383.jpg)
- content / image: [PXC_2021_07_27_10_43-1024x388.jpg](https://www.percona.com/wp-content/uploads/2026/03/PXC_2021_07_27_10_43-1024x388.jpg)
- content / image: [MySQL Node Change](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL_2021_07_27_11_56-1024x432.jpg)
- content / image: [percona monitoring and management](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL_Status_2021_07_27_11_56-1024x440.jpg)
- content / image: [traditional replication](https://www.percona.com/wp-content/uploads/2026/03/BP-PXC-handler-3.png)
- content / image: [PXC Scheduler Handler](https://www.percona.com/wp-content/uploads/2026/03/BP-PXC-handler-4.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
