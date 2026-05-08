---
title: What is innodb_autoinc_lock_mode?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-is-innodb_autoinc_lock_mode-and-why-should-i-care/
  post_id: 17136
source_author:
  name: Manjot Singh
  slug: manjot-singh
  url: https://www.percona.com/blog/author/manjot-singh/
  website: ''
published_at: '2017-07-26T15:15:14'
published_at_gmt: '2017-07-26T15:15:14'
modified_at: '2026-03-20T21:27:50'
modified_at_gmt: '2026-03-20T21:27:50'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Docker-Images-e1495564004383.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What is innodb_autoinc_lock_mode?

Source: [Percona Blog](https://www.percona.com/blog/what-is-innodb_autoinc_lock_mode-and-why-should-i-care/)

Auteur source: [Manjot Singh](https://www.percona.com/blog/author/manjot-singh/)

Publication: 2017-07-26T15:15:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I was recently discussing innodb_autoinc_lock_mode with some colleagues to address issues at a company I was working with. innodb_autoinc_lock_mode This variable defines the lock mode to use for generating auto-increment values. The permissible values are 0, 1 or 2 (for “traditional”, “consecutive” or “interleaved” lock mode, respectively). In most cases, this variable is set to … Continued

## Structure detectee

- H2: innodb_autoinc_lock_mode

## Images et graphiques reperes

- featured / image: [What is innodb_autoinc_lock_mode?](https://www.percona.com/wp-content/uploads/2026/03/Docker-Images-e1495564004383.jpg)
- content / image: [innodb_autoinc_lock_mode](https://www.percona.com/wp-content/uploads/2026/03/Docker-Images-300x263.jpg)

## Auteur source

Manjot Singh is an Architect with Percona in California. He loves to learn about new technologies and apply them to real world problems. Manjot is a veteran of startup and Fortune 50 enterprise companies alike with a few years spent in government, education, and hospital IT.
