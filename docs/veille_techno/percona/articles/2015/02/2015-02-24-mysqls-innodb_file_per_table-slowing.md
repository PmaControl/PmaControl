---
title: Is MySQL’s innodb_file_per_table slowing you down?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysqls-innodb_file_per_table-slowing/
  post_id: 9011
source_author:
  name: Brock Wilson
  slug: brock-wilson
  url: https://www.percona.com/blog/author/brock-wilson/
  website: http://www.percona.com/blog/
published_at: '2015-02-24T11:00:48'
published_at_gmt: '2015-02-24T11:00:48'
modified_at: '2026-05-04T19:43:50'
modified_at_gmt: '2026-05-04T19:43:50'
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
- MySQL
- Percona Services
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-services
- percona-software
tags:
- Benchmarking
- innodb_file_per_table
- MySQL
- Percona Consulting Jobs
- Percona Server for MySQL
- Primary
tag_slugs:
- benchmarking
- innodb_file_per_table
- mysql
- percona-consulting-jobs
- percona-server
- primary
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is MySQL’s innodb_file_per_table slowing you down?

Source: [Percona Blog](https://www.percona.com/blog/mysqls-innodb_file_per_table-slowing/)

Auteur source: [Brock Wilson](https://www.percona.com/blog/author/brock-wilson/)

Publication: 2015-02-24T11:00:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL’s innodb_file_per_table is a wonderful thing – most of the time. Having every table use its own .ibd file allows you to easily reclaim space when dropping or truncating tables. But in some use cases, it may cause significant performance issues. Many of you in the audience are responsible for running automated tests on your … Continued

## Structure detectee

- H2: The innodb_file_per_table Test:

## Auteur source

Brock Wilson is a consultant at Percona, joining the team in December 2014. Previously spending 8 years at a large hosting company and domain registrar, his job roles have included MySQL DBA, Linux sysadmin and MySQL operations supervisor. Brock graduated from Arizona State University with a BAS in computer systems administration. He lives in Arizona with his family, where he spends his free time reading, binge watching TV shows, and enjoying the Arizona outdoors (weather permitting).
