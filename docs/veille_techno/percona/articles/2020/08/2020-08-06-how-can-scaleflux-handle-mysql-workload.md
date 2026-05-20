---
title: How Can ScaleFlux Handle MySQL Workload?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-can-scaleflux-handle-mysql-workload/
  post_id: 22859
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2020-08-06T13:02:17'
published_at_gmt: '2020-08-06T13:02:17'
modified_at: '2026-05-05T16:31:51'
modified_at_gmt: '2026-05-05T16:31:51'
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
- Benchmarks
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- ScaleFlux
- Storage
tag_slugs:
- benchmarks
- mysql
- mysql-and-variants
- percona-server
- scaleflux
- storage
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ScaleFlux-Handle-MySQL.png
image_count: 11
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Can ScaleFlux Handle MySQL Workload?

Source: [Percona Blog](https://www.percona.com/blog/how-can-scaleflux-handle-mysql-workload/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2020-08-06T13:02:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I had the opportunity to test a storage device from ScaleFlux called CSD 2000. In this blog post, I will share the results of using it to run MySQL in comparison with an Intel device that had a similar capacity. First of all, why do we need another storage device? Why is ScaleFlux any … Continued

## Structure detectee

- H3: First of all, why do we need another storage device? Why is ScaleFlux any different?
- H3: Which Tests Did I Run?
- H3: Default Sysbench – Read/Write – 220G Datasize
- H3: Modified Sysbench – Read/Write – 440G Datasize
- H3: Modified Sysbench – Read/Write – 2.5T Datasize
- H3: Disk Latency
- H3: CPU Usage
- H4: ScaleFlux – Read/Write – Modified Sysbench – 540 tables – 2.5T
- H4: Intel – Read/Write – Modified Sysbench – 540 tables – 2.5T
- H2: Disk Operations
- H2: InnoDB Row Operations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How Can ScaleFlux Handle MySQL Workload?](https://www.percona.com/wp-content/uploads/2026/03/ScaleFlux-Handle-MySQL.png)
- content / image: [ScaleFlux Handle MySQL](https://www.percona.com/wp-content/uploads/2026/03/ScaleFlux-Handle-MySQL-300x168.png)
- content / image: [Default Sysbench - Read/Write - 220G Datasize](https://www.percona.com/wp-content/uploads/2026/03/Read_Write-Default-Sysbench-100-tables-220G-data.png)
- content / image: [Modified Sysbench - Read/Write - 440G Datasize](https://www.percona.com/wp-content/uploads/2026/03/Read_Write-Modified-Sysbench-100-tables-440G.png)
- content / image: [MySQL ScaleFlux](https://www.percona.com/wp-content/uploads/2026/03/Write-Only-Modified-Sysbench-100-Tables-440G-DW_noDW.png)
- content / image: [Modified Sysbench - Read/Write - 2.5T Datasize](https://www.percona.com/wp-content/uploads/2026/03/Read_Write-Modified-Sysbench-540-Tables-2.5T_new.png)
- content / graph_or_chart: [Disk-Latency-Read_Write-Modified-Sysbench-540-tables-2.5T-1.png](https://www.percona.com/wp-content/uploads/2026/03/Disk-Latency-Read_Write-Modified-Sysbench-540-tables-2.5T-1.png)
- content / image: [ScaleFlux - Read/Write - Modified Sysbench - 540 tables - 2.5T](https://www.percona.com/wp-content/uploads/2026/03/cpu-2.png)
- content / image: [Intel - Read/Write - Modified Sysbench - 540 tables - 2.5T](https://www.percona.com/wp-content/uploads/2026/03/cpu_intel.png)
- content / image: [Disk-Operations-Read_Write-Modified-Sysbench-540-tables-2.5T.png](https://www.percona.com/wp-content/uploads/2026/03/Disk-Operations-Read_Write-Modified-Sysbench-540-tables-2.5T.png)
- content / image: [InnoDB Row Operations](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Row-Operations-groupped-by-minutes-higher-is-better.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
