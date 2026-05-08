---
title: 'Performance Evaluation of SST Data Transfer: Without Encryption (Part 1)'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/performance-evaluation-of-sst-data-transfer-without-encryption-part-1/
  post_id: 16496
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2017-03-29T21:10:07'
published_at_gmt: '2017-03-29T21:10:07'
modified_at: '2026-05-05T19:45:37'
modified_at_gmt: '2026-05-05T19:45:37'
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
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Data Transfer
- Percona XtraDB Cluster
- Performance
- SST
tag_slugs:
- data-transfer
- percona-xtradb-cluster
- performance
- sst
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/SST-Data-Transfer.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Performance Evaluation of SST Data Transfer: Without Encryption (Part 1)

Source: [Percona Blog](https://www.percona.com/blog/performance-evaluation-of-sst-data-transfer-without-encryption-part-1/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2017-03-29T21:10:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at evaluating the performance of an SST data transfer without encryption. A State Snapshot Transfer (SST) operation is an important part of Percona XtraDB Cluster. It’s used to provision the joining node with all the necessary data. There are three methods of SST operation available: mysqldump, rsync, xtrabackup. The most … Continued

## Images et graphiques reperes

- featured / image: [Performance Evaluation of SST Data Transfer: Without Encryption (Part 1)](https://www.percona.com/wp-content/uploads/2026/03/SST-Data-Transfer.png)
- content / image: [SST Data Transfer](https://www.percona.com/wp-content/uploads/2026/03/sst_operations.no_ssl.v5.1.png)
- content / image: [SST Data Transfer](https://www.percona.com/wp-content/uploads/2026/03/sst_operations.no_ssl.v5.2.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
