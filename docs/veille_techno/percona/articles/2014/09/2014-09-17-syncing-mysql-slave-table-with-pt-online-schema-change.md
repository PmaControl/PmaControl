---
title: Syncing MySQL slave table with pt-online-schema-change
source:
  name: Percona Blog
  url: https://www.percona.com/blog/syncing-mysql-slave-table-with-pt-online-schema-change/
  post_id: 8550
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2014-09-17T14:06:49'
published_at_gmt: '2014-09-17T14:06:49'
modified_at: '2026-03-25T17:41:29'
modified_at_gmt: '2026-03-25T17:41:29'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Jervin Real
- NOOP ALTER
- Percona Toolkit
- Primary
- pt-online-schema-change
- pt-table-checksum
- pt-table-sync
tag_slugs:
- jervin-real
- noop-alter
- percona-toolkit
- primary
- pt-online-schema-change
- pt-table-checksum
- pt-table-sync
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Syncing MySQL slave table with pt-online-schema-change

Source: [Percona Blog](https://www.percona.com/blog/syncing-mysql-slave-table-with-pt-online-schema-change/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2014-09-17T14:06:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently encountered a situation in which after running Percona Toolkit’s pt-table-checksum on a customer system, 95% of the table on the MySQL master was different on the MySQL slave. Although this table was not a critical part of the infrastructure, from time to time, writes to the table from the master would break replication. … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
