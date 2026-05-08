---
title: Multiple purge threads in Percona Server 5.1.56 and MySQL 5.6.2
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multiple-purge-threads-in-percona-server-5-1-56-and-mysql-5-6-2/
  post_id: 2965
source_author:
  name: Laurynas Biveinis
  slug: laurynas-biveinis
  url: https://www.percona.com/blog/author/laurynas-biveinis/
  website: ''
published_at: '2011-05-03T07:00:01'
published_at_gmt: '2011-05-03T07:00:01'
modified_at: '2026-05-05T16:48:46'
modified_at_gmt: '2026-05-05T16:48:46'
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
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/5.1-0-fixedgraph.png
image_count: 21
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multiple purge threads in Percona Server 5.1.56 and MySQL 5.6.2

Source: [Percona Blog](https://www.percona.com/blog/multiple-purge-threads-in-percona-server-5-1-56-and-mysql-5-6-2/)

Auteur source: [Laurynas Biveinis](https://www.percona.com/blog/author/laurynas-biveinis/)

Publication: 2011-05-03T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Part of the InnoDB duties, being an MVCC-implementing storage engine, is to get rid of–purge–the old versions of the records as they become obsolete. In MySQL 5.1 this is done by the master InnoDB thread. Since then, InnoDB has been moving towards the parallelized purge: in MySQL 5.5 there is an option to have a … Continued

## Structure detectee

- H2: Percona Server 5.1 Results
- H2: MySQL 5.6.2 Results
- H2: MySQL 5.6.2 Results With Purge Sleeps Removed
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [Multiple purge threads in Percona Server 5.1.56 and MySQL 5.6.2](https://www.percona.com/wp-content/uploads/2026/03/5.1-0-fixedgraph.png)
- content / image: [Percona Server 5.1.56-rel12.7, 1 dedicated purge thread](https://www.percona.com/wp-content/uploads/2026/03/5.1-1.png)
- content / image: [Percona Server 5.1.56-rel12.7, 2 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.1-2.png)
- content / image: [Percona Server 5.1.56-rel12.7, 4 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.1-4.png)
- content / image: [Percona Server 5.1.56-rel12.7, 8 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.1-8.png)
- content / image: [Percona Server 5.1.56-rel12.7 history list length](https://www.percona.com/wp-content/uploads/2026/03/5.1-histlist.png)
- content / image: [Percona Server 5.1.56-rel12.7 TPS](https://www.percona.com/wp-content/uploads/2026/03/5.1-tps.png)
- content / image: [MySQL 5.6.2, 0 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-0.png)
- content / image: [MySQL 5.6.2, 1 dedicated purge thread](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-1.png)
- content / image: [MySQL 5.6.2, 2 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-2.png)
- content / image: [MySQL 5.6.2, 4 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-4.png)
- content / image: [MySQL 5.6.2, 8 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-8.png)
- content / image: [MySQL 5.6.2 history list length](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-histlist.png)
- content / image: [MySQL 5.6.2 TPS](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-tps.png)
- content / image: [MySQL 5.6.2-no-wait, 0 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-0.png)
- content / image: [MySQL 5.6.2-no-wait, 1 dedicated purge thread](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-1.png)
- content / image: [MySQL 5.6.2-no-wait, 2 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-2.png)
- content / image: [MySQL 5.6.2-no-wait, 4 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-4.png)
- content / image: [MySQL 5.6.2-no-wait, 8 dedicated purge threads](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-8.png)
- content / image: [MySQL 5.6.2-no-wait history list length](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-histlist.png)
- content / image: [MySQL 5.6.2-no-wait TPS](https://www.percona.com/wp-content/uploads/2026/03/5.6.2-no-purge-wait-tps.png)

## Auteur source

Laurynas is a software engineer and Percona Server lead whose primary interest is InnoDB performance. In the past he worked in industry, interned in Google as a compiler software engineer, as well as academia where he researched physical database indexes, including large-scale spatial models of the brain.
