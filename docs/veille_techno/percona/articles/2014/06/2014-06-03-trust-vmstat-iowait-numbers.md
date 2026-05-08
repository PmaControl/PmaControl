---
title: Do not trust vmstat IOwait numbers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/trust-vmstat-iowait-numbers/
  post_id: 8204
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-06-03T10:00:44'
published_at_gmt: '2014-06-03T10:00:44'
modified_at: '2026-05-04T22:21:46'
modified_at_gmt: '2026-05-04T22:21:46'
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
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-software
tags:
- IOwait
- Percona Cloud Tools
- Peter Zaitsev
tag_slugs:
- iowait
- percona-cloud-tools
- peter-zaitsev
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/cpu_usage_05may.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Do not trust vmstat IOwait numbers

Source: [Percona Blog](https://www.percona.com/blog/trust-vmstat-iowait-numbers/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-06-03T10:00:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve been running a benchmark today on my old test box with conventional hard drives (no raid with BBU) and noticed something unusual in the CPU utilization statistics being reported. The benchmark was run like this: sysbench --num-threads=64 --max-requests=0 --max-time=600000 --report-interval=10 --test=oltp --db-driver=mysql --oltp-dist-type=special --oltp-table-size=1000000 --mysql-user=root --mysql-password=password run 1 sysbench -- num - threads = 64 -- max - requests = 0 -- max - time = 600000 -- report - interval = 10 -- test = oltp -- db - driver = mysql -- oltp - dist - type = special -- oltp - table - size = 1000000 -- mysql - user = root -- mysql - password = password run Which means: create 64 threads and hammer the database with queries as quickly as possible. As the test … Continued

## Images et graphiques reperes

- featured / image: [Do not trust vmstat IOwait numbers](https://www.percona.com/wp-content/uploads/2026/03/cpu_usage_05may.png)
- content / graph_or_chart: [CPU Usage graph after optimization](https://www.percona.com/wp-content/uploads/2026/03/cpu_usage_optimized_05may.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
