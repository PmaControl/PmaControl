---
title: 'How to ALTER a VARCHAR Column Online in MySQL: Caveats and Solutions'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-alter-a-varchar-column-online-in-mysql-caveats-and-solutions/
  post_id: 27305
source_author:
  name: Kedar Vaijanapurkar
  slug: kedar-vaijanapurkar
  url: https://www.percona.com/blog/author/kedar-vaijanapurkar/
  website: http://kedar.nitty-witty.com/blog
published_at: '2023-08-01T13:51:56'
published_at_gmt: '2023-08-01T13:51:56'
modified_at: '2026-03-26T20:29:21'
modified_at_gmt: '2026-03-26T20:29:21'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- ALGORITHM=INPLACE
- alter varchar column
- MySQL
- mysql-and-variants
- Online Alter
tag_slugs:
- algorithminplace
- alter-varchar-column
- mysql
- mysql-and-variants
- online-alter
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to ALTER a VARCHAR Column Online in MySQL: Caveats and Solutions

Source: [Percona Blog](https://www.percona.com/blog/how-to-alter-a-varchar-column-online-in-mysql-caveats-and-solutions/)

Auteur source: [Kedar Vaijanapurkar](https://www.percona.com/blog/author/kedar-vaijanapurkar/)

Publication: 2023-08-01T13:51:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post explains the error Cannot change column type INPLACE for a VARCHAR column and offers solution to perform the ALTER operation.

## Structure detectee

- H2: Understanding the limits of in-place ALTER
- H3: How to ONLINE alter VARCHAR columns in such cases
- H3: ALTER TABLE to change CHARACTER SET of a VARCHAR column
- H3: Conclusion

## Auteur source

Kedar Vaijanapurkar is a Tier 2 MySQL DBA at Percona since Oct 2021. He's experienced in MySQL related technologies and constantly working on improving skills. He lives in the cultural city of Vadodara with his SPOF (wife) and two HA (highly active) replicas.
