---
title: ZFS from a MySQL perspective
source:
  name: Percona Blog
  url: https://www.percona.com/blog/zfs-from-a-mysql-perspective/
  post_id: 17652
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2017-11-15T10:44:29'
published_at_gmt: '2017-11-15T10:44:29'
modified_at: '2026-05-06T00:17:47'
modified_at_gmt: '2026-05-06T00:17:47'
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
- Hardware and Storage
- Insight for DBAs
- MySQL
category_slugs:
- hardware-and-storage
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Openzfs.svg.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ZFS from a MySQL perspective

Source: [Percona Blog](https://www.percona.com/blog/zfs-from-a-mysql-perspective/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2017-11-15T10:44:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Since the purpose of a database system is to store data, there is close relationship with the filesystem. As MySQL consultants, we always look at the filesystems for performance tuning opportunities. The most common choices in term of filesystems are XFS and EXT4, on Linux it is exceptional to encounter another filesystem. Both XFS and … Continued

## Structure detectee

- H2: Some context
- H2: ZFS features
- H4: 128 bits filesystem
- H4: Copy-on-write (COW)
- H4: Snapshot
- H4: Clone
- H4: Checksum
- H4: Compression
- H4: Encryption
- H4: Transactional
- H4: ZIL/SLOG
- H4: ARC/L2ARC
- H4: RAID
- H4: Self-healing
- H4: ZVOL block devices
- H4: Send/Receive
- H4: Deduplication

## Images et graphiques reperes

- featured / image: [ZFS from a MySQL perspective](https://www.percona.com/wp-content/uploads/2026/03/Openzfs.svg.png)
- content / image: [Open ZFS logo](https://www.percona.com/wp-content/uploads/2026/03/Openzfs.svg-300x280.png)
- content / graph_or_chart: [graph of the estimated development efforts for ZFS versus other filesystems](https://www.percona.com/wp-content/uploads/2026/03/devel_effort-1024x659.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
