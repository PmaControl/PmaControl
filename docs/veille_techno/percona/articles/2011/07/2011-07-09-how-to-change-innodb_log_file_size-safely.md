---
title: How to Change innodb_log_file_size
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-change-innodb_log_file_size-safely/
  post_id: 2864
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-07-09T07:00:01'
published_at_gmt: '2011-07-09T07:00:01'
modified_at: '2026-03-23T21:58:39'
modified_at_gmt: '2026-03-23T21:58:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
categories:
- MySQL
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-to-Change-innodb_log_file_size.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Change innodb_log_file_size

Source: [Percona Blog](https://www.percona.com/blog/how-to-change-innodb_log_file_size-safely/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-07-09T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you need to change MySQL’s innodb_log_file_size parameter (see How to calculate a good InnoDB log file size), you can’t just change the parameter in the my.cnf file and restart the server. If you do, InnoDB will refuse to start because the existing log files don’t match the configured size. Changing the innodb_log_file_size safely You … Continued

## Structure detectee

- H2: Changing the innodb_log_file_size safely
- H2: More Resources
- H3: Posts
- H3: eBooks (free to download)
- H3: Database Tools

## Images et graphiques reperes

- featured / image: [How to Change innodb_log_file_size](https://www.percona.com/wp-content/uploads/2026/03/How-to-Change-innodb_log_file_size.png)

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
