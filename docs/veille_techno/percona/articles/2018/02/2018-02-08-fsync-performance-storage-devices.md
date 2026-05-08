---
title: Fsync Performance on Storage Devices
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fsync-performance-storage-devices/
  post_id: 17984
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2018-02-08T15:53:16'
published_at_gmt: '2018-02-08T15:53:16'
modified_at: '2026-05-05T20:24:26'
modified_at_gmt: '2026-05-05T20:24:26'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- Insight for DBAs
- MySQL
category_slugs:
- hardware-and-storage
- insight-for-dbas
- mysql
tags:
- fsync
- fsync performance
- InnoDB
- MySQL
- rotating drives
- SSD
- Storage
tag_slugs:
- fsync
- fsync-performance
- innodb
- mysql
- rotating-drives
- ssd
- storage
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Hidden-Cost-of-Foreign-Key-Constraints-in-MySQL.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fsync Performance on Storage Devices

Source: [Percona Blog](https://www.percona.com/blog/fsync-performance-storage-devices/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2018-02-08T15:53:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While preparing a post on the design of ZFS based servers for use with MySQL, I stumbled on the topic of fsync call performance. The fsync call is very expensive, but it is essential to databases as it allows for durability (the “D” of the ACID acronym). Fsync Performance Let’s first review the type of … Continued

## Structure detectee

- H2: Fsync Performance
- H4: Fsync Results
- H4: Discussion
- H4: About fdatasync
- H4: O_DIRECT
- H4: ZFS
- H4: You May Also Like

## Images et graphiques reperes

- featured / image: [Fsync Performance on Storage Devices](https://www.percona.com/wp-content/uploads/2026/03/Hidden-Cost-of-Foreign-Key-Constraints-in-MySQL.png)
- content / image: [fsync performance](https://www.percona.com/wp-content/uploads/2026/03/disk-drive-doctor.jpg)
- content / image: [Download: The Hidden Costs of Not Properly Managing Your Databases eBook](https://www.percona.com/wp-content/uploads/2026/03/6d721c2f-fb18-49be-bcf1-41e12a01a2f3.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
