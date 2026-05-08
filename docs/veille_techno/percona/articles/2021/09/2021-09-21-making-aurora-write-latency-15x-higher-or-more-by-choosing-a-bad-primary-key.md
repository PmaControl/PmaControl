---
title: Making Aurora Write Latency 15x Higher (or More!) by Choosing a Bad Primary Key
source:
  name: Percona Blog
  url: https://www.percona.com/blog/making-aurora-write-latency-15x-higher-or-more-by-choosing-a-bad-primary-key/
  post_id: 24861
source_author:
  name: Francisco Bordenave
  slug: francisco-bordenave
  url: https://www.percona.com/blog/author/francisco-bordenave/
  website: ''
published_at: '2021-09-21T15:00:25'
published_at_gmt: '2021-09-21T15:00:25'
modified_at: '2026-04-28T15:00:34'
modified_at_gmt: '2026-04-28T15:00:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- cloud
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Aurora-MySQL-Write-Latency.png
image_count: 8
graph_or_chart_count: 4
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Making Aurora Write Latency 15x Higher (or More!) by Choosing a Bad Primary Key

Source: [Percona Blog](https://www.percona.com/blog/making-aurora-write-latency-15x-higher-or-more-by-choosing-a-bad-primary-key/)

Auteur source: [Francisco Bordenave](https://www.percona.com/blog/author/francisco-bordenave/)

Publication: 2021-09-21T15:00:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Primary Key design is an important thing for InnoDB performance, and choosing a poor PK definition will have an impact on performance and also write propagation in databases. When this comes to Aurora, this impact is even worse than you may notice. In short, we consider a poor definition of a Primary Key in InnoDB … Continued

## Structure detectee

- H2: The Analysis
- H2: Conclusion

## Images et graphiques reperes

- featured / graph_or_chart: [Making Aurora Write Latency 15x Higher (or More!) by Choosing a Bad Primary Key](https://www.percona.com/wp-content/uploads/2026/03/Aurora-MySQL-Write-Latency.png)
- content / graph_or_chart: [Aurora MySQL Write Latency](https://www.percona.com/wp-content/uploads/2026/03/Aurora-MySQL-Write-Latency-300x168.png)
- content / graph_or_chart: [Amazon Aurora Latency](https://www.percona.com/wp-content/uploads/2026/03/Captura-de-Pantalla-2021-09-17-a-las-11.10.45-1024x357.png)
- content / image: [Captura-de-Pantalla-2021-09-17-a-las-11.18.18-1024x358.png](https://www.percona.com/wp-content/uploads/2026/03/Captura-de-Pantalla-2021-09-17-a-las-11.18.18-1024x358.png)
- content / image: [MySQL Client Thread Activity](https://www.percona.com/wp-content/uploads/2026/03/Captura-de-Pantalla-2021-09-17-a-las-11.18.51-1024x365.png)
- content / image: [Captura-de-Pantalla-2021-09-17-a-las-11.08.21-1024x357.png](https://www.percona.com/wp-content/uploads/2026/03/Captura-de-Pantalla-2021-09-17-a-las-11.08.21-1024x357.png)
- content / image: [MySQL InnoDB](https://www.percona.com/wp-content/uploads/2026/03/Captura-de-Pantalla-2021-09-17-a-las-11.15.39-1024x356.png)
- content / graph_or_chart: [Cloudwatch for Aurora Write latency](https://www.percona.com/wp-content/uploads/2026/03/Captura-de-Pantalla-2021-09-17-a-las-16.50.32.png)

## Auteur source

Francisco has been working in MySQL since 2006, he has worked for several companies which includes Health Care industry to Gaming. Over the last 6 years he has been working as a Remote DBA and Database Consultant which help him to acquire a lot of technical and multi-cultural skills. He lives in La Plata, Argentina and during his free time he likes to play football, spent time with family and friends and cook.
