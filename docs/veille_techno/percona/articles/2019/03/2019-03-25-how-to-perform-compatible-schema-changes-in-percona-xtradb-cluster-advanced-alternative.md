---
title: How to Perform Compatible Schema Changes in Percona XtraDB Cluster (Advanced Alternative)?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-perform-compatible-schema-changes-in-percona-xtradb-cluster-advanced-alternative/
  post_id: 19706
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2019-03-25T12:37:36'
published_at_gmt: '2019-03-25T12:37:36'
modified_at: '2026-04-27T21:08:48'
modified_at_gmt: '2026-04-27T21:08:48'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- ddl
- galera
- RSU
- TOI
tag_slugs:
- ddl
- galera
- rsu
- toi
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PXC-schema-changes-options.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Perform Compatible Schema Changes in Percona XtraDB Cluster (Advanced Alternative)?

Source: [Percona Blog](https://www.percona.com/blog/how-to-perform-compatible-schema-changes-in-percona-xtradb-cluster-advanced-alternative/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2019-03-25T12:37:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you are using Galera replication, you know that schema changes may be a serious problem. With its current implementation, there is no way even a simple ALTER will be unobtrusive for live production traffic. It is a fact that with the default TOI alter method, Percona XtraDB Cluster (PXC) cluster suspends writes in order … Continued

## Structure detectee

- H3: RSU and Concurrent Queries
- H3: “Manual RSU”
- H3: Kill Problem
- H3: Summary

## Images et graphiques reperes

- featured / image: [How to Perform Compatible Schema Changes in Percona XtraDB Cluster (Advanced Alternative)?](https://www.percona.com/wp-content/uploads/2026/03/PXC-schema-changes-options.jpg)
- content / image: [PXC schema changes options](https://www.percona.com/wp-content/uploads/2026/03/PXC-schema-changes-options-300x200.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
