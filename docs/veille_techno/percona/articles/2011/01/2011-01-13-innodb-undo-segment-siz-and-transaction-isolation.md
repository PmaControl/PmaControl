---
title: Innodb undo segment size and transaction isolation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-undo-segment-siz-and-transaction-isolation/
  post_id: 2640
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-01-13T02:38:13'
published_at_gmt: '2011-01-13T02:38:13'
modified_at: '2026-04-28T21:22:28'
modified_at_gmt: '2026-04-28T21:22:28'
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
- InnoDB
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Innodb undo segment size and transaction isolation

Source: [Percona Blog](https://www.percona.com/blog/innodb-undo-segment-siz-and-transaction-isolation/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-01-13T02:38:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

You might know if you have long running transactions you’re risking having a lot of “garbage” accumulated in undo segment size which can cause performance degradation as well as increased disk space usage. Long transactions can also be bad for other reasons such as taking row level locks which will prevent other transactions for execution, … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
