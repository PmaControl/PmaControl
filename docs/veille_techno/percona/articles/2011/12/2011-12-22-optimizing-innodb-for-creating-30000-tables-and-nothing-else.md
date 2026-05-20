---
title: Optimizing InnoDB for creating 30,000 tables (and nothing else)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/optimizing-innodb-for-creating-30000-tables-and-nothing-else/
  post_id: 3218
source_author:
  name: Stewart Smith
  slug: stewart
  url: https://www.percona.com/blog/author/stewart/
  website: http://www.percona.com/about-us/our-team/stewart-smith/
published_at: '2011-12-22T23:11:04'
published_at_gmt: '2011-12-22T23:11:04'
modified_at: '2026-03-23T22:10:35'
modified_at_gmt: '2026-03-23T22:10:35'
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
- haildb
- InnoDB
- LD_PRELOAD
- libeatmydata
- MySQL
- test
tag_slugs:
- haildb
- innodb
- ld_preload
- libeatmydata
- mysql
- test
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Optimizing InnoDB for creating 30,000 tables (and nothing else)

Source: [Percona Blog](https://www.percona.com/blog/optimizing-innodb-for-creating-30000-tables-and-nothing-else/)

Auteur source: [Stewart Smith](https://www.percona.com/blog/author/stewart/)

Publication: 2011-12-22T23:11:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Once upon a time, it would have been considered madness to even attempt to create 30,000 tables in InnoDB. That time is now a memory. We have customers with a lot more tables than a mere 30,000. There have historically been no tests for anything near this many tables in the MySQL test suite. So, … Continued

## Auteur source

Stewart Smith has a deep background in database internals including MySQL, MySQL Cluster, Drizzle, InnoDB and HailDB. he is also one of the founding core developers of the Drizzle database server. He served at Percona from 2011-2014. He is a former Percona employee.
