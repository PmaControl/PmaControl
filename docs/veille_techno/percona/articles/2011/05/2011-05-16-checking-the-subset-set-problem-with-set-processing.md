---
title: Checking the subset sum set problem with set processing
source:
  name: Percona Blog
  url: https://www.percona.com/blog/checking-the-subset-set-problem-with-set-processing/
  post_id: 2931
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2011-05-16T07:00:01'
published_at_gmt: '2011-05-16T07:00:01'
modified_at: '2026-05-04T21:32:25'
modified_at_gmt: '2026-05-04T21:32:25'
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
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checking the subset sum set problem with set processing

Source: [Percona Blog](https://www.percona.com/blog/checking-the-subset-set-problem-with-set-processing/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2011-05-16T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Hi, Here is an easy way to run the subset sum check from SQL, which you can then distribute with Shard-Query: CREATE TABLE `the list` ( `id` bigint(20) NOT NULL AUTO_INCREMENT, `val` bigint(20) NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `id` (`id`) ) ENGINE=MyISAM; SELECT val as `val`, COUNT(DISTINCT (id)) as `cd` FROM test.data as d WHERE val in (-2,-3,-10,15,15,16) GROUP BY val; +-----+----------+----------+ | val | cd | CNT | +-----+----------+----------+ | -10 | 1 | 1 | | -3 | 1 | 1 | | -2 | 1 | 1 | | 15 | 35417088 | 35417088 | +-----+----------+----------+ 5 rows in set (40.20 sec) 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 CREATE TABLE ` the list ` ( ` id ` bigint ( 20 ) NOT NULL AUTO_INCREMENT , ` val ` bigint ( 20 ) NOT NULL DEFAULT '0' , PRIMARY KEY ( ` id ` ) , KEY ` id ` ( ` id ` ) ) ENGINE = MyISAM ; SELECT val as ` val ` , COUNT ( DISTINCT ( id ) ) as ` cd ` FROM...

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
