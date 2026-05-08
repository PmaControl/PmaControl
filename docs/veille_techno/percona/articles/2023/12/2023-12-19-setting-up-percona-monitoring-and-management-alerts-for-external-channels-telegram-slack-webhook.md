---
title: Setting Up Percona Monitoring and Management Alerts for External Channels (Telegram, Slack, WebHook)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setting-up-percona-monitoring-and-management-alerts-for-external-channels-telegram-slack-webhook/
  post_id: 27830
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2023-12-19T14:20:22'
published_at_gmt: '2023-12-19T14:20:22'
modified_at: '2026-05-05T17:26:00'
modified_at_gmt: '2026-05-05T17:26:00'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Insight for DBAs
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- percona-software
tags:
- alerting
- database monitoring
- Monitoring
- Percona Monitoring and Management
tag_slugs:
- alerting
- database-monitoring
- monitoring
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/abstract-technology-3d-render-high-detailed-structure-showing-complexity-data-analysis.jpg
image_count: 27
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setting Up Percona Monitoring and Management Alerts for External Channels (Telegram, Slack, WebHook)

Source: [Percona Blog](https://www.percona.com/blog/setting-up-percona-monitoring-and-management-alerts-for-external-channels-telegram-slack-webhook/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2023-12-19T14:20:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Setting up Percona Monitoring and Management (PMM) alerts for multiple channels can significantly enhance your monitoring strategy. In this blog post, we will talk about the steps to configure alerts for some well-known communication platforms like Telegram, Slack, and WebHook. Please note that I am not covering the basic alerting and configuration setup. For that, … Continued

## Structure detectee

- H2: Setting up PMM alerts for Slack
- H2: Setting up PMM Alerts for Telegram
- H4: Output:
- H2: Setting up PMM Alerts for WebHook
- H4: Response from the URL
- H3: Further references:

## Images et graphiques reperes

- featured / image: [Setting Up Percona Monitoring and Management Alerts for External Channels (Telegram, Slack, WebHook)](https://www.percona.com/wp-content/uploads/2026/03/abstract-technology-3d-render-high-detailed-structure-showing-complexity-data-analysis.jpg)
- content / image: [PMM alerts](https://www.percona.com/wp-content/uploads/2026/03/image2-1-9-1024x585.png)
- content / image: [image5-18-1024x586.png](https://www.percona.com/wp-content/uploads/2026/03/image5-18-1024x586.png)
- content / image: [Configuring PMM alerts for Slack](https://www.percona.com/wp-content/uploads/2026/03/image19-2-1024x436.png)
- content / image: [image11-4-1024x582.png](https://www.percona.com/wp-content/uploads/2026/03/image11-4-1024x582.png)
- content / image: [image7-12-1024x581.png](https://www.percona.com/wp-content/uploads/2026/03/image7-12-1024x581.png)
- content / image: [image3-21-1024x582.png](https://www.percona.com/wp-content/uploads/2026/03/image3-21-1024x582.png)
- content / image: [image13-5-1024x586.png](https://www.percona.com/wp-content/uploads/2026/03/image13-5-1024x586.png)
- content / image: [image16-2-1024x578.png](https://www.percona.com/wp-content/uploads/2026/03/image16-2-1024x578.png)
- content / image: [image23-1024x581.png](https://www.percona.com/wp-content/uploads/2026/03/image23-1024x581.png)
- content / image: [image4-1-6-1024x504.png](https://www.percona.com/wp-content/uploads/2026/03/image4-1-6-1024x504.png)
- content / image: [image1-1-14-1024x582.png](https://www.percona.com/wp-content/uploads/2026/03/image1-1-14-1024x582.png)
- content / image: [image6-14-1024x566.png](https://www.percona.com/wp-content/uploads/2026/03/image6-14-1024x566.png)
- content / image: [image20-1-1024x589.png](https://www.percona.com/wp-content/uploads/2026/03/image20-1-1024x589.png)
- content / image: [image22-1024x582.png](https://www.percona.com/wp-content/uploads/2026/03/image22-1024x582.png)
- content / image: [image18-2-1024x480.png](https://www.percona.com/wp-content/uploads/2026/03/image18-2-1024x480.png)
- content / image: [Configuring PMM Alerts for Telegram](https://www.percona.com/wp-content/uploads/2026/03/image25-1-1-1024x580.png)
- content / image: [image24-1024x589.png](https://www.percona.com/wp-content/uploads/2026/03/image24-1024x589.png)
- content / image: [image26-1-1024x582.png](https://www.percona.com/wp-content/uploads/2026/03/image26-1-1024x582.png)
- content / image: [image14-3-1024x585.png](https://www.percona.com/wp-content/uploads/2026/03/image14-3-1024x585.png)
- content / image: [image17-1-1-1024x582.png](https://www.percona.com/wp-content/uploads/2026/03/image17-1-1-1024x582.png)
- content / image: [image9-4-1024x580.png](https://www.percona.com/wp-content/uploads/2026/03/image9-4-1024x580.png)
- content / image: [image8-6-1024x587.png](https://www.percona.com/wp-content/uploads/2026/03/image8-6-1024x587.png)
- content / image: [image15-1-1024x389.png](https://www.percona.com/wp-content/uploads/2026/03/image15-1-1024x389.png)
- content / image: [image10-1-1-1024x538.png](https://www.percona.com/wp-content/uploads/2026/03/image10-1-1-1024x538.png)
- content / image: [image21-1-1024x853.png](https://www.percona.com/wp-content/uploads/2026/03/image21-1-1024x853.png)
- content / image: [Configuring PMM Alerts for WebHook](https://www.percona.com/wp-content/uploads/2026/03/image12-5-1024x325.png)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
