---
title: Should MySQL update the default innodb_log_file_size?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/should-mysql-update-the-default-innodb_log_file_size/
  post_id: 3207
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-11-21T16:15:14'
published_at_gmt: '2011-11-21T16:15:14'
modified_at: '2026-03-23T22:09:49'
modified_at_gmt: '2026-03-23T22:09:49'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Should MySQL update the default innodb_log_file_size?

Source: [Percona Blog](https://www.percona.com/blog/should-mysql-update-the-default-innodb_log_file_size/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-11-21T16:15:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Now that InnoDB is the default storage engine in MySQL, is it time to update the default configuration for the InnoDB log file size (innodb_log_file_size) setting? In general, there are two settings that simply can’t be left at their historical defaults for a production installation. MySQL 5.5 increased the default buffer pool size to something … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
