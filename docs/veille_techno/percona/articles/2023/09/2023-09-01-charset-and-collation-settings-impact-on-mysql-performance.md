---
title: Understanding How MySQL Collation and Charset Settings Impact Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/charset-and-collation-settings-impact-on-mysql-performance/
  post_id: 20107
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2023-09-01T10:11:30'
published_at_gmt: '2023-09-01T10:11:30'
modified_at: '2026-03-26T20:27:19'
modified_at_gmt: '2026-03-26T20:27:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Character Sets
- MySQL Character Sets
tag_slugs:
- character-sets
- mysql-character-sets
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-utf8mb4.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding How MySQL Collation and Charset Settings Impact Performance

Source: [Percona Blog](https://www.percona.com/blog/charset-and-collation-settings-impact-on-mysql-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2023-09-01T10:11:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog was originally published in February 2019 and was updated in September 2023. Web applications rely on databases to run the internet, powering everything from e-commerce platforms to social media networks to streaming services. MySQL is one of the most popular database management systems, playing a pivotal role in the functionality and performance of … Continued

## Structure detectee

- H2: Understanding Character Sets and Encoding in MySQL
- H2: MySQL Collation and its Relationship with Character Sets
- H2: Choosing the Appropriate Character Set
- H2: Impact of Charset and Collation on Indexing Strategies
- H2: Charset and Collation Effects on Query Execution
- H2: Testing Read-Only CPU Intensive Workloads
- H2: My Testing Setup
- H2: After Testing Conclusions
- H2: Best Practices for Charset and Collation Optimization
- H2: Looking to upgrade to MySQL 8.0 or stay on 5.7? Percona can help.

## Images et graphiques reperes

- featured / image: [Understanding How MySQL Collation and Charset Settings Impact Performance](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-utf8mb4.png)
- content / image: [mysql-5.7-latin1-vs-utf8mb4.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-5.7-latin1-vs-utf8mb4.png)
- content / image: [MySQL 8.0 defaultcollations](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0-defaultcollations.png)
- content / image: [MySQL 5.7 utf8mb4](https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.7-utf8mb4.png)
- content / image: [mysql 8 and 5.7 default collation](https://www.percona.com/wp-content/uploads/2026/03/mysql-8-and-5.7-default-collation.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
