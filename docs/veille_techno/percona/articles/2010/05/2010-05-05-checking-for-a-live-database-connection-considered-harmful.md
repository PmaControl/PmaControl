---
title: Checking for a live database connection considered harmful
source:
  name: Percona Blog
  url: https://www.percona.com/blog/checking-for-a-live-database-connection-considered-harmful/
  post_id: 2309
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-05-05T12:18:25'
published_at_gmt: '2010-05-05T12:18:25'
modified_at: '2026-04-28T21:11:38'
modified_at_gmt: '2026-04-28T21:11:38'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checking for a live database connection considered harmful

Source: [Percona Blog](https://www.percona.com/blog/checking-for-a-live-database-connection-considered-harmful/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-05-05T12:18:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It is very common for me to look at a customer’s database and notice a lot of overhead from checking whether a database connection is active before sending a query to it. This comes from the following design pattern, written in pseudo-code: function query_database(connection, sql) if !connection.is_alive() and !connection.reconnect() then throw exception end return connection.execute(sql) end 1 2 3 4 5 6 function query_database ( connection , sql ) if ! connection . is_alive ( ) and ! connection . reconnect ( ) then throw exception end return connection . execute ( sql ) end Many of the popular development platforms do something similar to this. Two … Continued

## Structure detectee

- H3: It Does Not Work
- H3: Performance Overhead

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
