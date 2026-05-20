---
title: 'An Overview of Indexes in MySQL 8.0: MySQL CREATE INDEX, Functional Indexes, and More'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-0-functional-indexes/
  post_id: 25279
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2023-07-15T12:24:54'
published_at_gmt: '2023-07-15T12:24:54'
modified_at: '2026-03-26T20:29:28'
modified_at_gmt: '2026-03-26T20:29:28'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-Functional-Indexes.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# An Overview of Indexes in MySQL 8.0: MySQL CREATE INDEX, Functional Indexes, and More

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-0-functional-indexes/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2023-07-15T12:24:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog was originally published in January 2022 and was updated in July 2023. Working with hundreds of different customers, I often face similar problems around running queries. One very common problem when trying to optimize a database environment is index usage. A query that cannot use an index is usually a long-running one, consuming … Continued

## Structure detectee

- H2: Introduction to MySQL Indexes
- H3: What is an Index?
- H3: MySQL CREATE INDEX
- H2: The Well-Known Indexing Problem
- H2: What is a Function-based Index?
- H2: How Do MySQL 8.0 Functional Indexes Work?
- H2: Which Functional Indexes are Permitted
- H2: Functional Index Internal
- H2: Limitations of Functional Indexes
- H3: Upgrade to MySQL 8.0, or Get EOL Support for MySQL 5.7 with Percona
- H2: FAQs
- H3: What are indexes in MySQL, and why are they important for database performance?
- H3: How does the MySQL CREATE INDEX statement work, and what are the steps to create an index?
- H3: How do indexes affect database storage and resource usage in MySQL 8.0?
- H3: What considerations should be taken into account when choosing columns for indexing?

## Images et graphiques reperes

- featured / image: [An Overview of Indexes in MySQL 8.0: MySQL CREATE INDEX, Functional Indexes, and More](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-Functional-Indexes.png)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
