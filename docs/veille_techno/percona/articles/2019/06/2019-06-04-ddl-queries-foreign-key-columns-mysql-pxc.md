---
title: DDL Queries on Foreign Key Columns in MySQL/PXC
source:
  name: Percona Blog
  url: https://www.percona.com/blog/ddl-queries-foreign-key-columns-mysql-pxc/
  post_id: 20459
source_author:
  name: Uday Varagani
  slug: uday-varagani
  url: https://www.percona.com/blog/author/uday-varagani/
  website: ''
published_at: '2019-06-04T13:30:28'
published_at_gmt: '2019-06-04T13:30:28'
modified_at: '2026-04-27T21:17:38'
modified_at_gmt: '2026-04-27T21:17:38'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- MySQL
- Percona XtraDB Cluster
tag_slugs:
- mysql
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/DDL-Queries-on-Foreign-Key-Columns.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# DDL Queries on Foreign Key Columns in MySQL/PXC

Source: [Percona Blog](https://www.percona.com/blog/ddl-queries-foreign-key-columns-mysql-pxc/)

Auteur source: [Uday Varagani](https://www.percona.com/blog/author/uday-varagani/)

Publication: 2019-06-04T13:30:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I received a support request where the customer wanted to convert an INTEGER column to BIGINT on two tables. These tables are related by a foreign key, and it is a 3 node PXC cluster. These tables are 20GB and 82 GB in size and DDL’s on such tables in a production environment is … Continued

## Structure detectee

- H2: Direct Alter:
- H2: pt-online-schema-change:
- H2: alter-foreign-keys-method=auto
- H2: alter-foreign-keys-method=rebuild_constraints
- H2: DDL Queries on Foreign Key Columns Workaround

## Images et graphiques reperes

- featured / image: [DDL Queries on Foreign Key Columns in MySQL/PXC](https://www.percona.com/wp-content/uploads/2026/03/DDL-Queries-on-Foreign-Key-Columns.jpg)
- content / image: [DDL Queries on Foreign Key Columns](https://www.percona.com/wp-content/uploads/2026/03/DDL-Queries-on-Foreign-Key-Columns-300x200.jpg)
