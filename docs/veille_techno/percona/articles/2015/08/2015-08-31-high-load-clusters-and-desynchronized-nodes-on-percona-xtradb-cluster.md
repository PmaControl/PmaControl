---
title: High-load clusters and desynchronized nodes on Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-load-clusters-and-desynchronized-nodes-on-percona-xtradb-cluster/
  post_id: 9953
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-08-31T10:00:03'
published_at_gmt: '2015-08-31T10:00:03'
modified_at: '2026-05-05T17:00:19'
modified_at_gmt: '2026-05-05T17:00:19'
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
- desynchronized nodes
- high-load clusters
- Jay Janssen
- MySQL
- MySQL Clustering
- wsrep_local_recv_queue
tag_slugs:
- desynchronized-nodes
- high-load-clusters
- jay-janssen
- mysql
- mysql-clustering
- wsrep_local_recv_queue
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.38.34-PM1.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# High-load clusters and desynchronized nodes on Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/high-load-clusters-and-desynchronized-nodes-on-percona-xtradb-cluster/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-08-31T10:00:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There can be a lot of confusion and lack of planning in Percona XtraDB Clusters in regards to nodes becoming desynchronized for various reasons. This can happen a few ways: An IST or SST joining node catching up after a state transfer (Joined/Joining state) Using wsrep_desync for something like a backup Executing a rolling-schema-upgrade using … Continued

## Structure detectee

- H2: Example setup
- H2: During the backup
- H2: Unlock tables, still wsrep_desync=ON
- H2: Flow Control as a way to recovery
- H2: Does it matter?

## Images et graphiques reperes

- featured / image: [High-load clusters and desynchronized nodes on Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.38.34-PM1.png)
- content / image: [Screen Shot 2015-08-19 at 2.38.50 PM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.38.50-PM.png)
- content / image: [Screen Shot 2015-08-19 at 2.39.04 PM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.39.04-PM.png)
- content / image: [Screen Shot 2015-08-19 at 2.42.16 PM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.42.16-PM.png)
- content / image: [Screen Shot 2015-08-19 at 2.43.13 PM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.43.13-PM.png)
- content / image: [Screen Shot 2015-08-19 at 2.47.12 PM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.47.12-PM.png)
- content / image: [Screen Shot 2015-08-19 at 2.48.07 PM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2015-08-19-at-2.48.07-PM.png)

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
