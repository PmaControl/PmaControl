---
title: How (not) to find unused indexes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-not-to-find-unused-indexes/
  post_id: 2024
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2009-10-16T16:02:38'
published_at_gmt: '2009-10-16T16:02:38'
modified_at: '2026-05-04T21:25:18'
modified_at_gmt: '2026-05-04T21:25:18'
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
- explain
- indexing
- MySQL
- Tips
tag_slugs:
- explain
- indexing
- mysql
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How (not) to find unused indexes

Source: [Percona Blog](https://www.percona.com/blog/how-not-to-find-unused-indexes/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2009-10-16T16:02:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve seen a few people link to an INFORMATION_SCHEMA query to be able to find any indexes that have low cardinality, in an effort to find out what indexes should be removed.Â This method is flawed – here’s the first reason why: CREATE TABLE `sales` ( `id` int(11) NOT NULL AUTO_INCREMENT, `customer_id` int(11) DEFAULT NULL, `status` enum('archived','active') DEFAULT NULL, PRIMARY KEY (`id`), KEY `status` (`status`) ) ENGINE=MyISAM AUTO_INCREMENT=65691 DEFAULT CHARSET=latin1; mysql> SELECT count(*), status FROM sales GROUP by status; +----------+---------+ | count(*) | statusÂ | +----------+---------+ |Â Â Â 65536 | archived | |Â Â Â Â Â 154 | activeÂ | +----------+---------+ 2 rows in set (0.17 sec) mysql> EXPLAIN SELECT * FROM sales WHERE status='active'; # query 1 +----+-------------+-------+------+---------------+--------+---------+-------+------+-------------+ | id | select_type |...

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
