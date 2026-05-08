---
title: 'InnoDB adaptive flushing in MySQL 5.6: checkpoint age and io capacity'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-adaptive-flushing-in-mysql-5-6-checkpoint-age-and-io-capacity/
  post_id: 7501
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2013-10-30T08:00:31'
published_at_gmt: '2013-10-30T08:00:31'
modified_at: '2026-04-28T21:57:25'
modified_at_gmt: '2026-04-28T21:57:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- adaptive flushing
- flush_list
- InnoDB
- innodb_io_capacity
- innodb_io_capacity_max
- MySQL 5.6
- page_cleaner
tag_slugs:
- adaptive-flushing
- flush_list
- innodb
- innodb_io_capacity
- innodb_io_capacity_max
- mysql-5-6
- page_cleaner
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Rplot04.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB adaptive flushing in MySQL 5.6: checkpoint age and io capacity

Source: [Percona Blog](https://www.percona.com/blog/innodb-adaptive-flushing-in-mysql-5-6-checkpoint-age-and-io-capacity/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2013-10-30T08:00:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In MySQL 5.6 InnoDB has a dedicated thread (page_cleaner) that’s responsible for performing flushing operations. Page_cleaner performs flushing of the dirty pages from the buffer pool based on two factors: – access pattern – the least recently used pages will be flushed by LRU flusher from LRU_list when buffer pool has no free pages anymore; … Continued

## Images et graphiques reperes

- featured / image: [InnoDB adaptive flushing in MySQL 5.6: checkpoint age and io capacity](https://www.percona.com/wp-content/uploads/2026/03/Rplot04.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
