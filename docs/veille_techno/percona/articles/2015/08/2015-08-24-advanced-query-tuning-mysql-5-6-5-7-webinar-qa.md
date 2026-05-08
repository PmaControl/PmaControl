---
title: 'Advanced Query Tuning in MySQL 5.6 and MySQL 5.7 Webinar: Q&A'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/advanced-query-tuning-mysql-5-6-5-7-webinar-qa/
  post_id: 9923
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2015-08-24T14:16:52'
published_at_gmt: '2015-08-24T14:16:52'
modified_at: '2026-04-28T22:41:42'
modified_at_gmt: '2026-04-28T22:41:42'
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
- MySQL
- Webinars
category_slugs:
- mysql
- webinars
tags:
- Alexander Rubin
- InnoDB
- MySQL 5.6
- MySQL 5.7
- MySQL webinar
- query tuning
tag_slugs:
- alexander-rubin
- innodb
- mysql-5-6
- mysql-5-7
- mysql-webinar
- query-tuning
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Advanced Query Tuning in MySQL 5.6 and MySQL 5.7 Webinar: Q&A

Source: [Percona Blog](https://www.percona.com/blog/advanced-query-tuning-mysql-5-6-5-7-webinar-qa/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2015-08-24T14:16:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Thank you for attending my July 22 webinar titled “Advanced Query Tuning in MySQL 5.6 and 5.7” (my slides and a replay available here). As promised here is the list of questions and my answers (thank you for your great questions). Q: Here is the explain example: MySQL mysql> explain extended select id, site_id from test_index_id where site_id=1 *************************** 1. row *************************** id: 1 select_type: SIMPLE table: test_index_id type: ref possible_keys: key_site_id key: key_site_id key_len: 5 ref: const rows: 1 filtered: 100.00 Extra: Using where; Using index 1 2 3 4 5 6 7 8 9 10 11 12 13 mysql > explain extended select id, site_id from test_index_id where site_id = 1 *************************** 1. row *************************** id: 1 select_type: SIMPLE table : test_index_id type : ref possible_keys: key_site_id key : key_site_id key_len: 5 ref: const rows :...

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
