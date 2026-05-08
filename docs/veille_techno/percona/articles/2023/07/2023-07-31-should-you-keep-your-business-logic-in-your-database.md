---
title: Should You Keep Your Business Logic In Your Database?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/should-you-keep-your-business-logic-in-your-database/
  post_id: 27307
source_author:
  name: David Stokes
  slug: david-stokes
  url: https://www.percona.com/blog/author/david-stokes/
  website: ''
published_at: '2023-07-31T12:00:27'
published_at_gmt: '2023-07-31T12:00:27'
modified_at: '2026-03-26T20:29:23'
modified_at_gmt: '2026-03-26T20:29:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Database Trends
- Insight for DBAs
- MySQL
category_slugs:
- database-trends
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/business-logic.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Should You Keep Your Business Logic In Your Database?

Source: [Percona Blog](https://www.percona.com/blog/should-you-keep-your-business-logic-in-your-database/)

Auteur source: [David Stokes](https://www.percona.com/blog/author/david-stokes/)

Publication: 2023-07-31T12:00:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Open source database architects usually do not implement business logic in their databases. This is in stark contrast to many commercial databases where this is a common practice. In the first case, all the heuristics are kept at the application layer, and the database has little or no effect on the data quality. The second … Continued

## Structure detectee

- H2: ENUMs
- H2: VIEWS
- H2: VIEWS with Data Masking in Percona Server for MySQL
- H2: Check constraints
- H2: Triggers
- H2: Stored procedures
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Should You Keep Your Business Logic In Your Database?](https://www.percona.com/wp-content/uploads/2026/03/business-logic.png)

## Auteur source

David Stokes is a Technology Evangelist for Percona Corporation, is the author of MySQL & JSON - A Practical Programming Guide, and resides in Texas.
