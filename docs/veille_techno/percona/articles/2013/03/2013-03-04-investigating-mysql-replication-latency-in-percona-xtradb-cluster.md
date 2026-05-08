---
title: Investigating MySQL Replication Latency in Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/investigating-mysql-replication-latency-in-percona-xtradb-cluster/
  post_id: 6653
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2013-03-04T02:10:40'
published_at_gmt: '2013-03-04T02:10:40'
modified_at: '2026-04-16T16:39:19'
modified_at_gmt: '2026-04-16T16:39:19'
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
- Benchmarks
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL Replication Latency
- Percona XtraDB Cluster
- Peter Zaitsev
tag_slugs:
- mysql-replication-latency
- percona-xtradb-cluster
- peter-zaitsev
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/magnifying_glass.jpg
image_count: 2
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Investigating MySQL Replication Latency in Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/investigating-mysql-replication-latency-in-percona-xtradb-cluster/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2013-03-04T02:10:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Investigating MySQL Replication Latency in Percona XtraDB Cluster I was curious to check how Percona XtraDB Cluster behaves in terms of replication latency (or data propagation latency). Specifically, I wanted to see if stale reads could occur on other nodes immediately after a write. To test this, I wrote a simple script (included at the … Continued

## Structure detectee

- H2: Baseline (No Load)
- H2: With Load on Write Node (DPE1)
- H2: Load on Read Node (DPE2)
- H2: Write-Heavy Workload
- H2: Write Load on Read Node
- H2: Load on Unused Node (DPE3)
- H2: Synchronous Reads Option
- H2: Large Transaction Impact
- H2: Observed Stall Behavior
- H2: Summary
- H2: Appendix: Test Script
- H2: Appendix: PXC Configuration

## Images et graphiques reperes

- featured / graph_or_chart: [Investigating MySQL Replication Latency in Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/magnifying_glass.jpg)
- content / graph_or_chart: [Investigating MySQL Replication Latency in Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/magnifying_glass-300x225.jpg)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
