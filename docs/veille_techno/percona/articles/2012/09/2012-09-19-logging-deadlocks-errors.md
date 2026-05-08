---
title: Logging Deadlock errors
source:
  name: Percona Blog
  url: https://www.percona.com/blog/logging-deadlocks-errors/
  post_id: 3803
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-09-19T07:56:05'
published_at_gmt: '2012-09-19T07:56:05'
modified_at: '2026-04-28T21:40:05'
modified_at_gmt: '2026-04-28T21:40:05'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
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

# Logging Deadlock errors

Source: [Percona Blog](https://www.percona.com/blog/logging-deadlocks-errors/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-09-19T07:56:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The principal source of information for InnoDB diagnostics is the output of SHOW ENGINE INNODB STATUS but there are some sections that are not very useful. For example, LATEST DETECTED DEADLOCK only shows, as the name implies, the latest error detected. If you have 100 deadlocks per minute you will be able to see only … Continued

## Structure detectee

- H3: pt-deadlock-logger

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
