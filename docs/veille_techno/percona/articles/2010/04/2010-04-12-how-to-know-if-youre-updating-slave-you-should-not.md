---
title: How to know if you’re updating Slave you should not ?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-know-if-youre-updating-slave-you-should-not/
  post_id: 2275
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-04-12T02:14:48'
published_at_gmt: '2010-04-12T02:14:48'
modified_at: '2026-04-28T21:10:34'
modified_at_gmt: '2026-04-28T21:10:34'
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

# How to know if you’re updating Slave you should not ?

Source: [Percona Blog](https://www.percona.com/blog/how-to-know-if-youre-updating-slave-you-should-not/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-04-12T02:14:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When replication runs out of sync first question you often ask is if someone could be writing to the slave. Of course there is read_only setting which is good to set in the slave but it is not set always and also users with SUPER privilege bypass it. Looking into binary log is obvious choice … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
