---
title: Syscalls Analysis in MySQL When Using innodb_flush_method and innodb_use_fdatasync
source:
  name: Percona Blog
  url: https://www.percona.com/blog/syscalls-analysis-in-mysql-when-using-innodb_flush_method-and-innodb_use_fdatasync/
  post_id: 27985
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2024-01-18T15:08:43'
published_at_gmt: '2024-01-18T15:08:43'
modified_at: '2026-03-26T20:26:43'
modified_at_gmt: '2026-03-26T20:26:43'
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
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-When-Using-innodb_flush_method-and-innodb_use_fdatasync.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Syscalls Analysis in MySQL When Using innodb_flush_method and innodb_use_fdatasync

Source: [Percona Blog](https://www.percona.com/blog/syscalls-analysis-in-mysql-when-using-innodb_flush_method-and-innodb_use_fdatasync/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2024-01-18T15:08:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will discuss how to validate at the operating system level the effects of changing the innodb_flush_method to variations other than the default (particularly for O_DIRECT which is most commonly used) and the use of innodb_use_fdatasync. Introduction First, let’s define what the innodb_flush_method parameter does. It dictates how InnoDB manages the flushing … Continued

## Structure detectee

- H2: Introduction
- H2: Test case
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Syscalls Analysis in MySQL When Using innodb_flush_method and innodb_use_fdatasync](https://www.percona.com/wp-content/uploads/2026/03/MySQL-When-Using-innodb_flush_method-and-innodb_use_fdatasync.jpg)
- content / image: [InnoDB architecture](https://www.percona.com/wp-content/uploads/2026/03/1-19.png)
- content / image: [syscalls](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-01-16-at-18.37.22-1024x575.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
