---
title: Multi Range Read (MRR) in MySQL 5.6 and MariaDB 5.5
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multi-range-read-mrr-in-mysql-5-6-and-mariadb-5-5/
  post_id: 3425
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2012-03-21T15:08:27'
published_at_gmt: '2012-03-21T15:08:27'
modified_at: '2026-04-28T21:34:03'
modified_at_gmt: '2026-04-28T21:34:03'
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
- InnoDB
- MariaDB
- mrr_buffer
- multi range read
- MySQL
- MySQL Index Scan
- MySQL Indexes
- Optimization
- Optimizer
- query
- random read
- range lookup
- read_rnd_buffer
- secondary key lookup
- sequential read
tag_slugs:
- innodb
- mariadb
- mrr_buffer
- multi-range-read
- mysql
- mysql-index-scan
- mysql-indexes
- optimization
- optimizer
- query
- random-read
- range-lookup
- read_rnd_buffer
- secondary-key-lookup
- sequential-read
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/oimg-12.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multi Range Read (MRR) in MySQL 5.6 and MariaDB 5.5

Source: [Percona Blog](https://www.percona.com/blog/multi-range-read-mrr-in-mysql-5-6-and-mariadb-5-5/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2012-03-21T15:08:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the second blog post in the series of blog posts leading up to the talk comparing the optimizer enhancements in MySQL 5.6 and MariaDB 5.5. This blog post is aimed at the optimizer enhancement Multi Range Read (MRR). Its available in both MySQL 5.6 and MariaDB 5.5 Now let’s take a look at … Continued

## Structure detectee

- H3: Multi Range Read
- H3: Benchmark results
- H4: In-memory workload
- H4: IO bound workload
- H4: MySQL Status Counters
- H3: Other Observations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Multi Range Read (MRR) in MySQL 5.6 and MariaDB 5.5](https://www.percona.com/wp-content/uploads/2026/03/oimg-12.png)
- content / image: [oimg-13.png](https://www.percona.com/wp-content/uploads/2026/03/oimg-13.png)
