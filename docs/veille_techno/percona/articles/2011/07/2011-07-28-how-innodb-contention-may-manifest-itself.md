---
title: How Innodb Contention may manifest itself
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-innodb-contention-may-manifest-itself/
  post_id: 3001
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-07-28T15:04:28'
published_at_gmt: '2011-07-28T15:04:28'
modified_at: '2026-03-23T22:03:03'
modified_at_gmt: '2026-03-23T22:03:03'
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

# How Innodb Contention may manifest itself

Source: [Percona Blog](https://www.percona.com/blog/how-innodb-contention-may-manifest-itself/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-07-28T15:04:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Even though multiple fixes have been implemented in Percona Server and MySQL 5.5, there are still workloads in which case mutex (or rw-lock) contention is a performance limiting factor, helped by ever growing number of cores available in the systems. It is interesting though the contention may manifest itself in the different form from the … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
