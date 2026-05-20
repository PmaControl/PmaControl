---
title: Concatenating MyISAM files
source:
  name: Percona Blog
  url: https://www.percona.com/blog/concatenating-myisam-files/
  post_id: 6457
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2012-11-19T18:41:20'
published_at_gmt: '2012-11-19T18:41:20'
modified_at: '2026-04-28T21:41:40'
modified_at_gmt: '2026-04-28T21:41:40'
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

# Concatenating MyISAM files

Source: [Percona Blog](https://www.percona.com/blog/concatenating-myisam-files/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2012-11-19T18:41:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I found myself involved in the migration of a large read-only InnoDB database to MyISAM (eventually packed). The only issue was that for one of the table, we were talking of 5 TB of data, 23B rows. Not small… I calculated that with something like insert into MyISAM_table… select * from Innodb_table… would take … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
