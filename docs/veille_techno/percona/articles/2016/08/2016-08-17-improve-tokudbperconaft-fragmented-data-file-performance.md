---
title: TokuDB/PerconaFT fragmented data file performance improvements
source:
  name: Percona Blog
  url: https://www.percona.com/blog/improve-tokudbperconaft-fragmented-data-file-performance/
  post_id: 15412
source_author:
  name: George O. Lorch III
  slug: glorch
  url: https://www.percona.com/blog/author/glorch/
  website: ''
published_at: '2016-08-17T17:05:11'
published_at_gmt: '2016-08-17T17:05:11'
modified_at: '2026-03-20T21:06:14'
modified_at_gmt: '2026-03-20T21:06:14'
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
- Percona Server for MySQL
- PerconaFT
- Performance
- TokuDB
tag_slugs:
- percona-server
- perconaft
- performance
- tokudb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/fragmented-data-file-performance.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB/PerconaFT fragmented data file performance improvements

Source: [Percona Blog](https://www.percona.com/blog/improve-tokudbperconaft-fragmented-data-file-performance/)

Auteur source: [George O. Lorch III](https://www.percona.com/blog/author/glorch/)

Publication: 2016-08-17T17:05:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss how we’ve improved TokuDB and PerconaFT fragmented data file performance. Through our internal benchmarking and some user reports, we have found that with long term heavy write use TokuDB/PerconaFT performance can degrade significantly on large data files. Using smaller node sizes makes the problem worse (which is one of … Continued

## Images et graphiques reperes

- featured / image: [TokuDB/PerconaFT fragmented data file performance improvements](https://www.percona.com/wp-content/uploads/2026/03/fragmented-data-file-performance.png)
- content / image: [fragmented data file performance](https://www.percona.com/wp-content/uploads/2026/03/blockfile.png)
- content / image: [fragmented data file performance](https://www.percona.com/wp-content/uploads/2026/03/Holes.png)
- content / image: [fragmented data file performance](https://www.percona.com/wp-content/uploads/2026/03/mhstree.png)
- content / image: [fragmented data file performance](https://www.percona.com/wp-content/uploads/2026/03/tps-1024x116.png)
- content / image: [fragmented data file performance](https://www.percona.com/wp-content/uploads/2026/03/responsetime-1-1024x118.png)
- content / image: [fragmented data file performance](https://www.percona.com/wp-content/uploads/2026/03/cpu-1-1024x118.png)

## Auteur source

George joined the Percona development team in April 2012. George has over 25 years of experience in software support, development, architecture and project management. Prior to joining Percona, George was focused on Windows based enterprise application server development and network protocol classification and optimization with heavy doses of database schema design, architecture and tuning. Now he humbly serves Percona in the role of Director of Server Engineering - Percona Server for MySQL, Percona XtraDB Cluster, and Percona XtraBackup.
