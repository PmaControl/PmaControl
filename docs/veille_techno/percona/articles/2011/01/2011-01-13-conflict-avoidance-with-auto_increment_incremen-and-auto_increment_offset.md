---
title: Conflict Avoidance with auto_increment_increment and auto_increment_offset
source:
  name: Percona Blog
  url: https://www.percona.com/blog/conflict-avoidance-with-auto_increment_incremen-and-auto_increment_offset/
  post_id: 2639
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-01-13T01:02:53'
published_at_gmt: '2011-01-13T01:02:53'
modified_at: '2026-03-23T21:50:08'
modified_at_gmt: '2026-03-23T21:50:08'
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
tags:
- Replication
tag_slugs:
- replication
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Conflict Avoidance with auto_increment_increment and auto_increment_offset

Source: [Percona Blog](https://www.percona.com/blog/conflict-avoidance-with-auto_increment_incremen-and-auto_increment_offset/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-01-13T01:02:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A lot of people are running MySQL Master-Master replication pairs in Active-Passive mode for purpose of high availabilities using MMM or other solutions. Such solutions generally have one major problem – you have to be very carefully switching writes as if you do not do it atomically (such as some scripts continue to write to … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
