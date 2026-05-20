---
title: MyDumper Refactors Locking Mechanisms
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mydumper-refactors-locking-mechanisms/
  post_id: 35092
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2025-07-14T13:56:20'
published_at_gmt: '2025-07-14T13:56:20'
modified_at: '2026-03-26T20:25:24'
modified_at_gmt: '2026-03-26T20:25:24'
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
- mydumper
- MySQL
- mysql-and-variants
tag_slugs:
- mydumper
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MyDumper-Locking-Mechanisms.jpg
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MyDumper Refactors Locking Mechanisms

Source: [Percona Blog](https://www.percona.com/blog/mydumper-refactors-locking-mechanisms/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2025-07-14T13:56:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous blog post, Understanding trx-consistency-only on MyDumper Before Removal, I talked about –trx-consistency-only removal, in which I explained that it acts like a shortcut, reducing the amount of time we have to block the write traffic to the database by skipping to check if we are going to backup any non-transactional tables. Now, … Continued

## Structure detectee

- H2: Merge of trx-consistency-only and less-locking
- H2: Thread sync mechanisms
- H3: FLUSH TABLE WITH READ LOCK
- H3: Lock all tables
- H3: No locks
- H3: Using GTID
- H3: AUTO
- H3: Conclusions

## Images et graphiques reperes

- featured / image: [MyDumper Refactors Locking Mechanisms](https://www.percona.com/wp-content/uploads/2026/03/MyDumper-Locking-Mechanisms.jpg)
- content / graph_or_chart: [how the main thread and the workers interact with each other](https://www.percona.com/wp-content/uploads/2026/03/Untitled-Diagram.drawio-25.png)
- content / image: [mysql performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-9.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
