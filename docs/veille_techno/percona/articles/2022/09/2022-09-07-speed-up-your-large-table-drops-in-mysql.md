---
title: Speed Up Your Large Table Drops in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/speed-up-your-large-table-drops-in-mysql/
  post_id: 25976
source_author:
  name: Kedar Vaijanapurkar
  slug: kedar-vaijanapurkar
  url: https://www.percona.com/blog/author/kedar-vaijanapurkar/
  website: http://kedar.nitty-witty.com/blog
published_at: '2022-09-07T12:25:57'
published_at_gmt: '2022-09-07T12:25:57'
modified_at: '2026-03-26T20:31:13'
modified_at_gmt: '2026-03-26T20:31:13'
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
- InnoDB
- MySQL
tag_slugs:
- innodb
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Large-Table-Drops-in-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Speed Up Your Large Table Drops in MySQL

Source: [Percona Blog](https://www.percona.com/blog/speed-up-your-large-table-drops-in-mysql/)

Auteur source: [Kedar Vaijanapurkar](https://www.percona.com/blog/author/kedar-vaijanapurkar/)

Publication: 2022-09-07T12:25:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A large table is a pain for many reasons as long as it is in a system. And as if that’s not enough, it is also a difficult task to get rid of it. In this post, we will understand why it is a pain to do this operation and what we can do about … Continued

## Structure detectee

- H3: 1. Traverse through the buffer pool and evict the pages found
- H4: Idea 1: The buffer pool is large and so is the linked list; can we temporarily reduce the buffer pool and make the linked list smaller?
- H4: Idea 2: Stop using the table (no selects, no writes on the table that need to be removed)
- H3: 2. Delete the file from OS disk
- H4: Idea 1: Smaller the file is on disk, the faster it will be to remove.
- H4: Idea 2: Don’t delete the underlying tablespace file (ibd)
- H2: What’s a hard link?
- H2: Steps for dropping a large table in MySQL

## Images et graphiques reperes

- featured / image: [Speed Up Your Large Table Drops in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Large-Table-Drops-in-MySQL.png)
- content / image: [Large Table Drops in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Large-Table-Drops-in-MySQL-300x157.png)

## Auteur source

Kedar Vaijanapurkar is a Tier 2 MySQL DBA at Percona since Oct 2021. He's experienced in MySQL related technologies and constantly working on improving skills. He lives in the cultural city of Vadodara with his SPOF (wife) and two HA (highly active) replicas.
