---
title: 'When MySQL Lies: Wrong seconds_behind_master with slave_parallel_workers > 0'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/wrong-seconds_behind_master-with-slave_parallel_workers-0/
  post_id: 16263
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2017-01-27T19:03:48'
published_at_gmt: '2017-01-27T19:03:48'
modified_at: '2026-05-05T18:30:50'
modified_at_gmt: '2026-05-05T18:30:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Bugs
- Multi-threaded replication
- MySQL
tag_slugs:
- bugs
- multi-threaded-replication
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When MySQL Lies: Wrong seconds_behind_master with slave_parallel_workers > 0

Source: [Percona Blog](https://www.percona.com/blog/wrong-seconds_behind_master-with-slave_parallel_workers-0/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2017-01-27T19:03:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In today’s blog, I will show an issue with seconds_behind_master that one of our clients faced when running slave_parallel_workers > 0. We found out that the reported seconds_behind_master from SHOW SLAVE STATUS was lying. To be more specific, I’m talking about bugs #84415 and #1654091. The Issue MySQL will not report the correct slave lag … Continued

## Structure detectee

- H2: The Issue
- H2: The Workaround
- H2: Summary

## Images et graphiques reperes

- featured / image: [When MySQL Lies: Wrong seconds_behind_master with slave_parallel_workers > 0](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
