---
title: Differences in PREPARE Statement Error Handling with Binary and Text Protocol (Percona XtraDB Cluster / Galera)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/difference-error-handling-prepare-statement-binary-text-protocol-percona-xtradb-cluster-galera/
  post_id: 17018
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-07-05T18:22:02'
published_at_gmt: '2017-07-05T18:22:02'
modified_at: '2026-05-05T20:01:18'
modified_at_gmt: '2026-05-05T20:01:18'
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
- Error
- errors
- galera
- MySQL
- Percona XtraDB Cluster
- Prepare Statement
tag_slugs:
- error
- errors
- galera
- mysql
- percona-xtradb-cluster
- prepare-statement
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PREPARE-Statement.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Differences in PREPARE Statement Error Handling with Binary and Text Protocol (Percona XtraDB Cluster / Galera)

Source: [Percona Blog](https://www.percona.com/blog/difference-error-handling-prepare-statement-binary-text-protocol-percona-xtradb-cluster-galera/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-07-05T18:22:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at the differences in how a PREPARE statement handles errors in binary and text protocols. Introduction Since Percona XtraDB Cluster is a multi-master solution, when an application executes conflicting workloads one of the workloads gets rolled back with a DEADLOCK error. While the same holds true even if you fire … Continued

## Structure detectee

- H2: Introduction
- H2: Base Workload
- H2: Different Scenarios Based on Configuration
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Differences in PREPARE Statement Error Handling with Binary and Text Protocol (Percona XtraDB Cluster / Galera)](https://www.percona.com/wp-content/uploads/2026/03/PREPARE-Statement.jpg)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
