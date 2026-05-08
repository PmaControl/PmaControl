---
title: Comparing Percona XtraDB Cluster with Semi-Sync replication Cross-WAN
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparing-percona-xtradb-cluster-with-semi-sync-replication-cross-wan/
  post_id: 3633
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2012-06-14T19:02:56'
published_at_gmt: '2012-06-14T19:02:56'
modified_at: '2026-05-05T21:43:53'
modified_at_gmt: '2026-05-05T21:43:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags:
- galera
- pxc
- semi-sync
tag_slugs:
- galera
- pxc
- semi-sync
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparing Percona XtraDB Cluster with Semi-Sync replication Cross-WAN

Source: [Percona Blog](https://www.percona.com/blog/comparing-percona-xtradb-cluster-with-semi-sync-replication-cross-wan/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2012-06-14T19:02:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have a customer who is considering Percona XtraDB Cluster (PXC) in a two colo WAN environment. They wanted me to do a test comparing PXC against semi-synchronous replication to see how they stack up against each other. Test Environment The test environment included AWS EC2 nodes in US-East and US-West (Oregon). The ping RTT … Continued

## Structure detectee

- H2: Test Environment
- H3: Control
- H3: Semi-sync
- H3: XtraDB Cluster 1-colo
- H3: XtraDB Cluster 2-colo
- H2: Tests
- H3: Single-write latency test
- H3: Sysbench 32-client test
- H2: Conclusion

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
