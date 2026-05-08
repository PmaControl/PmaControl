---
title: Why is the ibdata1 file continuously growing in MySQL?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-is-the-ibdata1-file-continuously-growing-in-mysql/
  post_id: 7273
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2013-08-20T05:00:30'
published_at_gmt: '2013-08-20T05:00:30'
modified_at: '2026-03-25T17:06:18'
modified_at_gmt: '2026-03-25T17:06:18'
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
categories:
- Insight for DBAs
- MySQL
- Percona Services
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-services
- percona-software
tags:
- ibdata1
- innochecksum
- innodb_doublewrite_file
- innodb_ibuf_max_size
- Miguel Angel Nieto
- Nagios
- Percona Support
tag_slugs:
- ibdata1
- innochecksum
- innodb_doublewrite_file
- innodb_ibuf_max_size
- miguel-angel-nieto
- nagios
- percona-support
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ibdata1-file.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why is the ibdata1 file continuously growing in MySQL?

Source: [Percona Blog](https://www.percona.com/blog/why-is-the-ibdata1-file-continuously-growing-in-mysql/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2013-08-20T05:00:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We often receive this question about the ibdata1 file in MySQL at Percona Support. The panic starts when the monitoring server sends an alert about the storage of the MySQL server – saying that the disk is about to get filled. After some research, you realize that most of the disk space is used by … Continued

## Structure detectee

- H2: What is stored in ibdata1?
- H2: What is causing the ibdata1 to grow that fast?
- H2: How can I check what is being stored in the ibdata1?
- H3: How can I solve the problem?
- H3: Is there any way to recover the used space?
- H3: Summary

## Images et graphiques reperes

- featured / image: [Why is the ibdata1 file continuously growing in MySQL?](https://www.percona.com/wp-content/uploads/2026/03/ibdata1-file.jpg)
- content / image: [Find and fix MySQL issues faster with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/4e347054-beae-4f4e-95fd-5bc84de30078.png)
- content / image: [Install Percona Software for MySQL Today!](https://www.percona.com/wp-content/uploads/2026/03/05a9268f-3da4-441f-9f82-5cac6b7a512d.png)

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
