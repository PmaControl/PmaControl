---
title: When does Innodb Start Transaction ?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-does-innodb-start-transaction/
  post_id: 2638
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-01-12T01:07:56'
published_at_gmt: '2011-01-12T01:07:56'
modified_at: '2026-04-28T21:22:13'
modified_at_gmt: '2026-04-28T21:22:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When does Innodb Start Transaction ?

Source: [Percona Blog](https://www.percona.com/blog/when-does-innodb-start-transaction/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-01-12T01:07:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When does Innodb Start Transaction ? The answer looks obvious – when you issue “BEGIN” command. This is however wrong answer from engine point of you. Run “SHOW INNODB STATUS” and you will see “not started” status in transaction list. It is only when you read (or write) to INNODB table you can see transaction … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
