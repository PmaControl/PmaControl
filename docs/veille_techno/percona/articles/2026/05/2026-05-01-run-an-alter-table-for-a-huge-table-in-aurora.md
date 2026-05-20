---
title: Run an ALTER TABLE for a huge table in Aurora
source:
  name: Percona Blog
  url: https://www.percona.com/blog/run-an-alter-table-for-a-huge-table-in-aurora/
  post_id: 43371
source_author:
  name: Eduardo Krieg
  slug: eduardo-krieg
  url: https://www.percona.com/blog/author/eduardo-krieg/
  website: ''
published_at: '2026-05-01T01:55:40'
published_at_gmt: '2026-05-01T01:55:40'
modified_at: '2026-05-01T13:10:52'
modified_at_gmt: '2026-05-01T13:10:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
categories:
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
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

# Run an ALTER TABLE for a huge table in Aurora

Source: [Percona Blog](https://www.percona.com/blog/run-an-alter-table-for-a-huge-table-in-aurora/)

Auteur source: [Eduardo Krieg](https://www.percona.com/blog/author/eduardo-krieg/)

Publication: 2026-05-01T01:55:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, we received an alert for one of our Managed Services customers indicating that the auto_increment value for the table was 80% of its maximum capacity. The column was INT UNSIGNED, which has a limit of 4,294,967,295. At 80%, we have enough time to change it to BIGINT.…. Right? Let’s see. So we used pt-online-schema-change … Continued

## Structure detectee

- H2: Conclusion:

## Auteur source

Eduardo started his career as a Web Developer, where he started interacting with MySQL, as he interacted more with databases, he started focusing on them until he became a full-time DBA. He has worked with other Open Source databases such as PostgreSQL and MongoDB. He joined Percona as a MySQL DBA in the Managed Services team in 2020, serving multiple clients from around the world.
