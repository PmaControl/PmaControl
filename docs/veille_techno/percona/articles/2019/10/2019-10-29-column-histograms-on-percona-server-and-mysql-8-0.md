---
title: Column Histograms on Percona Server and MySQL 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/column-histograms-on-percona-server-and-mysql-8-0/
  post_id: 21071
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2019-10-29T14:24:10'
published_at_gmt: '2019-10-29T14:24:10'
modified_at: '2026-05-05T17:33:50'
modified_at_gmt: '2026-05-05T17:33:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
- Open Source
- Percona Software
category_slugs:
- insight-for-developers
- mysql
- open-source
- percona-software
tags:
- MySQL
- Percona Software
tag_slugs:
- mysql
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Column-HIstorgrams.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Column Histograms on Percona Server and MySQL 8.0

Source: [Percona Blog](https://www.percona.com/blog/column-histograms-on-percona-server-and-mysql-8-0/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2019-10-29T14:24:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

From time to time you may have experienced that MySQL was not able to find the best execution plan for a query. You felt the query should have been faster. You felt that something didn’t work, but you didn’t realize exactly what. Maybe some of you did tests and discovered there was a better execution … Continued

## Structure detectee

- H2: What is a histogram
- H3: Singleton histogram
- H3: Equi-height histogram
- H2: How to use histograms
- H2: Where are the histogram statistics
- H2: Histogram maintenance
- H2: Sampling
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Column Histograms on Percona Server and MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Column-HIstorgrams.png)
- content / image: [MySQL Column HIstorgrams](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Column-HIstorgrams-300x168.png)
- content / image: [Schermata-2019-10-24-alle-01.12.11-1.png](https://www.percona.com/wp-content/uploads/2026/03/Schermata-2019-10-24-alle-01.12.11-1.png)
- content / image: [Schermata-2019-10-24-alle-01.15.59.png](https://www.percona.com/wp-content/uploads/2026/03/Schermata-2019-10-24-alle-01.15.59.png)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
