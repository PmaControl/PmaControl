---
title: 'MySQL 8.x DDL Rewriter and Query Rewriter Plugins: Implementation and Use Cases'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-x-ddl-rewriter-and-query-rewriter-plugins-implementation-and-use-cases/
  post_id: 22902
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2020-08-07T16:41:33'
published_at_gmt: '2020-08-07T16:41:33'
modified_at: '2026-04-27T22:11:36'
modified_at_gmt: '2026-04-27T22:11:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
tag_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8-DDL-Rewriter-and-Query-Rewriter.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.x DDL Rewriter and Query Rewriter Plugins: Implementation and Use Cases

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-x-ddl-rewriter-and-query-rewriter-plugins-implementation-and-use-cases/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2020-08-07T16:41:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Rewriting a MySQL query for performance is an important process that every DBA should be aware of so they can fix the wrong queries on runtime without code changes on the application end. ProxySQL has great support for rewriting the queries, which Alkin Tezuysal already explored in his excellent blog ProxySQL Query Rewrite Use Case. … Continued

## Structure detectee

- H2: Query Rewriter Plugin
- H3: Implementation
- H3: Test Case
- H4: (Remove the LOWER function from UPDATE to avoid the FTS)
- H3: Requirement
- H4: With LOWER Function
- H4: Without LOWER Function
- H2: DDL Rewriter Plugin
- H3: Implementation
- H3: Test Case
- H4: (Migrate table structure from source to destination without ENCRYPTION, DATA DIRECTORY, and INDEX DIRECTORY )
- H3: Requirement
- H3: Process
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL 8.x DDL Rewriter and Query Rewriter Plugins: Implementation and Use Cases](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8-DDL-Rewriter-and-Query-Rewriter.png)
- content / image: [MySQL 8 DDL Rewriter and Query Rewriter](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8-DDL-Rewriter-and-Query-Rewriter-300x157.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
