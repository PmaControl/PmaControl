---
title: Modeling InnoDB Scalability on Multi-Core Servers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/modeling-innodb-scalability-on-multi-core-servers/
  post_id: 2663
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-01-26T14:18:03'
published_at_gmt: '2011-01-26T14:18:03'
modified_at: '2026-03-23T21:50:55'
modified_at_gmt: '2026-03-23T21:50:55'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- MySQL
category_slugs:
- hardware-and-storage
- mysql
tags:
- Mat Keep
- Scalability
- sysbench
tag_slugs:
- mat-keep
- scalability
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/readonly-usl-model-vs-actual.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Modeling InnoDB Scalability on Multi-Core Servers

Source: [Percona Blog](https://www.percona.com/blog/modeling-innodb-scalability-on-multi-core-servers/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-01-26T14:18:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Mat Keep’s blog post on InnoDB-vs-MyISAM benchmarks that Oracle recently published prompted me to do some mathematical modeling of InnoDB’s scalability as the number of cores in the server increases. Vadim runs lots of benchmarks that measure what happens under increasing concurrency while holding the hardware constant, but not as many with varying numbers of … Continued

## Images et graphiques reperes

- featured / image: [Modeling InnoDB Scalability on Multi-Core Servers](https://www.percona.com/wp-content/uploads/2026/03/readonly-usl-model-vs-actual.png)
- content / image: [Read-Only Results](https://www.percona.com/wp-content/uploads/2026/03/readonly-usl-model-vs-actual-300x225.png)
  Caption: Read-Only Results
- content / image: [Read-Write Results](https://www.percona.com/wp-content/uploads/2026/03/readwrite-usl-model-vs-actual-300x225.png)
  Caption: Read-Write Results

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
