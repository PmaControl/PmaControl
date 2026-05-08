---
title: MySQL opening .frm even when table is in table definition cache
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-opening-frm-even-when-table-is-in-table-definition-cache/
  post_id: 3206
source_author:
  name: Stewart Smith
  slug: stewart
  url: https://www.percona.com/blog/author/stewart/
  website: http://www.percona.com/about-us/our-team/stewart-smith/
published_at: '2011-11-21T11:37:14'
published_at_gmt: '2011-11-21T11:37:14'
modified_at: '2026-05-04T21:36:53'
modified_at_gmt: '2026-05-04T21:36:53'
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
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL opening .frm even when table is in table definition cache

Source: [Percona Blog](https://www.percona.com/blog/mysql-opening-frm-even-when-table-is-in-table-definition-cache/)

Auteur source: [Stewart Smith](https://www.percona.com/blog/author/stewart/)

Publication: 2011-11-21T11:37:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

or… “the case of Stewart recognizing parameters to the read() system call in strace output”. Last week, a colleague asked a question: I have an instance of MySQL with 100 tables and the table_definition_cache set to 1000. My understanding of this is that MySQL won’t revert to opening the FRM files to read the table … Continued

## Auteur source

Stewart Smith has a deep background in database internals including MySQL, MySQL Cluster, Drizzle, InnoDB and HailDB. he is also one of the founding core developers of the Drizzle database server. He served at Percona from 2011-2014. He is a former Percona employee.
