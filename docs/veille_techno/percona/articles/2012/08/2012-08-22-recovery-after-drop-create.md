---
title: Recovery after DROP & CREATE
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recovery-after-drop-create/
  post_id: 3721
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2012-08-22T13:43:35'
published_at_gmt: '2012-08-22T13:43:35'
modified_at: '2026-05-04T21:50:47'
modified_at_gmt: '2026-05-04T21:50:47'
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
- drop table
- InnoDB
- Recovery
tag_slugs:
- drop-table
- innodb
- recovery
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recovery after DROP & CREATE

Source: [Percona Blog](https://www.percona.com/blog/recovery-after-drop-create/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2012-08-22T13:43:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a very popular data loss scenario a table is dropped and empty one is created with the same name. This is because mysqldump in many cases generates the “DROP TABLE” instruction before the “CREATE TABLE”: DROP TABLE IF EXISTS `actor`; /*!40101 SET @saved_cs_client = @@character_set_client */; /*!40101 SET character_set_client = utf8 */; CREATE TABLE `actor` ( `actor_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT, `first_name` varchar(45) NOT NULL, `last_name` varchar(45) NOT NULL, `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`actor_id`), KEY `idx_actor_last_name` (`last_name`) ) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8; /*!40101 SET character_set_client = @saved_cs_client */; 1 2 3 4 5 6 7 8 9 10 11 12 DROP TABLE IF EXISTS ` actor ` ; /*!40101 SET @saved_cs_client = @@character_set_client */ ; /*!40101 SET charac...

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
