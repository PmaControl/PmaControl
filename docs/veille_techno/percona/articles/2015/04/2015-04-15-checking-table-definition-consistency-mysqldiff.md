---
title: Checking table definition consistency with mysqldiff
source:
  name: Percona Blog
  url: https://www.percona.com/blog/checking-table-definition-consistency-mysqldiff/
  post_id: 9194
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2015-04-15T20:45:18'
published_at_gmt: '2015-04-15T20:45:18'
modified_at: '2026-04-28T22:18:42'
modified_at_gmt: '2026-04-28T22:18:42'
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
- MySQL
category_slugs:
- mysql
tags:
- information_schema
- Miguel Angel Nieto
- MySQL Utilities
- mysqldiff
- Oracle
- Primary
- primary key
- pt-table-checksum
- pt-table-sync
tag_slugs:
- information_schema
- miguel-angel-nieto
- mysql-utilities
- mysqldiff
- oracle
- primary
- primary-key
- pt-table-checksum
- pt-table-sync
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checking table definition consistency with mysqldiff

Source: [Percona Blog](https://www.percona.com/blog/checking-table-definition-consistency-mysqldiff/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2015-04-15T20:45:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Data inconsistencies in replication environments are a pretty common. There are lots of posts that explain how to fix those using pt-table-checksum and pt-table-sync. Usually we only care about the data but from time to time we receive this question in support: How can I check the table definition consistency between servers? Replication also allow … Continued

## Structure detectee

- H3: How can I check the table definition consistency between servers?
- H3: Find table definition inconsistencies
- H3: What mysqldiff runs under the hood?
- H3: Conclusion

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
