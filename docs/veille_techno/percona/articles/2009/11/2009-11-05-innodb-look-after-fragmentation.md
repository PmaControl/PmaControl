---
title: 'InnoDB: look after fragmentation'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-look-after-fragmentation/
  post_id: 2107
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-11-05T19:01:54'
published_at_gmt: '2009-11-05T19:01:54'
modified_at: '2026-04-28T21:05:04'
modified_at_gmt: '2026-04-28T21:05:04'
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
- InnoDB
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB: look after fragmentation

Source: [Percona Blog](https://www.percona.com/blog/innodb-look-after-fragmentation/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-11-05T19:01:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One problem made me puzzled for couple hours, but it was really interesting to figure out what’s going on. So let me introduce problem at first. The table is CREATE TABLE `c` ( `tracker_id` int(10) unsigned NOT NULL, `username` char(20) character set latin1 collate latin1_bin NOT NULL, `time_id` date NOT NULL, `block_id` int(10) unsigned default NULL, PRIMARY KEY (`tracker_id`,`username`,`time_id`), KEY `block_id` (`block_id`) ) ENGINE=InnoDB 1 2 3 4 5 6 7 8 CREATE TABLE ` c ` ( ` tracker_id ` int ( 10 ) unsigned NOT NULL , ` username ` char ( 20 ) character set latin1 collate latin1_bin NOT NULL , ` time_id ` date NOT NULL , ` block_id ` int ( 10 ) unsigned default NULL , PRIMARY KEY ( ` tracker_id ` , ` username ` , ` time_id ` ) , KEY ` block_id ` ( ` block_id ` ) ) ENGINE = InnoDB Table has 11864696 rows and takes Data_length: 698,351,616 bytes on disk The problem is that after re...

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
