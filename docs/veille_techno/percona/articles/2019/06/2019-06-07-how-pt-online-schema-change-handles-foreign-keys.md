---
title: How pt-online-schema-change Handles Foreign Keys
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-pt-online-schema-change-handles-foreign-keys/
  post_id: 20431
source_author:
  name: Uday Varagani
  slug: uday-varagani
  url: https://www.percona.com/blog/author/uday-varagani/
  website: ''
published_at: '2019-06-07T13:30:38'
published_at_gmt: '2019-06-07T13:30:38'
modified_at: '2026-04-27T21:15:42'
modified_at_gmt: '2026-04-27T21:15:42'
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
- ddl
- foreign key constraints
- pt-online-schema-change
- schema changes
tag_slugs:
- ddl
- foreign-key-constraints
- pt-online-schema-change
- schema-changes
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pt-online-schema-change-1.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How pt-online-schema-change Handles Foreign Keys

Source: [Percona Blog](https://www.percona.com/blog/how-pt-online-schema-change-handles-foreign-keys/)

Auteur source: [Uday Varagani](https://www.percona.com/blog/author/uday-varagani/)

Publication: 2019-06-07T13:30:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Foreign key related issues are very common when dealing with DDL changes in MySQL using Percona toolkit. In this blog post, I will explain how the tool (pt-online-schema-change) handles foreign key constraints when executing a DDL change. First of all, I would like to explain why foreign keys have to be handled at all before … Continued

## Structure detectee

- H2: How does pt-online-schema-change handle this?
- H3: alter-foreign-keys-method= drop_swap
- H3: alter-foreign-keys-method= rebuild_constraints

## Images et graphiques reperes

- featured / image: [How pt-online-schema-change Handles Foreign Keys](https://www.percona.com/wp-content/uploads/2026/03/pt-online-schema-change-1.jpg)
- content / image: [pt-online-schema-change](https://www.percona.com/wp-content/uploads/2026/03/pt-online-schema-change-1-300x200.jpg)
