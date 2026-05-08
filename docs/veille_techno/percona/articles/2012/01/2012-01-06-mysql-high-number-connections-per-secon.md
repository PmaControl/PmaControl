---
title: Configuring MySQL For High Number of Connections per Second
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-high-number-connections-per-secon/
  post_id: 3260
source_author:
  name: Ryan Lowe
  slug: ryanalowe
  url: https://www.percona.com/blog/author/ryanalowe/
  website: http://www.percona.com/blog/
published_at: '2012-01-06T15:02:07'
published_at_gmt: '2012-01-06T15:02:07'
modified_at: '2026-03-23T22:11:53'
modified_at_gmt: '2026-03-23T22:11:53'
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

# Configuring MySQL For High Number of Connections per Second

Source: [Percona Blog](https://www.percona.com/blog/mysql-high-number-connections-per-secon/)

Auteur source: [Ryan Lowe](https://www.percona.com/blog/author/ryanalowe/)

Publication: 2012-01-06T15:02:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One thing I noticed during the observation was that there were roughly 2,000 new connections to MySQL per second during peak times. This is a high number by any account. When a new connection to MySQL is made, it can go into the back_log, which effectively serves as a queue for new connections on operating … Continued

## Auteur source

Ryan was a principal consultant and team manager at Percona until July 2014. He has experience with many database technologies in industries such as health care, telecommunications, and social networking.
