---
title: Nondeterministic Functions in MySQL (i.e. rand) Can Surprise You
source:
  name: Percona Blog
  url: https://www.percona.com/blog/nondeterministic-functions-in-mysql-i-e-rand-can-surprise-you/
  post_id: 19684
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2018-12-05T18:44:26'
published_at_gmt: '2018-12-05T18:44:26'
modified_at: '2026-05-05T20:25:34'
modified_at_gmt: '2026-05-05T20:25:34'
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
- function
- random
tag_slugs:
- function
- random
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-non-deterministic-functions-rand.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Nondeterministic Functions in MySQL (i.e. rand) Can Surprise You

Source: [Percona Blog](https://www.percona.com/blog/nondeterministic-functions-in-mysql-i-e-rand-can-surprise-you/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2018-12-05T18:44:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Working on a test case with sysbench, I encountered this: MySQL mysql> select * from sbtest1 where id = round(rand()*10000, 0); +------+--------+-------------------------------------------------------------------------------------------------------------------------+-------------------------------------------------------------+ | id | k | c | pad | +------+--------+-------------------------------------------------------------------------------------------------------------------------+-------------------------------------------------------------+ | 179 | 499871 | 09833083632-34593445843-98203182724-77632394229-31240034691-22855093589-98577647071-95962909368-34814236148-76937610370 | 62233363025-41327474153-95482195752-11204169522-13131828192 | | 1606 | 502031 | 81212399253-12831141664-41940957498-63947990218-16408477860-15124776228-42269003436-07293216458-45216889819-75452278174 | 254...

## Structure detectee

- H2: Deterministic vs nondeterministic functions
- H2: Other databases
- H2: Conclusion
- H4: MySQL stored functions: deterministic vs not deterministic

## Images et graphiques reperes

- featured / image: [Nondeterministic Functions in MySQL (i.e. rand) Can Surprise You](https://www.percona.com/wp-content/uploads/2026/03/MySQL-non-deterministic-functions-rand.jpg)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
