---
title: Measuring Max Replication Throughput on Percona XtraDB Cluster with wsrep_desync
source:
  name: Percona Blog
  url: https://www.percona.com/blog/measuring-replication-throughput-percona-xtradb-cluster-wsrep_desync/
  post_id: 7447
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2013-10-17T05:00:24'
published_at_gmt: '2013-10-17T05:00:24'
modified_at: '2026-05-04T22:09:32'
modified_at_gmt: '2026-05-04T22:09:32'
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
- async replication
- Jay Janssen
- Percona XtraDB Cluster
- Replication
- wsrep_desync
tag_slugs:
- async-replication
- jay-janssen
- percona-xtradb-cluster
- replication
- wsrep_desync
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Measuring Max Replication Throughput on Percona XtraDB Cluster with wsrep_desync

Source: [Percona Blog](https://www.percona.com/blog/measuring-replication-throughput-percona-xtradb-cluster-wsrep_desync/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2013-10-17T05:00:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Checking throughput with async MySQL replication Replication throughput is the measure of just how fast the slaves can apply replication (at least by my definition). In MySQL async replication this is important to know because the single-threaded apply nature of async replication can be a write performance bottleneck. In a production system, we can tell … Continued

## Structure detectee

- H2: Checking throughput with async MySQL replication
- H2: Measuring an average apply rate on PXC
- H2: Measuring Max Replication throughput on PXC
- H2: Conclusion

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
