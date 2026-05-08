---
title: Using MySQL Event Scheduler and how to prevent contention
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-mysql-event-scheduler-and-how-to-prevent-contention/
  post_id: 9051
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2015-02-25T11:00:54'
published_at_gmt: '2015-02-25T11:00:54'
modified_at: '2026-04-28T22:16:32'
modified_at_gmt: '2026-04-28T22:16:32'
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
- Alexander Rubin
- Amazon RDS
- DBaaS
- Event Scheduler
- MySQL
- Primary
tag_slugs:
- alexander-rubin
- amazon-rds
- dbaas
- event-scheduler
- mysql
- primary
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using MySQL Event Scheduler and how to prevent contention

Source: [Percona Blog](https://www.percona.com/blog/using-mysql-event-scheduler-and-how-to-prevent-contention/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2015-02-25T11:00:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL introduced the Event Scheduler in version 5.1.6. The Event Scheduler is a MySQL-level “cron job”, which will run events inside MySQL. Up until now, this was not a very popular feature, however, it has gotten more popular since the adoption of Amazon RDS – as well as similar MySQL database as a service offerings … Continued

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
