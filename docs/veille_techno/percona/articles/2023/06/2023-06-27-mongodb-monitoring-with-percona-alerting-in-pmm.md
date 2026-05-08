---
title: MongoDB Monitoring With Percona Alerting in PMM
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-monitoring-with-percona-alerting-in-pmm/
  post_id: 27138
source_author:
  name: Michael Okoko
  slug: michael-okoko
  url: https://www.percona.com/blog/author/michael-okoko/
  website: ''
published_at: '2023-06-27T12:21:03'
published_at_gmt: '2023-06-27T12:21:03'
modified_at: '2026-03-26T20:14:22'
modified_at_gmt: '2026-03-26T20:14:22'
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
- MongoDB
- Monitoring
- Percona Software
category_slugs:
- mongodb
- monitoring
- percona-software
tags:
- MongoDB
- Percona Monitoring and Management
tag_slugs:
- mongodb
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Monitoring-1.jpg
image_count: 10
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Monitoring With Percona Alerting in PMM

Source: [Percona Blog](https://www.percona.com/blog/mongodb-monitoring-with-percona-alerting-in-pmm/)

Auteur source: [Michael Okoko](https://www.percona.com/blog/author/michael-okoko/)

Publication: 2023-06-27T12:21:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Alerting was introduced in Percona Monitoring and Management (PMM) 2.31, and it brought a range of custom alerting templates that makes it easier to create Alert rules to monitor your databases. In this article, we will go over how to set up Alerting in PMM running on Docker and receive notifications via emails when … Continued

## Structure detectee

- H2: Configuring PMM with Grafana SMTP
- H2: Adding MongoDB services
- H2: Customizing emails with notification templates
- H2: Alert Rules from Percona Alert Templates
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MongoDB Monitoring With Percona Alerting in PMM](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Monitoring-1.jpg)
- content / image: [PMM screen showing options to add instance of different databases](https://www.percona.com/wp-content/uploads/2026/03/mongodb-add-instance-1024x722.png)
- content / image: [MongoDB instance](https://www.percona.com/wp-content/uploads/2026/03/mongodb-add-instance-details.png)
- content / image: [mongodb-service-in-list-1024x284.png](https://www.percona.com/wp-content/uploads/2026/03/mongodb-service-in-list-1024x284.png)
- content / image: [template-edit-contact-point-1024x290.png](https://www.percona.com/wp-content/uploads/2026/03/template-edit-contact-point-1024x290.png)
- content / image: [test-contact-point-1024x612.png](https://www.percona.com/wp-content/uploads/2026/03/test-contact-point-1024x612.png)
- content / image: [new-alert-template-1024x294.png](https://www.percona.com/wp-content/uploads/2026/03/new-alert-template-1024x294.png)
- content / image: [normal-alert-state-1024x124.png](https://www.percona.com/wp-content/uploads/2026/03/normal-alert-state-1024x124.png)
- content / image: [fired-alert.png](https://www.percona.com/wp-content/uploads/2026/03/fired-alert.png)
- content / image: [email-alert-1.png](https://www.percona.com/wp-content/uploads/2026/03/email-alert-1.png)
