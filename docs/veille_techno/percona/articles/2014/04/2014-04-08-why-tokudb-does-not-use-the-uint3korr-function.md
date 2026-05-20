---
title: Why TokuDB does not use the ‘uint3korr’ function
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-tokudb-does-not-use-the-uint3korr-function/
  post_id: 3210
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2014-04-08T11:50:46'
published_at_gmt: '2014-04-08T11:50:46'
modified_at: '2026-05-04T20:52:21'
modified_at_gmt: '2026-05-04T20:52:21'
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
- TokuDB
- Valgrind
tag_slugs:
- mysql
- tokudb
- valgrind
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why TokuDB does not use the ‘uint3korr’ function

Source: [Percona Blog](https://www.percona.com/blog/why-tokudb-does-not-use-the-uint3korr-function/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2014-04-08T11:50:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The ‘uint3korr’ function inside of the mysqld server extracts a 3 byte unsigned integer from a memory buffer. One use is for ‘mediumint’ columns which encode their value in 3 bytes. MySQL 5.6 and MariaDB 10.0 claims to have optimized this function for x86 and x86_64 processors. There is a big comment that says: Attention: Please, note, uint3korr reads 4 bytes (not 3)! It means, that you have to provide enough allocated space. 1 2 Attention : Please , note , uint3korr reads 4 bytes ( not 3 ) ! It means , that you have to provide enough allocated space . … Continued
