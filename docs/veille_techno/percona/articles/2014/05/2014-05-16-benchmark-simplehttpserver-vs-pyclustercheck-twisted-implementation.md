---
title: 'Benchmark: SimpleHTTPServer vs pyclustercheck (twisted implementation)'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/benchmark-simplehttpserver-vs-pyclustercheck-twisted-implementation/
  post_id: 8159
source_author:
  name: David Busby
  slug: david-busby
  url: https://www.percona.com/blog/author/david-busby/
  website: http://www.percona.com
published_at: '2014-05-16T10:00:10'
published_at_gmt: '2014-05-16T10:00:10'
modified_at: '2026-05-05T21:57:43'
modified_at_gmt: '2026-05-05T21:57:43'
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
- clustercheck
- David Busby
- pxc
- pyclustercheck
- Python
- python-twisted
tag_slugs:
- clustercheck
- david-busby
- pxc
- pyclustercheck
- python
- python-twisted
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_throughput.png
image_count: 12
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Benchmark: SimpleHTTPServer vs pyclustercheck (twisted implementation)

Source: [Percona Blog](https://www.percona.com/blog/benchmark-simplehttpserver-vs-pyclustercheck-twisted-implementation/)

Auteur source: [David Busby](https://www.percona.com/blog/author/david-busby/)

Publication: 2014-05-16T10:00:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Github user Adrianlzt provided a python-twisted alternative version of pyclustercheck per discussion on issue 7. Due to sporadic performance issues noted with the original implementation in SimpleHTTPserver, the benchmarks which I’ve included as part of the project on github use mutli-mechanize library, cache time 1 sec 2 x 100 thread pools 60s ramp up time … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [Benchmark: SimpleHTTPServer vs pyclustercheck (twisted implementation)](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_throughput.png)
- content / image: [All_Transactions_response_times_intervals](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times_intervals.png)
- content / image: [All_Transactions_response_times](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times.png)
- content / image: [All_Transactions_throughput](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_throughput1.png)
- content / image: [All_Transactions_response_times_intervals](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times_intervals1.png)
- content / image: [All_Transactions_response_times](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times1.png)
- content / image: [All_Transactions_response_times](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times2.png)
- content / image: [All_Transactions_response_times_intervals](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times_intervals2.png)
- content / image: [All_Transactions_throughput](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_throughput2.png)
- content / image: [All_Transactions_response_times](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times3.png)
- content / image: [All_Transactions_response_times_intervals](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_response_times_intervals3.png)
- content / image: [All_Transactions_throughput](https://www.percona.com/wp-content/uploads/2026/03/All_Transactions_throughput3.png)

## Auteur source

David is an Information Security Architect, and CISSP qualified. He has worked with Percona since 2013 and has over 17 years' experience in DevOps, databases and security. David is a Ju-Jitsu instructor, assistant scout leader and also volunteers at a local secondary school to teach kids computing.
