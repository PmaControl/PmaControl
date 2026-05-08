---
title: Understanding GCache and Record-Set Cache in Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-gcache-record-set-cache-percona-xtradb-cluster/
  post_id: 14931
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2016-04-11T07:50:25'
published_at_gmt: '2016-04-11T07:50:25'
modified_at: '2026-05-05T18:05:07'
modified_at_gmt: '2026-05-05T18:05:07'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- Gcache
- PXC Internals
- record-set cache
- write-set cache
tag_slugs:
- gcache
- pxc-internals
- record-set-cache
- write-set-cache
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_277371281-Converted-e1479151923980.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding GCache and Record-Set Cache in Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/understanding-gcache-record-set-cache-percona-xtradb-cluster/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2016-04-11T07:50:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we will examine the differences between GCache and Record-Set Cache in Percona XtraDB Cluster. In Percona XtraDB Cluster (PXC), there is the concept of GCache and Record-Set cache (which can also be called transaction write-set cache). The use of these two caches is often confusing if you are running long transactions, as … Continued

## Images et graphiques reperes

- featured / image: [Understanding GCache and Record-Set Cache in Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_277371281-Converted-e1479151923980.png)
- content / image: [shutterstock_277371281-Converted-1-298x300.png](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_277371281-Converted-1-298x300.png)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
