---
title: Infinite Replication Loop
source:
  name: Percona Blog
  url: https://www.percona.com/blog/infinite-replication-loop/
  post_id: 3096
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2011-10-10T09:31:22'
published_at_gmt: '2011-10-10T09:31:22'
modified_at: '2026-05-05T21:41:05'
modified_at_gmt: '2026-05-05T21:41:05'
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
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Diagram9.gif
image_count: 18
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Infinite Replication Loop

Source: [Percona Blog](https://www.percona.com/blog/infinite-replication-loop/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2011-10-10T09:31:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last week I helped 2 different customers with infinite replication loops. I decided to write a blog post about these infinite loop of binary log statements in MySQL Replication. To explain what they are, how to identify them… and how to fix them.

## Images et graphiques reperes

- featured / image: [Infinite Replication Loop](https://www.percona.com/wp-content/uploads/2026/03/Diagram9.gif)
- content / image: [Diagram11.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram11.png)
- content / image: [Diagram2.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram2.png)
- content / image: [Diagram3.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram3.png)
- content / image: [Diagram4.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram4.png)
- content / image: [Diagram5.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram5.png)
- content / image: [Diagram7.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram7.png)
- content / image: [query1-300x133.png](https://www.percona.com/wp-content/uploads/2026/03/query1-300x133.png)
- content / image: [query2-300x151.png](https://www.percona.com/wp-content/uploads/2026/03/query2-300x151.png)
- content / image: [query3-300x151.png](https://www.percona.com/wp-content/uploads/2026/03/query3-300x151.png)
- content / image: [query4.png](https://www.percona.com/wp-content/uploads/2026/03/query4.png)
- content / image: [query5.png](https://www.percona.com/wp-content/uploads/2026/03/query5.png)
- content / image: [Diagram11.resized.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram11.resized.png)
- content / image: [Diagram12.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram12.png)
- content / image: [Diagram13.resized.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram13.resized.png)
- content / image: [Diagram14.resized.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram14.resized.png)
- content / image: [Diagram15.resized.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram15.resized.png)
- content / image: [Diagram16.resized.png](https://www.percona.com/wp-content/uploads/2026/03/Diagram16.resized.png)

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
