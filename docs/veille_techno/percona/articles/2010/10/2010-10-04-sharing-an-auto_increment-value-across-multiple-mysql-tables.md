---
title: Sharing an auto_increment value across multiple MySQL tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/sharing-an-auto_increment-value-across-multiple-mysql-tables/
  post_id: 2453
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2010-10-04T23:27:36'
published_at_gmt: '2010-10-04T23:27:36'
modified_at: '2026-05-04T21:28:52'
modified_at_gmt: '2026-05-04T21:28:52'
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
- Insight for Developers
- MySQL
category_slugs:
- benchmarks
- insight-for-developers
- mysql
tags:
- auto_increment
- autoincrement
- MySQL
- Tips
tag_slugs:
- auto_increment
- autoincrement
- mysql
- tips
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/results-autoincrement-test.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Sharing an auto_increment value across multiple MySQL tables

Source: [Percona Blog](https://www.percona.com/blog/sharing-an-auto_increment-value-across-multiple-mysql-tables/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2010-10-04T23:27:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The title is SEO bait – you can’t do it. We’ve seen a few recurring patterns trying to achieve similar – and I thought I would share with you my favorite two: Option #1: Use a table to insert into, and grab the insert_id: CREATE TABLE option1 (id int not null primary key auto_increment) engine=innodb; # each insert does one operations to get the value: INSERT INTO option1 VALUES (NULL); # $connection->insert_id(); 1 2 3 4 5 CREATE TABLE option1 ( id int not null primary key auto_increment ) engine = innodb ; # each insert does one operations to get the value: INSERT INTO option1 VALUES ( NULL ) ; # $connection->insert_id(); Option #2: Use a table with one just row: CREATE TABLE option2 (id int not null primary key) engine=innodb; INSERT INTO option2 VALUES (1); # start from 1 # each insert does two operations to get the value: UPDATE option2 SET id=@id:=id+1; SELECT @id; 1 2 3 4 5 6 C...

## Images et graphiques reperes

- featured / image: [Sharing an auto_increment value across multiple MySQL tables](https://www.percona.com/wp-content/uploads/2026/03/results-autoincrement-test.png)

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
