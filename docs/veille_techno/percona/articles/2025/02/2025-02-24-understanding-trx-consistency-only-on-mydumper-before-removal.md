---
title: Understanding trx-consistency-only on MyDumper Before Removal
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-trx-consistency-only-on-mydumper-before-removal/
  post_id: 29300
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2025-02-24T14:11:45'
published_at_gmt: '2025-02-24T14:11:45'
modified_at: '2026-03-26T20:25:44'
modified_at_gmt: '2026-03-26T20:25:44'
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
- mydumper
- MySQL
- mysql-and-variants
tag_slugs:
- mydumper
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/trx-consistency-only-on-MyDumper.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding trx-consistency-only on MyDumper Before Removal

Source: [Percona Blog](https://www.percona.com/blog/understanding-trx-consistency-only-on-mydumper-before-removal/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2025-02-24T14:11:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have been working on MyDumper for over three years now, and I usually don’t use the tax-consistency-only feature during backups because it wasn’t an option I quite understood. So, when reviewing another issue, I stepped into a curious scenario, and I finally got it and decided to share with you what I learned and when it should … Continued

## Structure detectee

- H2: What is trx-consistency-only on MyDumper?
- H2: Why do we have trx-consistency-only then?
- H2: Is this the best way to coordinate the threads?
- H3: Conclusions

## Images et graphiques reperes

- featured / image: [Understanding trx-consistency-only on MyDumper Before Removal](https://www.percona.com/wp-content/uploads/2026/03/trx-consistency-only-on-MyDumper.jpg)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
