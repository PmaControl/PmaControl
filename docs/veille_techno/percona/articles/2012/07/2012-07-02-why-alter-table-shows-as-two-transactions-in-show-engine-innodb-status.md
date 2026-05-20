---
title: Why ALTER TABLE shows as two transactions in SHOW ENGINE INNODB STATUS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-alter-table-shows-as-two-transactions-in-show-engine-innodb-status/
  post_id: 3698
source_author:
  name: Stewart Smith
  slug: stewart
  url: https://www.percona.com/blog/author/stewart/
  website: http://www.percona.com/about-us/our-team/stewart-smith/
published_at: '2012-07-02T00:54:32'
published_at_gmt: '2012-07-02T00:54:32'
modified_at: '2026-03-23T22:24:02'
modified_at_gmt: '2026-03-23T22:24:02'
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
category_slugs:
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why ALTER TABLE shows as two transactions in SHOW ENGINE INNODB STATUS

Source: [Percona Blog](https://www.percona.com/blog/why-alter-table-shows-as-two-transactions-in-show-engine-innodb-status/)

Auteur source: [Stewart Smith](https://www.percona.com/blog/author/stewart/)

Publication: 2012-07-02T00:54:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When executing an ALTER TABLE, InnoDB (and XtraDB) will create two InnoDB transactions: One transaction is created when the table being ALTERed is locked by the server.This will show up as something like “TABLE LOCK table schema.table_name trx id XXXX lock mode S” in SHOW ENGINE INNODB STATUS. Another is created when adding or dropping … Continued

## Auteur source

Stewart Smith has a deep background in database internals including MySQL, MySQL Cluster, Drizzle, InnoDB and HailDB. he is also one of the founding core developers of the Drizzle database server. He served at Percona from 2011-2014. He is a former Percona employee.
