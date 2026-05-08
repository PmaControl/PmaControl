---
title: LVM read performance during snapshots
source:
  name: Percona Blog
  url: https://www.percona.com/blog/lvm-read-performance-during-snapshots/
  post_id: 7173
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2013-07-09T10:00:27'
published_at_gmt: '2013-07-09T10:00:27'
modified_at: '2026-03-25T17:03:28'
modified_at_gmt: '2026-03-25T17:03:28'
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
- Backups
- COW space
- lvm
- read performance
- xfs
- Yves Trudeau
- ZFS
tag_slugs:
- backups
- cow-space
- lvm
- read-performance
- xfs
- yves-trudeau
- zfs
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lvm_read_performance-e1372952580937.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# LVM read performance during snapshots

Source: [Percona Blog](https://www.percona.com/blog/lvm-read-performance-during-snapshots/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2013-07-09T10:00:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For the same customer I am exploring ZFS for backups, the twin server is using regular LVM and XFS. On this twin, I have setup mylvmbackup for a more conservative backup approach. I quickly found some odd behaviors, the backup was taking much longer than what I was expecting. It is not the first time … Continued

## Images et graphiques reperes

- featured / image: [LVM read performance during snapshots](https://www.percona.com/wp-content/uploads/2026/03/lvm_read_performance-e1372952580937.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
