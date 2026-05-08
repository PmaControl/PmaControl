---
title: Update on the InnoDB double-write buffer and EXT4 transactions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/update-on-the-innodb-double-write-buffer-and-ext4-transactions/
  post_id: 9262
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2015-06-17T14:15:48'
published_at_gmt: '2015-06-17T14:15:48'
modified_at: '2026-03-25T18:00:17'
modified_at_gmt: '2026-03-25T18:00:17'
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
- Hardware and Storage
- MySQL
category_slugs:
- benchmarks
- hardware-and-storage
- mysql
tags:
- Benchmarks
- double-write buffer
- EXT4 transactions
- InnoDB
- MySQL
- Primary
- sysbench
- Yves Trudeau
tag_slugs:
- benchmarks
- double-write-buffer
- ext4-transactions
- innodb
- mysql
- primary
- sysbench
- yves-trudeau
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ext4trx_v2.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Update on the InnoDB double-write buffer and EXT4 transactions

Source: [Percona Blog](https://www.percona.com/blog/update-on-the-innodb-double-write-buffer-and-ext4-transactions/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2015-06-17T14:15:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

IMPORTANT: DON’T TRY THIS IN PRODUCTION. As demonstrated by Marko (see comments), it may corrupt your data. In a post, written a few months ago, I found that using EXT4 transactions with the “data=journal” mount option, improves the write performance significantly, by 55%, without putting data at risk. Many people commented on the post mentioning … Continued

## Images et graphiques reperes

- featured / image: [Update on the InnoDB double-write buffer and EXT4 transactions](https://www.percona.com/wp-content/uploads/2026/03/ext4trx_v2.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
