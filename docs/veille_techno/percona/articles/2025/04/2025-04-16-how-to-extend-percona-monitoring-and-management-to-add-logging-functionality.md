---
title: How to Extend Percona Monitoring and Management to Add Logging Functionality
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-extend-percona-monitoring-and-management-to-add-logging-functionality/
  post_id: 34804
source_author:
  name: Alexander Demidoff
  slug: alexander-demidoff
  url: https://www.percona.com/blog/author/alexander-demidoff/
  website: ''
published_at: '2025-04-16T13:10:05'
published_at_gmt: '2025-04-16T13:10:05'
modified_at: '2026-05-05T17:02:59'
modified_at_gmt: '2026-05-05T17:02:59'
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
- tag:pmm:2167
categories:
- Insight for DBAs
- Insight for Developers
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- monitoring
- percona-software
tags:
- Monitoring
- Percona Monitoring and Management
- PMM
tag_slugs:
- monitoring
- percona-monitoring-and-management
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Extend-Percona-Monitoring-and-Management-with-logging-capabilities.jpg
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Extend Percona Monitoring and Management to Add Logging Functionality

Source: [Percona Blog](https://www.percona.com/blog/how-to-extend-percona-monitoring-and-management-to-add-logging-functionality/)

Auteur source: [Alexander Demidoff](https://www.percona.com/blog/author/alexander-demidoff/)

Publication: 2025-04-16T13:10:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Evolution is one of the inherent traits of modern software. Many people reach out to product teams daily, asking to add more functionality to the software products they use and love. This is understandable: there will always be ways to make a product better by adding more features to the users’ delight so they can … Continued

## Structure detectee

- H2: What’s the plan?
- H2: Which PMM distribution should I take and why?
- H2: What logging tool should I choose?
- H2: The tech stack
- H2: Choosing the automation tool
- H2: Preparing an Ansible playbook
- H2: Configuring the tools
- H2: What logs to collect?
- H2: Building the image
- H2: Exploring the user interface
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How to Extend Percona Monitoring and Management to Add Logging Functionality](https://www.percona.com/wp-content/uploads/2026/03/Extend-Percona-Monitoring-and-Management-with-logging-capabilities.jpg)
- content / image: [Logging technology stack in PMM](https://www.percona.com/wp-content/uploads/2026/03/pmm-logging-technology-stack-scaled.png)
- content / image: [victorialogs-explore-nginx-logs-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/victorialogs-explore-nginx-logs-scaled.png)
- content / image: [passing-nginx-logs-through-logfmt-pipe-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/passing-nginx-logs-through-logfmt-pipe-scaled.png)
- content / image: [explore-nginx-logs-with-victorialogs-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/explore-nginx-logs-with-victorialogs-scaled.png)
- content / image: [expand-a-log-line-in-victorialogs-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/expand-a-log-line-in-victorialogs-scaled.png)
- content / image: [filtering-log-attributes-with-victorialogs-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/filtering-log-attributes-with-victorialogs-scaled.png)

## Auteur source

I'm a software engineer and architect with decades of experience in the software industry. In the past, I developed scalable web apps powered by open source databases. Now I'm contributing to the software experiences of the users of our own monitoring and management solution known as PMM.
