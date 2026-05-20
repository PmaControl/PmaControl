---
title: Using Flexviews – part two, change data capture
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-flexviews-part-two-change-data-capture/
  post_id: 2767
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2011-03-25T23:13:51'
published_at_gmt: '2011-03-25T23:13:51'
modified_at: '2026-05-05T17:31:27'
modified_at_gmt: '2026-05-05T17:31:27'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Flexviews – part two, change data capture

Source: [Percona Blog](https://www.percona.com/blog/using-flexviews-part-two-change-data-capture/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2011-03-25T23:13:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous post I introduced materialized view concepts. This post begins with an introduction to change data capture technology and describes some of the ways in which it can be leveraged for your benefit. This is followed by a description of FlexCDC, the change data capture tool included with Flexviews. It continues with an … Continued

## Structure detectee

- H2: What is Change Data Capture (CDC)?
- H2: Binary log based CDC
- H2: Setting up FlexCDC
- H2: Verify installation
- H2: Adding a changelog to a table
- H2: Examine the changes

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
