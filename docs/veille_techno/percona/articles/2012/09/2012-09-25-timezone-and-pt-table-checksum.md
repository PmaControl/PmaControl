---
title: Timezone and pt-table-checksum
source:
  name: Percona Blog
  url: https://www.percona.com/blog/timezone-and-pt-table-checksum/
  post_id: 3810
source_author:
  name: Mike Benshoof
  slug: mbenshoof
  url: https://www.percona.com/blog/author/mbenshoof/
  website: ''
published_at: '2012-09-25T18:11:00'
published_at_gmt: '2012-09-25T18:11:00'
modified_at: '2026-05-04T21:51:41'
modified_at_gmt: '2026-05-04T21:51:41'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Timezone and pt-table-checksum

Source: [Percona Blog](https://www.percona.com/blog/timezone-and-pt-table-checksum/)

Auteur source: [Mike Benshoof](https://www.percona.com/blog/author/mbenshoof/)

Publication: 2012-09-25T18:11:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently worked through an issue with a client trying to detect data drift across some servers that were located in different timezones. Unfortunately, several of the tables had timestamp fields and were set to a default value of CURRENT_TIMESTAMP. From the manual, here is how MySQL handles timezone locality with timestamp fields: Values for TIMESTAMP columns are … Continued

## Auteur source

Michael joined Percona in 2012 as a US based consultant and is currently a Technical Account Manager. Prior to joining Percona, Michael spent several years in a DevOps role maintaining a SaaS application specializing in social networking. His experiences include application development and scaling, systems administration, along with database administration and design. He enjoys designing extensible and flexible solutions to problems. When not working, he enjoys time outdoors, grilling, most sports, and spending time with the family.
