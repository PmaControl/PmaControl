---
title: Finding MySQL Table Size on Disk
source:
  name: Percona Blog
  url: https://www.percona.com/blog/finding_mysql_table_size_on_disk/
  post_id: 14595
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2016-01-26T22:16:39'
published_at_gmt: '2016-01-26T22:16:39'
modified_at: '2026-05-05T17:59:42'
modified_at_gmt: '2026-05-05T17:59:42'
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
- InnoDB
- MySQL 5.7
- table size
tag_slugs:
- innodb
- mysql-5-7
- table-size
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_359662646-Converted.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Finding MySQL Table Size on Disk

Source: [Percona Blog](https://www.percona.com/blog/finding_mysql_table_size_on_disk/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2016-01-26T22:16:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

So you want to know how much space a given MySQL table takes on disk. Looks trivial, right? Shouldn’t this information be readily available in the INFORMATION_SCHEMA .TABLES ? Not so fast! This simple question actually is quite complicated in MySQL. MySQL supports many storage engines (some of which don’t store data on disk at all) and … Continued

## Images et graphiques reperes

- featured / image: [Finding MySQL Table Size on Disk](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_359662646-Converted.png)
- content / image: [MySQL table size](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_359662646-Converted-300x300.png)
- content / graph_or_chart: [Click graphic to enlarge](https://www.percona.com/wp-content/uploads/2026/03/graph-table-size-1024x161.png)
  Caption: Click graphic to enlarge

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
