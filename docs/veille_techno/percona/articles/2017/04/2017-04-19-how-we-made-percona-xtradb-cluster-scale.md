---
title: How We Made Percona XtraDB Cluster Scale
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-we-made-percona-xtradb-cluster-scale/
  post_id: 16690
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-04-19T21:46:36'
published_at_gmt: '2017-04-19T21:46:36'
modified_at: '2026-03-20T21:23:08'
modified_at_gmt: '2026-03-20T21:23:08'
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
- commit monitor
- commitmonitor
- contention
- Percona XtraDB Cluster
- Performance
- pxc
tag_slugs:
- commit-monitor
- commitmonitor
- contention
- percona-xtradb-cluster
- performance
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How We Made Percona XtraDB Cluster Scale

Source: [Percona Blog](https://www.percona.com/blog/how-we-made-percona-xtradb-cluster-scale/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-04-19T21:46:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at the actions and efforts Percona experts took to scale Percona XtraDB Cluster. Introduction When we first started analyzing Percona XtraDB Cluster performance, it was pretty bad. We would see contention even with 16 threads. Performance was even worse with sync binlog=1, although the same pattern was observed even with … Continued

## Structure detectee

- H3: Introduction
- H3: Understanding How MySQL Commits a Transaction
- H3: What is a Monitor in Percona XtraDB Cluster World?
- H3: How Percona XtraDB Cluster Commits a Transaction
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How We Made Percona XtraDB Cluster Scale](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
