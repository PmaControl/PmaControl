---
title: 'Contention in MySQL InnoDB: Useful Info From the Semaphores Section'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/contention-in-mysql-innodb-useful-info-from-the-semaphores-section/
  post_id: 21348
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2019-12-20T17:33:18'
published_at_gmt: '2019-12-20T17:33:18'
modified_at: '2026-03-20T23:00:38'
modified_at_gmt: '2026-03-20T23:00:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MySQL
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- storage-engine
tags:
- MySQL
- Storage Engine
tag_slugs:
- mysql
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Contention-in-MySQL-InnoDB.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Contention in MySQL InnoDB: Useful Info From the Semaphores Section

Source: [Percona Blog](https://www.percona.com/blog/contention-in-mysql-innodb-useful-info-from-the-semaphores-section/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2019-12-20T17:33:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a high concurrency world, where more and more users->connections->threads are used, contention is a given. But how do we identify the contention point easily? Different approaches had been discussed previously, like the one using Perf and Flame graphs to track down the function taking way more time than expected. That method is great but … Continued

## Structure detectee

- H2: SEMAPHORES
- H3: Current Waits
- H2: Looking for Info
- H2: Looking Inside the Code
- H3: Finding the Repository
- H3: Finding the Release
- H3: Navigating the Code Tree
- H3: What was Going on Then?
- H2: In Conclusion

## Images et graphiques reperes

- featured / image: [Contention in MySQL InnoDB: Useful Info From the Semaphores Section](https://www.percona.com/wp-content/uploads/2026/03/Contention-in-MySQL-InnoDB.png)
- content / image: [Contention in MySQL InnoDB](https://www.percona.com/wp-content/uploads/2026/03/Contention-in-MySQL-InnoDB-300x168.png)
- content / image: [Screen-Shot-2019-12-16-at-6.34.16-1024x526.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-12-16-at-6.34.16-1024x526.png)
- content / image: [Screen-Shot-2019-12-16-at-7.28.55-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-12-16-at-7.28.55-scaled.png)
- content / image: [Screen-Shot-2019-12-16-at-7.34.51-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-12-16-at-7.34.51-scaled.png)
- content / image: [Screen-Shot-2019-12-16-at-7.36.29-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2019-12-16-at-7.36.29-scaled.png)

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
