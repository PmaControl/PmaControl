---
title: Query Planner Gotchas
source:
  name: Percona Blog
  url: https://www.percona.com/blog/query-planner-gotchas/
  post_id: 9576
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2011-06-29T05:34:33'
published_at_gmt: '2011-06-29T05:34:33'
modified_at: '2026-04-28T22:39:40'
modified_at_gmt: '2026-04-28T22:39:40'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Query Planner Gotchas

Source: [Percona Blog](https://www.percona.com/blog/query-planner-gotchas/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2011-06-29T05:34:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Indexes can reduce the amount of data your query touches by orders of magnitude. This results in a proportional query speedup. So what happens when you define a nice set of indexes and you don’t get the performance pop you were expecting? Consider the following example: PgSQL mysql> show create table t; | t | CREATE TABLE `t` ( `a` varchar(255) DEFAULT NULL, `b` bigint(20) NOT NULL DEFAULT '0', `c` bigint(20) NOT NULL DEFAULT '0', `d` bigint(20) DEFAULT NULL, `e` char(255) DEFAULT NULL, PRIMARY KEY (`b`,`c`), KEY `a` (`a`,`b`,`d`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1 1 2 3 4 5 6 7 8 9 10 mysql & gt; show create table t; | t | CREATE TABLE `t` ( `a` varchar (255) DEFAULT NULL , `b` bigint (20) NOT NULL DEFAULT '0' , `c` bigint (20) NOT NULL DEFAULT '0' , `d` bigint (20) DEFAULT NULL , `e` char (255) DEFAULT NULL , PRIMARY KEY (`b`,`c`), KEY `a` (`a`,`b`,`d`) ) ENGINE = InnoDB DEFAULT...

## Structure detectee

- H2: Take home messages
