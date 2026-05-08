---
title: Increasing slow MySQL query performance with the parallel query execution
source:
  name: Percona Blog
  url: https://www.percona.com/blog/increasing-slow-query-performance-with-parallel-query-execution/
  post_id: 7641
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2014-01-07T08:00:08'
published_at_gmt: '2014-01-07T08:00:08'
modified_at: '2026-05-05T16:52:55'
modified_at_gmt: '2026-05-05T16:52:55'
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
tags:
- MySQL Query Tuning
- parallelization
tag_slugs:
- mysql-query-tuning
- parallelization
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Increasing slow MySQL query performance with the parallel query execution

Source: [Percona Blog](https://www.percona.com/blog/increasing-slow-query-performance-with-parallel-query-execution/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2014-01-07T08:00:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL and Scaling-up (using more powerful hardware) was always a hot topic. Originally MySQL did not scale well with multiple CPUs; there were times when InnoDB performed poorer with more CPU cores than with fewer CPU cores. MySQL 5.6 can scale significantly better; however, there is still 1 big limitation: 1 SQL query will eventually … Continued

## Structure detectee

- H2: MySQL parallel query execution

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
