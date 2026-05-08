---
title: 'MySQL: Using UNION, INTERSECT, & EXCEPT'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-using-union-intersect-except/
  post_id: 26219
source_author:
  name: David Stokes
  slug: david-stokes
  url: https://www.percona.com/blog/author/david-stokes/
  website: ''
published_at: '2022-11-09T12:51:22'
published_at_gmt: '2022-11-09T12:51:22'
modified_at: '2026-03-26T20:30:49'
modified_at_gmt: '2026-03-26T20:30:49'
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
- Percona Software
category_slugs:
- insight-for-developers
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Using-UNION-INTERSECT-EXCEPT.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL: Using UNION, INTERSECT, & EXCEPT

Source: [Percona Blog](https://www.percona.com/blog/mysql-using-union-intersect-except/)

Auteur source: [David Stokes](https://www.percona.com/blog/author/david-stokes/)

Publication: 2022-11-09T12:51:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 8.0.31 added INTERSECT and EXCEPT to augment the long-lived UNION operator. That is the good news. The bad news is that you have to be careful using the EXCEPT operator as there is a trick. Let’s start with some simple tables and load some simple data. SQL > create table a (id int, nbr int);<br>Query OK, 0 rows affected (0.0180 sec)<br>SQL > create table b (id int, nbr int);<br>Query OK, 0 rows affected (0.0199 sec)<br>SQL > insert into a (id,nbr) values (1,10),(3,30),(5,50),(7,70);<br>Query OK, 4 rows affected (0.0076 sec)<br><br>Records: 4 Duplicates: 0 Warnings: 0<br>SQL > insert into b (id,nbr) values (1,10),(2,20),(3,30),(4,40);<br>Query OK, 4 rows affected (0.0159 sec)<br><br>Records: 4 Duplicates: 0 Warnings: 0<br> 1 SQL & gt ; create table a ( id int , nbr int ) ; < br > Query OK , 0 rows affected ( 0.0180 sec ) < br > SQL & gt ; create table b ( id int , nbr int ) ; < b...

## Images et graphiques reperes

- featured / image: [MySQL: Using UNION, INTERSECT, & EXCEPT](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Using-UNION-INTERSECT-EXCEPT.png)
- content / image: [MySQL: Using UNION, INTERSECT, & EXCEPT](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Using-UNION-INTERSECT-EXCEPT-300x157.png)

## Auteur source

David Stokes is a Technology Evangelist for Percona Corporation, is the author of MySQL & JSON - A Practical Programming Guide, and resides in Texas.
