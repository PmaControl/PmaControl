---
title: On Character Sets and Disappearing Tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/on-character-sets-and-disappearing-tables/
  post_id: 3648
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2012-06-21T17:07:50'
published_at_gmt: '2012-06-21T17:07:50'
modified_at: '2026-05-04T21:46:57'
modified_at_gmt: '2026-05-04T21:46:57'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# On Character Sets and Disappearing Tables

Source: [Percona Blog](https://www.percona.com/blog/on-character-sets-and-disappearing-tables/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2012-06-21T17:07:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The MySQL manual tells us that regardless of whether or not we use “SET FOREIGN_KEY_CHECKS=0” before making schema changes, InnoDB will not allow a column referenced by a foreign key constraint to be modified in such a way that the foreign key will reference a column with a mismatched data type. For instance, if we … Continued

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
