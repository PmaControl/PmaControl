---
title: Amazon RDS and pt-online-schema-change
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pt-online-schema-change-amazon-rds/
  post_id: 15398
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2016-07-01T17:30:31'
published_at_gmt: '2016-07-01T17:30:31'
modified_at: '2026-05-05T20:34:57'
modified_at_gmt: '2026-05-05T20:34:57'
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
- Cloud
- MySQL
category_slugs:
- cloud
- mysql
tags:
- amazon
- Amazon RDS
- MySQL
- pt-online-schema-change
tag_slugs:
- amazon
- amazon-rds
- mysql
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Amazon-RDS-and-pt-online-schema-change.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Amazon RDS and pt-online-schema-change

Source: [Percona Blog](https://www.percona.com/blog/pt-online-schema-change-amazon-rds/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2016-07-01T17:30:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I discuss some of the insights needed when using Amazon RDS and pt-online-schema-change together. The pt-online-schema-change tool runs DDL queries (ALTER) online so that the table is not locked for reads and writes. It is a commonly used tool by community users and customers. Using it on Amazon RDS requires knowing … Continued

## Images et graphiques reperes

- featured / image: [Amazon RDS and pt-online-schema-change](https://www.percona.com/wp-content/uploads/2026/03/Amazon-RDS-and-pt-online-schema-change.png)

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
