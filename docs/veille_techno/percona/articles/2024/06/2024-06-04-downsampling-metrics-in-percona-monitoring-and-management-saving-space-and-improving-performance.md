---
title: 'Downsampling Metrics in Percona Monitoring and Management: Saving Space and Improving Performance'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/downsampling-metrics-in-percona-monitoring-and-management-saving-space-and-improving-performance/
  post_id: 28570
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2024-06-04T16:07:01'
published_at_gmt: '2024-06-04T16:07:01'
modified_at: '2026-05-05T16:47:49'
modified_at_gmt: '2026-05-05T16:47:49'
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
- Insight for DBAs
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- percona-software
tags:
- Monitoring
- PMM
tag_slugs:
- monitoring
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Downsampling-Metrics-in-Percona-Monitoring-and-Management.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Downsampling Metrics in Percona Monitoring and Management: Saving Space and Improving Performance

Source: [Percona Blog](https://www.percona.com/blog/downsampling-metrics-in-percona-monitoring-and-management-saving-space-and-improving-performance/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2024-06-04T16:07:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Downsampling is the process by which we can selectively prune (discard, summarize, or recalculate) data from a series of samples in order to decrease how much storage is consumed. This has the downside of reducing the accuracy of the data, but has the great benefit of allowing us to store data from a wider sampling … Continued

## Structure detectee

- H2: PMM and VictoriaMetrics
- H2: Enabling downsampling in PMM
- H2: Increasing metric retention days
- H2: Error scenarios
- H3: Multiples of previous intervals
- H3: Bigger than next interval
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Downsampling Metrics in Percona Monitoring and Management: Saving Space and Improving Performance](https://www.percona.com/wp-content/uploads/2026/03/Downsampling-Metrics-in-Percona-Monitoring-and-Management.jpg)
- content / image: [PMM Settings](https://www.percona.com/wp-content/uploads/2026/03/retention-1024x601.png)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
