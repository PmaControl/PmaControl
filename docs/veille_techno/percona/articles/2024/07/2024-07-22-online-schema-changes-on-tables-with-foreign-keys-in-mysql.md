---
title: Online Schema Changes on Tables with Foreign Keys in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/online-schema-changes-on-tables-with-foreign-keys-in-mysql/
  post_id: 28826
source_author:
  name: Kedar Vaijanapurkar
  slug: kedar-vaijanapurkar
  url: https://www.percona.com/blog/author/kedar-vaijanapurkar/
  website: http://kedar.nitty-witty.com/blog
published_at: '2024-07-22T14:48:31'
published_at_gmt: '2024-07-22T14:48:31'
modified_at: '2026-03-26T20:26:06'
modified_at_gmt: '2026-03-26T20:26:06'
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
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- foreign keys
- MySQL
- mysql-and-variants
- Online Alter
- Percona Toolkit
- pt-online-schema-change
tag_slugs:
- foreign-keys
- mysql
- mysql-and-variants
- online-alter
- percona-toolkit
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Online-Schema-Changes-on-Tables-with-Foreign-Keys-in-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Online Schema Changes on Tables with Foreign Keys in MySQL

Source: [Percona Blog](https://www.percona.com/blog/online-schema-changes-on-tables-with-foreign-keys-in-mysql/)

Auteur source: [Kedar Vaijanapurkar](https://www.percona.com/blog/author/kedar-vaijanapurkar/)

Publication: 2024-07-22T14:48:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

pt-online-schema-change is an amazing tool for assisting in table modifications in cases where ONLINE ALTER is not an option. But if you have foreign keys, this could be an interesting and important read for you. Tables with foreign keys are always complicated, and they have to be handled with care. The use case I am … Continued

## Structure detectee

- H2: Setting up the lab with similar tables (removing extra fields)
- H2: Reasoning
- H2: Analysis and conclusion
- H2: Improvements

## Images et graphiques reperes

- featured / image: [Online Schema Changes on Tables with Foreign Keys in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Online-Schema-Changes-on-Tables-with-Foreign-Keys-in-MySQL.jpg)

## Auteur source

Kedar Vaijanapurkar is a Tier 2 MySQL DBA at Percona since Oct 2021. He's experienced in MySQL related technologies and constantly working on improving skills. He lives in the cultural city of Vadodara with his SPOF (wife) and two HA (highly active) replicas.
