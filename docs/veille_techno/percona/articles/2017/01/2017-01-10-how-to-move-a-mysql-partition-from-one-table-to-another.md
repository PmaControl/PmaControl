---
title: How to Move a MySQL Partition from One Table to Another
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-move-a-mysql-partition-from-one-table-to-another/
  post_id: 9428
source_author:
  name: Pablo Padua
  slug: pablo-paduapercona-com
  url: https://www.percona.com/blog/author/pablo-paduapercona-com/
  website: ''
published_at: '2017-01-10T18:00:43'
published_at_gmt: '2017-01-10T18:00:43'
modified_at: '2026-05-05T22:39:58'
modified_at_gmt: '2026-05-05T22:39:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- '5.7'
- MySQL
- partition
- Storage
tag_slugs:
- 5-7
- mysql
- partition
- storage
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Move-a-MySQL-Partition.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Move a MySQL Partition from One Table to Another

Source: [Percona Blog](https://www.percona.com/blog/how-to-move-a-mysql-partition-from-one-table-to-another/)

Auteur source: [Pablo Padua](https://www.percona.com/blog/author/pablo-paduapercona-com/)

Publication: 2017-01-10T18:00:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post we’ll look at how to move a MySQL partition from one table to another, for MySQL versions before 5.7. Up to version 5.7, MySQL had a limitation that made it impossible to directly exchange partitions between partitioned tables. Now and then, we get questions about how to import an .ibd for … Continued

## Structure detectee

- H4: 1. Copy the .ibd data file from that particular partition
- H4: 2. Prepare a temporary table to import the tablespace
- H4: 3. Import the tablespace to the temporary table
- H4: 4. Swap the tablespace with the destination table’s partition tablespace
- H4: 5. Check that the partitions are correctly exchanged before dropping the one from the source table

## Images et graphiques reperes

- featured / image: [How to Move a MySQL Partition from One Table to Another](https://www.percona.com/wp-content/uploads/2026/03/Move-a-MySQL-Partition.jpg)
