---
title: Impact of DDL Operations on Aurora MySQL Readers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/impact-of-ddl-operations-on-aurora-mysql-readers/
  post_id: 26401
source_author:
  name: Brijesh Chauhan
  slug: brijesh-chauhan
  url: https://www.percona.com/blog/author/brijesh-chauhan/
  website: ''
published_at: '2022-12-28T13:18:34'
published_at_gmt: '2022-12-28T13:18:34'
modified_at: '2026-03-26T20:30:23'
modified_at_gmt: '2026-03-26T20:30:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- cloud
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Impact-of-DDL-Operations-on-Aurora-MySQL-Readers.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Impact of DDL Operations on Aurora MySQL Readers

Source: [Percona Blog](https://www.percona.com/blog/impact-of-ddl-operations-on-aurora-mysql-readers/)

Auteur source: [Brijesh Chauhan](https://www.percona.com/blog/author/brijesh-chauhan/)

Publication: 2022-12-28T13:18:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I came across an interesting investigation about long-running transactions getting killed on an Aurora Reader instance. In this article, I will explain why it is advisable to avoid long-running transactions on Aurora readers when executing frequent DDL operations on the Writer, or at least be aware of how a DDL can impact your Aurora … Continued

## Images et graphiques reperes

- featured / image: [Impact of DDL Operations on Aurora MySQL Readers](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-DDL-Operations-on-Aurora-MySQL-Readers.png)
- content / image: [Impact of DDL Operations on Aurora MySQL Readers](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-DDL-Operations-on-Aurora-MySQL-Readers-300x168.png)

## Auteur source

Brijesh Chauhan is an experienced MySQL DBA who has been working with Percona since May 2021. Prior to joining Percona, he worked for a leading cloud service provider. Brijesh currently resides in Bangalore with his wife and daughter.
