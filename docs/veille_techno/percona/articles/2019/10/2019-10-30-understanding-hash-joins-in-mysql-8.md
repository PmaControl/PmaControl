---
title: Understanding Hash Joins in MySQL 8
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-hash-joins-in-mysql-8/
  post_id: 21091
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2019-10-30T16:03:32'
published_at_gmt: '2019-10-30T16:03:32'
modified_at: '2026-04-27T21:24:18'
modified_at_gmt: '2026-04-27T21:24:18'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- hash join
- MySQL
tag_slugs:
- hash-join
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/hash-joins-mysql.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding Hash Joins in MySQL 8

Source: [Percona Blog](https://www.percona.com/blog/understanding-hash-joins-in-mysql-8/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2019-10-30T16:03:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In MySQL 8.0.18 there is a new feature called Hash Joins, and I wanted to see how it works and in which situations it can help us. Here you can find a nice detailed explanation about how it works under the hood. The high-level basics are the following: if there is a join, it will … Continued

## Structure detectee

- H3: Great, but does this give us any performance benefits?
- H3: First test – Hash Joins
- H3: Second Test – Non-Hash Joins
- H3: Third Test – Joins Based on Indexes
- H3: Limitations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Understanding Hash Joins in MySQL 8](https://www.percona.com/wp-content/uploads/2026/03/hash-joins-mysql.png)
- content / image: [hash joins mysql](https://www.percona.com/wp-content/uploads/2026/03/hash-joins-mysql-300x168.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
