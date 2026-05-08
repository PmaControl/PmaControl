---
title: MySQL 8.0 Dynamic Redo Log Sizing
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-0-dynamic-redo-log-sizing/
  post_id: 25890
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2022-08-10T12:07:40'
published_at_gmt: '2022-08-10T12:07:40'
modified_at: '2026-03-26T20:31:29'
modified_at_gmt: '2026-03-26T20:31:29'
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
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Dynamic-Redo-Log-Sizing.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.0 Dynamic Redo Log Sizing

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-0-dynamic-redo-log-sizing/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2022-08-10T12:07:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post will discuss the newest feature available in MySQL 8.0.30: dynamic redo log sizing. After the InnoDB buffer pool size, we can say that having a proper size for the redo logs is crucial for MySQL performance. There are numerous blog posts about how to calculate a good redo log size. One of … Continued

## Structure detectee

- H2: Estimating the redo log capacity
- H2: Deprecated parameters
- H3: Conclusion
- H3: Useful Resources

## Images et graphiques reperes

- featured / image: [MySQL 8.0 Dynamic Redo Log Sizing](https://www.percona.com/wp-content/uploads/2026/03/Dynamic-Redo-Log-Sizing.png)
- content / image: [Dynamic Redo Log Sizing](https://www.percona.com/wp-content/uploads/2026/03/Dynamic-Redo-Log-Sizing-300x157.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
