---
title: How Apache Spark makes your slow MySQL queries 10x faster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/apache-spark-makes-slow-mysql-queries-10x-faster/
  post_id: 15519
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2016-08-17T15:26:55'
published_at_gmt: '2016-08-17T15:26:55'
modified_at: '2026-05-05T20:12:35'
modified_at_gmt: '2026-05-05T20:12:35'
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
- apache
- MySQL
- slow queries
tag_slugs:
- apache
- mysql
- slow-queries
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/slow-MySQL-queries.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Apache Spark makes your slow MySQL queries 10x faster

Source: [Percona Blog](https://www.percona.com/blog/apache-spark-makes-slow-mysql-queries-10x-faster/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2016-08-17T15:26:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss how to improve the performance of slow MySQL queries using Apache Spark. In my previous blog post, I wrote about using Apache Spark with MySQL for data analysis and showed how to transform and analyze a large volume of data (text files) with Apache Spark. Vadim also performed a … Continued

## Structure detectee

- H2: Speed up Slow MySQL Queries
- H2: Apache Spark Setup
- H3: Running MySQL queries via Apache Spark
- H3: MySQL Query Example
- H3: SQL in Spark
- H3: Inside MySQL
- H3: Pushing down the whole query into MySQL
- H3: Query cache in Spark
- H3: Using Spark with Percona XtraDB Cluster
- H3: Query Performance Benchmark
- H4: Note about partitioning
- H4: Where Spark doesn’t work well
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [How Apache Spark makes your slow MySQL queries 10x faster](https://www.percona.com/wp-content/uploads/2026/03/slow-MySQL-queries.jpg)
- content / image: [spark_jobs](https://www.percona.com/wp-content/uploads/2026/03/spark_jobs-scaled.png)
- content / image: [slow MySQL queries](https://www.percona.com/wp-content/uploads/2026/03/visual_explain_spark.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
