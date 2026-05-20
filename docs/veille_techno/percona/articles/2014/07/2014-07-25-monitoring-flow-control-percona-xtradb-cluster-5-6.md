---
title: Monitoring MySQL flow control in Percona XtraDB Cluster 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/monitoring-flow-control-percona-xtradb-cluster-5-6/
  post_id: 8390
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-07-25T14:41:00'
published_at_gmt: '2014-07-25T14:41:00'
modified_at: '2026-05-04T22:24:24'
modified_at_gmt: '2026-05-04T22:24:24'
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
- flow control
- galera
- MySQL Replication
- Percona XtraDB Cluster
- Stephane Combaudon
- sysbench
tag_slugs:
- flow-control
- galera
- mysql-replication
- percona-xtradb-cluster
- stephane-combaudon
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/wsrep_flow_control_pxc3.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Monitoring MySQL flow control in Percona XtraDB Cluster 5.6

Source: [Percona Blog](https://www.percona.com/blog/monitoring-flow-control-percona-xtradb-cluster-5-6/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-07-25T14:41:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Monitoring flow control in a Galera cluster is very important. If you do not, you will not understand why writes may sometimes be stalled. Percona XtraDB Cluster 5.6 provides 2 status variables for such monitoring: wsrep_flow_control_paused and wsrep_flow_control_paused_ns. Which one should you use? What is flow control? Flow control does not exist with regular MySQL replication, … Continued

## Structure detectee

- H2: What is flow control?
- H2: Triggering flow control and graphing it
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Monitoring MySQL flow control in Percona XtraDB Cluster 5.6](https://www.percona.com/wp-content/uploads/2026/03/wsrep_flow_control_pxc3.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
