---
title: When Should You Store SQL Serialized Data in the Database?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-should-you-store-serialized-objects-in-the-database/
  post_id: 2216
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2010-01-21T21:39:59'
published_at_gmt: '2010-01-21T21:39:59'
modified_at: '2026-03-23T21:38:17'
modified_at_gmt: '2026-03-23T21:38:17'
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
- eblob
- friendly
- MySQL
- Tips
tag_slugs:
- eblob
- friendly
- mysql
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When Should You Store SQL Serialized Data in the Database?

Source: [Percona Blog](https://www.percona.com/blog/when-should-you-store-serialized-objects-in-the-database/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2010-01-21T21:39:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A while back Friendfeed posted a blog post explaining how they changed from storing data in MySQL columns to serializing data and just storing it inside TEXT/BLOB columns. It seems that since then, the technique has gotten more popular with Ruby gems now around to do this for you automatically.

## Structure detectee

- H2: So when is it a good idea to store SQL serialized Data with this technique?

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
