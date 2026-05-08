---
title: Upgrading to MySQL 5.7, focusing on temporal types
source:
  name: Percona Blog
  url: https://www.percona.com/blog/upgrading-to-mysql-5-7-focusing-on-temporal-types/
  post_id: 15058
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2016-04-27T20:30:24'
published_at_gmt: '2016-04-27T20:30:24'
modified_at: '2026-05-05T20:10:56'
modified_at_gmt: '2026-05-05T20:10:56'
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
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- datetime
- fractional
- MySQL
- time
- timestamp
tag_slugs:
- datetime
- fractional
- mysql
- time
- timestamp
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/temporal-types.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Upgrading to MySQL 5.7, focusing on temporal types

Source: [Percona Blog](https://www.percona.com/blog/upgrading-to-mysql-5-7-focusing-on-temporal-types/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2016-04-27T20:30:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll discuss how MySQL 5.7 handles the old temporal types during an upgrade. MySQL changed the temporal types in MySQL 5.6.4, and it introduced a new feature: microseconds resolution in the TIME, TIMESTAMP and DATETIME types. Now these parameters can be set down to microsecond granularity. Obviously, this means format changes, but … Continued

## Structure detectee

- H2: Are they converted automatically to the new format?
- H2: How can we find these tables?
- H2: Can I upgrade to MySQL 5.7?
- H2: Can we avoid this at upgrade?
- H2: Can we still write these fields?
- H2: Does the Replication work?
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Upgrading to MySQL 5.7, focusing on temporal types](https://www.percona.com/wp-content/uploads/2026/03/temporal-types.jpg)
- content / image: [temporal types](https://www.percona.com/wp-content/uploads/2026/03/temporal-types-300x225.jpg)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
