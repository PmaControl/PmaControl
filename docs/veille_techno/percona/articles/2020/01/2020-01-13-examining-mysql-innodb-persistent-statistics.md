---
title: Examining MySQL InnoDB Persistent Statistics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/examining-mysql-innodb-persistent-statistics/
  post_id: 21468
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-01-13T16:27:03'
published_at_gmt: '2020-01-13T16:27:03'
modified_at: '2026-04-27T21:29:23'
modified_at_gmt: '2026-04-27T21:29:23'
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
- Storage Engine
category_slugs:
- mysql
- storage-engine
tags:
- InnoDB
- MySQL
tag_slugs:
- innodb
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-InnoDB-Persistent-Statistics.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Examining MySQL InnoDB Persistent Statistics

Source: [Percona Blog](https://www.percona.com/blog/examining-mysql-innodb-persistent-statistics/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-01-13T16:27:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A few days ago I wrote about how grossly outdated statistics returned through MySQL’s Information_Schema can be. In that post, Øystein Grøvlen suggested taking a look at mysql.innodb_table_stats and mysql.innodb_index_stats as a better source of information. Let’s do just that! Let’s start with the good news. Unlike MySQL Data Dictionary Tables (mysql.table_stats, etc), mysql.innodb_table_stats and … Continued

## Structure detectee

- H3: Summary

## Images et graphiques reperes

- featured / image: [Examining MySQL InnoDB Persistent Statistics](https://www.percona.com/wp-content/uploads/2026/03/MySQL-InnoDB-Persistent-Statistics.png)
- content / image: [MySQL InnoDB Persistent Statistics](https://www.percona.com/wp-content/uploads/2026/03/MySQL-InnoDB-Persistent-Statistics-300x168.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
