---
title: Can’t We Assign a Default Value to the BLOB, TEXT, GEOMETRY, and JSON Data Types?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/cant-we-assign-a-default-value-to-the-blob-text-geometry-and-json-data-types/
  post_id: 27977
source_author:
  name: Edwin Wang
  slug: edwin-wang
  url: https://www.percona.com/blog/author/edwin-wang/
  website: ''
published_at: '2024-01-12T14:39:28'
published_at_gmt: '2024-01-12T14:39:28'
modified_at: '2026-03-26T20:26:44'
modified_at_gmt: '2026-03-26T20:26:44'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql_57_3x4.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Can’t We Assign a Default Value to the BLOB, TEXT, GEOMETRY, and JSON Data Types?

Source: [Percona Blog](https://www.percona.com/blog/cant-we-assign-a-default-value-to-the-blob-text-geometry-and-json-data-types/)

Auteur source: [Edwin Wang](https://www.percona.com/blog/author/edwin-wang/)

Publication: 2024-01-12T14:39:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of our customers wants to create a table having a column of data type TEXT with the default value, but they encountered an error: ERROR 1101 ( 42000 ) : BLOB , TEXT , GEOMETRY or JSON column 'b' can ' t have a default value . It seems reasonable at first glimpse, as we know that each BLOB, TEXT, GEOMETRY, or JSON value is represented internally by a separately allocated object. This is in contrast … Continued

## Structure detectee

- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Can’t We Assign a Default Value to the BLOB, TEXT, GEOMETRY, and JSON Data Types?](https://www.percona.com/wp-content/uploads/2026/03/mysql_57_3x4.jpg)

## Auteur source

A father with 1 wife, 2 kids, and 2 dogs. A DBA with 20 years of experience in RDBMS i.e. MySQL, Oracle Etc. Currently working at Percona as Senior Mysql Database Administrator working on different environments and scenarios, including database installation/configuration/maintenance, trouble-shooting, design, performance tuning, DB High Availability architecture, and other infrastructure-related issues, AWS cloud, ansible, GCP, etc.
