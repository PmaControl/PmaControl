---
title: How To Inject an Empty XA Transaction in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-inject-an-empty-xa-transaction-in-mysql/
  post_id: 23090
source_author:
  name: Jake Davis
  slug: jake-davis
  url: https://www.percona.com/blog/author/jake-davis/
  website: ''
published_at: '2020-09-15T16:28:42'
published_at_gmt: '2020-09-15T16:28:42'
modified_at: '2026-04-27T22:14:39'
modified_at_gmt: '2026-04-27T22:14:39'
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
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Inject-an-Empty-XA-Transaction-in-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Inject an Empty XA Transaction in MySQL

Source: [Percona Blog](https://www.percona.com/blog/how-to-inject-an-empty-xa-transaction-in-mysql/)

Auteur source: [Jake Davis](https://www.percona.com/blog/author/jake-davis/)

Publication: 2020-09-15T16:28:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you are using XA transactions, then you’ve likely run into a few replication issues with the 2PCs (2 Phase Commits). Here is a common error we see in Percona’s Managed Services and a few ways to handle it, including injecting an empty XA transaction. Shell Last_Error: Error 'XAER_NOTA: Unknown XID' on query. Default database: 'punisher'. Query: 'XA COMMIT X'1a',X'a1',1' 1 Last_Error : Error 'XAER_NOTA: Unknown XID' on query . Default database : 'punisher' . Query : 'XA COMMIT X' 1a ',X' a1 ',1' What Does it Mean? It means that replication … Continued

## Structure detectee

- H3: What Does it Mean?
- H3: How Do We Fix It?
- H3: How Does This Happen?
- H3: Summary

## Images et graphiques reperes

- featured / image: [How To Inject an Empty XA Transaction in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Inject-an-Empty-XA-Transaction-in-MySQL.png)
- content / image: [Inject an Empty XA Transaction in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Inject-an-Empty-XA-Transaction-in-MySQL-300x157.png)

## Auteur source

Jake has been a Percona DBA on the Managed Services team since 2018. He enjoys killing queries and clean failovers. You can find him listening to podcasts on all things Linux and tinkering with open source projects in his home environment.
