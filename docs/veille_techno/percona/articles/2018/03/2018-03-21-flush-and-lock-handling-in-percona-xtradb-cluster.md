---
title: FLUSH and LOCK Handling in Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/flush-and-lock-handling-in-percona-xtradb-cluster/
  post_id: 18325
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2018-03-21T19:30:24'
published_at_gmt: '2018-03-21T19:30:24'
modified_at: '2026-05-05T19:57:39'
modified_at_gmt: '2026-05-05T19:57:39'
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
- desynced node
- flush table with read lock
- FTWRL
- LOCK TABLE
- pause node
tag_slugs:
- desynced-node
- flush-table-with-read-lock
- ftwrl
- lock-table
- pause-node
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/FLUSH-and-LOCK-Handling-e1521659103246.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# FLUSH and LOCK Handling in Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/flush-and-lock-handling-in-percona-xtradb-cluster/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2018-03-21T19:30:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how Percona XtraDB Cluster (PXC) executes FLUSH and LOCK handling. Introduction Percona XtraDB Cluster is a multi-master solution that allows parallel execution of the transactions on multiple nodes at the same point in time. Given this semantics, it is important to understand how Percona XtraDB Cluster executes statements … Continued

## Structure detectee

- H2: FLUSH TABLE WITH READ LOCK
- H2: FLUSH TABLE <tablename> (WITH READ LOCK|FOR EXPORT)
- H2: LOCK TABLE <tablename> READ/WRITE
- H3: Tracking active lock/flush
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [FLUSH and LOCK Handling in Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/FLUSH-and-LOCK-Handling-e1521659103246.jpg)
- content / image: [FLUSH and LOCK Handling](https://www.percona.com/wp-content/uploads/2026/03/FLUSH-and-LOCK-Handling-300x200.jpg)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
