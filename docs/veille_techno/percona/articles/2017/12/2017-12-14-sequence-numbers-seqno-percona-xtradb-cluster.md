---
title: Demystifying Sequence Numbers (seqno) in Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/sequence-numbers-seqno-percona-xtradb-cluster/
  post_id: 17730
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-12-14T15:21:31'
published_at_gmt: '2017-12-14T15:21:31'
modified_at: '2026-03-20T21:35:55'
modified_at_gmt: '2026-03-20T21:35:55'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Percona XtraDB Cluster
- seqno
- Sequence Numbers
tag_slugs:
- percona-xtradb-cluster
- seqno
- sequence-numbers
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2017-12-01-16-39-15.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Demystifying Sequence Numbers (seqno) in Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/sequence-numbers-seqno-percona-xtradb-cluster/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-12-14T15:21:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how sequence numbers work in Percona XtraDB Cluster. Introduction Percona XtraDB Cluster uses multiple sequence numbers (seqno), each having a special role to play. Let’s try to understand the significance of each sequence number. global_seqno An active Percona XtraDB Cluster cluster has multiple write-sets (transactions) generated from one … Continued

## Structure detectee

- H4: Introduction
- H4: global_seqno
- H4: local_seqno
- H4: last_seen_seqno
- H4: depends_seqno

## Images et graphiques reperes

- featured / image: [Demystifying Sequence Numbers (seqno) in Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2017-12-01-16-39-15.png)
- content / image: [global_seqno](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2017-12-01-15-27-14-300x228.png)
- content / image: [local_seqno](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2017-12-01-15-56-33-300x233.png)
- content / image: [last_seen_seqno](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2017-12-01-16-27-45-300x182.png)
- content / image: [depends_seqno](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2017-12-01-16-39-15-300x264.png)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
