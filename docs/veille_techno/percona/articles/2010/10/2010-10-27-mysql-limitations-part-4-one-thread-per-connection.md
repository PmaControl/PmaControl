---
title: 'MySQL Limitations Part 4: One Thread per Connection'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-limitations-part-4-one-thread-per-connection/
  post_id: 2482
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-10-27T16:19:32'
published_at_gmt: '2010-10-27T16:19:32'
modified_at: '2026-03-23T21:46:34'
modified_at_gmt: '2026-03-23T21:46:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- Thread pool
tag_slugs:
- thread-pool
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Limitations-Connections.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Limitations Part 4: One Thread per Connection

Source: [Percona Blog](https://www.percona.com/blog/mysql-limitations-part-4-one-thread-per-connection/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-10-27T16:19:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the third in a series on what’s seriously limiting MySQL in core use cases (links: part 1, 2, 3). This post is about the way MySQL handles connections, allocating one thread per connection to the server. MySQL Limitations: Connections MySQL is a single process with multiple threads. Not all databases are architected this … Continued

## Structure detectee

- H2: MySQL Limitations: Connections

## Images et graphiques reperes

- featured / image: [MySQL Limitations Part 4: One Thread per Connection](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Limitations-Connections.jpg)

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
