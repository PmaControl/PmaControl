---
title: Measuring failover time for ScaleArc load balancer
source:
  name: Percona Blog
  url: https://www.percona.com/blog/measuring-failover-time-for-scalearc-load-balancer/
  post_id: 8370
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2014-08-19T12:00:32'
published_at_gmt: '2014-08-19T12:00:32'
modified_at: '2026-05-04T19:43:32'
modified_at_gmt: '2026-05-04T19:43:32'
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
- Percona Services
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-services
- percona-software
tags:
- failover time
- load balancer
- MHA
- ScaleArc
tag_slugs:
- failover-time
- load-balancer
- mha
- scalearc
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Measuring failover time for ScaleArc load balancer

Source: [Percona Blog](https://www.percona.com/blog/measuring-failover-time-for-scalearc-load-balancer/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2014-08-19T12:00:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ScaleArc hired Percona to benchmark failover times for the ScaleArc database traffic management software in different scenarios. We tested failover times for various clustered setups, where ScaleArc itself was the load balancer for the cluster. These tests complement other performance tests on the ScaleArc software – sysbench testing for latency and testing for WordPress acceleration. … Continued

## Structure detectee

- H2: ScaleArc+MHA
- H3: ScaleArc+MHA manual switchover
- H3: ScaleArc+MHA non-graceful failover
- H2: ScaleArc+Percona XtraDB Cluster tests
- H2: Conclusion

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
