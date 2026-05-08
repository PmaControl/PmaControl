---
title: DBD::mysql 4.014 breaks pt-table-checksum 2.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/dbd-mysql-4-014-breaks-pt-table-checksum-2-0/
  post_id: 3366
source_author:
  name: Daniel Nichter
  slug: daniel
  url: https://www.percona.com/blog/author/daniel/
  website: http://www.percona.com
published_at: '2012-02-21T20:28:41'
published_at_gmt: '2012-02-21T20:28:41'
modified_at: '2026-03-23T22:15:31'
modified_at_gmt: '2026-03-23T22:15:31'
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
tags:
- DBD-mysql
- pt-table-checksum
tag_slugs:
- dbd-mysql
- pt-table-checksum
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# DBD::mysql 4.014 breaks pt-table-checksum 2.0

Source: [Percona Blog](https://www.percona.com/blog/dbd-mysql-4-014-breaks-pt-table-checksum-2-0/)

Auteur source: [Daniel Nichter](https://www.percona.com/blog/author/daniel/)

Publication: 2012-02-21T20:28:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

DBD::mysql 4.014 breaks pt-table-checksum 2.0. The cause is unknown, but the effect is a lot of errors like: DBD::mysql::st execute failed: called with 2 bind variables when 6 are needed [for Statement “…” with ParamValues: …] at ./pt-table-checksum line 7216. The fix is simple: upgrade (or even downgrade) DBD::mysql to any version except 4.014. To … Continued
