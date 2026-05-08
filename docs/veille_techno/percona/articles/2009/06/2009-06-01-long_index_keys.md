---
title: Long Index Keys
source:
  name: Percona Blog
  url: https://www.percona.com/blog/long_index_keys/
  post_id: 9468
source_author:
  name: kuszmaul
  slug: kuszmaul
  url: https://www.percona.com/blog/author/kuszmaul/
  website: ''
published_at: '2009-06-01T23:33:00'
published_at_gmt: '2009-06-01T23:33:00'
modified_at: '2026-05-04T22:40:18'
modified_at_gmt: '2026-05-04T22:40:18'
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
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Long Index Keys

Source: [Percona Blog](https://www.percona.com/blog/long_index_keys/)

Auteur source: [kuszmaul](https://www.percona.com/blog/author/kuszmaul/)

Publication: 2009-06-01T23:33:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post we’ll describe a query that accrued significant performance advantages from using a relatively long index key. (This posting is by Zardosht and Bradley.) We ran across this query recently when interacting with a customer (who gave us permission to post this sanitized version of the story): SELECT name, Count(e2) AS CountOfe2 FROM (SELECT distinct name, e2 FROM (SELECT atable.NAME AS name, pd1.NAME AS e2 FROM atable INNER JOIN atable AS pd1 ON (atable.id = pd1.id) AND (atable.off = pd1.off) AND (atable.len = pd1.len)) ent WHERE ((ent.name<>ent.e2))) outside GROUP BY outside.name order by CountOfe2 desc; 1 2 3 4 5 6 7 8 9 10 11 SELECT name , Count ( e2 ) AS CountOfe2 FROM ( SELECT distinct name , e2 FROM ( SELECT atable . NAME AS name , pd1 . NAME AS e2 FROM atable INNER JOIN atable AS pd1 ON ( atable . id = pd1 . id ) AND ( atable . off = pd1 . off ) AND ( atable . len = p...
