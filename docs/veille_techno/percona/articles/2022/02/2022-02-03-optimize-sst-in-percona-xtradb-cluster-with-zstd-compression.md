---
title: Optimize SST in Percona XtraDB Cluster with ZSTD Compression
source:
  name: Percona Blog
  url: https://www.percona.com/blog/optimize-sst-in-percona-xtradb-cluster-with-zstd-compression/
  post_id: 25391
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2022-02-03T13:23:22'
published_at_gmt: '2022-02-03T13:23:22'
modified_at: '2026-04-28T15:04:40'
modified_at_gmt: '2026-04-28T15:04:40'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- pigz
- qpress
- SST
- zstd
tag_slugs:
- mysql
- mysql-and-variants
- pigz
- qpress
- sst
- zstd
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Optimize-SST-in-Percona-XtraDB-Cluster-with-ZSTD-Compression.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Optimize SST in Percona XtraDB Cluster with ZSTD Compression

Source: [Percona Blog](https://www.percona.com/blog/optimize-sst-in-percona-xtradb-cluster-with-zstd-compression/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2022-02-03T13:23:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster (PXC) offers a great deal of flexibility when it comes to the state transfer (SST) options (used when a new node is automatically provisioned with data). For many environments, on-the-fly compression capability gives great benefits of saving network bandwidth during the process of sending sometimes terabytes of data. The usual choice for … Continued

## Images et graphiques reperes

- featured / image: [Optimize SST in Percona XtraDB Cluster with ZSTD Compression](https://www.percona.com/wp-content/uploads/2026/03/Optimize-SST-in-Percona-XtraDB-Cluster-with-ZSTD-Compression.png)
- content / image: [Optimize SST in Percona XtraDB Cluster with ZSTD Compression](https://www.percona.com/wp-content/uploads/2026/03/Optimize-SST-in-Percona-XtraDB-Cluster-with-ZSTD-Compression-300x168.png)
- content / image: [SST MySQL](https://www.percona.com/wp-content/uploads/2026/03/sst_time.jpg)
- content / image: [sst_size.jpg](https://www.percona.com/wp-content/uploads/2026/03/sst_size.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
