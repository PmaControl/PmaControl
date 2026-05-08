---
title: Measuring the amount of writes in InnoDB redo logs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/measuring-the-amount-of-writes-in-innodb-redo-logs/
  post_id: 3817
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2012-10-08T19:10:52'
published_at_gmt: '2012-10-08T19:10:52'
modified_at: '2026-03-23T22:28:15'
modified_at_gmt: '2026-03-23T22:28:15'
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

# Measuring the amount of writes in InnoDB redo logs

Source: [Percona Blog](https://www.percona.com/blog/measuring-the-amount-of-writes-in-innodb-redo-logs/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2012-10-08T19:10:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Choosing a good InnoDB log file size is key to InnoDB write performance. This can be done by measuring the amount of writes in the redo logs. You can find a detailed explanation in this post. To sum up, here are the main points: The redo logs should be large enough to store at most … Continued

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
