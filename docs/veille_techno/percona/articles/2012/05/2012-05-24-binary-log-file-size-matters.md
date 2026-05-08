---
title: Binary log file size matters (sometimes)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/binary-log-file-size-matters/
  post_id: 3604
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2012-05-24T14:20:27'
published_at_gmt: '2012-05-24T14:20:27'
modified_at: '2026-04-28T21:35:55'
modified_at_gmt: '2026-04-28T21:35:55'
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
- binary logs
- ext3
- MySQL
tag_slugs:
- binary-logs
- ext3
- mysql
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Binary log file size matters (sometimes)

Source: [Percona Blog](https://www.percona.com/blog/binary-log-file-size-matters/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2012-05-24T14:20:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I used to think one should never look at max_binlog_size, however last year I had a couple of interesting cases which showed that sometimes it may be very important variable to tune properly. I meant to write about it earlier but never really had a chance to do it. I have it now!

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
