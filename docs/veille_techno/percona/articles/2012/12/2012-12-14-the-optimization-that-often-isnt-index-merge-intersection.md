---
title: 'The Optimization That (Often) Isn’t: Index Merge Intersection'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-optimization-that-often-isnt-index-merge-intersection/
  post_id: 6440
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2012-12-14T12:17:32'
published_at_gmt: '2012-12-14T12:17:32'
modified_at: '2026-05-04T21:53:02'
modified_at_gmt: '2026-05-04T21:53:02'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Optimization That (Often) Isn’t: Index Merge Intersection

Source: [Percona Blog](https://www.percona.com/blog/the-optimization-that-often-isnt-index-merge-intersection/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2012-12-14T12:17:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Prior to version 5.0, MySQL could only use one index per table in a given query without any exceptions; folks that didn’t understand this limitation would often have tables with lots of single-column indexes on columns which commonly appeared in their WHERE clauses, and they’d wonder why the EXPLAIN plan for a given SELECT would … Continued

## Structure detectee

- H2: Index Merge Intersection

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
