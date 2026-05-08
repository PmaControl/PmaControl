---
title: Understanding Linux IOWait
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-linux-iowait/
  post_id: 26987
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2023-05-10T12:13:33'
published_at_gmt: '2023-05-10T12:13:33'
modified_at: '2026-05-05T16:45:52'
modified_at_gmt: '2026-05-05T16:45:52'
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
- IOwait
- Linux
- Monitoring
- Percona Monitoring and Management
tag_slugs:
- iowait
- linux
- monitoring
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/IO-Wait-.jpg
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding Linux IOWait

Source: [Percona Blog](https://www.percona.com/blog/understanding-linux-iowait/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2023-05-10T12:13:33

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have seen many Linux Performance engineers looking at the “IOWait” portion of CPU usage as something to indicate whenever the system is I/O-bound. In this blog post, I will explain why this approach is unreliable and what better indicators you can use. Let’s start by running a little experiment – generating heavy I/O usage … Continued

## Images et graphiques reperes

- featured / image: [Understanding Linux IOWait](https://www.percona.com/wp-content/uploads/2026/03/IO-Wait-.jpg)
- content / image: [CPU Usage in Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/image2-1-7-1024x364.png)
- content / image: [heavy CPU usage](https://www.percona.com/wp-content/uploads/2026/03/image4-1-5-1024x374.png)
- content / image: [four core VM CPU usage](https://www.percona.com/wp-content/uploads/2026/03/image5-1-3-1024x374.png)
- content / image: [CPU intensive load will mask IOWait](https://www.percona.com/wp-content/uploads/2026/03/image1-1-12-1024x373.png)
- content / image: [image3-19-1024x338.png](https://www.percona.com/wp-content/uploads/2026/03/image3-19-1024x338.png)
- content / image: [image6-12-1024x320.png](https://www.percona.com/wp-content/uploads/2026/03/image6-12-1024x320.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
