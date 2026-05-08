---
title: 'Data inconsistencies on MySQL replicas: Beyond pt-table-checksum'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/data-inconsistencies-on-mysql-replicas-beyond-pt-table-checksum/
  post_id: 8687
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-11-04T08:00:27'
published_at_gmt: '2014-11-04T08:00:27'
modified_at: '2026-05-04T22:28:29'
modified_at_gmt: '2026-05-04T22:28:29'
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
- tag:percona-toolkit:378
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- Data inconsistencies
- MySQL replicas
- Percona Toolkit
- Primary
- pt-table-checksum
- pt-table-sync
- Stephane Combaudon
tag_slugs:
- data-inconsistencies
- mysql-replicas
- percona-toolkit
- primary
- pt-table-checksum
- pt-table-sync
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Data inconsistencies on MySQL replicas: Beyond pt-table-checksum

Source: [Percona Blog](https://www.percona.com/blog/data-inconsistencies-on-mysql-replicas-beyond-pt-table-checksum/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-11-04T08:00:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Toolkit’s pt-table-checksum is a great tool to find data inconsistencies between a MySQL master and its replicas. However it is sometimes not enough to know that there are inconsistencies and let pt-table-sync fix the issue: you may want to know which exact rows are different to identify the statements that created the inconsistency. This … Continued

## Structure detectee

- H2: The issue
- H2: The solution
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
