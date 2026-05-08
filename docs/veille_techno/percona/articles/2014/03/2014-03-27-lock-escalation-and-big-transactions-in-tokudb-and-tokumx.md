---
title: Lock Escalation and Big Transactions in TokuDB and TokuMX
source:
  name: Percona Blog
  url: https://www.percona.com/blog/lock-escalation-and-big-transactions-in-tokudb-and-tokumx/
  post_id: 3208
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2014-03-27T13:25:43'
published_at_gmt: '2014-03-27T13:25:43'
modified_at: '2026-03-23T22:09:54'
modified_at_gmt: '2026-03-23T22:09:54'
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
tags:
- MySQL
- TokuDB
tag_slugs:
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Lock Escalation and Big Transactions in TokuDB and TokuMX

Source: [Percona Blog](https://www.percona.com/blog/lock-escalation-and-big-transactions-in-tokudb-and-tokumx/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2014-03-27T13:25:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We have seen TokuDB lock escalation stall the execution of SQL operations for tens of seconds. To address this problem, we changed the lock escalation algorithm used by TokuDB and TokuMX so that the cost of lock escalation only affects big transactions. We also eliminated a serialization point when running lock escalation. Transactions in TokuDB … Continued
