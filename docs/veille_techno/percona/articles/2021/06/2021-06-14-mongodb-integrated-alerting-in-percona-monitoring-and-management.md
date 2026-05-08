---
title: MongoDB Integrated Alerting in Percona Monitoring and Management
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-integrated-alerting-in-percona-monitoring-and-management/
  post_id: 24448
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2021-06-14T15:42:49'
published_at_gmt: '2021-06-14T15:42:49'
modified_at: '2026-03-26T20:15:18'
modified_at_gmt: '2026-03-26T20:15:18'
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
- tag:pmm:2167
categories:
- MongoDB
- Monitoring
- Percona Software
category_slugs:
- mongodb
- monitoring
- percona-software
tags:
- alerting
- MongoDB
- Percona Software
- PMM
tag_slugs:
- alerting
- mongodb
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Integrated-Alerting.png
image_count: 13
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Integrated Alerting in Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/mongodb-integrated-alerting-in-percona-monitoring-and-management/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2021-06-14T15:42:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Monitoring and Management (PMM) recently introduced the Integrated Alerting feature as a technical preview. This was a very eagerly awaited feature, as PMM doesn’t need to integrate with an external alerting system anymore. Recently we blogged about the release of this feature. PMM includes some built-in templates, and in this post, I am going … Continued

## Structure detectee

- H2: Enable Integrated Alerting
- H2: Configuring Alert Destinations
- H2: Creating a Custom Alert Template
- H2: Creating MongoDB Alerts
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MongoDB Integrated Alerting in Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Integrated-Alerting.png)
- content / image: [MongoDB Integrated Alerting](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Integrated-Alerting-300x168.png)
- content / image: [Screen-Shot-2021-06-10-at-9.48.28-AM-1024x549.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-9.48.28-AM-1024x549.png)
- content / image: [Screen-Shot-2021-06-10-at-9.49.51-AM-1024x706.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-9.49.51-AM-1024x706.png)
- content / image: [Screen-Shot-2021-06-11-at-9.15.51-AM-1024x707.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-11-at-9.15.51-AM-1024x707.png)
- content / image: [Screen-Shot-2021-06-10-at-9.50.14-AM-1024x370.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-9.50.14-AM-1024x370.png)
- content / image: [Screen-Shot-2021-06-10-at-9.52.46-AM-1024x565.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-9.52.46-AM-1024x565.png)
- content / image: [Screen-Shot-2021-06-10-at-10.07.42-AM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-10.07.42-AM.png)
- content / image: [Screen-Shot-2021-06-10-at-10.31.20-AM-1024x320.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-10.31.20-AM-1024x320.png)
- content / image: [Screen-Shot-2021-06-10-at-10.13.10-AM-1024x542.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-10.13.10-AM-1024x542.png)
- content / image: [Screen-Shot-2021-06-10-at-10.16.45-AM-1024x713.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-10.16.45-AM-1024x713.png)
- content / image: [Screen-Shot-2021-06-10-at-11.33.37-AM-1024x401.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-10-at-11.33.37-AM-1024x401.png)
- content / image: [Screen-Shot-2021-06-11-at-9.13.24-AM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-06-11-at-9.13.24-AM.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
