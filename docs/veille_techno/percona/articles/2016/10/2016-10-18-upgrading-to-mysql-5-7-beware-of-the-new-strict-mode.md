---
title: Upgrading to MySQL 5.7? Beware of the new STRICT mode
source:
  name: Percona Blog
  url: https://www.percona.com/blog/upgrading-to-mysql-5-7-beware-of-the-new-strict-mode/
  post_id: 15829
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2016-10-18T23:45:20'
published_at_gmt: '2016-10-18T23:45:20'
modified_at: '2026-05-05T18:21:23'
modified_at_gmt: '2026-05-05T18:21:23'
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
- MySQL 5.7
- sql_mode
- strict mode
tag_slugs:
- mysql-5-7
- sql_mode
- strict-mode
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/STRICT-Mode.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Upgrading to MySQL 5.7? Beware of the new STRICT mode

Source: [Percona Blog](https://www.percona.com/blog/upgrading-to-mysql-5-7-beware-of-the-new-strict-mode/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2016-10-18T23:45:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post discusses the ramifications of STRICT mode in MySQL 5.7. In short By default, MySQL 5.7 is much “stricter” than older versions of MySQL. That can make your application fail. To temporarily fix this, change the SQL_MODE to NO_ENGINE_SUBSTITUTION (same as in MySQL 5.6): MySQL mysql> set global SQL_MODE="NO_ENGINE_SUBSTITUTION"; 1 mysql > set global SQL_MODE= "NO_ENGINE_SUBSTITUTION" ; MySQL 5.7, dates and default values The default … Continued

## Images et graphiques reperes

- featured / image: [Upgrading to MySQL 5.7? Beware of the new STRICT mode](https://www.percona.com/wp-content/uploads/2026/03/STRICT-Mode.jpg)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
