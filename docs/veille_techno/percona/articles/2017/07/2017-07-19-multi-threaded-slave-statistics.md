---
title: Multi-Threaded Slave Statistics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multi-threaded-slave-statistics/
  post_id: 17103
source_author:
  name: Juan Arruti
  slug: juan-arruti
  url: https://www.percona.com/blog/author/juan-arruti/
  website: ''
published_at: '2017-07-19T17:02:34'
published_at_gmt: '2017-07-19T17:02:34'
modified_at: '2026-05-05T18:45:16'
modified_at_gmt: '2026-05-05T18:45:16'
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
- MTS
- Multi-Threaded Slave
- MySQL
- Replication
- Statistics
tag_slugs:
- mts
- multi-threaded-slave
- mysql
- replication
- statistics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Multi-Threaded-Slave-Statistics.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multi-Threaded Slave Statistics

Source: [Percona Blog](https://www.percona.com/blog/multi-threaded-slave-statistics/)

Auteur source: [Juan Arruti](https://www.percona.com/blog/author/juan-arruti/)

Publication: 2017-07-19T17:02:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll talk about multi-threaded slave statistics printed in MySQL error log file. MySQL version 5.6 and later allows you to execute replicated events using parallel threads. This feature is called Multi-Threaded Slave (MTS), and to enable it you need to modify the slave_parallel_workers variable to a value greater than 1. Recently, … Continued

## Images et graphiques reperes

- featured / image: [Multi-Threaded Slave Statistics](https://www.percona.com/wp-content/uploads/2026/03/Multi-Threaded-Slave-Statistics.jpg)

## Auteur source

Juan Pablo joined Percona in 2016 as a member of Technical Services Team. Before coming to Percona, he worked as DBA in several companies such as IBM, Turner and Oracle.
