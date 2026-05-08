---
title: Watch Out for Disk I/O Performance Issues when Running EXT4
source:
  name: Percona Blog
  url: https://www.percona.com/blog/watch-out-for-disk-i-o-performance-issues-when-running-ext4/
  post_id: 21163
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2019-11-12T19:09:00'
published_at_gmt: '2019-11-12T19:09:00'
modified_at: '2026-04-29T14:46:37'
modified_at_gmt: '2026-04-29T14:46:37'
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
category_slugs:
- benchmarks
- mysql
tags:
- ext4
- Linux
tag_slugs:
- ext4
- linux
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Performance-Issues-When-Running-EXT4.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Watch Out for Disk I/O Performance Issues when Running EXT4

Source: [Percona Blog](https://www.percona.com/blog/watch-out-for-disk-i-o-performance-issues-when-running-ext4/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2019-11-12T19:09:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, at Percona Live Europe 2019, Dimitri Kravchuk from Oracle mentioned that he observed some unclear drop in performance for MySQL on an ext4 filesystem with the latest Linux kernels. I decided to check this case out on my side and found out that indeed, starting from linux kernel 4.9, there are some cases with … Continued

## Structure detectee

- H2: ext4 Performance Regression
- H4: Observations:
- H2: How it Affects MySQL
- H3: O_DIRECT
- H4: Observations
- H3: O_DSYNC
- H4: Observations:
- H2: Conclusions/workarounds

## Images et graphiques reperes

- featured / image: [Watch Out for Disk I/O Performance Issues when Running EXT4](https://www.percona.com/wp-content/uploads/2026/03/Performance-Issues-When-Running-EXT4.png)
- content / image: [innodb.fio_.ext4_.ssd_.nvme_.png](https://www.percona.com/wp-content/uploads/2026/03/innodb.fio_.ext4_.ssd_.nvme_.png)
- content / image: [innodb.sysbench.ext4_.ssd_.nvme_.png](https://www.percona.com/wp-content/uploads/2026/03/innodb.sysbench.ext4_.ssd_.nvme_.png)
- content / image: [innodb.sysbench.ext4_.nvme_.flags_.png](https://www.percona.com/wp-content/uploads/2026/03/innodb.sysbench.ext4_.nvme_.flags_.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
