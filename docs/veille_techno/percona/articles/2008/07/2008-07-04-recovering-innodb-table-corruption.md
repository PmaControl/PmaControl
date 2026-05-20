---
title: Recovering Innodb table Corruption
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recovering-innodb-table-corruption/
  post_id: 1641
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2008-07-04T08:27:10'
published_at_gmt: '2008-07-04T08:27:10'
modified_at: '2026-04-28T20:37:37'
modified_at_gmt: '2026-04-28T20:37:37'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
categories:
- Insight for DBAs
category_slugs:
- insight-for-dbas
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

# Recovering Innodb table Corruption

Source: [Percona Blog](https://www.percona.com/blog/recovering-innodb-table-corruption/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2008-07-04T08:27:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Assume you’re running MySQL with Innodb tables and you’ve got crappy hardware, driver bug, kernel bug, unlucky power failure or some rare MySQL bug and some pages in Innodb tablespace got corrupted. In such cases Innodb will typically print something like this: InnoDB: Database page corruption on disk or a failed InnoDB: file read of … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
