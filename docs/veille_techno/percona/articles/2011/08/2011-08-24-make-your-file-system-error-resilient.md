---
title: Make your file system error resilient
source:
  name: Percona Blog
  url: https://www.percona.com/blog/make-your-file-system-error-resilient/
  post_id: 3031
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-08-24T13:00:59'
published_at_gmt: '2011-08-24T13:00:59'
modified_at: '2026-03-23T22:04:18'
modified_at_gmt: '2026-03-23T22:04:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- Insight for DBAs
- MySQL
category_slugs:
- hardware-and-storage
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

# Make your file system error resilient

Source: [Percona Blog](https://www.percona.com/blog/make-your-file-system-error-resilient/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-08-24T13:00:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the typical problems I see setting up ext2/3/4 file system is sticking to defaults when it comes to behavior on errors. By default these filesystems are configured to Continue when error (such as IO error or meta data inconsistency) is discovered which can continue spreading corruption. This manifests itself in a worst way … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
