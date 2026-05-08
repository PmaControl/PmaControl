---
title: A case for MariaDB’s Hash Joins
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-case-for-mariadbs-hash-joins/
  post_id: 3607
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2012-05-31T07:27:45'
published_at_gmt: '2012-05-31T07:27:45'
modified_at: '2026-05-05T17:39:50'
modified_at_gmt: '2026-05-05T17:39:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- batched key access
- block nested loop
- hash join
- JOIN Performance
- MariaDB
- Performance
tag_slugs:
- batched-key-access
- block-nested-loop
- hash-join
- join-performance
- mariadb
- performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/q23.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A case for MariaDB’s Hash Joins

Source: [Percona Blog](https://www.percona.com/blog/a-case-for-mariadbs-hash-joins/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2012-05-31T07:27:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MariaDB 5.3/5.5 has introduced a new join type “Hash Joins” which is an implementation of a Classic Block-based Hash Join Algorithm. In this post we will see what the Hash Join is, how it works and for what types of queries would it be the right choice. I will show the results of executing benchmarks … Continued

## Structure detectee

- H3: Overview
- H3: Benchmarks
- H4: Configuration
- H4: Benchmark Machine Specs
- H4: Table Structure
- H4: Test Cases
- H3: How does optimizer work with the different Join Algorithms available?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [A case for MariaDB’s Hash Joins](https://www.percona.com/wp-content/uploads/2026/03/q23.png)
- content / image: [q24.png](https://www.percona.com/wp-content/uploads/2026/03/q24.png)
- content / image: [q25.png](https://www.percona.com/wp-content/uploads/2026/03/q25.png)
- content / image: [q26.png](https://www.percona.com/wp-content/uploads/2026/03/q26.png)
