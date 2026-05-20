---
title: 'MySQL Limitations Part 2: The Binary Log'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-limitations-part-2-the-binary-log/
  post_id: 2478
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-10-23T15:01:56'
published_at_gmt: '2010-10-23T15:01:56'
modified_at: '2026-03-23T21:46:22'
modified_at_gmt: '2026-03-23T21:46:22'
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
- Binary Log
- Replication
tag_slugs:
- binary-log
- replication
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Limitations Part 2: The Binary Log

Source: [Percona Blog](https://www.percona.com/blog/mysql-limitations-part-2-the-binary-log/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-10-23T15:01:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the second in a series on what’s seriously limiting MySQL in certain circumstances (links: part 1). In the first part, I wrote about single-threaded replication. Upstream from the replicas is the primary, which enables replication by writing a so-called “binary log” of events that modify data in the server. The binary log is … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
