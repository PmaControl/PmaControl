---
title: Intro to Grafana Plugin Development
source:
  name: Percona Blog
  url: https://www.percona.com/blog/intro-to-grafana-plugin-development/
  post_id: 22010
source_author:
  name: Roman Misyurin
  slug: roman-misyurin
  url: https://www.percona.com/blog/author/roman-misyurin/
  website: ''
published_at: '2020-06-02T15:10:06'
published_at_gmt: '2020-06-02T15:10:06'
modified_at: '2026-03-23T15:10:54'
modified_at_gmt: '2026-03-23T15:10:54'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
- Percona Toolkit
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Insight for Developers
- Monitoring
- Open Source
- Percona Software
category_slugs:
- insight-for-developers
- monitoring
- open-source
- percona-software
tags:
- Grafana
- Monitoring
- mysql-and-variants
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- grafana
- monitoring
- mysql-and-variants
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/grafana-plugin-development.png
image_count: 6
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Intro to Grafana Plugin Development

Source: [Percona Blog](https://www.percona.com/blog/intro-to-grafana-plugin-development/)

Auteur source: [Roman Misyurin](https://www.percona.com/blog/author/roman-misyurin/)

Publication: 2020-06-02T15:10:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The frontend part of Percona Monitoring and Management (PMM) is a set of extensions for Grafana, and the Grafana documentation provides a basic understanding of how things work. But after I studied it, it was still difficult to understand how to approach development in practice. The purpose of this series of articles is to summarize … Continued

## Structure detectee

- H3: What is Grafana?
- H3: Why Should I Use Grafana?
- H3: What Are the Downsides?
- H2: The Main Elements of Grafana
- H4: Panel
- H4: Dashboard
- H4: Datasource
- H4: App
- H3: What are Panel Plugins in Terms of Development?
- H3: What We Use
- H3: What Will I Need to Start Development?

## Images et graphiques reperes

- featured / image: [Intro to Grafana Plugin Development](https://www.percona.com/wp-content/uploads/2026/03/grafana-plugin-development.png)
- content / image: [grafana plugin development](https://www.percona.com/wp-content/uploads/2026/03/grafana-plugin-development-300x157.png)
- content / image: [Grafana Plugin Development](https://www.percona.com/wp-content/uploads/2026/03/panel-plugin.png)
- content / graph_or_chart: [dashboard.png](https://www.percona.com/wp-content/uploads/2026/03/dashboard.png)
- content / image: [datasource-1.png](https://www.percona.com/wp-content/uploads/2026/03/datasource-1.png)
- content / image: [app.png](https://www.percona.com/wp-content/uploads/2026/03/app.png)

## Auteur source

Roman is a frontend engineer with a striving for making handy tools for other engineers. His interests are mostly around the quickening of the frontend development process, creating simple and useful interfaces, and mentoring JS developers.
