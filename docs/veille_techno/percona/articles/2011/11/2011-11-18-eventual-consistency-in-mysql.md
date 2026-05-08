---
title: Eventual Consistency in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/eventual-consistency-in-mysql/
  post_id: 3196
source_author:
  name: Bill Karwin
  slug: bill-karwin
  url: https://www.percona.com/blog/author/bill-karwin/
  website: http://www.percona.com/blog
published_at: '2011-11-18T16:47:51'
published_at_gmt: '2011-11-18T16:47:51'
modified_at: '2026-05-05T17:38:48'
modified_at_gmt: '2026-05-05T17:38:48'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Eventual Consistency in MySQL

Source: [Percona Blog](https://www.percona.com/blog/eventual-consistency-in-mysql/)

Auteur source: [Bill Karwin](https://www.percona.com/blog/author/bill-karwin/)

Publication: 2011-11-18T16:47:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We’re told that foreign key constraints are the best way to enforce logical referential integrity (RI) in SQL, preventing rows from becoming orphaned. But then we learn that the enforcement of foreign keys incurs a significant performance overhead.1,2 MySQL allows us to set FOREIGN_KEY_CHECKS=0 to disable enforcement of RI when the overhead is too high. … Continued

## Structure detectee

- H2: Quality Control Queries
- H2: Generating SQL with SQL
- H2: Using The Quality Control Query
- H2: DIY RI
- H2: Conclusion

## Auteur source

Bill Karwin has been a software professional for over 20 years. He's helped thousands of developers with SQL technology. Bill authored the book "SQL Antipatterns," collecting frequent blunders and showing better solutions.
