---
title: InnoDB ALTER TABLE ADD INDEX and INSERT performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-alter-table-add-index-and-insert-performance/
  post_id: 20434
source_author:
  name: Satya Bodapati
  slug: satya-bodapati
  url: https://www.percona.com/blog/author/satya-bodapati/
  website: ''
published_at: '2019-06-27T13:20:32'
published_at_gmt: '2019-06-27T13:20:32'
modified_at: '2026-04-27T21:16:03'
modified_at_gmt: '2026-04-27T21:16:03'
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
category_slugs:
- mysql
tags:
- MySQL
tag_slugs:
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-ALTER-TABLE-ADD-INDEX.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB ALTER TABLE ADD INDEX and INSERT performance

Source: [Percona Blog](https://www.percona.com/blog/innodb-alter-table-add-index-and-insert-performance/)

Auteur source: [Satya Bodapati](https://www.percona.com/blog/author/satya-bodapati/)

Publication: 2019-06-27T13:20:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous blog post, I explained the internals of the sorted index build process. The blog ended with saying “there is one disadvantage.” Beginning in MySQL 5.6, many DDLs including ALTER TABLE ADD INDEX became “ONLINE”. Meaning, when the ALTER is in progress, there can be concurrent SELECTS and DMLs. See the MySQL documentation … Continued

## Structure detectee

- H4: Is it fixed?
- H4: How much is the improvement?
- H4: How does it compare to 5.6?
- H4: The problem from a design perspective
- H4: Fix
- H4: Test Case
- H4: Numbers

## Images et graphiques reperes

- featured / image: [InnoDB ALTER TABLE ADD INDEX and INSERT performance](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-ALTER-TABLE-ADD-INDEX.jpeg)
- content / image: [InnoDB ALTER TABLE ADD INDEX](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-ALTER-TABLE-ADD-INDEX-300x207.jpeg)

## Auteur source

Satya works with Percona Server Engineering team. He is responsible for Percona Server for MySQL, XtraBackup features, bug fixes, etc. He has an overall experience of 18 years in MySQL. He joined Percona in 2018. Before joining Percona, Satya worked with the InnoDB Development team at Oracle for 6 years.
