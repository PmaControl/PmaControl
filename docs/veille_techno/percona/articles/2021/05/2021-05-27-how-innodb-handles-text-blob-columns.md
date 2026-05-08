---
title: How InnoDB Handles TEXT/BLOB Columns
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-innodb-handles-text-blob-columns/
  post_id: 24382
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2021-05-27T16:04:36'
published_at_gmt: '2021-05-27T16:04:36'
modified_at: '2026-05-04T20:47:19'
modified_at_gmt: '2026-05-04T20:47:19'
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
- MySQL
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- storage-engine
tags:
- InnoDB
- MySQL
- mysql-and-variants
- Storage Engine
tag_slugs:
- innodb
- mysql
- mysql-and-variants
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Handles-TEXT-BLOB-Columns.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How InnoDB Handles TEXT/BLOB Columns

Source: [Percona Blog](https://www.percona.com/blog/how-innodb-handles-text-blob-columns/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2021-05-27T16:04:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently we had a debate in the consulting team about how InnoDB handles TEXT/BLOB columns. More specifically, the argument was around the Barracuda file format with dynamic rows. In the InnoDB official documentation, you can find this extract: When a table is created with ROW_FORMAT=DYNAMIC, InnoDB can store long variable-length column values (for VARCHAR, VARBINARY, … Continued

## Structure detectee

- H2: Experimentation with TEXT/BLOB
- H3: Cutoff to an Overflow Page
- H2: Performance Impacts
- H3: Storage
- H3: Reads
- H3: Writes
- H3: JSON
- H2: How to Best Deal with TEXT/BLOB Columns?
- H3: Data Compression
- H3: Avoid Returning TEXT/BLOB Columns
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How InnoDB Handles TEXT/BLOB Columns](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Handles-TEXT-BLOB-Columns.png)
- content / image: [InnoDB Handles TEXT BLOB Columns](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Handles-TEXT-BLOB-Columns-300x157.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
