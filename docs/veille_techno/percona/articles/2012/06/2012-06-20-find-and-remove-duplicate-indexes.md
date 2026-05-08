---
title: Find and remove duplicate indexes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/find-and-remove-duplicate-indexes/
  post_id: 3647
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-06-20T21:15:46'
published_at_gmt: '2012-06-20T21:15:46'
modified_at: '2026-03-23T22:22:51'
modified_at_gmt: '2026-03-23T22:22:51'
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

# Find and remove duplicate indexes

Source: [Percona Blog](https://www.percona.com/blog/find-and-remove-duplicate-indexes/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-06-20T21:15:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Having duplicate keys in our schemas can hurt the performance of our database: They make the optimizer phase slower because MySQL needs to examine more query plans. The storage engine needs to maintain, calculate and update more index statistics DML and even read queries can be slower because MySQL needs update fetch more data to … Continued

## Structure detectee

- H3: Duplicate keys on the same column
- H3: Redundant keys on composite indexes
- H3: Redundant suffixes on clustered index
- H3: How can I find all those keys?
- H3: Conclusion

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
