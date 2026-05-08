---
title: Data Compression in InnoDB for Text and Blob Fields
source:
  name: Percona Blog
  url: https://www.percona.com/blog/data-compression-in-innodb-for-text-and-blob-fields/
  post_id: 3586
source_author:
  name: Michael Coburn
  slug: michael-coburn
  url: https://www.percona.com/blog/author/michael-coburn/
  website: ''
published_at: '2012-05-30T17:36:15'
published_at_gmt: '2012-05-30T17:36:15'
modified_at: '2026-05-05T20:33:35'
modified_at_gmt: '2026-05-05T20:33:35'
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
- Insight for DBAs
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Data Compression in InnoDB for Text and Blob Fields

Source: [Percona Blog](https://www.percona.com/blog/data-compression-in-innodb-for-text-and-blob-fields/)

Auteur source: [Michael Coburn](https://www.percona.com/blog/author/michael-coburn/)

Publication: 2012-05-30T17:36:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Have you wanted to compress only certain types of columns in a table while leaving other columns uncompressed? While working on a customer case this week, I saw an interesting problem where a table had many heavily utilized TEXT fields with some read queries exceeding 500MB (!!) and stored in a 100GB table. In this … Continued

## Auteur source

Michael Coburn works at Percona on the Professional Services team in the role of Principal Architect. Michael joined Percona in 2012 as a Consultant after having worked as a DBA with stock photography websites and email service provider platforms. With a foundation in Systems Administration, Michael previously served as Product Manager responsible for Percona Monitoring and Management (PMM).
