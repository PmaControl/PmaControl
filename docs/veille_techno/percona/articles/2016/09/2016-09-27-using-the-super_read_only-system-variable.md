---
title: Using the MySQL super_read_only system variable
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-the-super_read_only-system-variable/
  post_id: 9969
source_author:
  name: Pablo Padua
  slug: pablo-paduapercona-com
  url: https://www.percona.com/blog/author/pablo-paduapercona-com/
  website: ''
published_at: '2016-09-27T18:06:30'
published_at_gmt: '2016-09-27T18:06:30'
modified_at: '2026-05-05T17:00:48'
modified_at_gmt: '2026-05-05T17:00:48'
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
- super_read_only
- system variable
tag_slugs:
- mysql
- super_read_only
- system-variable
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/super_read_only-system-variable.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using the MySQL super_read_only system variable

Source: [Percona Blog](https://www.percona.com/blog/using-the-super_read_only-system-variable/)

Auteur source: [Pablo Padua](https://www.percona.com/blog/author/pablo-paduapercona-com/)

Publication: 2016-09-27T18:06:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post will discuss how to use the MySQL super_read_only system variable. It is well known that replica servers in a master/slave configuration, to avoid breaking replication due to duplicate keys, missing rows or other similar issues, should not receive write queries. It’s a good practice to set read_only = 1 on slave servers to prevent … Continued

## Images et graphiques reperes

- featured / image: [Using the MySQL super_read_only system variable](https://www.percona.com/wp-content/uploads/2026/03/super_read_only-system-variable.jpg)
