---
title: A recovery trivia or how to recover from a lost ibdata1 file
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-recovery-trivia-or-how-to-recover-from-a-lost-ibdata1-file/
  post_id: 2912
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2011-06-03T07:00:01'
published_at_gmt: '2011-06-03T07:00:01'
modified_at: '2026-05-04T21:31:06'
modified_at_gmt: '2026-05-04T21:31:06'
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

# A recovery trivia or how to recover from a lost ibdata1 file

Source: [Percona Blog](https://www.percona.com/blog/a-recovery-trivia-or-how-to-recover-from-a-lost-ibdata1-file/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2011-06-03T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A few day ago, a customer came to Percona needing to recover data. Basically, while doing a transfer from one SAN to another, something went wrong and they lost the ibdata1 file, where all the table meta-data is stored. Fortunately, they were running with innodb_file_per_table so the data itself was available. What they could provide … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
