---
title: Table locks in SHOW INNODB STATUS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/table-locks-in-show-innodb-status/
  post_id: 2358
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-06-09T02:56:02'
published_at_gmt: '2010-06-09T02:56:02'
modified_at: '2026-04-28T21:14:02'
modified_at_gmt: '2026-04-28T21:14:02'
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
tags:
- InnoDB
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Table locks in SHOW INNODB STATUS

Source: [Percona Blog](https://www.percona.com/blog/table-locks-in-show-innodb-status/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-06-09T02:56:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Quite frequently I see people confused what table locks reported by SHOW INNODB STATUS really mean. Check this out for example: ---TRANSACTION 0 4872, ACTIVE 32 sec, process no 7142, OS thread id 1141287232 2 lock struct(s), heap size 368 MySQL thread id 8, query id 164 localhost root TABLE LOCK table `test/t1` trx id 0 4872 lock mode IX 1 2 3 4 -- - TRANSACTION 0 4872 , ACTIVE 32 sec , process no 7142 , OS thread id 1141287232 2 lock struct ( s ) , heap size 368 MySQL thread id 8 , query id 164 localhost root TABLE LOCK table ` test / t1 ` trx id 0 4872 lock mode IX This output gives us an impression Innodb has taken table lock on test/t1 table and many people tend to think Innodb in fact in some circumstances would abandon its row level locking and … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
