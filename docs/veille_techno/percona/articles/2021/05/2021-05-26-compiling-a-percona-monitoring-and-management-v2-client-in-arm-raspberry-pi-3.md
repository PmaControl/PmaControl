---
title: 'Compiling a Percona Monitoring and Management v2 Client in ARM: Raspberry Pi 3 Reprise'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/compiling-a-percona-monitoring-and-management-v2-client-in-arm-raspberry-pi-3/
  post_id: 24398
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2021-05-26T15:20:55'
published_at_gmt: '2021-05-26T15:20:55'
modified_at: '2026-04-27T22:27:57'
modified_at_gmt: '2026-04-27T22:27:57'
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
categories:
- Cloud
- Monitoring
- Percona Software
category_slugs:
- cloud
- monitoring
- percona-software
tags:
- cloud
- Monitoring
- Percona Software
- Raspberry Pi
tag_slugs:
- cloud
- monitoring
- percona-software
- raspberry-pi
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-Client-Raspberry-Pi-3.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Compiling a Percona Monitoring and Management v2 Client in ARM: Raspberry Pi 3 Reprise

Source: [Percona Blog](https://www.percona.com/blog/compiling-a-percona-monitoring-and-management-v2-client-in-arm-raspberry-pi-3/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2021-05-26T15:20:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this follow-up blog to Compiling a Percona Monitoring and Management v2 Client in ARM Architecture, we will show what changes are needed to get the latest versions of PMM working on ARM architecture. In this case, we will do it using a Raspberry Pi 3, instead of the AWS EC2 ARM node. With these … Continued

## Structure detectee

- H2: Installing Dependencies
- H2: Compiling
- H2: Moving the Files to Their Final Destination
- H2: Starting the PMM Client
- H2: OS and Disk Usage
- H2: Extra Tools
- H2: We Come in Peace… We Mean you no Harm
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Compiling a Percona Monitoring and Management v2 Client in ARM: Raspberry Pi 3 Reprise](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-Client-Raspberry-Pi-3.png)
- content / image: [Percona Monitoring and Management Client Raspberry Pi 3](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-Client-Raspberry-Pi-3-300x157.png)
- content / image: [nodes-overview-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/nodes-overview-scaled.png)
- content / image: [pt-summary-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/pt-summary-scaled.png)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
