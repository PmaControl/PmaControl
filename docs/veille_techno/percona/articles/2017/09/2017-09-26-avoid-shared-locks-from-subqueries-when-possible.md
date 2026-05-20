---
title: Avoid Shared Locks from Subqueries When Possible
source:
  name: Percona Blog
  url: https://www.percona.com/blog/avoid-shared-locks-from-subqueries-when-possible/
  post_id: 17361
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2017-09-26T00:50:56'
published_at_gmt: '2017-09-26T00:50:56'
modified_at: '2026-05-05T18:50:32'
modified_at_gmt: '2026-05-05T18:50:32'
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
- locks
- MySQL
- shared locks
tag_slugs:
- innodb
- locks
- mysql
- shared-locks
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Shared-Locks.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Avoid Shared Locks from Subqueries When Possible

Source: [Percona Blog](https://www.percona.com/blog/avoid-shared-locks-from-subqueries-when-possible/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2017-09-26T00:50:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how to avoid shared locks from subqueries. I’m pretty sure most of you have seen an UPDATE statement matching rows returned from a SELECT query: MySQL update ibreg set k=1 where id in (select id from ibcmp where id > 90000); 1 update ibreg set k = 1 where id in ( select id from ibcmp where id > 90000); This query, when executed with autocommit = 1 , is normally harmless. However, this can have bad effects when combined with other statements in the … Continued

## Images et graphiques reperes

- featured / image: [Avoid Shared Locks from Subqueries When Possible](https://www.percona.com/wp-content/uploads/2026/03/Shared-Locks.jpg)
- content / image: [Shared Locks](https://www.percona.com/wp-content/uploads/2026/03/Shared-Locks-300x200.jpg)

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
