---
title: MyRocks and LOCK IN SHARE MODE
source:
  name: Percona Blog
  url: https://www.percona.com/blog/myrocks-and-lock-in-share-mode/
  post_id: 16876
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2017-05-11T20:36:30'
published_at_gmt: '2017-05-11T20:36:30'
modified_at: '2026-05-05T18:40:32'
modified_at_gmt: '2026-05-05T18:40:32'
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
- Webinars
category_slugs:
- mysql
- storage-engine
- webinars
tags:
- locks
- MyRocks
- MySQL Troubleshooting Webinar
tag_slugs:
- locks
- myrocks
- mysql-troubleshooting-webinar
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/LOCK-IN-SHARE-MODE.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MyRocks and LOCK IN SHARE MODE

Source: [Percona Blog](https://www.percona.com/blog/myrocks-and-lock-in-share-mode/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2017-05-11T20:36:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at MyRocks and the LOCK IN SHARE MODE . Those who attended the March 30th webinar “MyRocks Troubleshooting” might remember our discussion with Yoshinori on LOCK IN SHARE MODE . I did more tests, and I can confirm that his words are true: LOCK IN SHARE MODE works in MyRocks. This quick example demonstrates this. The initial setup: MySQL CREATE TABLE t ( id int(11) NOT NULL, f varchar(100) DEFAULT NULL, PRIMARY KEY (id) ) ENGINE=ROCKSDB; insert into t values(12345, 'value1'), (54321, 'value2'); 1 2 3 4 5 6 7 CREATE TABLE t ( id int (11) NOT NULL , f varchar (100) DEFAULT NULL , PRIMARY KEY (id) ) ENGINE = ROCKSDB; insert into t values (12345, 'value1' ), (54321, 'value2' ); In … Continued

## Images et graphiques reperes

- featured / image: [MyRocks and LOCK IN SHARE MODE](https://www.percona.com/wp-content/uploads/2026/03/LOCK-IN-SHARE-MODE.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
