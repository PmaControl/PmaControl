---
title: How fast is FLUSH TABLES WITH READ LOCK?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-fast-is-flush-tables-with-read-lock/
  post_id: 2295
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-04-24T21:05:39'
published_at_gmt: '2010-04-24T21:05:39'
modified_at: '2026-04-28T21:11:05'
modified_at_gmt: '2026-04-28T21:11:05'
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
tags:
- Backups
tag_slugs:
- backups
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How fast is FLUSH TABLES WITH READ LOCK?

Source: [Percona Blog](https://www.percona.com/blog/how-fast-is-flush-tables-with-read-lock/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-04-24T21:05:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A week or so ago at the MySQL conference, I visited one of the backup vendors in the Expo Hall. I started to chat with them about their MySQL backup product. One of the representatives told me that their backup product uses FLUSH TABLES WITH READ LOCK, which he admitted takes a global lock on … Continued

## Structure detectee

- H3: Requesting the lock
- H3: Waiting for the lock
- H3: Flushing tables
- H3: Holding the lock
- H3: Conclusion

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
