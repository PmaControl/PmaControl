---
title: Creating Custom Sysbench Scripts
source:
  name: Percona Blog
  url: https://www.percona.com/blog/creating-custom-sysbench-scripts/
  post_id: 20240
source_author:
  name: Matthew Boehm
  slug: matthew-boehm
  url: https://www.percona.com/blog/author/matthew-boehm/
  website: https://www.percona.com/training
published_at: '2019-04-25T11:44:46'
published_at_gmt: '2019-04-25T11:44:46'
modified_at: '2026-04-27T21:13:58'
modified_at_gmt: '2026-04-27T21:13:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- Insight for Developers
- Monitoring
- MySQL
- Percona Services
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- monitoring
- mysql
- percona-services
tags:
- Benchmarks
- database performance
- MySQL
- sysbench
tag_slugs:
- benchmarks
- database-performance
- mysql
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/sysbench-lua-for-benchmark-tooling.jpg
image_count: 1
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Creating Custom Sysbench Scripts

Source: [Percona Blog](https://www.percona.com/blog/creating-custom-sysbench-scripts/)

Auteur source: [Matthew Boehm](https://www.percona.com/blog/author/matthew-boehm/)

Publication: 2019-04-25T11:44:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Sysbench has long been established as the de facto standard when it comes to benchmarking MySQL performance. Percona relies on it daily, and even Oracle uses it when blogging about new features in MySQL 8. Sysbench comes with several pre-defined benchmarking tests. These tests are written in an easy-to-understand scripting language called Lua. Some of … Continued

## Structure detectee

- H2: Sysbench API
- H2: Sanity checks and options
- H2: The queries
- H2: Parse and execute
- H2: Handle inserts
- H2: Example run
- H2: Conclusion

## Images et graphiques reperes

- featured / graph_or_chart: [Creating Custom Sysbench Scripts](https://www.percona.com/wp-content/uploads/2026/03/sysbench-lua-for-benchmark-tooling.jpg)

## Auteur source

Matthew joined Percona in the fall of 2012 as a MySQL Consultant; now Principal Architect / Senior Instructor. His areas of knowledge include the traditional LAMP stack, MySQL high availability, massive sharding topologies, and PHP/GoLang/C/C++ MySQL development. Previously, Matthew was a DBA for the 5th largest world-wide MySQL installation at eBay/PayPal. During his off-hours, Matthew is a nationally ranked, competitive West Coast Swing dancer and travels to competitions around the US. He enjoys working out, camping, biking, and shooting Junior-Olympic Recurve Archery with his oldest son.
