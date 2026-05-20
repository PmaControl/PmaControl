---
title: Compression Options in MySQL (Part 1)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/compression-options-in-mysql-part-1/
  post_id: 19612
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2018-11-23T15:18:01'
published_at_gmt: '2018-11-23T15:18:01'
modified_at: '2026-05-05T19:56:40'
modified_at_gmt: '2026-05-05T19:56:40'
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
- application performance
- database performance
- MySQL Performance Tuning
- performance tuning
- Tuning best practices
tag_slugs:
- application-performance
- database-performance
- mysql-performance-tuning
- performance-tuning
- tuning-best-practices
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Dataset_size.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Compression Options in MySQL (Part 1)

Source: [Percona Blog](https://www.percona.com/blog/compression-options-in-mysql-part-1/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2018-11-23T15:18:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Over the last year, I have been pursuing a part time hobby project exploring ways to squeeze as much data as possible in MySQL. As you will see, there are quite a few different ways. Of course things like compression ratio matters a lot but, other items like performance of inserts, selects and updates, along … Continued

## Structure detectee

- H2: The compression options
- H2: The test datasets
- H2: The test queries
- H2: The metrics recorded
- H2: The procedure
- H2: First results: Traditional storage options
- H3: Inserting the data
- H3: Range selects
- H3: 20k updates
- H2: What we learned?
- H2: Next?

## Images et graphiques reperes

- featured / image: [Compression Options in MySQL (Part 1)](https://www.percona.com/wp-content/uploads/2026/03/Dataset_size.png)
- content / image: [Insert_time.png](https://www.percona.com/wp-content/uploads/2026/03/Insert_time.png)
- content / image: [data_written_inserts.png](https://www.percona.com/wp-content/uploads/2026/03/data_written_inserts.png)
- content / image: [select_time.png](https://www.percona.com/wp-content/uploads/2026/03/select_time.png)
- content / image: [20k_updates_time.png](https://www.percona.com/wp-content/uploads/2026/03/20k_updates_time.png)
- content / image: [data_written_20kupdates.png](https://www.percona.com/wp-content/uploads/2026/03/data_written_20kupdates.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
