---
title: Using Prometheus to Check for auto_increment Value Exhaustion
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-prometheus-to-check-for-auto_increment-value-exhaustion/
  post_id: 17607
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2017-11-08T19:48:41'
published_at_gmt: '2017-11-08T19:48:41'
modified_at: '2026-05-05T20:18:04'
modified_at_gmt: '2026-05-05T20:18:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- Percona Software
category_slugs:
- insight-for-dbas
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/auto_increment-Value-Exhaustion-small.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Prometheus to Check for auto_increment Value Exhaustion

Source: [Percona Blog](https://www.percona.com/blog/using-prometheus-to-check-for-auto_increment-value-exhaustion/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2017-11-08T19:48:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how to use Prometheus to check for auto_increment value exhaustion. One of the proactive tasks DBAs perform is checking if a field defined as auto_increment is about to reach the maximum allowed value of the int definition. For example, if a field is defined as smallint unsigned and … Continued

## Structure detectee

- H3: Prometheus and the mysqld exporter
- H3: Tablestats
- H3: Prometheus query
- H3: GUI
- H3: API
- H3: PMM’s MySQL Table Statistics dashboard
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Using Prometheus to Check for auto_increment Value Exhaustion](https://www.percona.com/wp-content/uploads/2026/03/auto_increment-Value-Exhaustion-small.png)
- content / image: [Prometheus GUI](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-10-30-at-15.16.44-scaled.png)
  Caption: Prometheus GUI
- content / image: [Screen-Shot-2017-10-31-at-13.11.21-1024x424.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2017-10-31-at-13.11.21-1024x424.png)
- content / image: [image-3-1024x738.png](https://www.percona.com/wp-content/uploads/2026/03/image-3-1024x738.png)

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
