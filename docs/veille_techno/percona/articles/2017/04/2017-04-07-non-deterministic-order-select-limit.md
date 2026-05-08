---
title: Non-Deterministic Order for SELECT with LIMIT
source:
  name: Percona Blog
  url: https://www.percona.com/blog/non-deterministic-order-select-limit/
  post_id: 16578
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2017-04-07T19:26:39'
published_at_gmt: '2017-04-07T19:26:39'
modified_at: '2026-05-05T20:04:44'
modified_at_gmt: '2026-05-05T20:04:44'
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
- LIMIT
- MySQL
- non-deterministic order
- SELECT
tag_slugs:
- limit
- mysql
- non-deterministic-order
- select
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Non-Deterministic-Order.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Non-Deterministic Order for SELECT with LIMIT

Source: [Percona Blog](https://www.percona.com/blog/non-deterministic-order-select-limit/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2017-04-07T19:26:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at how queries in systems with parallel processing can return rows in a non-deterministic order (and how to fix it). Short story: Do not rely on the order of your rows if your query does not use ORDER BY . Even with ORDER BY , rows with the same values can be sorted differently. … Continued

## Images et graphiques reperes

- featured / image: [Non-Deterministic Order for SELECT with LIMIT](https://www.percona.com/wp-content/uploads/2026/03/Non-Deterministic-Order.jpg)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
