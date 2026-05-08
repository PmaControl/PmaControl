---
title: Attaching a Percona Monitoring and Management Graph Image Along with an Alerting Notification
source:
  name: Percona Blog
  url: https://www.percona.com/blog/attaching-a-percona-monitoring-and-management-graph-image-along-with-an-alerting-notification/
  post_id: 29190
source_author:
  name: Yunus Shaikh
  slug: yunus-shaikh
  url: https://www.percona.com/blog/author/yunus-shaikh/
  website: ''
published_at: '2025-01-08T14:38:24'
published_at_gmt: '2025-01-08T14:38:24'
modified_at: '2026-03-26T20:25:47'
modified_at_gmt: '2026-03-26T20:25:47'
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
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Insight for DBAs
- Monitoring
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- mysql
- percona-software
tags:
- alerting
- Grafana
- grafana-image-renderer
- Monitoring
- MySQL
- Percona Monitoring and Management
- PMM ALERTING
tag_slugs:
- alerting
- grafana
- grafana-image-renderer
- monitoring
- mysql
- percona-monitoring-and-management
- pmm-alerting
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Attaching-a-Percona-Monitoring-and-Management-Graph-Image-Along-with-an-Alerting-Notification.jpg
image_count: 4
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Attaching a Percona Monitoring and Management Graph Image Along with an Alerting Notification

Source: [Percona Blog](https://www.percona.com/blog/attaching-a-percona-monitoring-and-management-graph-image-along-with-an-alerting-notification/)

Auteur source: [Yunus Shaikh](https://www.percona.com/blog/author/yunus-shaikh/)

Publication: 2025-01-08T14:38:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This article will be helpful if you use the Percona Monitoring and Management (PMM) instance and alert notifications, as it is nice to capture the image of the graph when you receive the alert. We will see how to capture and attach the image of the graph when receiving the alert notification (email, telegram, Slack, … Continued

## Structure detectee

- H2: Existing setup
- H2: How do we capture images in the alert notifications?
- H3: Setting up the Grafana image renderer remote plugin container
- H3: Integrating PMM server with grafana-image-renderer container
- H3: Summary

## Images et graphiques reperes

- featured / graph_or_chart: [Attaching a Percona Monitoring and Management Graph Image Along with an Alerting Notification](https://www.percona.com/wp-content/uploads/2026/03/Attaching-a-Percona-Monitoring-and-Management-Graph-Image-Along-with-an-Alerting-Notification.jpg)
- content / image: [Telegram_alert_without_image-924x1024.png](https://www.percona.com/wp-content/uploads/2026/03/Telegram_alert_without_image-924x1024.png)
- content / image: [Fixing-Data-Slowdowns.png](https://www.percona.com/wp-content/uploads/2026/03/Fixing-Data-Slowdowns.png)
- content / image: [Telegram_alert_screenshot-650x1024.png](https://www.percona.com/wp-content/uploads/2026/03/Telegram_alert_screenshot-650x1024.png)

## Auteur source

Yunus have joined Percona since 2018 as a Database Administrator. He also has spent working as System administrator earlier in his career having good experience in open source tools.
