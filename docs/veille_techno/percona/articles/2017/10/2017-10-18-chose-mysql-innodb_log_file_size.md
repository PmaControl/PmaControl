---
title: How to Choose the MySQL innodb_log_file_size
source:
  name: Percona Blog
  url: https://www.percona.com/blog/chose-mysql-innodb_log_file_size/
  post_id: 17547
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2017-10-18T22:15:33'
published_at_gmt: '2017-10-18T22:15:33'
modified_at: '2026-03-20T21:33:19'
modified_at_gmt: '2026-03-20T21:33:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- innodb_log_file_size
- log file size
- MySQL
- Percona Monitoring and Management
- Performance
- PMM
tag_slugs:
- innodb
- innodb_log_file_size
- log-file-size
- mysql
- percona-monitoring-and-management
- performance
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_log_file_size-1024x293-1.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Choose the MySQL innodb_log_file_size

Source: [Percona Blog](https://www.percona.com/blog/chose-mysql-innodb_log_file_size/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2017-10-18T22:15:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll provide some guidance on how to choose the MySQL innodb_log_file_size. Like many database management systems, MySQL uses logs to achieve data durability (when using the default InnoDB storage engine). This ensures that when a transaction is committed, data is not lost in the event of crash or power loss. MySQL’s … Continued

## Structure detectee

- H4: Summary

## Images et graphiques reperes

- featured / image: [How to Choose the MySQL innodb_log_file_size](https://www.percona.com/wp-content/uploads/2026/03/innodb_log_file_size-1024x293-1.png)
- content / image: [innodb_log_file_size](https://www.percona.com/wp-content/uploads/2026/03/innodb_log_file_size-1024x293.png)
- content / image: [innodb_log_file_size 2](https://www.percona.com/wp-content/uploads/2026/03/innodb_log_file_size-2-1024x280.png)
- content / image: [innodb_log_file_size 3](https://www.percona.com/wp-content/uploads/2026/03/innodb_log_file_size-3-1024x286.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
