---
title: More Trackable Flow Control for Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/more-trackable-flow-control-for-percona-xtradb-cluster/
  post_id: 16709
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-04-20T17:38:33'
published_at_gmt: '2017-04-20T17:38:33'
modified_at: '2026-05-05T19:48:01'
modified_at_gmt: '2026-05-05T19:48:01'
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
- flow control status
- flow_control
- Percona XtraDB Cluster
- query queue
- wsrep_flow_control_status
tag_slugs:
- flow-control-status
- flow_control
- percona-xtradb-cluster
- query-queue
- wsrep_flow_control_status
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# More Trackable Flow Control for Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/more-trackable-flow-control-for-percona-xtradb-cluster/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-04-20T17:38:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss trackable flow control in Percona XtraDB Cluster. Introduction Percona XtraDB Cluster has a self-regulating mechanism called Flow Control. This mechanism helps to avoid a situation wherein the weakest/slowest member of the cluster falls significantly behind other members of the cluster. When a member of a cluster is slow at … Continued

## Structure detectee

- H3: Introduction
- H3: Finding if a node is in flow control
- H4: So how can one view the higher and lower watermarks?
- H4: Takeaway thoughts

## Images et graphiques reperes

- featured / image: [More Trackable Flow Control for Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
