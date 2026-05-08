---
title: Is It a Read Intensive or a Write Intensive Workload?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/read-intensive-or-write-intensive-workload/
  post_id: 19254
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2018-08-30T11:19:45'
published_at_gmt: '2018-08-30T11:19:45'
modified_at: '2026-03-20T22:00:35'
modified_at_gmt: '2026-03-20T22:00:35'
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
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- Insight for Developers
- Monitoring
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- monitoring
- mysql
- percona-software
tags:
- Database Workloads
- read intensive
- write intensive
tag_slugs:
- database-workloads
- read-intensive
- write-intensive
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb-row-operations-featured.png
image_count: 9
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is It a Read Intensive or a Write Intensive Workload?

Source: [Percona Blog](https://www.percona.com/blog/read-intensive-or-write-intensive-workload/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2018-08-30T11:19:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the common ways to classify database workloads is whether it is “read intensive” or “write intensive”. In other words, whether the workload is dominated by reads or writes. Why should you care? Because recognizing if the workload is read intensive or write intensive will impact your hardware choices, database configuration as well as … Continued

## Structure detectee

- H2: Analyzing read/write workload by counts
- H2: Analyzing Read/Write Workload by Response Time
- H2: Summary
- H4: More resources that you might enjoy

## Images et graphiques reperes

- featured / image: [Is It a Read Intensive or a Write Intensive Workload?](https://www.percona.com/wp-content/uploads/2026/03/innodb-row-operations-featured.png)
- content / image: [analyzing read write workload by counts](https://www.percona.com/wp-content/uploads/2026/03/analyzing-read-write-workload-by-counts-1.png)
- content / image: [innodb row operations](https://www.percona.com/wp-content/uploads/2026/03/innodb-row-operations-2.png)
- content / image: [io activity](https://www.percona.com/wp-content/uploads/2026/03/io-activity-3.png)
- content / image: [top tables by row read](https://www.percona.com/wp-content/uploads/2026/03/top-tables-by-row-read-4.png)
- content / image: [top tables by rows changed](https://www.percona.com/wp-content/uploads/2026/03/top-tables-by-rows-changed-5.png)
- content / image: [query analytics providing time analysis](https://www.percona.com/wp-content/uploads/2026/03/query-time-analysis-6.png)
- content / graph_or_chart: [table operations dashboard](https://www.percona.com/wp-content/uploads/2026/03/table-operations-dashboard-7.png)
- content / image: [disk io load](https://www.percona.com/wp-content/uploads/2026/03/disk-io-load-8.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
