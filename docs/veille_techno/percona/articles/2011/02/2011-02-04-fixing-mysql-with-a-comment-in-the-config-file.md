---
title: Fixing MySQL with a comment in the config file
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fixing-mysql-with-a-comment-in-the-config-file/
  post_id: 2672
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-02-04T12:35:19'
published_at_gmt: '2011-02-04T12:35:19'
modified_at: '2026-03-23T21:51:08'
modified_at_gmt: '2026-03-23T21:51:08'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- my.cnf
- mysql_multi
- mysqld_multi
tag_slugs:
- my-cnf
- mysql_multi
- mysqld_multi
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fixing MySQL with a comment in the config file

Source: [Percona Blog](https://www.percona.com/blog/fixing-mysql-with-a-comment-in-the-config-file/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-02-04T12:35:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A customer called with an emergency issue: A server that normally runs many MySQL instances wouldn’t start them up. Not only would it not start all of them, it wouldn’t even start the first one. The multiple instances were started through the mysql_multi init script. Perhaps you already know what was wrong!

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
