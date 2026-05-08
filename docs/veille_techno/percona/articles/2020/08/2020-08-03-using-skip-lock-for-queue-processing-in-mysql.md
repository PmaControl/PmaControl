---
title: Using SKIP LOCK For Queue Processing in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-skip-lock-for-queue-processing-in-mysql/
  post_id: 22843
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2020-08-03T13:30:03'
published_at_gmt: '2020-08-03T13:30:03'
modified_at: '2026-04-27T22:09:52'
modified_at_gmt: '2026-04-27T22:09:52'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
- queue processing
- scaling
tag_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
- queue-processing
- scaling
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/SKIP-LOCK-in-MySQL.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using SKIP LOCK For Queue Processing in MySQL

Source: [Percona Blog](https://www.percona.com/blog/using-skip-lock-for-queue-processing-in-mysql/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2020-08-03T13:30:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A small thing that provides a huge help. The other day I was writing some code to process a very large amount of items coming from a social media API. My items were ending in a queue in MySQL and then needed to be processed and eventually moved. The task was not so strange, but … Continued

## Structure detectee

- H3: Conclusion
- H2: NOTE !!
- H3: References

## Images et graphiques reperes

- featured / image: [Using SKIP LOCK For Queue Processing in MySQL](https://www.percona.com/wp-content/uploads/2026/03/SKIP-LOCK-in-MySQL.png)
- content / image: [SKIP LOCK in MySQL](https://www.percona.com/wp-content/uploads/2026/03/SKIP-LOCK-in-MySQL-300x168.png)
- content / image: [Picture-1-1.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-1-1.png)
- content / image: [Picture-2-1.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-2-1.png)
- content / image: [Picture-3-1.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-3-1.png)
- content / image: [Picture-4-1.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-4-1.png)
- content / image: [Picture-15.png](https://www.percona.com/wp-content/uploads/2026/03/Picture-15.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
