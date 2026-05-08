---
title: Internal Temporary Tables in MySQL 5.7
source:
  name: Percona Blog
  url: https://www.percona.com/blog/internal-temporary-tables-mysql-5-7/
  post_id: 17333
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2017-12-04T14:51:38'
published_at_gmt: '2017-12-04T14:51:38'
modified_at: '2026-03-20T21:30:21'
modified_at_gmt: '2026-03-20T21:30:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:pmm
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- InnoDB
- temporary table
tag_slugs:
- innodb
- temporary-table
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-08-28-at-3.09.12-PM.png
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Internal Temporary Tables in MySQL 5.7

Source: [Percona Blog](https://www.percona.com/blog/internal-temporary-tables-mysql-5-7/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2017-12-04T14:51:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I investigate a case of spiking InnoDB Rows inserted in the absence of a write query, and find internal temporary tables to be the culprit. Recently I was investigating an interesting case for a customer. We could see the regular spikes on a graph depicting “InnoDB rows inserted” metric (jumping from … Continued

## Structure detectee

- H4: Conclusion

## Images et graphiques reperes

- featured / image: [Internal Temporary Tables in MySQL 5.7](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-08-28-at-3.09.12-PM.png)
- content / graph_or_chart: [InnoDB row operations graph from PMM](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-08-28-at-3.09.12-PM-1024x376.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
