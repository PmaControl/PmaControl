---
title: Troubleshooting Percona Monitoring and Management (PMM) Metrics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/troubleshooting-percona-monitoring-and-management-pmm-metrics/
  post_id: 17913
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2018-01-17T16:28:51'
published_at_gmt: '2018-01-17T16:28:51'
modified_at: '2026-05-05T18:59:22'
modified_at_gmt: '2026-05-05T18:59:22'
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
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- percona-software
tags:
- Metrics
- Percona Monitoring and Management
- PMM
- troubleshooting
- Troubleshooting Percona Monitoring and Management Metrics
tag_slugs:
- metrics
- percona-monitoring-and-management
- pmm
- troubleshooting
- troubleshooting-percona-monitoring-and-management-metrics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-small.png
image_count: 10
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Troubleshooting Percona Monitoring and Management (PMM) Metrics

Source: [Percona Blog](https://www.percona.com/blog/troubleshooting-percona-monitoring-and-management-pmm-metrics/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2018-01-17T16:28:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll look at some helpful tips on troubleshooting Percona Monitoring and Management metrics. With any luck, Percona Monitoring and Management (PMM) works for you out of the box. Sometimes, however, things go awry and you see empty or broken graphs instead of dashboards full of insights. Before we go through troubleshooting … Continued

## Images et graphiques reperes

- featured / image: [Troubleshooting Percona Monitoring and Management (PMM) Metrics](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-small.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 1](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-1-1024x419.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 2](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-2.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 3](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-3.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 4](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-4-1024x545.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 5](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-5-1024x313.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 6](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-6-1024x580.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 7](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-7-1024x584.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 8](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-8-1024x574.png)
- content / image: [Troubleshooting Percona Monitoring and Management Metrics 9](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Percona-Monitoring-and-Management-Metrics-9-1024x477.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
