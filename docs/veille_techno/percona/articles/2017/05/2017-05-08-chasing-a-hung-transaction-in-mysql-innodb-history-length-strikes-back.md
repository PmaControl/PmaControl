---
title: 'Chasing a Hung MySQL Transaction: InnoDB History Length Strikes Back'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/chasing-a-hung-transaction-in-mysql-innodb-history-length-strikes-back/
  post_id: 16868
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2017-05-08T19:09:23'
published_at_gmt: '2017-05-08T19:09:23'
modified_at: '2026-05-05T18:39:37'
modified_at_gmt: '2026-05-05T18:39:37'
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
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Hung MySQL Transaction
- InnoDB
- MySQL
- transaction log
tag_slugs:
- hung-mysql-transaction
- innodb
- mysql
- transaction-log
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Hung-MySQL-Transaction-e1494270500555.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Chasing a Hung MySQL Transaction: InnoDB History Length Strikes Back

Source: [Percona Blog](https://www.percona.com/blog/chasing-a-hung-transaction-in-mysql-innodb-history-length-strikes-back/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2017-05-08T19:09:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll review how a hung MySQL transaction can cause the InnoDB history length to grow and negatively affect MySQL performance. Recently I was helping a customer discover why SELECT queries were running slower and slower until the server restarts (which got things back to normal). It took some time to get … Continued

## Structure detectee

- H3: Simulation

## Images et graphiques reperes

- featured / image: [Chasing a Hung MySQL Transaction: InnoDB History Length Strikes Back](https://www.percona.com/wp-content/uploads/2026/03/Hung-MySQL-Transaction-e1494270500555.png)
- content / image: [Hung MySQL Transaction](https://www.percona.com/wp-content/uploads/2026/03/innodb_history_length-1024x375.png)
- content / image: [Hung MySQL Transaction](https://www.percona.com/wp-content/uploads/2026/03/innodb_history_length_fixed-1024x361.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
