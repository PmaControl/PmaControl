---
title: Storing UUID and Generated Columns
source:
  name: Percona Blog
  url: https://www.percona.com/blog/uuid-generated-columns/
  post_id: 16590
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2017-05-03T18:15:56'
published_at_gmt: '2017-05-03T18:15:56'
modified_at: '2026-05-05T18:34:05'
modified_at_gmt: '2026-05-05T18:34:05'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Storing-UUID.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Storing UUID and Generated Columns

Source: [Percona Blog](https://www.percona.com/blog/uuid-generated-columns/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2017-05-03T18:15:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A lot of things have been said about UUID, and storing UUID in an optimized way. Now that we have generated columns, we can store the decomposed information inside the UUID and merge it again with generated columns. This blog post demonstrates this process. First, I used a simple table with one char field that … Continued

## Structure detectee

- H3: Conclusions

## Images et graphiques reperes

- featured / image: [Storing UUID and Generated Columns](https://www.percona.com/wp-content/uploads/2026/03/Storing-UUID.png)
- content / image: [uno.png](https://www.percona.com/wp-content/uploads/2026/03/uno.png)
- content / image: [dos.png](https://www.percona.com/wp-content/uploads/2026/03/dos.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
