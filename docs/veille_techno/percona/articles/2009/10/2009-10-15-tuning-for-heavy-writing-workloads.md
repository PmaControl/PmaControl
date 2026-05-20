---
title: Tuning for heavy writing workloads
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tuning-for-heavy-writing-workloads/
  post_id: 2049
source_author:
  name: Yasufumi Kinoshita
  slug: yasufumi
  url: https://www.percona.com/blog/author/yasufumi/
  website: http://www.percona.com/blog
published_at: '2009-10-15T00:06:55'
published_at_gmt: '2009-10-15T00:06:55'
modified_at: '2026-04-28T21:03:44'
modified_at_gmt: '2026-04-28T21:03:44'
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
- Patches
- Tuning
- XtraDB
tag_slugs:
- innodb
- patches
- tuning
- xtradb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/purge_thread_test_1ST_TUNE.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tuning for heavy writing workloads

Source: [Percona Blog](https://www.percona.com/blog/tuning-for-heavy-writing-workloads/)

Auteur source: [Yasufumi Kinoshita](https://www.percona.com/blog/author/yasufumi/)

Publication: 2009-10-15T00:06:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For the my previous post, there was comment to suggest to test db_STRESS benchmark on XtraDB by Dimitri. And I tested and tuned for the benchmark. I will show you the tunings. It should be also tuning procedure for general heavy writing workloads. At first, <tuning peak performance>. The next, <tuning purge operation> to stabilize … Continued

## Images et graphiques reperes

- featured / image: [Tuning for heavy writing workloads](https://www.percona.com/wp-content/uploads/2026/03/purge_thread_test_1ST_TUNE.png)
- content / image: [purge_thread_test_TPS](https://www.percona.com/wp-content/uploads/2026/03/purge_thread_test_TPS.png)
- content / image: [purge_thread_test_HIST_LENGTH](https://www.percona.com/wp-content/uploads/2026/03/purge_thread_test_HIST_LENGTH.png)
- content / image: [purge_thread_test_2_TPS](https://www.percona.com/wp-content/uploads/2026/03/purge_thread_test_2_TPS.png)

## Auteur source

Yasufumi is a former Percona employee. Yasufumi is one of the foremost InnoDB experts in the world, and has been researching and improving InnoDB internals for years. He is the original author of the first InnoDB scalability enhancements and has developed many improvements to InnoDB's internal algorithms such as flushing, locking, and recovery.
