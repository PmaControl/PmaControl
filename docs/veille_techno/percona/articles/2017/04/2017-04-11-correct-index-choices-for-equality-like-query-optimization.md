---
title: Correct Index Choices for Equality + LIKE Query Optimization
source:
  name: Percona Blog
  url: https://www.percona.com/blog/correct-index-choices-for-equality-like-query-optimization/
  post_id: 3280
source_author:
  name: marcos.albe
  slug: marcos-albe
  url: https://www.percona.com/blog/author/marcos-albe/
  website: http://www.percona.com
published_at: '2017-04-11T19:51:48'
published_at_gmt: '2017-04-11T19:51:48'
modified_at: '2026-05-04T21:38:15'
modified_at_gmt: '2026-05-04T21:38:15'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- index
- MySQL
- Optimization
- queries
tag_slugs:
- index
- mysql
- optimization
- queries
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Query-Optimization.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Correct Index Choices for Equality + LIKE Query Optimization

Source: [Percona Blog](https://www.percona.com/blog/correct-index-choices-for-equality-like-query-optimization/)

Auteur source: [marcos.albe](https://www.percona.com/blog/author/marcos-albe/)

Publication: 2017-04-11T19:51:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As part of our support services, we do a lot of query optimization. This is where most performance gains come from. Here’s an example of the work we do. Some days ago a customer arrived with the following table: CREATE TABLE `infamous_table` ( `id` int(11) NOT NULL AUTO_INCREMENT, `member_id` int(11) NOT NULL DEFAULT '0', `email` varchar(200) NOT NULL DEFAULT '', `msg_type` varchar(255) NOT NULL DEFAULT '', `t2send` int(11) NOT NULL DEFAULT '0', `flag` char(1) NOT NULL DEFAULT '', `sent` varchar(100) NOT NULL DEFAULT '', PRIMARY KEY (`id`), KEY `f` (`flag`), KEY `email` (`email`), KEY `msg_type` (`msg_type`(5)), KEY `t_msg` (`t2send`,`msg_type`(5)) ) ENGINE=InnoDB DEFAULT CHARSET=latin1 1 2 3 4 5 6 7 8 9 10 11 12 13 14 CREATE TABLE ` infamous_table ` ( ` id ` int ( 11 ) NOT NULL AUTO_INCREMENT , ` member_id ` int ( 11 ) NOT NULL DEFAULT '0' , ` email ` varchar ( 200 ) NOT NULL DEFAULT...

## Structure detectee

- H4: Conclusion

## Images et graphiques reperes

- featured / image: [Correct Index Choices for Equality + LIKE Query Optimization](https://www.percona.com/wp-content/uploads/2026/03/Query-Optimization.jpg)
