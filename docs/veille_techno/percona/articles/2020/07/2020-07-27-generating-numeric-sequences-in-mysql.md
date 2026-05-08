---
title: Generating Numeric Sequences in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/generating-numeric-sequences-in-mysql/
  post_id: 22159
source_author:
  name: Yura Sorokin
  slug: yura-sorokin
  url: https://www.percona.com/blog/author/yura-sorokin/
  website: ''
published_at: '2020-07-27T14:27:26'
published_at_gmt: '2020-07-27T14:27:26'
modified_at: '2026-04-27T21:36:13'
modified_at_gmt: '2026-04-27T21:36:13'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
- Percona Server for MySQL
tag_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Generating-Numeric-Sequences-in-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Generating Numeric Sequences in MySQL

Source: [Percona Blog](https://www.percona.com/blog/generating-numeric-sequences-in-mysql/)

Auteur source: [Yura Sorokin](https://www.percona.com/blog/author/yura-sorokin/)

Publication: 2020-07-27T14:27:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

What is the easiest way to generate a sequence of integers in MySQL? In other words, which “SELECT <something>” statement should I write to get 0, 1, 2, … N – 1? This is the question I have been struggling with for years and it looks like I have finally got the answer (although I … Continued

## Structure detectee

- H2: The Old School Way
- H3: UNION to the Rescue
- H3: Existing Table With a Unique Column
- H3: Session Variable Increment Within a SELECT
- H3: Joining Multiple Views
- H2: Classicism
- H3: Stored Procedures
- H3: Prepared Statements
- H3: Sequence Storage Engine (MariaDB)
- H2: Modern Way
- H3: Recursive Common Table Expressions (CTE)
- H3: VALUES ROW(…), ROW(…) …
- H3: JSON_TABLE()
- H2: Post-Modern Way
- H3: SEQUENCE_TABLE()
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [Generating Numeric Sequences in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Generating-Numeric-Sequences-in-MySQL.png)
- content / image: [Generating Numeric Sequences in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Generating-Numeric-Sequences-in-MySQL-300x168.png)

## Auteur source

Yura is a Principal Software Engineer at Percona, mostly working on Percona Server Core. You might have heard of him as an author of "Compressed Columns with Dictionaries", "SEQUENCE_TABLE()" and "C++ UDF wrappers". Before joining in July 2015 he was leading a cloud file service backend dev team which was focusing on client-side encryption. He has 20+ years of software development experience, primarily in C++. Yura holds Master degree in Computer Science from National Technical University of Ukraine. He lives in Kyiv, Ukraine.
