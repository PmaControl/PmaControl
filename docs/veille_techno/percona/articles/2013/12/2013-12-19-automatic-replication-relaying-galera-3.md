---
title: Automatic replication relaying in Galera 3.x (available with PXC 5.6)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/automatic-replication-relaying-galera-3/
  post_id: 7610
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2013-12-19T15:12:31'
published_at_gmt: '2013-12-19T15:12:31'
modified_at: '2026-05-05T17:41:47'
modified_at_gmt: '2026-05-05T17:41:47'
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
category_slugs:
- mysql
tags:
- data centers
- Galera 3.x
- Jay Janssen
- MySQL
- Percona XtraDB Cluster
- replication relaying
tag_slugs:
- data-centers
- galera-3-x
- jay-janssen
- mysql
- percona-xtradb-cluster
- replication-relaying
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/nosegments.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Automatic replication relaying in Galera 3.x (available with PXC 5.6)

Source: [Percona Blog](https://www.percona.com/blog/automatic-replication-relaying-galera-3/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2013-12-19T15:12:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A decade ago MySQL folks were in love with the concept of a relay slave for MySQL high availability across data centers. A relay is a single slave in a remote data center that receives replication from the global master and, in turn, replicates to all the other local slaves in that data center. This … Continued

## Structure detectee

- H2: Replication traffic with default Galera tuning (and pre-3.x)
- H2: Replication traffic with Galera 3 WAN segments configured
- H2: What about commit latency?
- H2: No Segments
- H2: With Segments
- H2: Test for yourself

## Images et graphiques reperes

- featured / image: [Automatic replication relaying in Galera 3.x (available with PXC 5.6)](https://www.percona.com/wp-content/uploads/2026/03/nosegments.png)
- content / image: [segments](https://www.percona.com/wp-content/uploads/2026/03/segments.png)
- content / image: [chart_1 (1)](https://www.percona.com/wp-content/uploads/2026/03/chart_1-1-1.png)

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
