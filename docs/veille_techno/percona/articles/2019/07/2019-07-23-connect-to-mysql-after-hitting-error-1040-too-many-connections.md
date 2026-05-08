---
title: 'Connect to MySQL after hitting ERROR 1040: Too many connections'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/connect-to-mysql-after-hitting-error-1040-too-many-connections/
  post_id: 20658
source_author:
  name: marcos.albe
  slug: marcos-albe
  url: https://www.percona.com/blog/author/marcos-albe/
  website: http://www.percona.com
published_at: '2019-07-23T19:43:57'
published_at_gmt: '2019-07-23T19:43:57'
modified_at: '2026-04-27T21:19:43'
modified_at_gmt: '2026-04-27T21:19:43'
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
tags:
- MySQL
tag_slugs:
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ERROR-1040-Too-many-connections.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Connect to MySQL after hitting ERROR 1040: Too many connections

Source: [Percona Blog](https://www.percona.com/blog/connect-to-mysql-after-hitting-error-1040-too-many-connections/)

Auteur source: [marcos.albe](https://www.percona.com/blog/author/marcos-albe/)

Publication: 2019-07-23T19:43:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ERROR 1040…again A pretty common topic in Support tickets is the rather infamous error: ERROR 1040: Too many connections. The issue is pretty self-explanatory: your application/users are trying to create more connections than the server allows, or in other words, the current number of connections exceeds the value of the max_connections variable. This situation on … Continued

## Structure detectee

- H3: ERROR 1040…again
- H3: Root user can’t connect either! Why!?
- H3: How to guarantee access to the instance
- H4: Setting up in Percona Server
- H4: Setting up in MySQL Community
- H3: Using it for monitoring and health-checks
- H3: Help! I need to login but I don’t have an extra port!

## Images et graphiques reperes

- featured / image: [Connect to MySQL after hitting ERROR 1040: Too many connections](https://www.percona.com/wp-content/uploads/2026/03/ERROR-1040-Too-many-connections.jpeg)
