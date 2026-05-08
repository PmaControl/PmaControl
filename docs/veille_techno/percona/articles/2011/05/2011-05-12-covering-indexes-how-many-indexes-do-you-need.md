---
title: 'Covering Indexes: How many indexes do you need?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/covering-indexes-how-many-indexes-do-you-need/
  post_id: 9563
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2011-05-12T12:48:09'
published_at_gmt: '2011-05-12T12:48:09'
modified_at: '2026-03-25T18:17:38'
modified_at_gmt: '2026-03-25T18:17:38'
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
tags:
- clustering indexes
- covering indexes
- MySQL
- TokuDB
tag_slugs:
- clustering-indexes
- covering-indexes
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Covering Indexes: How many indexes do you need?

Source: [Percona Blog](https://www.percona.com/blog/covering-indexes-how-many-indexes-do-you-need/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2011-05-12T12:48:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve recently been blogging about how partitioning is a poor man’s answer to covering indexes. I got the following comment from Jaimie Sirovich: “There are many environments where you could end up creating N! indices to cover queries for queries against lots of dimensions.” [Just a note: this is only one of several points he … Continued

## Structure detectee

- H3: How many indexes does it take to beat partitioning?
- H3: How many indexes does it take in general to cover your queries?
- H3: So how many indexes do I need?
- H3: What if I don’t know what queries I’ll be performing?
- H3: Good but not perfect covering is OK too
