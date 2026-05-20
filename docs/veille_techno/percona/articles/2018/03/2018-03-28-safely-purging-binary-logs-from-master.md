---
title: Purging Binary Logs From Master
source:
  name: Percona Blog
  url: https://www.percona.com/blog/safely-purging-binary-logs-from-master/
  post_id: 18208
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2018-03-28T16:36:54'
published_at_gmt: '2018-03-28T16:36:54'
modified_at: '2026-05-05T20:00:49'
modified_at_gmt: '2026-05-05T20:00:49'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
categories:
- Insight for DBAs
- Monitoring
- MySQL
category_slugs:
- insight-for-dbas
- monitoring
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Purging-Bin-Logs-e1522252960868.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Purging Binary Logs From Master

Source: [Percona Blog](https://www.percona.com/blog/safely-purging-binary-logs-from-master/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2018-03-28T16:36:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss some of the options available when purging binary logs. We’ll look at how to safely purge them when you have slaves in your topology and want to avoid deleting any binary log that still needs to be applied. Safely Purging Binary Logs From Master We generally want to ensure … Continued

## Structure detectee

- H2: Safely Purging Binary Logs From Master
- H4: Caveats
- H4: Summary

## Images et graphiques reperes

- featured / image: [Purging Binary Logs From Master](https://www.percona.com/wp-content/uploads/2026/03/Purging-Bin-Logs-e1522252960868.jpg)
- content / image: [Purging Binary Logs](https://www.percona.com/wp-content/uploads/2026/03/Purging-Bin-Logs-300x230.jpg)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
