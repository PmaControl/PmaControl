---
title: Using Referential Constraints with Partitioned Tables in InnoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-referential-constraints-with-partitioned-tables-in-innodb/
  post_id: 21346
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2019-12-26T17:04:38'
published_at_gmt: '2019-12-26T17:04:38'
modified_at: '2026-05-05T20:05:41'
modified_at_gmt: '2026-05-05T20:05:41'
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
- Storage Engine
category_slugs:
- mysql
- storage-engine
tags:
- MySQL
- Storage Engine
tag_slugs:
- mysql
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/partioned-tables-innodb.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Referential Constraints with Partitioned Tables in InnoDB

Source: [Percona Blog](https://www.percona.com/blog/using-referential-constraints-with-partitioned-tables-in-innodb/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2019-12-26T17:04:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of our support customers approached us with the following problem the other day: Shell mysql> CREATE TABLE child_table ( `id` int unsigned auto_increment, `column1` varchar(64) NOT NULL, parent_id int unsigned NOT NULL, PRIMARY KEY (`id`), CONSTRAINT FOREIGN KEY (parent_id) REFERENCES parent_table (id)); ERROR 1215 (HY000): Cannot add foreign key constraint 1 2 3 4 5 6 7 mysql > CREATE TABLE child_table ( ` id ` int unsigned auto_increment , ` column1 ` varchar ( 64 ) NOT NULL , parent_id int unsigned NOT NULL , PRIMARY KEY ( ` id ` ) , CONSTRAINT FOREIGN KEY ( parent_id ) REFERENCES parent_table ( id ) ) ; ERROR 1215 ( HY000 ) : Cannot add foreign key constraint They could not create a table with an FK relation! So, of course, we asked to see the parent table definition, which was: Shell CREATE TABLE `parent_table` ( `id` int unsigned auto_increment, `column1` varchar(64) COLLATE...

## Structure detectee

- H2: Testing the Triggers:
- H2: Test Update:
- H2: Test Delete:

## Images et graphiques reperes

- featured / image: [Using Referential Constraints with Partitioned Tables in InnoDB](https://www.percona.com/wp-content/uploads/2026/03/partioned-tables-innodb.png)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
