---
title: A schema change inconsistency with Galera Cluster for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-schema-change-inconsistency-with-galera-cluster-for-mysql/
  post_id: 8382
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-07-21T07:00:32'
published_at_gmt: '2014-07-21T07:00:32'
modified_at: '2026-05-04T22:23:58'
modified_at_gmt: '2026-05-04T22:23:58'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Galera Cluster
- Percona XtraDB Cluster
- schema change inconsistencies
tag_slugs:
- galera-cluster
- percona-xtradb-cluster
- schema-change-inconsistencies
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A schema change inconsistency with Galera Cluster for MySQL

Source: [Percona Blog](https://www.percona.com/blog/a-schema-change-inconsistency-with-galera-cluster-for-mysql/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-07-21T07:00:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently worked on a case where one node of a Galera cluster had its schema desynchronized with the other nodes. And that was although Total Order Isolation method was in effect to perform the schema changes. Let’s see what happened. Background For those of you who are not familiar with how Galera can perform … Continued

## Structure detectee

- H2: Background
- H2: A test case
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
