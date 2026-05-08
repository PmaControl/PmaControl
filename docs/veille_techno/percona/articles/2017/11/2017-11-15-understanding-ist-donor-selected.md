---
title: Understanding how an IST donor is selected
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-ist-donor-selected/
  post_id: 17662
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-11-15T16:11:44'
published_at_gmt: '2017-11-15T16:11:44'
modified_at: '2026-03-20T21:34:58'
modified_at_gmt: '2026-03-20T21:34:58'
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
- Gcache
- IST donor
tag_slugs:
- gcache
- ist-donor
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pexels-photo-276218.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding how an IST donor is selected

Source: [Percona Blog](https://www.percona.com/blog/understanding-ist-donor-selected/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-11-15T16:11:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a clustering environment, we often see a node that needs to be taken down for maintenance. For a node to rejoin, it should re-sync with the cluster state. In PXC (Percona XtraDB Cluster), there are 2 ways for the rejoining node to re-sync: State Snapshot Transfer (SST) and Incremental State Transfer (IST). SST involves … Continued

## Structure detectee

- H2: Selecting an IST DONOR
- H3: Safety gap and how it affects DONOR selection
- H3: Twist at the end

## Images et graphiques reperes

- featured / image: [Understanding how an IST donor is selected](https://www.percona.com/wp-content/uploads/2026/03/pexels-photo-276218.jpeg)
- content / image: [IST donor cluster](https://www.percona.com/wp-content/uploads/2026/03/pexels-photo-276218-300x200.jpeg)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
