---
title: More on MySQL Error Messages
source:
  name: Percona Blog
  url: https://www.percona.com/blog/more-on-mysql-error-messages/
  post_id: 2802
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-04-06T17:30:55'
published_at_gmt: '2011-04-06T17:30:55'
modified_at: '2026-04-28T21:25:23'
modified_at_gmt: '2026-04-28T21:25:23'
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

# More on MySQL Error Messages

Source: [Percona Blog](https://www.percona.com/blog/more-on-mysql-error-messages/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-04-06T17:30:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I wrote about MySQL Error Messages before and as you might guess I’m not very happy with quality for error messages it produces. Now I’m revisiting this subject with couple of more annoying examples I ran into during last couple of days. mysql> drop database test; ERROR 1010 (HY000): Error dropping database (can't rmdir './test/', errno: 17) 1 2 mysql & gt ; drop database test ; ERROR 1010 ( HY000 ) : Error dropping database ( can 't rmdir ' . / test / ' , errno : 17 )

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
