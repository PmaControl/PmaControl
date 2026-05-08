---
title: Innodb Table Locks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-table-locks/
  post_id: 3708
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-07-31T23:28:19'
published_at_gmt: '2012-07-31T23:28:19'
modified_at: '2026-04-28T21:37:45'
modified_at_gmt: '2026-04-28T21:37:45'
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

# Innodb Table Locks

Source: [Percona Blog](https://www.percona.com/blog/innodb-table-locks/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-07-31T23:28:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Innodb uses row level locks right ? So if you see locked tables reported in SHOW ENGINE INNODB STATUS you might be confused and rightfully so as Innodb table locking is a bit more complicated than traditional MyISAM table locks. Let me start with some examples. First lets run SELECT Query: ---TRANSACTION 12303, ACTIVE 26 sec mysql tables in use 2, locked 0 MySQL thread id 53038, OS thread handle 0x7ff759b22700, query id 3918786 localhost root Sending data select count(*) from sbtest,sbtest x Trx read view will not see trx with id >= 12304, sees < 12301 1 2 3 4 5 -- - TRANSACTION 12303 , ACTIVE 26 sec mysql tables in use 2 , locked 0 MySQL thread id 53038 , OS thread handle 0x7ff759b22700 , query id 3918786 localhost root Sending data select count ( * ) from sbtest , sbtest x Trx read view will not see trx with id & gt ; = 12304 , sees & lt ; 12301 As you can … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
