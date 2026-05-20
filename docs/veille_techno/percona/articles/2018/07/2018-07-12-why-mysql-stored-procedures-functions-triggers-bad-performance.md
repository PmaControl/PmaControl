---
title: Why MySQL Stored Procedures, Functions and Triggers Are Bad For Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-mysql-stored-procedures-functions-triggers-bad-performance/
  post_id: 18989
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2018-07-12T13:19:18'
published_at_gmt: '2018-07-12T13:19:18'
modified_at: '2026-05-05T20:26:05'
modified_at_gmt: '2026-05-05T20:26:05'
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
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- application performance
- database performance
- MySQL function calls
- MySQL triggers
- stored functions
- stored procedures
- triggers
tag_slugs:
- application-performance
- database-performance
- mysql-function-calls
- mysql-triggers
- stored-functions
- stored-procedures
- triggers
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/func1.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why MySQL Stored Procedures, Functions and Triggers Are Bad For Performance

Source: [Percona Blog](https://www.percona.com/blog/why-mysql-stored-procedures-functions-triggers-bad-performance/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2018-07-12T13:19:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL stored procedures, functions and triggers, are tempting constructs for application developers. However, as I discovered, there can be an impact on database performance when using MySQL stored routines. Not being entirely sure of what I was seeing during a customer visit, I set out to create some simple tests to measure the impact of … Continued

## Structure detectee

- H2: Why stored routines are not optimal performance-wise
- H2: Profiling MySQL stored functions
- H4: Visualizing all system calls from functions
- H4: When slow functions actually make a difference
- H4: Memory allocation
- H4: Conclusion
- H4: You May Also Like

## Images et graphiques reperes

- featured / image: [Why MySQL Stored Procedures, Functions and Triggers Are Bad For Performance](https://www.percona.com/wp-content/uploads/2026/03/func1.png)
- content / image: [Execution map for func1()](https://www.percona.com/wp-content/uploads/2026/03/func1-1024x555.png)
- content / image: [Execution map for func2()](https://www.percona.com/wp-content/uploads/2026/03/func2.png)
- content / image: [Execution map for func3()](https://www.percona.com/wp-content/uploads/2026/03/func3.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
