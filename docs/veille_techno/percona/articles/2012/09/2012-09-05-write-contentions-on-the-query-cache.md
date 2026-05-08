---
title: Write contentions on the query cache
source:
  name: Percona Blog
  url: https://www.percona.com/blog/write-contentions-on-the-query-cache/
  post_id: 3743
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2012-09-05T14:36:40'
published_at_gmt: '2012-09-05T14:36:40'
modified_at: '2026-04-28T21:38:33'
modified_at_gmt: '2026-04-28T21:38:33'
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

# Write contentions on the query cache

Source: [Percona Blog](https://www.percona.com/blog/write-contentions-on-the-query-cache/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2012-09-05T14:36:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While doing a performance audit for a customer a few weeks ago, I tried to improve the response time of their top slow query according to pt-query-digest‘s report. This query was run very frequently and had very unstable performance: during the time data was collected, response time varied from 50µs to 1s. When I ran … Continued

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
