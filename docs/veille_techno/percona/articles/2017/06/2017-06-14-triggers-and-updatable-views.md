---
title: MySQL Triggers and Updatable Views
source:
  name: Percona Blog
  url: https://www.percona.com/blog/triggers-and-updatable-views/
  post_id: 17006
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2017-06-14T18:54:43'
published_at_gmt: '2017-06-14T18:54:43'
modified_at: '2026-05-05T18:42:52'
modified_at_gmt: '2026-05-05T18:42:52'
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
- MySQL
- MySQL triggers
- views
tag_slugs:
- mysql
- mysql-triggers
- views
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Triggers-3-e1497466335438.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Triggers and Updatable Views

Source: [Percona Blog](https://www.percona.com/blog/triggers-and-updatable-views/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2017-06-14T18:54:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post we’ll review how MySQL triggers can affect queries. Contrary to what the documentation states, we can activate triggers even while operating on views: https://dev.mysql.com/doc/refman/5.7/en/triggers.html Important: MySQL triggers activate only for changes made to tables by SQL statements. They do not activate for changes in views, nor by changes to tables made by … Continued

## Structure detectee

- H3: Corollary to the Discussion

## Images et graphiques reperes

- featured / image: [MySQL Triggers and Updatable Views](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Triggers-3-e1497466335438.jpg)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
