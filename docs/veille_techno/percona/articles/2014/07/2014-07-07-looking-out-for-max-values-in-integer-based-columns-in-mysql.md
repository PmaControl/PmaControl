---
title: Looking out for max values in integer-based columns in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/looking-out-for-max-values-in-integer-based-columns-in-mysql/
  post_id: 8365
source_author:
  name: Matthew Boehm
  slug: matthew-boehm
  url: https://www.percona.com/blog/author/matthew-boehm/
  website: https://www.percona.com/training
published_at: '2014-07-07T10:00:54'
published_at_gmt: '2014-07-07T10:00:54'
modified_at: '2026-05-04T20:56:51'
modified_at_gmt: '2026-05-04T20:56:51'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- integer-based columns
- Matthew Boehm
- max values
- pt-online-schema-change
tag_slugs:
- integer-based-columns
- matthew-boehm
- max-values
- pt-online-schema-change
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Looking out for max values in integer-based columns in MySQL

Source: [Percona Blog](https://www.percona.com/blog/looking-out-for-max-values-in-integer-based-columns-in-mysql/)

Auteur source: [Matthew Boehm](https://www.percona.com/blog/author/matthew-boehm/)

Publication: 2014-07-07T10:00:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Yay! My first blog post! As long as at least 1 person finds it useful, I’ve done my job. 😉 Recently, one of my long-term clients was noticing that while their INSERTs were succeeding, a particular column counter was not incrementing. A quick investigation determined the column was of type int(11) and they had reached … Continued

## Auteur source

Matthew joined Percona in the fall of 2012 as a MySQL Consultant; now Principal Architect / Senior Instructor. His areas of knowledge include the traditional LAMP stack, MySQL high availability, massive sharding topologies, and PHP/GoLang/C/C++ MySQL development. Previously, Matthew was a DBA for the 5th largest world-wide MySQL installation at eBay/PayPal. During his off-hours, Matthew is a nationally ranked, competitive West Coast Swing dancer and travels to competitions around the US. He enjoys working out, camping, biking, and shooting Junior-Olympic Recurve Archery with his oldest son.
