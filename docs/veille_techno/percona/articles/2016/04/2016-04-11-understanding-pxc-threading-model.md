---
title: Understanding Percona XtraDB Cluster threading model
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-pxc-threading-model/
  post_id: 14652
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2016-04-11T05:37:32'
published_at_gmt: '2016-04-11T05:37:32'
modified_at: '2026-03-20T20:54:37'
modified_at_gmt: '2026-03-20T20:54:37'
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
- PXC threading model
tag_slugs:
- pxc-threading-model
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-300x250.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding Percona XtraDB Cluster threading model

Source: [Percona Blog](https://www.percona.com/blog/understanding-pxc-threading-model/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2016-04-11T05:37:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll discuss how the Percona XtraDB Cluster threading model works. Percona XtraDB Cluster (PXC) creates a different set of threads to service its operations. These threads are in addition to existing MySQL threads. Let’s understand what these threads are and how they’re used. There are three main groups of threads: Applier thread(s) Applier threads … Continued

## Images et graphiques reperes

- featured / image: [Understanding Percona XtraDB Cluster threading model](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-300x250.png)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
