---
title: InnoDB Gap Locks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodbs-gap-locks/
  post_id: 3435
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-03-28T06:40:37'
published_at_gmt: '2012-03-28T06:40:37'
modified_at: '2026-03-23T22:17:31'
modified_at_gmt: '2026-03-23T22:17:31'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Gap-Locks.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB Gap Locks

Source: [Percona Blog](https://www.percona.com/blog/innodbs-gap-locks/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-03-28T06:40:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the most important features of InnoDB is the row level locking. This feature provides better concurrency under heavy write load but needs additional precautions to avoid phantom reads and to get a consistent Statement based replication. To accomplish that, row level locking databases also acquire gap locks. What is a Phantom Read A … Continued

## Structure detectee

- H2: What is a Phantom Read
- H2: What is a gap lock?

## Images et graphiques reperes

- featured / image: [InnoDB Gap Locks](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Gap-Locks.jpeg)

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
