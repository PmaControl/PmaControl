---
title: ACTIVE with Locks – Now thats a problem !
source:
  name: Percona Blog
  url: https://www.percona.com/blog/active-with-locks-now-thats-a-problem/
  post_id: 2913
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-06-02T07:00:01'
published_at_gmt: '2011-06-02T07:00:01'
modified_at: '2026-05-04T21:31:31'
modified_at_gmt: '2026-05-04T21:31:31'
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

# ACTIVE with Locks – Now thats a problem !

Source: [Percona Blog](https://www.percona.com/blog/active-with-locks-now-thats-a-problem/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-06-02T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of item I always look at SHOW ENGINE INNODB STATUS to see if there are any transactions spending very long time in ACTIVE state. In the perfect world if you’re running online system you should not see transactions spending more than couple of seconds in ACTIVE state. Especially ACTIVE transactions which do not currently … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
