---
title: Streaming MySQL Binlogs to S3 (or Any Object Storage)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/streaming-mysql-binlogs-to-s3-or-any-object-storage/
  post_id: 24637
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2021-07-09T13:34:07'
published_at_gmt: '2021-07-09T13:34:07'
modified_at: '2026-04-27T22:31:23'
modified_at_gmt: '2026-04-27T22:31:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- AWS
- cloud
- MySQL
- mysql-and-variants
tag_slugs:
- aws
- cloud
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Streaming-MySQL-Binlogs-to-S3.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Streaming MySQL Binlogs to S3 (or Any Object Storage)

Source: [Percona Blog](https://www.percona.com/blog/streaming-mysql-binlogs-to-s3-or-any-object-storage/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2021-07-09T13:34:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Problem Statement Having backups of binary logs is fairly normal these days. The more recent binary logs are copied offsite, the better RPO (Recovery Point Objective) can be achieved. I was asked multiple times recently if something could be done to “stream” the binary logs to S3 as close to real-time as possible. Unfortunately, there … Continued

## Structure detectee

- H2: Problem Statement
- H2: Uploading to Object Storage
- H2: Proof of Concept Implementation
- H3: Example

## Images et graphiques reperes

- featured / image: [Streaming MySQL Binlogs to S3 (or Any Object Storage)](https://www.percona.com/wp-content/uploads/2026/03/Streaming-MySQL-Binlogs-to-S3.png)
- content / image: [Streaming MySQL Binlogs to S3](https://www.percona.com/wp-content/uploads/2026/03/Streaming-MySQL-Binlogs-to-S3-300x157.png)
- content / image: [MySQL Bin](https://www.percona.com/wp-content/uploads/2026/03/binlog.png)
- content / image: [MySQL Bin](https://www.percona.com/wp-content/uploads/2026/03/binlog2s3.png)

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
