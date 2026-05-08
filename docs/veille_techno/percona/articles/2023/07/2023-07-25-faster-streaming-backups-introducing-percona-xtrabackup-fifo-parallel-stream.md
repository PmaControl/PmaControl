---
title: Faster Streaming Backups – Introducing Percona XtraBackup FIFO Parallel Stream
source:
  name: Percona Blog
  url: https://www.percona.com/blog/faster-streaming-backups-introducing-percona-xtrabackup-fifo-parallel-stream/
  post_id: 27091
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2023-07-25T12:12:45'
published_at_gmt: '2023-07-25T12:12:45'
modified_at: '2026-03-26T20:29:26'
modified_at_gmt: '2026-03-26T20:29:26'
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
- tag:percona-xtrabackup:330
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
- Percona XtraBackup
tag_slugs:
- mysql
- mysql-and-variants
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-FIFO-Parallel-Stream.jpg
image_count: 5
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Faster Streaming Backups – Introducing Percona XtraBackup FIFO Parallel Stream

Source: [Percona Blog](https://www.percona.com/blog/faster-streaming-backups-introducing-percona-xtrabackup-fifo-parallel-stream/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2023-07-25T12:12:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When it comes to backups, there are several options for saving backup files. You can choose to save them locally on the same server, stream them to different servers, or store them in object storage. Percona XtraBackup facilitates streaming through the use of an auxiliary tool called xbcloud. STDOUT Datasink This diagram displays the process … Continued

## Structure detectee

- H2: STDOUT Datasink
- H2: FIFO Datasink
- H2: Usage
- H2: Performance
- H2: Summary

## Images et graphiques reperes

- featured / image: [Faster Streaming Backups – Introducing Percona XtraBackup FIFO Parallel Stream](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-FIFO-Parallel-Stream.jpg)
- content / image: [STDOUT Datasink](https://www.percona.com/wp-content/uploads/2026/03/Xbcloud-Streaming-Frame-103-1024x435.jpg)
- content / image: [FIFO Datasink](https://www.percona.com/wp-content/uploads/2026/03/Xbcloud-Streaming-Frame-112-1024x435.jpg)
- content / graph_or_chart: [throughput-5.png](https://www.percona.com/wp-content/uploads/2026/03/throughput-5.png)
- content / image: [fifo_put.png](https://www.percona.com/wp-content/uploads/2026/03/fifo_put.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
